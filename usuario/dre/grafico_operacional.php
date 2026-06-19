<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';

session_start();
$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];
if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']->cargo != 3 ||
    $_SESSION['usuario']->permissao_bancario != 1 ||
    $empresa_usuario_obj->permissao_bancario != 1
) {
    header('Location: /');
    exit;
}

$lateral_financeiro = true;
$lateral_target  = 'dre';
$dre_target      = 'grafico_operacional';

// ─── Filtros GET ─────────────────────────────────────────────────────────────
$get_ano         = filter_input(INPUT_GET, 'ano')               ?: date('Y');
$get_operacional = filter_input(INPUT_GET, 'filtro_operacional') ?: null;
$get_tipo        = filter_input(INPUT_GET, 'filtro_tipo')        ?: 'C';
$get_todas_emp   = filter_input(INPUT_GET, 'todas_empresas') == 'on' ? 1 : 0;

// ─── Empresas ────────────────────────────────────────────────────────────────
$empresa_sessao = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0];

if ($get_todas_emp == '1') {
    $empresa_lista = Empresa::read(cnpj_principal: $empresa_sessao->cnpj_principal);
} else {
    $empresa_lista = [$empresa_sessao];
}

$meses_labels = [
    1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',
    7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez',
];

// Paleta de cores para múltiplas empresas
$paleta = [
    ['border'=>'#5856d6','bg'=>'rgba(88,86,214,0.15)'],
    ['border'=>'#e74c3c','bg'=>'rgba(231,76,60,0.15)'],
    ['border'=>'#27ae60','bg'=>'rgba(39,174,96,0.15)'],
    ['border'=>'#f39c12','bg'=>'rgba(243,156,18,0.15)'],
    ['border'=>'#2980b9','bg'=>'rgba(41,128,185,0.15)'],
    ['border'=>'#8e44ad','bg'=>'rgba(142,68,173,0.15)'],
    ['border'=>'#16a085','bg'=>'rgba(22,160,133,0.15)'],
    ['border'=>'#d35400','bg'=>'rgba(211,84,0,0.15)'],
];

// ─── Estrutura de dados: por empresa → por mês ───────────────────────────────
// $dados_empresa[i] = ['nome' => ..., 'valores' => [1..12 => float]]
$dados_empresa = [];
$total_geral   = array_fill(1, 12, 0.0);

foreach ($empresa_lista as $i => $empresa) {
    $dados_empresa[$i] = [
        'nome'   => $empresa->nom_fant ?? $empresa->razao_soc ?? 'Empresa ' . ($i+1),
        'valores'=> array_fill(1, 12, 0.0),
    ];

    for ($mes = 1; $mes <= 12; $mes++) {
        $data_inicial = sprintf('%04d-%02d-01', $get_ano, $mes);
        $data_final   = date('Y-m-t', strtotime($data_inicial));

        $total = Rec02::read(
            id_empresa:          $empresa->id,
            filtro_data_inicial: $data_inicial,
            filtro_data_final:   $data_final,
            filtro_operacional:  $get_operacional,
            filtro_por:'pagamento',
            filtro_opcao:'quitados',
            read_total:          true
        );

        $valor = (float)($total ?? 0);
        $dados_empresa[$i]['valores'][$mes] = $valor;
        $total_geral[$mes] += $valor;
    }
}

// ─── Montar datasets JSON para Chart.js ──────────────────────────────────────
$datasets = [];

if (count($empresa_lista) === 1) {
    // Uma empresa: gradiente azul com área preenchida
    $cor = $paleta[0];
    $datasets[] = [
        'label'           => $dados_empresa[0]['nome'],
        'data'            => array_values($dados_empresa[0]['valores']),
        'borderColor'     => $cor['border'],
        'backgroundColor' => $cor['bg'],
        'borderWidth'     => 2,
        'pointRadius'     => 4,
        'pointHoverRadius'=> 6,
        'fill'            => true,
        'tension'         => 0.3,
    ];
} else {
    // Múltiplas empresas: uma linha por empresa + linha de total geral
    foreach ($dados_empresa as $i => $de) {
        $cor = $paleta[$i % count($paleta)];
        $datasets[] = [
            'label'           => $de['nome'],
            'data'            => array_values($de['valores']),
            'borderColor'     => $cor['border'],
            'backgroundColor' => $cor['bg'],
            'borderWidth'     => 2,
            'pointRadius'     => 4,
            'pointHoverRadius'=> 6,
            'fill'            => false,
            'tension'         => 0.3,
        ];
    }
    // Linha de total geral
    $datasets[] = [
        'label'           => 'Total Geral',
        'data'            => array_values($total_geral),
        'borderColor'     => '#343434',
        'backgroundColor' => 'rgba(52,52,52,0.08)',
        'borderWidth'     => 2,
        'borderDash'      => [6, 3],
        'pointRadius'     => 4,
        'pointHoverRadius'=> 6,
        'fill'            => false,
        'tension'         => 0.3,
    ];
}

$chart_labels   = json_encode(array_values($meses_labels));
$chart_datasets = json_encode($datasets);

// Tipo de label para o título do gráfico
$tipo_label = $get_tipo === 'D' ? 'Despesas' : 'Receitas';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="/../../style/dre.css">
    <link rel="stylesheet" href="../../choices/choices.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
    <title>Gestor Office Control</title>
    <style>
        .grafico-wrap {
            position: relative;
            width: 100%;
            min-height: 400px;
        }
        .card-totais {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .card-total-empresa {
            background: #f8f8ff;
            border-left: 4px solid #5856d6;
            border-radius: 6px;
            padding: 10px 18px;
            min-width: 180px;
            flex: 1;
        }
        .card-total-empresa .label {
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .card-total-empresa .valor {
            font-size: 1.1rem;
            font-weight: 700;
            color: #343434;
        }
        .card-total-empresa.total-geral {
            background: #343434;
            border-left-color: #5856d6;
        }
        .card-total-empresa.total-geral .label,
        .card-total-empresa.total-geral .valor {
            color: #fff;
        }
    </style>
</head>
<body id="body">

    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php' ?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>

    <div class="main" id="container">
        <div class="col-md-12" style="padding: 0;">
            <div class="card">
                <?php require_once __DIR__ . '/../../componentes/bancario/dre-header.php'; ?>

                <!-- ── Filtros ──────────────────────────────────────────── -->
                <div class="card-header-div">
                    <div class="card-header-borda">
                        <h5 class="card-title">Filtros</h5>
                        <form method="get" action="grafico_operacional.php">
                            <div class="d-flex flex-row">
                                <div class="inputs-dre d-flex flex-row flex-wrap gap-2 align-items-end">
                                    <div class="d-flex flex-row">
                                        <div>
                                            <label for="ano" style="font-size:90%;">Ano:</label>
                                            <input type="number" id="ano" name="ano"
                                                value="<?= htmlspecialchars($get_ano) ?>"
                                                min="2000" max="2099" step="1"
                                                class="form-control rounded-0">
                                        </div>
                                        <div>
                                            <label for="filtro_tipo" style="font-size:90%;">Tipo:</label>
                                            <select id="filtro_tipo" name="filtro_tipo" class="form-control rounded-0">
                                                <option value="C" <?= $get_tipo === 'C' ? 'selected' : '' ?>>Receitas</option>
                                                <option value="D" <?= $get_tipo === 'D' ? 'selected' : '' ?>>Despesas</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="filtro_operacional" style="font-size:90%;">Operacional:</label>
                                            <select id="filtro_operacional" name="filtro_operacional" class="form-control rounded-0">
                                                <option value=""  <?= $get_operacional === null ? 'selected' : '' ?>>Todos</option>
                                                <option value="1" <?= $get_operacional === '1'  ? 'selected' : '' ?>>Operacional</option>
                                                <option value="2" <?= $get_operacional === '2'  ? 'selected' : '' ?>>Não Operacional</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-center">
                                        <label id="input-label-todas-empresas">Todas as Empresas:</label>
                                        <input <?php if($get_todas_emp == '1') echo 'checked' ?> type="checkbox" name="todas_empresas">
                                    </div>
                                </div>
                                <div class="d-flex align-items-end gap-2" style="padding-bottom:2px;">
                                    <button type="submit" class="btn btn-sm" style="background-color:#5856d6; color:white;">Filtrar</button>
                                    <a href="grafico_operacional.php" class="btn btn-secondary btn-sm">Limpar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- ── /Filtros ─────────────────────────────────────────── -->

                <div class="card-body">

                    <!-- ── Cards de totais ─────────────────────────────── -->
                    <div class="card-totais">
                        <?php
                        $paleta_idx = 0;
                        foreach ($dados_empresa as $i => $de):
                            $cor_borda = $paleta[$i % count($paleta)]['border'];
                            $total_emp = array_sum($de['valores']);
                        ?>
                        <div class="card-total-empresa" style="border-left-color: <?= $cor_borda ?>;">
                            <div class="label"><?= htmlspecialchars($de['nome']) ?></div>
                            <div class="valor">R$ <?= number_format($total_emp, 2, ',', '.') ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($empresa_lista) > 1): ?>
                        <div class="card-total-empresa total-geral">
                            <div class="label">Total Geral <?= $get_ano ?></div>
                            <div class="valor">R$ <?= number_format(array_sum($total_geral), 2, ',', '.') ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── Gráfico ─────────────────────────────────────── -->
                    <div class="grafico-wrap">
                        <canvas id="graficoAnual"></canvas>
                    </div>

                </div><!-- /.card-body -->
            </div><!-- /.card -->
        </div>
    </div>

<?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../choices/choices.js"></script>

<script>
(function () {
    const labels   = <?= $chart_labels ?>;
    const datasets = <?= $chart_datasets ?>;

    // Se dataset tem borderDash (Total Geral), aplica como propriedade nativa
    datasets.forEach(function(ds) {
        if (ds.borderDash) {
            ds.borderDash = ds.borderDash;
        }
    });

    const ctx = document.getElementById('graficoAnual').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: <?= count($empresa_lista) > 1 ? 'true' : 'false' ?>,
                    position: 'top',
                    labels: { font: { size: 12 }, padding: 16 }
                },
                title: {
                    display: true,
                    text: '<?= $tipo_label ?> Mensais — <?= $get_ano ?>',
                    font: { size: 14, weight: 'bold' },
                    padding: { bottom: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const val = ctx.parsed.y;
                            return ' ' + ctx.dataset.label + ': R$ ' +
                                val.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        font: { size: 11 },
                        callback: function(val) {
                            return 'R$ ' + val.toLocaleString('pt-BR', {minimumFractionDigits: 0});
                        }
                    }
                }
            }
        }
    });

    /* ── userMenu ───────────────────────────────────── */
    var userBtn  = document.getElementById('userBtn');
    var userMenu = document.getElementById('userMenu');
    if (userBtn && userMenu) {
        userBtn.onclick = function(e) {
            e.stopPropagation();
            userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
        };
        document.addEventListener('click', function() {
            if (userMenu.style.display === 'block') userMenu.style.display = 'none';
        });
        userMenu.onclick = function(e) { e.stopPropagation(); };
    }
})();
</script>

</body>
</html>