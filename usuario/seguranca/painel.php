<?php

require_once __DIR__ . '/../../db/base.php';
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

date_default_timezone_set('America/Sao_Paulo');
session_start();

// A sessão precisa ser validada ANTES de ler a empresa do usuário.
if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']->cargo != 3 ||
    $_SESSION['usuario']->permissao_seguranca != 1
) {
    header('Location: /');
    exit();
}

$idEmpresa = (int) $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($idEmpresa)[0] ?? null;

if (!$empresa_usuario_obj || $empresa_usuario_obj->permissao_seguranca != 1) {
    header('Location: /');
    exit();
}

$empresa_lista = Empresa::read(cnpj_principal:$empresa_usuario_obj->cnpj_principal);

$nomeEmpresa = $empresa_usuario_obj->nom_fant;
$lateral_seguranca = true;
$lateral_target = 'painel';

// ─────────────────────────────────────────────────────────────────────────────
// Funções auxiliares
// ─────────────────────────────────────────────────────────────────────────────
function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}


function rotuloTipo(string $tipo): string
{
    return [
        'inicio' => 'Início',
        'termino' => 'Término',
        'controle' => 'Controle',
    ][strtolower(trim($tipo))] ?? ucfirst($tipo);
}

/** @return array{0:string,1:string} [texto, classe css da linha] */

function urlPagina(array $base, int $pagina): string
{
    return '?' . http_build_query($base + ['pagina' => $pagina]);
}

$hoje = new DateTimeImmutable('today');

// O banco guarda o horário 3h à frente do local (por isso normalizarLimite subtrai 3h).
const DESLOCAMENTO_BD_HORAS = -3;

function dataValida(?string $valor): ?string
{
    if ($valor === null || $valor === '') {
        return null;
    }

    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

    return ($dt && $dt->format('Y-m-d') === $valor) ? $valor : null;
}

function paraLocal(?string $valor): ?string
{
    if ($valor === null || $valor === '') {
        return null;
    }

    return (new DateTimeImmutable($valor))
        ->modify(sprintf('%+d hours', DESLOCAMENTO_BD_HORAS))
        ->format('Y-m-d H:i:s');
}

// ── Filtros ──────────────────────────────────────────────────────────────────
$dataIni = dataValida(filter_input(INPUT_GET, 'data_ini')) ?? $hoje->format('Y-m-d');
$dataFim = dataValida(filter_input(INPUT_GET, 'data_fim')) ?? $hoje->format('Y-m-d');
if ($dataFim < $dataIni) {
    [$dataIni, $dataFim] = [$dataFim, $dataIni];
}

$tiposValidos = ['inicio', 'termino', 'controle'];
$tipo = strtolower(trim((string) filter_input(INPUT_GET, 'tipo')));
if (!in_array($tipo, $tiposValidos, true)) {
    $tipo = '';
}

$idColaborador = max(0, (int) filter_input(INPUT_GET, 'colaborador', FILTER_VALIDATE_INT));

$porPaginaPermitidos = [10, 25, 50, 100];
$porPagina = (int) filter_input(INPUT_GET, 'numero_exibido', FILTER_VALIDATE_INT);
if (!in_array($porPagina, $porPaginaPermitidos, true)) {
    $porPagina = 25;
}

$pagina = max(1, (int) filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT));

// ── Empresas do grupo (só as do mesmo CNPJ principal podem ser filtradas) ────
$empresasGrupo = [];
foreach ($empresa_lista as $e) {
    $empresasGrupo[(int) $e->id] = (string) $e->nom_fant;
}
if (!isset($empresasGrupo[$idEmpresa])) {
    $empresasGrupo[$idEmpresa] = $nomeEmpresa;
}

$idEmpresaFiltro = (int) filter_input(INPUT_GET, 'empresa', FILTER_VALIDATE_INT);
if (!isset($empresasGrupo[$idEmpresaFiltro])) {
    $idEmpresaFiltro = 0; // 0 = todas
}
$idsConsulta = $idEmpresaFiltro > 0 ? [$idEmpresaFiltro] : array_keys($empresasGrupo);

// ── Colaboradores das empresas consultadas ───────────────────────────────────
// ATENÇÃO: ajuste à assinatura real de Usuario::read().
$colaboradores = [];
$nomesColaboradores = [];
try {
    foreach ($idsConsulta as $idE) {
        foreach (Usuario::read(idempresa:$idE, cargo: 4) as $u) {
            $idsColaboradores[(int) $u->id] = (string) $u->id;
            $nomesColaboradores[(int) $u->id] = (string) $u->nome;
        }
    }
    asort($nomesColaboradores, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($nomesColaboradores as $id => $nome) {
        $colaboradores[] = ['id' => $id, 'nome' => $nome];
    }
} catch (Throwable $e) {
    // o select fica só com "Todos"
}

// ── Consulta ao Controle02 ───────────────────────────────────────────────────
$registros = [];
$erroConsulta = null;

try {
    foreach ($idsConsulta as $idE) {
        $itens = Controle02::read(
            null,
            $idE,
            $idColaborador > 0 ? $idColaborador : null,
            $tipo !== '' ? $tipo : null,
            $dataIni . ' 00:00:00',
            $dataFim . ' 23:59:59',
            read_painel: true,
        );

        foreach ($itens as $c) {
            $esperada   = paraLocal($c->hora_esperada);
            $respondida = paraLocal($c->hora_respondida);

            $registros[] = [
                'id'              => (int) $c->id,
                'empresa'         => $empresasGrupo[(int) $c->id_empresa] ?? $nomeEmpresa,
                'tipo'            => (string) $c->tipo,
                'colaborador'     => $nomesColaboradores[(int) $c->id_usuario] ?? ('#' . (int) $c->id_usuario),
                'hora_esperada'   => $esperada ?? $respondida,
                'hora_respondida' => $respondida,
                'tolerancia'      => (int) $c->tolerancia,
            ];
        }
    }

    // mais recentes primeiro (também ordena quando junta várias empresas)
    usort($registros, fn($a, $b) => strcmp((string) $b['hora_esperada'], (string) $a['hora_esperada']));
} catch (Throwable $e) {
    $erroConsulta = 'Não foi possível carregar os registros no momento.';
    error_log('Painel Controle02: ' . $e->getMessage());
}

$panicos_lista = [];

if(!isset($idsColaboradores)) {
    $idsColaboradores = [];
    foreach ($idsConsulta as $idE) {
        foreach (Usuario::read(idempresa:$idE, cargo: 4) as $u) {
            $idsColaboradores[(int) $u->id] = (int) $u->id;
        }
    }
}
foreach($idsColaboradores as $idC) {
    foreach(Panico::read(null, $idC, $dataIni . ' 00:00:00', $dataFim . ' 23:59:59') as $p) {
        $panicos_lista[] = $p;
    }
}

// ── Paginação ────────────────────────────────────────────────────────────────
$total = count($registros);
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);

$registros = array_slice($registros, ($pagina - 1) * $porPagina, $porPagina);

$exibidoDe  = $total === 0 ? 0 : (($pagina - 1) * $porPagina) + 1;
$exibidoAte = $total === 0 ? 0 : $exibidoDe + count($registros) - 1;

$paginasVisiveis = array_values(array_unique(array_filter(
    array_merge([1, $totalPaginas], range($pagina - 2, $pagina + 2)),
    fn($p) => $p >= 1 && $p <= $totalPaginas
)));
sort($paginasVisiveis);

$baseQuery = [
    'data_ini'       => $dataIni,
    'data_fim'       => $dataFim,
    'empresa'        => $idEmpresaFiltro,
    'colaborador'    => $idColaborador,
    'tipo'           => $tipo,
    'numero_exibido' => $porPagina,
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/style.css">
    <link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">

    <title>Gestor Office Control</title>

    <style>
        .pn-filtros {
            margin: 16px;
            padding: 12px 16px 16px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: #f7f7f7;
        }

        .pn-filtros .form-label { margin-bottom: 2px; font-size: .9rem; }

        .pn-opcoes { display: flex; align-items: flex-end; gap: 18px; flex-wrap: wrap; }
        .pn-opcoes > span { font-size: .85rem; padding-bottom: 2px; }

        .pn-opcao {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            margin: 0;
            font-size: .85rem;
            cursor: pointer;
        }

        .pn-opcao .form-check-input { margin: 0; }

        .pn-tabela thead th {
            background: #fff;
            text-align: center;
            vertical-align: middle;
            font-weight: 700;
            border-bottom: 1px solid #dee2e6;
            white-space: nowrap;
        }

        .pn-tabela td { text-align: center; white-space: nowrap; font-variant-numeric: tabular-nums; }

        .pn-tabela tbody tr.pn-verde { --bs-table-bg: #90ee90; }
        .pn-tabela tbody tr.pn-laranja { --bs-table-bg: #ffcf7a; }
        .pn-tabela tbody tr.pn-vermelho { --bs-table-bg: #f49494; }
        .pn-tabela tbody tr.pn-vazio td { padding: 24px; white-space: normal; }
    </style>
</head>

<body id="body" data-nome-empresa="<?= h($nomeEmpresa) ?>">

<?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'; ?>
<?php require_once __DIR__ . '/../../componentes/header/header.php'; ?>

<div class="main" id="container">
    <div class="row">
        <div class="col-md-12" style="padding: 0;">

            <div class="card">
                <div class="card-header">
                    <div class="card-header-lancamento d-flex justify-content-between align-items-center">
                        <h3>Painel de controle</h3>
                        <div>
                            <button type="button" class="btn btn-primary btn-lg" id="pnToggle"
                                aria-expanded="true" aria-controls="pnFiltros">
                                <span id="pnToggleTexto">Ocultar filtros</span>
                            </button>
                        </div>
                    </div>
                </div>

                <form method="get" id="pnFiltros" class="pn-filtros">
                    <h5 class="mb-2">Filtros</h5>
                    <input type="hidden" name="numero_exibido" value="<?= $porPagina ?>">

                    <div class="row g-3 align-items-between">
                        <div class="w-75 d-flex flex-row">
                            <div class="row g-2 w-100">
                                <div class="col-md-2">
                                    <label class="form-label" for="pnDataIni">Data inicial:</label>
                                    <input type="date" class="form-control" id="pnDataIni" name="data_ini" value="<?= h($dataIni) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="pnDataFim">Data final:</label>
                                    <input type="date" class="form-control" id="pnDataFim" name="data_fim" value="<?= h($dataFim) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="pnEmpresa">Empresa:</label>
                                    <select class="form-select" id="pnEmpresa" name="empresa">
                                        <option value="0">Todas</option>
                                        <?php foreach ($empresasGrupo as $idE => $nomeE): ?>
                                            <option value="<?= (int) $idE ?>" <?= (int) $idE === $idEmpresaFiltro ? 'selected' : '' ?>>
                                                <?= h($nomeE) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="pnColaborador">Colaborador:</label>
                                    <select class="form-select" id="pnColaborador" name="colaborador">
                                        <option value="0">Todos</option>
                                        <?php foreach ($colaboradores as $c): ?>
                                            <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === $idColaborador ? 'selected' : '' ?>>
                                                <?= h($c['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="pn-opcoes" role="radiogroup" aria-label="Tipo">
                                        <span>Tipo:</span>
                                        <?php foreach (['' => 'Todos', 'inicio' => 'Início', 'termino' => 'Término', 'controle' => 'Controle'] as $valor => $rotulo): ?>
                                            <label class="pn-opcao">
                                                <?= h($rotulo) ?>
                                                <input class="form-check-input" type="radio" name="tipo" value="<?= h($valor) ?>" <?= $tipo === $valor ? 'checked' : '' ?>>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-25">
                            <div class="d-grid gap-2 align-items-end">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a class="btn btn-secondary" href="<?= h(strtok($_SERVER['REQUEST_URI'], '?')) ?>">Limpar</a>
                            </div>
                        </div>
                    </div>
                </form>

                

                <div class="table-responsive table-striped">
                    <table class="table table-borderless align-middle mb-0 pn-tabela">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Empresa</th>
                                <th>Localização</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!$panicos_lista): ?>
                            <tr class="pn-vazio">
                                <td colspan="7">Nenhum Pânico Encontrado Com Esses Filtros.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($panicos_lista as $p):
                            $usuario_id = $p->id_usuario;

                            $usuario_obj = Usuario::read($usuario_id)[0] ?? 'Desconhecido';
                            $usuario_nome = $usuario_obj->nome;
                            $empresa_obj = Empresa::read($usuario_obj->id_empresa)[0];
                            $empresa_nome = $empresa_obj->nom_fant ?? $empresa_obj->razao_soc ?? 'Desconhecida';
                            [$latitude, $longitude] = explode( ',', $p->localizacao);
                            [$panico_data, $panico_hora] = explode(' ', paraLocal($p->hora) ?? '');
                        ?>
                            <tr class="parcela_cor_vermelha">
                                <td><?= $panico_data ?></td>
                                <td><?= $panico_hora ?></td>
                                <td><?= h($empresa_nome) ?></td>
                                <td>
                                    <a 
                                    class="small" 
                                    href="https://maps.google.com/?q=<?= urlencode($latitude . ',' . $longitude ) ?>"
                                    target="_blank"
                                    rel="noopener">
                                    Ver localização no mapa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive table-striped">
                    <table class="table table-borderless align-middle mb-0 pn-tabela">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Empresa</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!$registros): ?>
                            <tr class="pn-vazio">
                                <td colspan="7">Nenhum registro encontrado para os filtros selecionados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($registros as $r):
                            $esperada = new DateTime($r['hora_esperada']);
                            $esperada->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                            if($r['tipo'] == 'inicio') {
                                $classe_linha = 'parcela_cor_verde';
                            } else if($r['tipo'] == 'controle') {
                                $classe_linha = 'parcela_cor_azul';
                            } else if($r['tipo'] == 'termino') {
                                $classe_linha = 'parcela_cor_amarela';
                            }
                        ?>
                            <tr class="<?= $classe_linha ?>">
                                <td><?= $esperada->format('d/m/Y') ?></td>
                                <td><?= $esperada->modify('+3 hours')->format('H:i') ?></td>
                                <td><?= h($r['empresa']) ?></td>
                                <td><?= h(rotuloTipo((string) $r['tipo'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    var botao = document.getElementById('pnToggle');
    var texto = document.getElementById('pnToggleTexto');
    var filtros = document.getElementById('pnFiltros');
    var chave = 'painelControleFiltrosOcultos';

    function aplicar(ocultar) {
        filtros.hidden = ocultar;
        botao.setAttribute('aria-expanded', String(!ocultar));
        texto.textContent = ocultar ? 'Mostrar filtros' : 'Ocultar filtros';
    }

    var salvo = null;
    try { salvo = localStorage.getItem(chave); } catch (e) {}
    aplicar(salvo === '1');

    botao.addEventListener('click', function () {
        var ocultar = !filtros.hidden;
        aplicar(ocultar);
        try { localStorage.setItem(chave, ocultar ? '1' : '0'); } catch (e) {}
    });
})();
</script>

</body>
</html>