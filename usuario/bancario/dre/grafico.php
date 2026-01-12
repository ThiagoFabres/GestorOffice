<!DOCTYPE html>
<?php

require_once __DIR__ . '/../../../db/base.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/categoria.php';
require_once __DIR__ . '/../../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/pagar.php';
require_once __DIR__ . '/../../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../../db/entities/banco02.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
session_start();
// Função para alinhar valores monetários

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
$lateral_bancario = true;
$lateral_target = 'dreBancario';
function format_valor_alinhado($valor) {
    $formatado = number_format($valor, 2, ',', '.');
    // 12 caracteres para alinhar valores grandes e pequenos
    $formatado = str_pad($formatado, 12, ' ', STR_PAD_LEFT);
    return $formatado;
}

$get_periodo = filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_titulo = filter_input(INPUT_GET, 'titulo') ?: null;
$filtro_tipo = filter_input(INPUT_GET, 'filtro_tipo') ?: null;

if($get_periodo != null){
$get_periodo = str_replace('%2F', '/', $get_periodo);
$primeiro_dia_periodo = DateTime::createFromFormat('d/m/Y', '1/' . $get_periodo)->format('Y-m-01');
$ultimo_dia_periodo = DateTime::createFromFormat('m/Y', $get_periodo)->format('Y-m-t');
// $get_periodo = new Datetime('first day of this month');

                        $ban02_lista = Ban02::read(id_empresa: $_SESSION['usuario']->id_empresa, filtro_data_inicial: $primeiro_dia_periodo ?? null, filtro_data_final: $ultimo_dia_periodo ?? null, dre_read:true, filtro_tipo: $filtro_tipo ?? null);
                        $lista_tabela = [];
                        foreach($ban02_lista as $ban02) {
                            $idCon = $ban02->id_con02;
                            if (!isset($lista_tabela[$idCon])) {
                                $lista_tabela[$idCon] = [
                                    'valor' => 0,
                                    'con02' => $idCon,
                                    'receitas' => 0,
                                    'despesas' => 0
                                ];
                            }
                            $valor = $ban02->valor ?? 0;
                            $lista_tabela[$idCon]['valor'] += $valor;
                            if ($valor > 0) {
                                $lista_tabela[$idCon]['receitas'] += $valor;
                            } else {
                                $lista_tabela[$idCon]['despesas'] += abs($valor);
                            }
                        }
}

$get_subtitulo = null;
$get_custos = filter_input(INPUT_GET, 'filtro_custos') ?? null;
$todas_empresas = filter_input(INPUT_GET, 'todas_empresas') == 'on' ? 1 : 0;
if ($get_titulo != null)
    $get_subtitulo = filter_input(INPUT_GET, 'subtitulo') ?: null;
if($todas_empresas) {
    $empresa = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0];
    $empresa_lista = Empresa::read(cnpj_principal: $empresa->cnpj_principal);
} else {
    $empresa_lista = Empresa::read(id: $_SESSION['usuario']->id_empresa);
}


?>







<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="../../style/dre.css">

<link rel="stylesheet" href="../../../choices/choices.css">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">

    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>
    
    <div class="main" id="container">
        <div class="row">

            <div class="col-md-12" style="padding: 0;">


                <div class="card">
                    <div class="card-header">
                        <button class="btn btn-primary dre-menu-btn" id="btn-sintetico" onclick="window.location.href='sintetico.php'"  id="btn-sintetico">
                            <h3>DRE - Sintético</h3>
                        </button>

                        <button class="btn btn-primary dre-menu-btn" onclick="window.location.href='analitico.php'"  id="btn-analitico">
                            <h3>DRE - Analitico</h3>
                        </button>

                        <button class="btn btn-primary btn-dre-selecionado dre-menu-btn" style="border-bottom: 2px solid #5856d6;"  id="btn-grafico">
                            <h3>Gráfico</h3>
                        </button>

                        <button class="btn btn-primary dre-menu-btn" onclick="window.location.href='grafico_periodo.php'" id="btn-grafico-periodo">
                            <h3>Gráfico por período</h3>
                        </button>
                    </div>

                    <div class="card-header-div">
                        <div class="card-header-borda">
                            <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                aria-labelledby="vendas-tab">
                                <h5 class="card-title">Filtros</h5>
                                <form method="get" action="grafico.php">
                                    <div class="row">
                                        <div class="inputs-dre">
                                        <div class="inputs-dre-text">
                                            <div>
                                                <label for="data_inicial">Período</label>:</label>
                                                <input type="text" id="data_inicial" placeholder="mm/aaaa" name="periodo"
                                                    value="<?= $get_periodo ?>" class="form-control">
                                            </div>
                                        <div>
                                                <label for="filtro_tipo">Tipo:</label>
                                                <select name="filtro_tipo">
                                                    <option value="">Todos</option>
                                                    <option value="C" <?php if($filtro_tipo == 'C') echo 'selected'; ?>>Receitas</option>
                                                    <option value="D" <?php if($filtro_tipo == 'D') echo 'selected'; ?>>Despesas</option>
                                                </select>
                                            </div>
                                                            

                                        </div>
                                        <div class="inputs-dre-btn" id="inputs-btn-analitico">
                                            <div class="botoes-acao">
                                                <button type="submit" class="btn-sm btn" style="background-color: #5856d6; color: white; ">Filtrar</button>
                                                <a href="analitico.php" class="btn btn-secondary btn-sm">Limpar</a>
                                            </div>

                                            
                                                <?php if ((isset($get_data_inicial) && $get_data_inicial != '') || (isset($get_data_final) && $get_data_final != '') || (isset($get_titulo) && $get_titulo != '') || (isset($get_subtitulo) && $get_subtitulo != '') || $get_custos != '') { ?>
                                                    <div class="botoes-gerar">
                                                        <button type="button" class="btn-sm btn" id="botao-gerar-pdf"
                                                            onclick="prepararGeracao('pdf')">Gerar PDF</button>
                                                        <button type="button" class="btn-sm btn" id="botao-gerar-excel"
                                                            onclick="prepararGeracao('excel')">Gerar Excel</button>
                                                    </div>
                                                <?php } ?>
                                            
                                        </div>
                                    </div>

                                    </div>
                                </form>



                            </div>
                        </div>



                    </div>
                    <div class="card-body">
                        <?php
                        if($get_periodo != null) {
                        

                        
                        ?>

                        <?php
                        // Preparar dados para o gráfico (pie ou confronto receitas/despesas)
                        $chart_labels = [];
                        $chart_data = [];
                        $chart_receitas = [];
                        $chart_despesas = [];
                        $chart_mode = empty($filtro_tipo) ? 'line' : 'pie';
                        if (!empty($lista_tabela)) {
                            foreach ($lista_tabela as $item_chart) {
                                $sub_chart = Con02::read(id: $item_chart['con02'])[0];
                                $label = $sub_chart->nome ?? ('Conta ' . ($item_chart['con02'] ?? ''));
                                $chart_labels[] = $label;
                                if ($chart_mode === 'pie') {
                                    $chart_data[] = round($item_chart['valor'] ?? 0, 2);
                                } else {
                                    $chart_receitas[] = round($item_chart['receitas'] ?? 0, 2);
                                    $chart_despesas[] = round($item_chart['despesas'] ?? 0, 2);
                                }
                            }
                        }

                        $total_tabela = 0;
                        ?>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Distribuição - Gráfico de <?php echo $filtro_tipo != null ? 'Pizza' : 'Linha'?></h5>
                                <div class="chart-container" style="max-width:100%; height:20%; margin:0 auto;">
                                    <canvas id="drePieChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($lista_tabela as $item) {?>
                                <tr>
                                    <td>

                                        <?php 
                                        $sub = Con02::read(id: $item['con02'])[0];
                                        echo $sub->nome;
                                        ?>
                                    </td>
                                    <td>
                                        <div class="valor-monetario">
                                            <div>
                                                R$
                                            </div>
                                            <div>  
                                            <?= format_valor_alinhado($item['valor']); ?>
                                            </div>
                                        </div>  
                                    </td>
                                    
                                <?php
                                $total_tabela += $item['valor'];
                                } ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-row justify-content-end">
                                        <strong style="width: 100%; text-align: end;">Total</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="valor-monetario">
                                        <strong>R$</strong><strong><?php echo format_valor_alinhado($total_tabela); ?></strong>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php } ?>
                    </div>

                    
                    </div>
                </div>
            </div>
        </div>

            

</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../../choices/choices.js"></script>

<?php
if(!isset($get_titulo)) { ?>
    <script>
    var subtituloDiv = document.getElementById('subtitulo-dre-div');
    var tituloSelect = document.getElementById('input-titulo');
    var subtituloSelect = document.getElementById('input-subtitulo');
    var subtituloDiv = document.getElementById('subtitulo-dre-div');
    var options = subtituloSelect.querySelectorAll('option');
    var divText = document.querySelector('.inputs-dre-text');
    var divChildren = divText.children
    var divBtn = document.querySelector('.inputs-dre-btn');

    subtituloDiv.style.visibility= 'hidden';
    </script>
<?php } else { ?>
    <script>
    var subtituloDiv = document.getElementById('subtitulo-dre-div');
    var tituloSelect = document.getElementById('input-titulo');
    var subtituloSelect = document.getElementById('input-subtitulo');
    var subtituloDiv = document.getElementById('subtitulo-dre-div');
    var options = subtituloSelect.querySelectorAll('option');
    var divText = document.querySelector('.inputs-dre-text');
    var divChildren = divText.children
    var divBtn = document.querySelector('.inputs-dre-btn');

     subtituloDiv.style.visibility = 'visible';


    </script>
<?php }
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var userBtn = document.getElementById('userBtn');
        var userMenu = document.getElementById('userMenu');
        if (userBtn && userMenu) {
            userBtn.onclick = function (e) {
                e.stopPropagation();
                if (userMenu.style.display === 'block') {
                    userMenu.style.display = 'none';
                } else {
                    userMenu.style.display = 'block';
                }
            };
            document.addEventListener('click', function (e) {
                if (userMenu.style.display === 'block') {
                    userMenu.style.display = 'none';
                }
            });
            userMenu.onclick = function (e) {
                e.stopPropagation();
            };
        }
    });

    function prepararGeracao(target) {
    let titulo = document.getElementById('input-titulo').options[document.getElementById('input-titulo').selectedIndex].text;
    let subtitulo = document.getElementById('input-subtitulo').options[document.getElementById('input-subtitulo').selectedIndex].text;
    let nomeEmpresa = document.querySelector('#nome-empresa h1').innerHTML

    if(subtitulo == 'Selecione' || subtitulo == null || titulo == null) {
        subtitulo = '';
    }
    if(titulo == 'Selecione' || titulo == null) {
        titulo = ''
    }
    if (subtitulo !== '' && subtitulo !== 'Selecione') {
        subtitulo = ' - ' + subtitulo;
    }
    let data_inicial = document.getElementById('data_inicial').value;
    let data_final = document.getElementById('data_final').value;
    let dataTexto = '';
    if (data_inicial !== '' && data_final !== '') {
        dataTexto = 'Período: ' + data_inicial + ' até ' + data_final;
    } else if (data_inicial !== '') {
        dataTexto = 'Data Inicial: ' + data_inicial;
    } else if (data_final !== '') {
        dataTexto = 'Data Final: ' + data_final;
    }
    if(target == 'pdf'){
         gerarpdf('analitico', dataTexto, titulo + subtitulo, nomeEmpresa);
    } else if(target == 'excel'){
        gerarexcel('analitico', dataTexto, titulo + subtitulo, nomeEmpresa);
    }
}
    
    function checarTitulo(resetSubtitulo = false) {
        var tituloSelect = document.getElementById('input-titulo');
        var tituloId = tituloSelect.value;
        var subtituloSelect = document.getElementById('input-subtitulo');
        let subtituloDiv = document.getElementById('subtitulo-dre-div');
        var options = subtituloSelect.querySelectorAll('option');
        let divText = document.querySelector('.inputs-dre-text');
        divChildren = divText.children
        let divBtn = document.querySelector('.inputs-dre-btn');

        options.forEach(function (option) {
            if (option.value === "") {
                option.style.display = '';
                return;
            }
            if (option.getAttribute('data-titulo-id') === tituloId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });

        if (tituloId == '') {
            subtituloDiv.style.visibility= 'hidden';
            // divText.style.width = 'calc(45% + 1em)';
            // divBtn.style.width = 'calc(55% - 1em)';

        //     divChildren.forEach(divChildren => {
        //     divChildren.style.width = 'calc(100%/3)'
        // });
        } else {
            subtituloDiv.style.visibility = 'visible';
            // divText.style.width = 'calc(60% + 1em)';
            // divBtn.style.width = 'calc(40% - 1em)';

        //     divChildren.forEach(divChildren => {
        //     divChildren.style.width = 'calc(100%/4)'
        // });
        }

        

        if (resetSubtitulo) {
            subtituloSelect.value = ""; // Só reseta se for troca de título
        }
        
    }

    document.getElementById('input-titulo').addEventListener('change', function () {
        checarTitulo();
    });
    checarTitulo()




    function checar() {
        let nome = document.querySelector('.input-nome input').value;
        let fantasia = document.querySelector('.input-fantasia input').value;
        let cpf = document.querySelector('.input-cpf input').value;
        let cnpj = document.querySelector('.input-cnpj input').value;
        let cep = document.querySelector('.input-cep input').value;
        let endereco = document.querySelector('.input-endereco input').value;
        let bairro = document.querySelector('.input-bairro input').value;
        let cidade = document.querySelector('.input-cidade input').value;
        let estado = document.querySelector('.input-estado input').value;
        let celular = document.querySelector('.input-celular input').value;
        let telefone = document.querySelector('.input-telefone input').value;
        let email = document.querySelector('.input-email input').value;




        if (nome !== '' && fantasia !== '' && cpf !== '' && cnpj !== '' && cep !== '' && endereco !== '' && bairro !== '' && cidade !== '' && estado !== '' && celular !== '' && telefone !== '' && email !== '') {
            document.querySelector('button[name="acao"]').disabled = false;
        } else {
            document.querySelector('button[name="acao"]').disabled = true;
        }
    }

    function encolher() {
        let barra = document.getElementById('barra-lateral');
        let container = document.getElementById('container');
        let superior = document.getElementById('header');
        let body = document.getElementById('body');






        if (barra.style.animationName === 'encolher') {

            superior.style.animationName = 'expandir-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'backwards';

            barra.style.animationName = 'expandir';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'backwards';

            container.style.animationName = 'expandir-container'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'backwards';

            body.style.animationName = 'expandir-container'
            body.style.animationDuration = '0.5s';
            body.style.animationFillMode = 'backwards';
            return;
        } else {

            superior.style.animationName = 'encolher-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'forwards';

            barra.style.animationName = 'encolher';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'forwards';

            container.style.animationName = 'encolher'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'forwards';

            body.style.animationName = 'encolher'
            body.style.animationDuration = '0.5s';
            body.style.animationFillMode = 'forwards';

        }
    }



</script>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="gerar.js"></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function(){
        const chartMode = <?php echo json_encode($chart_mode ?? 'pie'); ?>;
        const labels = <?php echo json_encode($chart_labels ?? []); ?>;
        const data = <?php echo json_encode($chart_data ?? []); ?>;
        const receitas = <?php echo json_encode($chart_receitas ?? []); ?>;
        const despesas = <?php echo json_encode($chart_despesas ?? []); ?>;

        if (chartMode === 'pie') {
            if (labels.length && data.length) {
                const ctx = document.getElementById('drePieChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                '#1a46cdff', '#15d18cff', '#15b8d1ff', '#ca9713ff', '#d12112ff', '#1c30c8ff', '#ac1a1aff', '#990ed1ff'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });
            }
        } else {
            // Linha comparando receitas x despesas por categoria
            if (labels.length && (receitas.length || despesas.length)) {
                const ctx = document.getElementById('drePieChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Receitas',
                                data: receitas,
                                borderColor: '#1cc88a',
                                backgroundColor: 'rgba(28,200,138,0.15)',
                                tension: 0.2,
                                fill: true
                            },
                            {
                                label: 'Despesas',
                                data: despesas,
                                borderColor: '#e74a3b',
                                backgroundColor: 'rgba(231,74,59,0.15)',
                                tension: 0.2,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        }
    })();
</script>




</html>