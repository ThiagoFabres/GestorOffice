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

$get_data_final = filter_input(INPUT_GET, 'data_final', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_titulo = filter_input(INPUT_GET, 'titulo') ?: null;
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
                                <form method="get" action="analitico.php">
                                    <div class="row">
                                        <div class="inputs-dre">
                                        <div class="inputs-dre-text">
                                        <div class="data-dre">
                                            <div>
                                                <label for="data_inicial">Data Inicial:</label>
                                                <input type="text" id="data_inicial" placeholder="MM/AAAA" name="data_inicial"
                                                    value="<?= $get_data_inicial ?>" class="form-control">
                                            </div>

                                            <div>
                                                <label for="data_final">Data Final:</label>
                                                <input type="date" id="data_final" name="data_final"
                                                    value="<?= $get_data_final ?>" class="form-control">
                                            </div>

                                            
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




</html>