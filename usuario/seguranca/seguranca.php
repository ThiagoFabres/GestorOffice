<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/cargo.php';
require_once __DIR__ . '/../../db/entities/seguranca/panico.php';
require_once __DIR__ . '/../../db/entities/seguranca/ponto.php';
require_once __DIR__ . '/../../db/entities/seguranca/ronda.php';
require_once __DIR__ . '/../../db/entities/seguranca/turno.php';
require_once __DIR__ . '/../../db/entities/seguranca/ocorrencia.php';
require_once __DIR__ . '/../../db/entities/controle.php';
require_once __DIR__ . '/../../db/entities/controle02.php';

session_start();

$empresa_usuario_obj = Empresa::read($_SESSION['usuario']->id_empresa)[0];
$nomeEmpresa = $empresa_usuario_obj->nom_fant;

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']->cargo != 3 ||
    $_SESSION['usuario']->permissao_seguranca != 1 ||
    $empresa_usuario_obj->permissao_seguranca != 1
) {
    header('Location: /');
    exit();
}

$erro = filter_input(INPUT_GET, 'erro');
$lateral_seguranca = true;
$lateral_target = 'ocorrencias';

$filtro_hora_inicio = filter_input(INPUT_GET, 'filtro_hora_inicio');
$filtro_hora_final = filter_input(INPUT_GET, 'filtro_hora_final');
$filtro_seguranca = filter_input(INPUT_GET, 'filtro_seguranca');

$segurancas = Usuario::read(
    id: $filtro_seguranca,
    idempresa: $empresa_usuario_obj->id,
    cargo: 4
);

$turnos = [];
$panicos = [];
$rondas = [];
$pontos = [];
$ocorrencias = [];
$controlesTurno = [];

$controles = Controle::read($empresa_usuario_obj->id);

$timezoneLocal = new DateTimeZone('America/Sao_Paulo');

$timestampHorarioLocal = static function ($valor) use ($timezoneLocal): ?int {
    if (!is_string($valor) || trim($valor) === '') {
        return null;
    }

    try {
        return (new DateTimeImmutable($valor, $timezoneLocal))->getTimestamp();
    } catch (Throwable $e) {
        return null;
    }
};

$normalizarHorarioPonto = static function ($valor) use ($timezoneLocal): ?string {
    if (empty($valor) || strtotime((string) $valor) === false) {
        return null;
    }

    return (new DateTime((string) $valor, new DateTimeZone('UTC')))
        ->setTimezone($timezoneLocal)
        ->format('Y-m-d H:i:s');
};

$pontoDentroDoPrazo = static function ($ponto, array $controles) use ($timezoneLocal): bool {
    $valorHora = $ponto->hora ?? $ponto->created_at;

    if (empty($valorHora)) {
        return false;
    }

    try {
        $horaPontoUtc = new DateTimeImmutable((string) $valorHora, new DateTimeZone('UTC'));
        $horaPonto = $horaPontoUtc->getTimestamp();
        $dataPonto = $horaPontoUtc->setTimezone($timezoneLocal)->format('Y-m-d');
    } catch (Throwable $e) {
        return false;
    }

    foreach ($controles as $controle) {
        $horaControle = substr((string) $controle->hora, 0, 8);
        try {
            $inicio = (new DateTimeImmutable(
                $dataPonto . ' ' . $horaControle,
                $timezoneLocal
            ))->getTimestamp();
        } catch (Throwable $e) {
            continue;
        }

        $fim = $inicio + (max(0, (int) $controle->tolerancia) * 60);

        if ($horaPonto >= $inicio && $horaPonto <= $fim) {
            return true;
        }
    }

    return false;
};

/*
 * O banco grava o tipo como "inicio" / "termino" (sem acento). Normaliza
 * para aceitar também "término" e variações de caixa/espaço.
 */
$normalizarTipo = static function ($tipo): string {
    return strtr(
        mb_strtolower(trim((string) $tipo), 'UTF-8'),
        ['é' => 'e', 'ê' => 'e']
    );
};

$controlesAgendados = array_values(array_filter(
    $controles,
    static fn($controle) => $normalizarTipo($controle->tipo) === 'controle'
));

/*
 * Procura o controle de início/término mais próximo do horário real do turno
 * (até 6h de distância). Se o horário real passou do fim da tolerância,
 * devolve o horário esperado formatado; caso contrário, null.
 */
$esperadoSeAtrasado = static function (
    string $tipo,
    ?string $horaUtc,
    array $controles
) use ($normalizarTipo): ?string {
    if (empty($horaUtc)) {
        return null;
    }

    // Turnos são gravados em UTC; controle02 está em horário local (-3h)
    $realTs = strtotime(
        (new DateTime($horaUtc))->modify('-3 hours')->format('Y-m-d H:i:s')
    );

    if ($realTs === false) {
        return null;
    }

    $melhor = null;
    $menorDiferenca = 6 * 3600;

    foreach ($controles as $controle) {
        if ($normalizarTipo($controle->tipo) !== $tipo) {
            continue;
        }

        $esperadaTs = strtotime((string) $controle->hora_esperada);

        if ($esperadaTs === false) {
            continue;
        }

        $diferenca = abs($realTs - $esperadaTs);

        if ($diferenca <= $menorDiferenca) {
            $menorDiferenca = $diferenca;
            $melhor = [$esperadaTs, max(0, (int) $controle->tolerancia)];
        }
    }

    if ($melhor === null) {
        return null;
    }

    [$esperadaTs, $tolerancia] = $melhor;

    return $realTs > $esperadaTs + ($tolerancia * 60)
        ? date('d/m/Y H:i', $esperadaTs)
        : null;
};

foreach ($segurancas as $i => $seguranca) {
    $turnos[$seguranca->id] = Turno::read(
        id_usuario: $seguranca->id,
        filtro_hora_inicio: $filtro_hora_inicio,
        filtro_hora_final: $filtro_hora_final
    );

    foreach ($turnos[$seguranca->id] as $turno) {
        $inicioTurno = $turno->started_at;
        $fimTurno = $turno->ended_at;

        $panicos[$seguranca->id][$turno->id] = Panico::read(
            id_usuario: $seguranca->id,
            filtro_hora_inicio: $inicioTurno,
            filtro_hora_final: $fimTurno
        );

        $pontos[$seguranca->id][$turno->id] = PontoControle::read(
            id_usuario: $seguranca->id,
            filtro_hora_inicio: $inicioTurno,
            filtro_hora_final: $fimTurno
        );

        $ocorrencias[$seguranca->id][$turno->id] =
            Ocorrencia::read(id_turno: $turno->id)[0] ?? null;

        /*
         * Turnos e pontos são gravados em UTC; Controle02::read desconta 3h
         * para comparar com controle02 (gravado em America/Sao_Paulo).
         * Por isso o limite do turno em andamento precisa ser o "agora"
         * em UTC.
         */
        $fimJanelaTurno = $fimTurno ??
            (new DateTime('now', new DateTimeZone('UTC')))
                ->format('Y-m-d H:i:s');

        $controlesTurno[$seguranca->id][$turno->id] = Controle02::read(
            id_usuario: $seguranca->id,
            hora_inicio: $inicioTurno,
            hora_fim: $fimJanelaTurno
        );

        $rondas[$seguranca->id][$turno->id] = Ronda::read(
            id_usuario: $seguranca->id,
            hora_inicio: $inicioTurno,
            hora_fim: $fimTurno
        );
    }
}

/*
 * "Não Executados": início/término são cobrados por EMPRESA. O cron grava o
 * controle02 no nome de um usuário de referência (o vigia do último turno ou,
 * se a empresa nunca teve turno, o usuário ativo mais recente), que pode não
 * ser um dos seguranças listados. Por isso a leitura é feita pela empresa,
 * e não segurança por segurança.
 *
 * Mantém somente controles "inicio"/"término" que ainda não foram
 * respondidos e cuja tolerância já terminou (hora_esperada é horário local).
 */
$fusoLocal = new DateTimeZone('America/Sao_Paulo');
$agoraLocal = new DateTime('now', $fusoLocal);


/*
 * Filtro de data dos Controle02 atrasados.
 *
 * O filtro da tela trabalha com datas locais:
 * Data Inicial -> 00:00:00
 * Data Final   -> 23:59:59
 */
$inicioControleFiltro = !empty($filtro_hora_inicio)
    ? $filtro_hora_inicio . ' 00:00:00'
    : null;

$fimControleFiltro = !empty($filtro_hora_final)
    ? $filtro_hora_final . ' 23:59:59'
    : null;

/*
 * Busca os Controle02 da empresa respeitando o período informado.
 * Não utiliza $inicioTurno/$fimTurno, pois essas variáveis pertencem
 * ao processamento individual dos turnos.
 */
$controlesPendentes = Controle02::read(
    id_empresa: $empresa_usuario_obj->id,
    hora_inicio: $inicioControleFiltro,
    hora_fim: $fimControleFiltro
);

/*
 * Cópia (sem filtro de respondido/tolerância) usada para descobrir o horário
 * esperado de início/término de cada turno.
 */
$controlesInicioFim = array_values(array_filter(
    $controlesPendentes,
    static fn($c) => in_array(
        $normalizarTipo($c->tipo),
        ['inicio', 'termino'],
        true
    )
));

/*
 * Mantém somente:
 * - início ou término;
 * - ainda não respondido;
 * - tolerância já encerrada;
 * - dentro do período selecionado.
 */
$controlesPendentes = array_filter(
    $controlesPendentes,
    static function ($controle) use (
        $agoraLocal,
        $fusoLocal,
        $normalizarTipo,
        $filtro_hora_inicio,
        $filtro_hora_final
    ) {
        if (
            !in_array(
                $normalizarTipo($controle->tipo),
                ['inicio', 'termino'],
                true
            ) ||
            $controle->hora_respondida !== null ||
            empty($controle->hora_esperada)
        ) {
            return false;
        }

        try {
            $esperada = new DateTime(
                (string) $controle->hora_esperada,
                $fusoLocal
            );

            /*
             * Garante o filtro mesmo que Controle02::read()
             * não aplique corretamente o intervalo recebido.
             */
            if (!empty($filtro_hora_inicio)) {
                $inicioFiltro = new DateTime(
                    $filtro_hora_inicio . ' 00:00:00',
                    $fusoLocal
                );

                if ($esperada < $inicioFiltro) {
                    return false;
                }
            }

            if (!empty($filtro_hora_final)) {
                $fimFiltro = new DateTime(
                    $filtro_hora_final . ' 23:59:59',
                    $fusoLocal
                );

                if ($esperada > $fimFiltro) {
                    return false;
                }
            }

            /*
             * Só considera atrasado depois do fim da tolerância.
             */
            $limite = clone $esperada;

            $limite->modify(
                '+' . max(0, (int) $controle->tolerancia) . ' minutes'
            );

            return $limite <= $agoraLocal;

        } catch (Throwable $e) {
            return false;
        }
    }
);

/*
 * Se o controle já corresponde a um turno existente de qualquer segurança da
 * empresa, ele continua aparecendo dentro do accordion desse turno e não é
 * duplicado na lista de não executados.
 */
foreach ($turnos as $turnosDoSeguranca) {
    foreach ($turnosDoSeguranca as $turnoExistente) {
        foreach ($controlesPendentes as $indiceControle => $controlePendente) {
            $esperadoTs = strtotime((string) $controlePendente->hora_esperada);

            if ($esperadoTs === false) {
                continue;
            }

            $tipoPendente = $normalizarTipo($controlePendente->tipo);

            $horaTurno = $tipoPendente === 'inicio'
                ? ($turnoExistente->started_at ?? null)
                : ($turnoExistente->ended_at ?? null);

            if (empty($horaTurno)) {
                continue;
            }

            $horaTurnoTs = strtotime(
                (new DateTime((string) $horaTurno))
                    ->modify('-3 hours')
                    ->format('Y-m-d H:i:s')
            );

            if (
                $horaTurnoTs !== false &&
                abs($horaTurnoTs - $esperadoTs) <= (15 * 60)
            ) {
                unset($controlesPendentes[$indiceControle]);
            }
        }
    }
}

/*
 * Monta a lista única de turnos atrasados (início ou fim), exibida no
 * accordion "Não Executados". O mesmo atraso (tipo + horário esperado +
 * tolerância) aparece uma única vez.
 */
$atrasados = [];

foreach ($controlesPendentes as $controlePendente) {
    $tipo = $normalizarTipo($controlePendente->tipo);
    $esperadaTs = strtotime((string) $controlePendente->hora_esperada);

    if ($esperadaTs === false) {
        continue;
    }

    $tolerancia = max(0, (int) $controlePendente->tolerancia);
    $chave = $tipo . '|' . $esperadaTs . '|' . $tolerancia;

    if (isset($atrasados[$chave])) {
        continue;
    }

    $atrasados[$chave] = [
        'titulo'   => $tipo === 'inicio'
            ? 'Turno não iniciado'
            : 'Turno não finalizado',
        'icone'    => $tipo === 'inicio'
            ? 'bi-play-circle-fill'
            : 'bi-stop-circle-fill',
        'esperada' => date('d/m/Y H:i:s', $esperadaTs),
        'limite'   => date('H:i:s', $esperadaTs + ($tolerancia * 60)),
        'ordem'    => $esperadaTs,
    ];
}

$atrasados = array_values($atrasados);

usort(
    $atrasados,
    static fn(array $a, array $b): int => $a['ordem'] <=> $b['ordem']
);

$qtdAtrasados = count($atrasados);
?>
<!DOCTYPE html>
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
        integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer">
    </script>

    <link rel="stylesheet"
        href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
        crossorigin="anonymous">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
    <link rel="stylesheet" href="/../components/header/header.css">
    <link rel="stylesheet" href="/../components/lateral/lateral.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="../choices/choices.css">

    <title>Gestor Office Control</title>
</head>

<body id="body" data-nome-empresa="<?= htmlspecialchars($nomeEmpresa) ?>">

<?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'; ?>
<?php require_once __DIR__ . '/../../componentes/header/header.php'; ?>

<div class="main" id="container">
    <div class="col-md-12" style="padding: 0;">
        <div class="card">
            <div class="card-header-div">
                <div class="card-header-borda">
                    <form method="get" action="seguranca.php">
                        <div class="d-flex flex-row justify-content-between align-items-center">
                            <div class="d-flex flex-row">
                                <div class="d-flex flex-column" style="height: 1em;">
                                    <label for="filtro_hora_inicio" class="form-label">Data Inicial</label>
                                    <input type="date" name="filtro_hora_inicio" class="form-control" value="<?= $filtro_hora_inicio ?? '' ?>">
                                </div>

                                <div class="d-flex flex-column" style="height: 1em;">
                                    <label for="filtro_hora_final" class="form-label">Data Final</label>
                                    <input type="date"
                                        name="filtro_hora_final"
                                        class="form-control"
                                        value="<?= $filtro_hora_final ?? '' ?>">
                                </div>

                                <div class="d-flex flex-column">
                                    <label for="filtro_seguranca" class="form-label">Colaborador</label>

                                    <select name="filtro_seguranca" class="form-control">
                                        <option value="">Selecione</option>

                                        <?php foreach (
                                            Usuario::read(
                                                idempresa: $empresa_usuario_obj->id,
                                                cargo: 4
                                            ) as $seguranca
                                        ): ?>
                                            <option
                                                value="<?= $seguranca->id ?>"
                                                <?= ($filtro_seguranca == $seguranca->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(
                                                    $seguranca->nome ??
                                                    'Segurança #' . $seguranca->id
                                                ) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="inputs-dre-btn">
                                <div class="botoes-acao">
                                    <button type="submit"
                                        class="btn-sm btn"
                                        style="background-color: #5856d6; color: white;">
                                        Filtrar
                                    </button>

                                    <a href="seguranca.php"
                                        class="btn btn-secondary btn-sm">
                                        Limpar
                                    </a>
                                </div>

                                <div id="inputs-btn-analitico">
                                    <div class="botoes-gerar">
                                        <button type="button"
                                            class="btn-sm btn"
                                            id="botao-gerar-pdf"
                                            onclick="prepararGeracaoSeguranca('pdf')">
                                            Gerar PDF
                                        </button>

                                        <button type="button"
                                            class="btn-sm btn"
                                            id="botao-gerar-excel"
                                            onclick="prepararGeracaoSeguranca('excel')">
                                            Gerar Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($segurancas)): ?>
                    <div class="alert alert-info mb-0">
                        Nenhum segurança cadastrado para esta empresa.
                    </div>
                <?php else: ?>

                    <!-- Atrasados -->
                    <div class="accordion mb-3" id="accordion-atrasados">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-atrasados">
                                <button class="accordion-button collapsed"
                                    type="button"
                                    style="color:black;"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse-atrasados"
                                    aria-expanded="false"
                                    aria-controls="collapse-atrasados">

                                    <i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>
                                    Não Executados

                                    <span class="badge <?= $qtdAtrasados > 0 ? 'bg-danger' : 'bg-secondary' ?> ms-2">
                                        <?= $qtdAtrasados ?>
                                    </span>
                                </button>
                            </h2>

                            <div id="collapse-atrasados"
                                class="accordion-collapse collapse"
                                data-bs-parent="#accordion-atrasados">

                                <div class="accordion-body">
                                    <?php if (empty($atrasados)): ?>

                                        <p class="text-muted mb-0">
                                            Nenhum turno atrasado.
                                        </p>

                                    <?php else: ?>

                                        <?php foreach ($atrasados as $atrasado): ?>
                                            <div class="alert alert-danger d-flex align-items-center justify-content-between mb-2 py-3 px-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi <?= $atrasado['icone'] ?> me-2"></i>

                                                    <strong>
                                                        <?= htmlspecialchars($atrasado['titulo']) ?>
                                                    </strong>
                                                </div>

                                                <div class="small">
                                                    <strong>Esperado:</strong>
                                                    <?= htmlspecialchars($atrasado['esperada']) ?>

                                                    <span class="mx-1">|</span>

                                                    <strong>Limite:</strong>
                                                    <?= htmlspecialchars($atrasado['limite']) ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion" id="accordion-segurancas">

                        <?php foreach ($segurancas as $seguranca): ?>

                            <?php
                            $segId = 'seg' . $seguranca->id;
                            $turnosSeg = $turnos[$seguranca->id] ?? [];
                            $qtdTurnos = count($turnosSeg);
                            ?>

                            <div class="accordion-item"
                                data-seguranca-nome="<?= htmlspecialchars(
                                    $seguranca->nome ??
                                    'Segurança #' . $seguranca->id
                                ) ?>">

                                <h2 class="accordion-header"
                                    id="heading-<?= $segId ?>">

                                    <button class="accordion-button collapsed"
                                        type="button"
                                        style="color:black;"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-<?= $segId ?>"
                                        aria-expanded="false"
                                        aria-controls="collapse-<?= $segId ?>">

                                        <?= htmlspecialchars(
                                            $seguranca->nome ??
                                            'Segurança #' . $seguranca->id
                                        ) ?>

                                        <span class="badge bg-secondary ms-2">
                                            <?= $qtdTurnos ?>
                                            turno<?= $qtdTurnos == 1 ? '' : 's' ?>
                                        </span>
                                    </button>
                                </h2>

                                <div id="collapse-<?= $segId ?>"
                                    class="accordion-collapse collapse"
                                    data-bs-parent="#accordion-segurancas">

                                    <div class="accordion-body">

                                        <?php if (empty($turnosSeg)): ?>

                                            <p class="text-muted mb-0">
                                                Nenhum turno registrado para este segurança.
                                            </p>

                                        <?php else: ?>

                                            <div class="accordion"
                                                id="accordion-turnos-<?= $segId ?>">

                                                <?php foreach ($turnosSeg as $turno): ?>

                                                    <?php
                                                    $turnoId = $segId . '-turno' . $turno->id;

                                                    $listaPontos =
                                                        $pontos[$seguranca->id][$turno->id] ?? [];

                                                    $listaPanicos =
                                                        $panicos[$seguranca->id][$turno->id] ?? [];

                                                    $listaRondas =
                                                        $rondas[$seguranca->id][$turno->id] ?? [];

                                                    $ocorrenciaTurno =
                                                        $ocorrencias[$seguranca->id][$turno->id] ?? null;

                                                    $listaControles =
                                                        $controlesTurno[$seguranca->id][$turno->id] ?? [];
                                                    $listaControles = array_values(array_filter(
                                                        $listaControles,
                                                        static fn($controle) =>
                                                            $normalizarTipo($controle->tipo) === 'controle'
                                                    ));

                                                    $controlesUnificados = [];
                                                    $controlesVistos = [];

                                                    $controle02Usados = [];

                                                    foreach ($listaPontos as $ponto) {
                                                        $horaRespondida = $normalizarHorarioPonto(
                                                            $ponto->hora ?? $ponto->created_at
                                                        );

                                                        $horaPonto = $timestampHorarioLocal($horaRespondida);

                                                        // 1) controle02 do mesmo evento
                                                        $controle02Correspondente = null;
                                                        $menorDiferenca = 61;

                                                        if ($horaPonto !== null) {
                                                            foreach ($listaControles as $candidato) {
                                                                if (
                                                                    $candidato->hora_respondida === null ||
                                                                    $normalizarTipo($candidato->tipo) !== 'controle' ||
                                                                    isset($controle02Usados[$candidato->id])
                                                                ) {
                                                                    continue;
                                                                }

                                                                $respostaTs = $timestampHorarioLocal(
                                                                    (string) $candidato->hora_respondida
                                                                );

                                                                if ($respostaTs === null) {
                                                                    continue;
                                                                }

                                                                $diferenca = abs($respostaTs - $horaPonto);

                                                                if ($diferenca < $menorDiferenca) {
                                                                    $menorDiferenca = $diferenca;
                                                                    $controle02Correspondente = $candidato;
                                                                }
                                                            }
                                                        }

                                                        if ($controle02Correspondente !== null) {
                                                            $controle02Usados[$controle02Correspondente->id] = true;

                                                            $esperadaTs = $timestampHorarioLocal(
                                                                (string) $controle02Correspondente->hora_esperada
                                                            );

                                                            $respostaTs = $timestampHorarioLocal(
                                                                (string) $controle02Correspondente->hora_respondida
                                                            );

                                                            $limiteTs = $esperadaTs !== null
                                                                ? $esperadaTs
                                                                    + (max(0, (int) $controle02Correspondente->tolerancia) * 60)
                                                                : null;

                                                            $dentroDoPrazo =
                                                                $esperadaTs !== null &&
                                                                $respostaTs !== null &&
                                                                $respostaTs >= $esperadaTs &&
                                                                $respostaTs <= $limiteTs;

                                                            $controlesUnificados[] = [
                                                                'hora_esperada' => $esperadaTs !== null
                                                                    ? $controle02Correspondente->hora_esperada
                                                                    : null,

                                                                'hora_limite' => $limiteTs !== null
                                                                    ? (new DateTimeImmutable('@' . $limiteTs))
                                                                        ->setTimezone($timezoneLocal)
                                                                        ->format('Y-m-d H:i:s')
                                                                    : null,

                                                                // Mesmo valor do controle02: o filtro de duplicados mais abaixo
                                                                // compara exatamente este texto e descarta a linha repetida.
                                                                'hora_respondida' => $controle02Correspondente->hora_respondida,
                                                                'localizacao' => $ponto->localizacao ?? null,

                                                                'status' => $dentroDoPrazo ? 'Dentro do prazo' : 'Fora do prazo',

                                                                'classe' => $dentroDoPrazo ? 'table-success' : 'table-danger',

                                                                'ordem' => $respostaTs ?? PHP_INT_MAX,
                                                            ];

                                                            continue;
                                                        }

                                                        // 2) Sem controle02 correspondente: usa a tabela de controles da empresa.
                                                        $controleRespondido = null;

                                                        if ($horaPonto !== null) {
                                                            $dataPonto = (new DateTimeImmutable('@' . $horaPonto))
                                                                ->setTimezone($timezoneLocal)
                                                                ->format('Y-m-d');

                                                            foreach ($controlesAgendados as $controle) {
                                                                $horaControle = substr((string) $controle->hora, 0, 8);

                                                                try {
                                                                    $inicioControle = (new DateTimeImmutable(
                                                                        $dataPonto . ' ' . $horaControle,
                                                                        $timezoneLocal
                                                                    ))->getTimestamp();
                                                                } catch (Throwable $e) {
                                                                    continue;
                                                                }

                                                                $fimJanelaControle = $inicioControle
                                                                    + (max(0, (int) $controle->tolerancia) * 60);

                                                                if (
                                                                    $horaPonto >= $inicioControle &&
                                                                    $horaPonto <= $fimJanelaControle
                                                                ) {
                                                                    $controleRespondido = $controle;
                                                                    break;
                                                                }
                                                            }
                                                        }

                                                        $dentroDoPrazo = $pontoDentroDoPrazo($ponto, $controlesAgendados);

                                                        $controlesUnificados[] = [
                                                            'hora_esperada' => $controleRespondido?->hora,

                                                            'hora_limite' => $controleRespondido
                                                                ? (
                                                                    new DateTimeImmutable(
                                                                        $horaRespondida,
                                                                        $timezoneLocal
                                                                    )
                                                                )
                                                                    ->setTime(
                                                                        (int) substr((string) $controleRespondido->hora, 0, 2),
                                                                        (int) substr((string) $controleRespondido->hora, 3, 2),
                                                                        (int) substr((string) $controleRespondido->hora, 6, 2)
                                                                    )
                                                                    ->modify(
                                                                        '+' . (int) $controleRespondido->tolerancia . ' minutes'
                                                                    )
                                                                    ->format('H:i:s')
                                                                : null,

                                                            'hora_respondida' => $horaRespondida,
                                                            'localizacao' => $ponto->localizacao ?? null,

                                                            'status' => $dentroDoPrazo ? 'Dentro do prazo' : 'Fora do prazo',

                                                            'classe' => $dentroDoPrazo ? 'table-success' : 'table-danger',

                                                            'ordem' => $horaPonto ?? PHP_INT_MAX,
                                                        ];
                                                    }

                                                    /*
                                                     * Processa primeiro os controle02 respondidos:
                                                     * assim um pendente do mesmo horário já encontra
                                                     * o respondido na lista e é descartado.
                                                     */
                                                    usort(
                                                        $listaControles,
                                                        static function ($a, $b): int {
                                                            return
                                                                ($a->hora_respondida === null ? 1 : 0)
                                                                <=>
                                                                ($b->hora_respondida === null ? 1 : 0);
                                                        }
                                                    );

                                                    foreach ($listaControles as $controleTurno) {
                                                        $horaRespondida =
                                                            $controleTurno->hora_respondida;

                                                        $horaEsperada =
                                                            $controleTurno->hora_esperada;

                                                        $horaEsperadaTimestamp = $timestampHorarioLocal(
                                                            (string) $horaEsperada
                                                        );
                                                        $horaRespondidaTimestamp = $timestampHorarioLocal(
                                                            (string) $horaRespondida
                                                        );

                                                        $chaveControle =
                                                            $horaRespondida !== null
                                                                ? 'respondido:' .
                                                                    $horaRespondidaTimestamp
                                                                : 'pendente:' .
                                                                    $horaEsperadaTimestamp .
                                                                    ':' .
                                                                    (int) $controleTurno->tolerancia;

                                                        if (isset($controlesVistos[$chaveControle])) {
                                                            continue;
                                                        }

                                                        $controlesVistos[$chaveControle] = true;
                                                        $jaRepresentadoPorPonto = false;

                                                        if ($horaRespondida !== null) {
                                                            foreach (
                                                                $controlesUnificados
                                                                as $controleUnificado
                                                            ) {
                                                                if (
                                                                    $controleUnificado['hora_respondida'] !== null &&
                                                                    $controleUnificado['hora_respondida'] ===
                                                                        $horaRespondida
                                                                ) {
                                                                    $jaRepresentadoPorPonto = true;
                                                                    break;
                                                                }
                                                            }
                                                        } elseif ($horaEsperada !== null) {
                                                            $inicioJanela = $horaEsperadaTimestamp;

                                                            if ($inicioJanela !== null) {
                                                                $fimJanela =
                                                                    $inicioJanela +
                                                                    (
                                                                        max(
                                                                            0,
                                                                            (int) $controleTurno->tolerancia
                                                                        ) * 60
                                                                    );

                                                                foreach (
                                                                    $controlesUnificados
                                                                    as $controleUnificado
                                                                ) {
                                                                    if (
                                                                        $controleUnificado['hora_respondida'] ===
                                                                        null
                                                                    ) {
                                                                        continue;
                                                                    }

                                                                    $respostaTs = $timestampHorarioLocal(
                                                                        (string) $controleUnificado['hora_respondida']
                                                                    );

                                                                    if (
                                                                        $respostaTs !== null &&
                                                                        $respostaTs >= $inicioJanela &&
                                                                        $respostaTs <= $fimJanela
                                                                    ) {
                                                                        $jaRepresentadoPorPonto = true;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($jaRepresentadoPorPonto) {
                                                            continue;
                                                        }

                                                        $controlesUnificados[] = [
                                                            'hora_esperada' =>
                                                                $horaEsperada,

                                                            'hora_limite' =>
                                                                $horaEsperadaTimestamp !== null
                                                                    ? (new DateTimeImmutable('@' . (
                                                                        $horaEsperadaTimestamp +
                                                                        (max(0, (int) $controleTurno->tolerancia) * 60)
                                                                    )))
                                                                        ->setTimezone($timezoneLocal)
                                                                        ->format('Y-m-d H:i:s')
                                                                    : null,

                                                            'hora_respondida' =>
                                                                $horaRespondida,

                                                            'status' => $horaRespondida === null
                                                                ? 'Não respondido'
                                                                : (
                                                                    $horaEsperadaTimestamp !== null &&
                                                                    $horaRespondidaTimestamp !== null &&
                                                                    $horaRespondidaTimestamp >= $horaEsperadaTimestamp &&
                                                                    $horaRespondidaTimestamp <=
                                                                        $horaEsperadaTimestamp +
                                                                        (max(0, (int) $controleTurno->tolerancia) * 60)
                                                                        ? 'Dentro do prazo'
                                                                        : 'Fora do prazo'
                                                                ),

                                                            'classe' => $horaRespondida === null ||
                                                                $horaEsperadaTimestamp === null ||
                                                                $horaRespondidaTimestamp === null ||
                                                                $horaRespondidaTimestamp < $horaEsperadaTimestamp ||
                                                                $horaRespondidaTimestamp >
                                                                    $horaEsperadaTimestamp +
                                                                    (max(0, (int) $controleTurno->tolerancia) * 60)
                                                                        ? 'table-danger'
                                                                        : 'table-success',

                                                            'ordem' =>
                                                                $horaRespondida !== null
                                                                    ? (
                                                                        $horaRespondidaTimestamp ?? PHP_INT_MAX
                                                                    )
                                                                    : (
                                                                        $horaEsperadaTimestamp ?? PHP_INT_MAX
                                                                    ),
                                                        ];
                                                    }

                                                    usort(
                                                        $controlesUnificados,
                                                        static function (
                                                            array $a,
                                                            array $b
                                                        ): int {
                                                            return $a['ordem'] <=> $b['ordem'];
                                                        }
                                                    );

                                                    $inicioFmt = !empty($turno->started_at)
                                                        ? (
                                                            new DateTime(
                                                                $turno->started_at
                                                            )
                                                        )
                                                            ->modify('-3 hours')
                                                            ->format('d/m/Y H:i')
                                                        : '—';

                                                    $fimFmt = !empty($turno->ended_at)
                                                        ? (
                                                            new DateTime(
                                                                $turno->ended_at
                                                            )
                                                        )
                                                            ->modify('-3 hours')
                                                            ->format('d/m/Y H:i')
                                                        : 'Em Andamento';

                                                    $inicioHora = !empty($turno->started_at)
                                                        ? (
                                                            new DateTime(
                                                                $turno->started_at
                                                            )
                                                        )
                                                            ->modify('-3 hours')
                                                            ->format('H:i')
                                                        : '—';

                                                    $fimHora = !empty($turno->ended_at)
                                                        ? (
                                                            new DateTime(
                                                                $turno->ended_at
                                                            )
                                                        )
                                                            ->modify('-3 hours')
                                                            ->format('H:i')
                                                        : null;

                                                    // Horário esperado (só preenchido se o início/fim foi atrasado)
                                                    $inicioEsperado = $esperadoSeAtrasado(
                                                        'inicio',
                                                        $turno->started_at ?? null,
                                                        $controlesInicioFim
                                                    );

                                                    $fimEsperado = $esperadoSeAtrasado(
                                                        'termino',
                                                        $turno->ended_at ?? null,
                                                        $controlesInicioFim
                                                    );
                                                    $inicioLocalizacao = array_map(
                                                        'trim',
                                                        explode(',', (string) ($turno->localizacao_inicial ?? ''), 2)
                                                    );
                                                    $inicioLocalizacaoLat = $inicioLocalizacao[0] ?? null;
                                                    $inicioLocalizacaoLng = $inicioLocalizacao[1] ?? null;
                                                    $inicioLocalizacaoLat = $inicioLocalizacaoLat !== ''
                                                        ? $inicioLocalizacaoLat
                                                        : null;
                                                    $inicioLocalizacaoLng = $inicioLocalizacaoLng !== ''
                                                        ? $inicioLocalizacaoLng
                                                        : null;

                                                    $fimLocalizacao = array_map(
                                                        'trim',
                                                        explode(',', (string) ($turno->localizacao_final ?? ''), 2)
                                                    );
                                                    $fimLocalizacaoLat = $fimLocalizacao[0] ?? null;
                                                    $fimLocalizacaoLng = $fimLocalizacao[1] ?? null;
                                                    $fimLocalizacaoLat = $fimLocalizacaoLat !== ''
                                                        ? $fimLocalizacaoLat
                                                        : null;
                                                    $fimLocalizacaoLng = $fimLocalizacaoLng !== ''
                                                        ? $fimLocalizacaoLng
                                                        : null;
                                                    ?>

                                                    <div class="accordion-item"
                                                        data-turno-inicio="<?= htmlspecialchars($inicioFmt) ?>"
                                                        data-turno-fim="<?= htmlspecialchars(
                                                            $fimFmt !== 'Em Andamento'
                                                                ? $fimFmt
                                                                : 'Em andamento'
                                                        ) ?>"
                                                        data-panicos="<?= count($listaPanicos) ?>"
                                                        data-controles="<?= count($controlesUnificados) ?>"
                                                        data-rondas="<?= count($listaRondas) ?>">

                                                        <h2 class="accordion-header"
                                                            id="heading-<?= $turnoId ?>">

                                                            <button class="accordion-button collapsed"
                                                                type="button"
                                                                style="color:black;"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#collapse-<?= $turnoId ?>"
                                                                aria-expanded="false"
                                                                aria-controls="collapse-<?= $turnoId ?>">

                                                                <i class="bi bi-clock-history me-2"></i>

                                                                <?= htmlspecialchars($inicioFmt) ?>

                                                                <?= $fimFmt !== 'Em Andamento'
                                                                    ? ' até ' . htmlspecialchars($fimFmt)
                                                                    : ' - Em andamento' ?>

                                                                <span class="badge bg-info ms-2">
                                                                    <?= count($controlesUnificados) ?>
                                                                </span>

                                                                <span class="badge bg-danger ms-2">
                                                                    <?= count($listaPanicos) ?>
                                                                </span>

                                                                <span class="badge bg-primary ms-2">
                                                                    <?= count($listaRondas) ?>
                                                                </span>
                                                            </button>
                                                        </h2>

                                                        <div id="collapse-<?= $turnoId ?>"
                                                            class="accordion-collapse collapse"
                                                            data-bs-parent="#accordion-turnos-<?= $segId ?>">

                                                            <div class="accordion-body">
                                                                <div class="accordion"
                                                                    id="accordion-cat-<?= $turnoId ?>">

                                                                    <div class="badge bg-secondary w-100 mb-3 text-start"
                                                                        style="font-size: 1.5em;">
                                                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                                                            <div>
                                                                                Início: <?= htmlspecialchars($inicioFmt) ?>
                                                                                <?php if ($inicioEsperado !== null) { ?>
                                                                                    <span class="ms-2 text-warning" style="font-size: .7em;">
                                                                                        Esperado: <?= htmlspecialchars($inicioEsperado) ?>
                                                                                    </span>
                                                                                <?php } ?>
                                                                            </div>
                                                                            <?php if ($inicioLocalizacaoLat !== null && $inicioLocalizacaoLng !== null) { ?>
                                                                                    <a class="small text-nowrap"
                                                                                    style="color: white;"
                                                                                        href="https://maps.google.com/?q=<?= urlencode($inicioLocalizacaoLat . ',' . $inicioLocalizacaoLng) ?>"
                                                                                        target="_blank" rel="noopener">Ver localização no mapa</a>
                                                                            <?php } ?>
                                                                        </div>

                                                                        <div class="d-flex justify-content-between align-items-center gap-3 mt-2">
                                                                            <div>
                                                                                Fim: <?= htmlspecialchars($fimFmt) ?>
                                                                                <?php if ($fimEsperado !== null) { ?>
                                                                                    <span class="ms-2 text-warning" style="font-size: .7em;">
                                                                                        Esperado: <?= htmlspecialchars($fimEsperado) ?>
                                                                                    </span>
                                                                                <?php } ?>
                                                                            </div>
                                                                            <?php if ($fimLocalizacaoLat !== null && $fimLocalizacaoLng !== null) { ?>
                                                                                <a class="small text-nowrap"
                                                                                    style="color: white;"
                                                                                        href="https://maps.google.com/?q=<?= urlencode($inicioLocalizacaoLat . ',' . $inicioLocalizacaoLng) ?>"
                                                                                        target="_blank" rel="noopener">Ver localização no mapa</a>
                                                                            <?php } ?>
                                                                        </div>
                                                                    </div>

                                                                    <?php if ($ocorrenciaTurno !== null): ?>
                                                                        <div class="alert alert-primary mb-3"
                                                                            role="alert">

                                                                            <strong>Ocorrência:</strong>

                                                                            <?= htmlspecialchars(
                                                                                $ocorrenciaTurno->texto ?? ''
                                                                            ) ?>
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header"
                                                                            id="heading-controle-<?= $turnoId ?>">

                                                                            <button class="accordion-button collapsed"
                                                                                type="button"
                                                                                style="color:black;"
                                                                                data-bs-toggle="collapse"
                                                                                data-bs-target="#collapse-controle-<?= $turnoId ?>"
                                                                                aria-expanded="false"
                                                                                aria-controls="collapse-controle-<?= $turnoId ?>">

                                                                                <i class="bi bi-clock me-2 text-info"></i>

                                                                                Controle

                                                                                <span class="badge bg-secondary ms-2">
                                                                                    <?= count($controlesUnificados) ?>
                                                                                </span>
                                                                            </button>
                                                                        </h2>

                                                                        <div id="collapse-controle-<?= $turnoId ?>"
                                                                            class="accordion-collapse collapse"
                                                                            data-bs-parent="#accordion-cat-<?= $turnoId ?>">

                                                                            <div class="accordion-body">

                                                                                <?php if (empty($controlesUnificados)): ?>

                                                                                    <p class="text-muted mb-0">
                                                                                        Nenhum controle registrado neste turno.
                                                                                    </p>

                                                                                <?php else: ?>

                                                                                    <table class="table table-striped table-bordered">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>Horário esperado</th>
                                                                                                <th>Horário respondido</th>
                                                                                                <th>Localização</th>
                                                                                                <th>Status</th>
                                                                                            </tr>
                                                                                        </thead>

                                                                                        <tbody>
                                                                                            <?php foreach (
                                                                                                $controlesUnificados
                                                                                                as $controle
                                                                                            ): ?>

                                                                                                <tr class="<?= $controle['classe'] ?>">
                                                                                                    <td>
                                                                                                        <?= $controle['hora_esperada'] === null
                                                                                                            ? '—'
                                                                                                            : htmlspecialchars(
                                                                                                                (
                                                                                                                    new DateTime(
                                                                                                                        (string) $controle['hora_esperada']
                                                                                                                    )
                                                                                                                )->format('H:i:s') .
                                                                                                                ' - ' .
                                                                                                                (
                                                                                                                    new DateTime(
                                                                                                                        (string) $controle['hora_limite']
                                                                                                                    )
                                                                                                                )->format('H:i:s')
                                                                                                            ) ?>
                                                                                                    </td>

                                                                                                    <td>
                                                                                                        <?= $controle['hora_respondida'] === null
                                                                                                            ? '—'
                                                                                                            : htmlspecialchars(
                                                                                                                (
                                                                                                                    new DateTime(
                                                                                                                        (string) $controle['hora_respondida']
                                                                                                                    )
                                                                                                                )->format('H:i:s')
                                                                                                            ) ?>
                                                                                                    </td>

                                                                                                    <td>
                                                                                                        <?php if (!empty($controle['localizacao'])) { ?>
                                                                                                            <a class="small"
                                                                                                                href="https://maps.google.com/?q=<?= urlencode((string) $controle['localizacao']) ?>"
                                                                                                                target="_blank"
                                                                                                                rel="noopener">
                                                                                                                Ver localização no mapa
                                                                                                            </a>
                                                                                                        <?php } else { ?>
                                                                                                            —
                                                                                                        <?php } ?>
                                                                                                    </td>

                                                                                                    <td>
                                                                                                        <?= htmlspecialchars(
                                                                                                            $controle['status']
                                                                                                        ) ?>
                                                                                                    </td>
                                                                                                </tr>

                                                                                            <?php endforeach; ?>
                                                                                        </tbody>
                                                                                    </table>

                                                                                <?php endif; ?>

                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Pânicos -->
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header"
                                                                            id="heading-panico-<?= $turnoId ?>">

                                                                            <button class="accordion-button collapsed"
                                                                                type="button"
                                                                                style="color:black;"
                                                                                data-bs-toggle="collapse"
                                                                                data-bs-target="#collapse-panico-<?= $turnoId ?>"
                                                                                aria-expanded="false"
                                                                                aria-controls="collapse-panico-<?= $turnoId ?>">

                                                                                <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>

                                                                                Pânico

                                                                                <span class="badge bg-secondary ms-2">
                                                                                    <?= count($listaPanicos) ?>
                                                                                </span>
                                                                            </button>
                                                                        </h2>

                                                                        <div id="collapse-panico-<?= $turnoId ?>"
                                                                            class="accordion-collapse collapse"
                                                                            data-bs-parent="#accordion-cat-<?= $turnoId ?>">

                                                                            <div class="accordion-body">

                                                                                <?php if (empty($listaPanicos)): ?>

                                                                                    <p class="text-muted mb-0">
                                                                                        Nenhum acionamento de pânico neste turno.
                                                                                    </p>

                                                                                <?php else: ?>

                                                                                    <ul class="list-group">

                                                                                        <?php foreach (
                                                                                            $listaPanicos
                                                                                            as $panico
                                                                                        ): ?>

                                                                                            <li class="list-group-item">

                                                                                                <div class="d-flex justify-content-between align-items-start">
                                                                                                    <span>
                                                                                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                                                                                        Acionamento de pânico
                                                                                                    </span>

                                                                                                    <small class="text-muted ms-2">
                                                                                                        <?= !empty($panico->created_at)
                                                                                                            ? htmlspecialchars(
                                                                                                                (
                                                                                                                    new DateTime(
                                                                                                                        $panico->created_at
                                                                                                                    )
                                                                                                                )
                                                                                                                    ->modify('-3 hours')
                                                                                                                    ->format('d/m/Y H:i')
                                                                                                            )
                                                                                                            : '' ?>
                                                                                                    </small>
                                                                                                </div>

                                                                                                <?php
                                                                                                if (
                                                                                                    !empty($panico->localizacao) &&
                                                                                                    $panico->localizacao !==
                                                                                                        'Localização não informada'
                                                                                                ):
                                                                                                    [$latitude, $longitude] =
                                                                                                        explode(
                                                                                                            ',',
                                                                                                            $panico->localizacao
                                                                                                        );
                                                                                                ?>

                                                                                                    <a class="small"
                                                                                                        href="https://maps.google.com/?q=<?= urlencode(
                                                                                                            $latitude . ',' . $longitude
                                                                                                        ) ?>"
                                                                                                        target="_blank"
                                                                                                        rel="noopener">
                                                                                                        Ver localização no mapa
                                                                                                    </a>

                                                                                                <?php else: ?>

                                                                                                    <p class="small text-muted mb-0">
                                                                                                        Localização não informada.
                                                                                                    </p>

                                                                                                <?php endif; ?>

                                                                                            </li>

                                                                                        <?php endforeach; ?>

                                                                                    </ul>

                                                                                <?php endif; ?>

                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Rondas -->
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header"
                                                                            id="heading-ronda-<?= $turnoId ?>">

                                                                            <button class="accordion-button collapsed"
                                                                                type="button"
                                                                                style="color:black;"
                                                                                data-bs-toggle="collapse"
                                                                                data-bs-target="#collapse-ronda-<?= $turnoId ?>"
                                                                                aria-expanded="false"
                                                                                aria-controls="collapse-ronda-<?= $turnoId ?>">

                                                                                <i class="bi bi-shield-check me-2 text-primary"></i>

                                                                                Ronda - Ocorrência

                                                                                <span class="badge bg-secondary ms-2">
                                                                                    <?= count($listaRondas) ?>
                                                                                </span>
                                                                            </button>
                                                                        </h2>

                                                                        <div id="collapse-ronda-<?= $turnoId ?>"
                                                                            class="accordion-collapse collapse"
                                                                            data-bs-parent="#accordion-cat-<?= $turnoId ?>">

                                                                            <div class="accordion-body">

                                                                                <?php if (empty($listaRondas)): ?>

                                                                                    <p class="text-muted mb-0">
                                                                                        Nenhuma ronda registrada neste turno.
                                                                                    </p>

                                                                                <?php else: ?>

                                                                                    <table class="table table-striped table-bordered">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>Descrição</th>
                                                                                                <th>Horário</th>
                                                                                                <th>Localização</th>
                                                                                            </tr>
                                                                                        </thead>

                                                                                        <tbody>

                                                                                            <?php foreach (
                                                                                                $listaRondas
                                                                                                as $ronda
                                                                                            ): ?>

                                                                                                <tr>
                                                                                                    <td>
                                                                                                        <?= htmlspecialchars(
                                                                                                            $ronda->descricao ??
                                                                                                            'Ronda'
                                                                                                        ) ?>
                                                                                                    </td>

                                                                                                    <td>
                                                                                                        <?= !empty($ronda->hora)
                                                                                                            ? htmlspecialchars(
                                                                                                                date(
                                                                                                                    'd/m/Y H:i',
                                                                                                                    strtotime(
                                                                                                                        $ronda->hora
                                                                                                                    )
                                                                                                                )
                                                                                                            )
                                                                                                            : htmlspecialchars(
                                                                                                                $ronda->created_at ?? ''
                                                                                                            ) ?>
                                                                                                    </td>

                                                                                                    <td>
                                                                                                        <?php if (!empty($ronda->localizacao)) { ?>
                                                                                                            <a class="small"
                                                                                                                href="https://maps.google.com/?q=<?= urlencode((string) $ronda->localizacao) ?>"
                                                                                                                target="_blank"
                                                                                                                rel="noopener">
                                                                                                                Ver localização no mapa
                                                                                                            </a>
                                                                                                        <?php } else { ?>
                                                                                                            —
                                                                                                        <?php } ?>
                                                                                                    </td>
                                                                                                </tr>

                                                                                            <?php endforeach; ?>

                                                                                        </tbody>
                                                                                    </table>

                                                                                <?php endif; ?>

                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                <?php endforeach; ?>

                                            </div>

                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>
<script src="gerar.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var userBtn = document.getElementById('userBtn');
    var userMenu = document.getElementById('userMenu');

    if (userBtn && userMenu) {
        userBtn.onclick = function(e) {
            e.stopPropagation();

            if (userMenu.style.display === 'block') {
                userMenu.style.display = 'none';
            } else {
                userMenu.style.display = 'block';
            }
        };

        document.addEventListener('click', function(e) {
            if (userMenu.style.display === 'block') {
                userMenu.style.display = 'none';
            }
        });

        userMenu.onclick = function(e) {
            e.stopPropagation();
        };
    }
});

const consultar = document.querySelector('input[name="consultar"]');
const processar = document.querySelector('input[name="processar"]');

if (consultar && processar) {
    if (!consultar.checked) {
        processar.checked = false;
    }

    if (processar.checked) {
        consultar.checked = true;
    }
}

<?php if (isset($get_acao) && $get_acao == 'adicionar') { ?>
window.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('modal_usuario');
    var Modal = new bootstrap.Modal(modalEl);

    Modal.show();

    modalEl.addEventListener('hidden.bs.modal', function() {
        window.location.href = 'index.php';
    });
});
<?php }

if (isset($erro) && $erro == 'usado') { ?>
alert(
    'Não é possível adicionar esse usuario, pois já existe um usuario ou gestor com esse e-mail'
);

window.location.href = 'index.php';
<?php } ?>
</script>

</html>