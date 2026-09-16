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

session_start();


    
$empresa_usuario_obj = Empresa::read($_SESSION['usuario']->id_empresa)[0];
$nomeEmpresa = $empresa_usuario_obj->nom_fant;

if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->permissao_seguranca != 1 || $empresa_usuario_obj->permissao_seguranca != 1) {
    header('Location: /');
    exit();
}

$erro = filter_input(INPUT_GET, 'erro');
$lateral_seguranca = true;
$lateral_target = 'ocorrencias';
$filtro_hora_inicio = filter_input(INPUT_GET, 'filtro_hora_inicio');
$filtro_hora_final = filter_input(INPUT_GET, 'filtro_hora_final');
$filtro_seguranca = filter_input(INPUT_GET, 'filtro_seguranca');

$segurancas = Usuario::read(id:$filtro_seguranca, idempresa: $empresa_usuario_obj->id, cargo:4);

$turnos = [];
$panicos = [];
$rondas = [];
$pontos = [];
$ocorrencias = [];
$controles = Controle::read($empresa_usuario_obj->id);

$pontoDentroDoPrazo = static function ($ponto, array $controles): bool {
    $horaPonto = strtotime($ponto->hora ?? $ponto->created_at);
    if ($horaPonto === false) {
        return false;
    }

    $dataPonto = date('Y-m-d', $horaPonto);
    foreach ($controles as $controle) {
        $horaControle = substr((string) $controle->hora, 0, 8);
        $inicio = strtotime($dataPonto . ' ' . $horaControle);
        if ($inicio === false) {
            continue;
        }

        $fim = $inicio + (max(0, (int) $controle->tolerancia) * 60);
        if ($horaPonto >= $inicio && $horaPonto <= $fim) {
            return true;
        }
    }

    return false;
};

foreach($segurancas as $i => $seguranca) {
    $turnos[$seguranca->id] = Turno::read(id_usuario:$seguranca->id, filtro_hora_inicio: $filtro_hora_inicio, filtro_hora_final: $filtro_hora_final);
    
    foreach($turnos[$seguranca->id] as $turno) {
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
        $ocorrencias[$seguranca->id][$turno->id] = Ocorrencia::read(id_turno: $turno->id)[0] ?? null;

        $rondas[$seguranca->id][$turno->id] = Ronda::read(
            id_usuario: $seguranca->id,
            hora_inicio: $inicioTurno,
            hora_fim: $fimTurno
        );
        
    }

}

?>
<!DOCTYPE html>
<head>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js" integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
            <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
    <link rel="stylesheet" href="/../components/header/header.css"> 
    <link rel="stylesheet" href="/../components/lateral/lateral.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="../choices/choices.css"></link>

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
                                    <input type="date" name="filtro_hora_final" class="form-control" value="<?= $filtro_hora_final ?? '' ?>">
                                </div>
                                <div class="d-flex flex-column">
                                    <label for="filtro_hora_inicio" class="form-label">Segurança</label>
                                    <select name="filtro_seguranca" class="form-control">
                                        <option value="">Selecione</option>
                                        <?php foreach (Usuario::read(idempresa: $empresa_usuario_obj->id, cargo:4) as $seguranca): ?>
                                            <option value="<?= $seguranca->id ?>" <?= ($filtro_seguranca == $seguranca->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($seguranca->nome ?? 'Segurança #' . $seguranca->id) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="inputs-dre-btn">
                                                   <div class="botoes-acao">
                                                    <button type="submit" class="btn-sm btn" style="background-color: #5856d6; color: white;">Filtrar</button>
                                                    <a href="analitico.php" class="btn btn-secondary btn-sm">Limpar</a>
                                                     </div>   
                                                    <div id="inputs-btn-analitico">
                                                                <div class="botoes-gerar">
                                                                    <button type="button" class="btn-sm btn" id="botao-gerar-pdf"
                                                                        onclick="prepararGeracaoSeguranca('pdf')">Gerar PDF</button>
                                                                    <button type="button" class="btn-sm btn" id="botao-gerar-excel"
                                                                        onclick="prepararGeracaoSeguranca('excel')">Gerar Excel</button>
                                                                </div>
                                                    </div>
                                                </div>
                            
                        </div>
                    </form>
                </div>
            </div>
 
            <div class="card-body">
 
                <?php if (empty($segurancas)): ?>
 
                    <div class="alert alert-info mb-0">Nenhum segurança cadastrado para esta empresa.</div>
 
                <?php else: ?>
 
                    <div class="accordion" id="accordion-segurancas">
                        <?php foreach ($segurancas as $seguranca): ?>
                            <?php
                                $segId          = 'seg' . $seguranca->id;
                                $turnosSeg      = $turnos[$seguranca->id] ?? [];
                                $qtdTurnos      = count($turnosSeg);
                            ?>
 
                            <div class="accordion-item" data-seguranca-nome="<?= htmlspecialchars($seguranca->nome ?? 'Segurança #' . $seguranca->id) ?>">
                                <h2 class="accordion-header" id="heading-<?= $segId ?>">
                                    <button class="accordion-button collapsed" type="button"
                                    style="color:black;"
                                            data-bs-toggle="collapse" data-bs-target="#collapse-<?= $segId ?>"
                                            aria-expanded="false" aria-controls="collapse-<?= $segId ?>">
                                        <?= htmlspecialchars($seguranca->nome ?? 'Segurança #' . $seguranca->id) ?>
                                        <span class="badge bg-secondary ms-2"><?= $qtdTurnos ?> turno<?= $qtdTurnos == 1 ? '' : 's' ?></span>
                                    </button>
                                </h2>
                                <div id="collapse-<?= $segId ?>" class="accordion-collapse collapse"
                                     data-bs-parent="#accordion-segurancas">
                                    <div class="accordion-body">
 
                                        <?php if (empty($turnosSeg)): ?>
 
                                            <p class="text-muted mb-0">Nenhum turno registrado para este segurança.</p>
 
                                        <?php else: ?>
 
                                            <div class="accordion" id="accordion-turnos-<?= $segId ?>">
                                                <?php foreach ($turnosSeg as $turno): ?>
                                                    <?php
                                                        $turnoId  = $segId . '-turno' . $turno->id;
 
                                                        $listaPontos  = $pontos[$seguranca->id][$turno->id] ?? [];
                                                        $listaPanicos = $panicos[$seguranca->id][$turno->id] ?? [];
                                                        $listaRondas  = $rondas[$seguranca->id][$turno->id]  ?? [];
                                                        $ocorrenciaTurno = $ocorrencias[$seguranca->id][$turno->id] ?? null;
 
                                                        $inicioFmt = !empty($turno->started_at)
                                                            ? (new DateTime($turno->started_at))->modify('-3 hours')->format('d/m/Y H:i')
                                                            : '—';
                                                        $fimFmt = !empty($turno->ended_at)
                                                            ? (new DateTime($turno->ended_at))->modify('-3 hours')->format('d/m/Y H:i')
                                                            : 'Em Andamento';
                                                        $inicioHora = !empty($turno->started_at)
                                                            ? (new DateTime($turno->started_at))->modify('-3 hours')->format('H:i')
                                                            : '—';
                                                        $fimHora = !empty($turno->ended_at)
                                                            ? (new DateTime($turno->ended_at))->modify('-3 hours')->format('H:i')
                                                            : null;
                                                    ?>
 
                                                    <div class="accordion-item"
                                                         data-turno-inicio="<?= htmlspecialchars($inicioFmt) ?>"
                                                         data-turno-fim="<?= htmlspecialchars($fimFmt !== 'Em Andamento' ? $fimFmt : 'Em andamento') ?>"
                                                         data-pontos="<?= count($listaPontos) ?>"
                                                         data-panicos="<?= count($listaPanicos) ?>"
                                                         data-rondas="<?= count($listaRondas) ?>">
                                                        <h2 class="accordion-header" id="heading-<?= $turnoId ?>">
                                                            <button class="accordion-button collapsed" type="button"
                                                            style="color:black;"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapse-<?= $turnoId ?>"
                                                                    aria-expanded="false" aria-controls="collapse-<?= $turnoId ?>">
                                                                
                                                                <i class="bi bi-clock-history me-2"></i>
                                                                <?= htmlspecialchars($inicioFmt) ?>
                                                                <?= $fimFmt !== 'Em Andamento' ? ' até ' . htmlspecialchars($fimFmt) : ' - Em andamento' ?>

                                                                <span class="badge bg-info ms-2"><?= count($listaPontos) ?></span>
                                                                <span class="badge bg-danger ms-2"><?= count($listaPanicos) ?></span>
                                                                <span class="badge bg-primary ms-2"><?= count($listaRondas) ?></span>

                                                            </button>
                                                        </h2>
                                                        <div id="collapse-<?= $turnoId ?>" class="accordion-collapse collapse"
                                                             data-bs-parent="#accordion-turnos-<?= $segId ?>">
                                                            <div class="accordion-body">
 
                                                                <div class="accordion" id="accordion-cat-<?= $turnoId ?>">

                                                                    <!-- Mostrar Data e hora de inicio e fim-->

                                                                    <div class="badge bg-secondary w-100 mb-3 text-start" style="font-size: 1.5em;">
                                                                        Inicio:<?= htmlspecialchars($inicioFmt) ?>
                                                                        <br>
                                                                        <?= $fimFmt !== 'Em Andamento' ? 'Fim:' . htmlspecialchars($fimFmt) : ' Em andamento' ?>
                                                                    </div>

                                                                    <?php if ($ocorrenciaTurno !== null): ?>
                                                                        <div class="alert alert-primary mb-3" role="alert">
                                                                            <strong>Ocorrência:</strong>
                                                                            <?= htmlspecialchars($ocorrenciaTurno->texto ?? '') ?>
                                                                        </div>
                                                                    <?php endif; ?>

 
                                                                    <!-- ── Pontos ──────────────────────────────────── -->
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="heading-ponto-<?= $turnoId ?>">
                                                                            <button class="accordion-button collapsed" type="button"
                                                                            style="color:black;"
                                                                                    data-bs-toggle="collapse" data-bs-target="#collapse-ponto-<?= $turnoId ?>"
                                                                                    aria-expanded="false" aria-controls="collapse-ponto-<?= $turnoId ?>">
                                                                                <i class="bi bi-geo-alt-fill me-2 text-info"></i>
                                                                                Pontos
                                                                                <span class="badge bg-secondary ms-2"><?= count($listaPontos) ?></span>
                                                                            </button>
                                                                        </h2>
                                                                        <div id="collapse-ponto-<?= $turnoId ?>" class="accordion-collapse collapse"
                                                                             data-bs-parent="#accordion-cat-<?= $turnoId ?>">
                                                                            <div class="accordion-body">
                                                                                <?php if (empty($listaPontos)): ?>
                                                                                    <p class="text-muted mb-0">Nenhum ponto registrado neste turno.</p>
                                                                                <?php else: ?>
                                                                                    <table class="table table-striped table-bordered">
                                                                                        <thead>
                                                                                            <th>Horário</th>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?php foreach ($listaPontos as $ponto): ?>
                                                                                                <?php
                                                                                                    $pontoNoPrazo = $pontoDentroDoPrazo($ponto, $controles);
                                                                                                    $horaPonto = $ponto->hora ?? $ponto->created_at;
                                                                                                ?>
                                                                                                <tr>
                                                                                                    <td class="<?= $pontoNoPrazo ? 'table-success' : 'table-danger' ?>">
                                                                                                        <span class="d-flex justify-content-between align-items-center">
                                                                                                            <span>
                                                                                                                <?= !empty($horaPonto) && strtotime($horaPonto) !== false
                                                                                                                ? (new DateTime($horaPonto))->modify('-3 hours')->format('d/m/Y H:i')
                                                                                                                : (new DateTime($horaPonto))->modify('-3 hours')->format('d/m/Y H:i') ?>
                                                                                                            </span>
                                                                                                            <strong><?= $pontoNoPrazo ? 'Dentro do prazo' : 'Fora do prazo' ?></strong>
                                                                                                        </span>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            <?php endforeach; ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
 
                                                                    <!-- ── Pânicos ─────────────────────────────────── -->
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="heading-panico-<?= $turnoId ?>">
                                                                            <button class="accordion-button collapsed" type="button"
                                                                            style="color:black;"
                                                                                    data-bs-toggle="collapse" data-bs-target="#collapse-panico-<?= $turnoId ?>"
                                                                                    aria-expanded="false" aria-controls="collapse-panico-<?= $turnoId ?>">
                                                                                <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
                                                                                Pânicos
                                                                                <span class="badge bg-secondary ms-2"><?= count($listaPanicos) ?></span>
                                                                            </button>
                                                                        </h2>
                                                                        <div id="collapse-panico-<?= $turnoId ?>" class="accordion-collapse collapse"
                                                                             data-bs-parent="#accordion-cat-<?= $turnoId ?>">
                                                                            <div class="accordion-body">
                                                                                <?php if (empty($listaPanicos)): ?>
                                                                                    <p class="text-muted mb-0">Nenhum acionamento de pânico neste turno.</p>
                                                                                <?php else: ?>
                                                                                    <ul class="list-group">
                                                                                        <?php foreach ($listaPanicos as $panico): ?>
                                                                                            <li class="list-group-item">
                                                                                                <div class="d-flex justify-content-between align-items-start">
                                                                                                    <span>
                                                                                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                                                                                        Acionamento de pânico
                                                                                                    </span>
                                                                                                    <small class="text-muted ms-2">
                                                                                                        <?= !empty($panico->created_at)
                                                                                                            ? htmlspecialchars((new DateTime($panico->created_at))->modify('-3 hours')->format('d/m/Y H:i'))
                                                                                                            : '' ?>
                                                                                                    </small>
                                                                                                </div>
                                                                                                <?php if (!empty($panico->localizacao) && $panico->localizacao !== 'Localização não informada'): 

                                                                                                    [$latitude, $longitude] = explode(',' , $panico->localizacao)
                                                                                                    ?>

                                                                                                    
                                                                                                    <a class="small"
                                                                                                       href="https://maps.google.com/?q=<?= urlencode($latitude . ',' . $longitude) ?>"
                                                                                                       target="_blank" rel="noopener">
                                                                                                        Ver localização no mapa
                                                                                                    </a>
                                                                                                <?php else: ?>
                                                                                                    <p class="small text-muted mb-0">Localização não informada.</p>
                                                                                                <?php endif; ?>
                                                                                            </li>
                                                                                        <?php endforeach; ?>
                                                                                    </ul>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
 
                                                                    <!-- ── Rondas ──────────────────────────────────── -->
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="heading-ronda-<?= $turnoId ?>">
                                                                            <button class="accordion-button collapsed" type="button"
                                                                            style="color:black;"
                                                                                    data-bs-toggle="collapse" data-bs-target="#collapse-ronda-<?= $turnoId ?>"
                                                                                    aria-expanded="false" aria-controls="collapse-ronda-<?= $turnoId ?>">
                                                                                <i class="bi bi-shield-check me-2 text-primary"></i>
                                                                                Rondas
                                                                                <span class="badge bg-secondary ms-2"><?= count($listaRondas) ?></span>
                                                                            </button>
                                                                        </h2>
                                                                        <div id="collapse-ronda-<?= $turnoId ?>" class="accordion-collapse collapse"
                                                                             data-bs-parent="#accordion-cat-<?= $turnoId ?>">
                                                                            <div class="accordion-body">
 
                                                                                <?php if (empty($listaRondas)): ?>
 
                                                                                    <p class="text-muted mb-0">Nenhuma ronda registrada neste turno.</p>
 
                                                                                <?php else: ?>
                                                                                    <table class="table table-striped table-bordered">
                                                                                        <thead>
                                                                                            <th>Descrição</th>
                                                                                            <th>Horário</th>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?php foreach ($listaRondas as $ronda): ?>
                                                                                                <tr>
                                                                                                    <td><?= htmlspecialchars($ronda->descricao ?? 'Ronda') ?></td>
                                                                                                    <td><?= !empty($ronda->hora)
                                                                                                        ? htmlspecialchars(date('d/m/Y H:i', strtotime($ronda->hora)))
                                                                                                        : htmlspecialchars($ronda->created_at ?? '') ?></td>
                                                                                                </tr>
                                                                                            <?php endforeach; ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                <?php endif; ?>
 
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- ── /Rondas ─────────────────────────────────── -->
 
                                                                </div><!-- /accordion-cat -->
 
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
 
            </div><!-- /.card-body -->
        </div><!-- /.card -->
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



//     function checar() {
//         var nome = document.querySelector('.input-nome input').value;
//         var email = document.querySelector('.input-email input').value;
//         let consultar = document.querySelector('input[name="consultar"]');
//         let processar = document.querySelector('input[name="processar"]');
        



// if (nome !== '' && email !== '' && (consultar.checked || processar.checked)) {
//   document.querySelector('button[name="acao"]').disabled = false;
// } else {
//   document.querySelector('button[name="acao"]').disabled = true;
// }
const consultar = document.querySelector('input[name="consultar"]');

const processar = document.querySelector('input[name="processar"]');

if (!consultar.checked) {
            processar.checked = false;
        }

        if (processar.checked) {
            consultar.checked = true;
        }

    // }
<?php if (isset($get_acao) && $get_acao == 'adicionar') { ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_usuario');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'index.php';
            });
        });
<?php } if(isset($erro) && $erro == 'usado') { ?>
                alert('Não é possível adicionar esse usuario, pois já existe um usuario ou gestor com esse e-mail');
                window.location.href = 'index.php';
<?php } ?>

</script>



</html>