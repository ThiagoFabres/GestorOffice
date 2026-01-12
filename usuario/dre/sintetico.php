<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
require_once __DIR__ . '/../../db/entities/pagar.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
$lateral_target = 'dre';
function format_valor_alinhado($valor) {
    $formatado = number_format($valor, 2, ',', '.');
    // 12 caracteres para alinhar valores grandes e pequenos
    $formatado = str_pad($formatado, 12, ' ', STR_PAD_LEFT);
    return $formatado;
}

$get_data_final = filter_input(INPUT_GET, 'data_final', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$get_titulo = filter_input(INPUT_GET, 'titulo') ?: null;
$get_subtitulo = null;
$get_custos = filter_input(INPUT_GET, 'filtro_custos') ?? null;
$get_operacional = filter_input(INPUT_GET, 'filtro_operacional') ?: null;
$todas_empresas = filter_input(INPUT_GET, 'todas_empresas') == 'on' ? 1 : 0;
if ($get_titulo != null)
    $get_subtitulo = filter_input(INPUT_GET, 'subtitulo') ?: null;
if($todas_empresas) {
    $empresa = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0];
    $empresa_lista = Empresa::read(cnpj_principal: $empresa->cnpj_principal);
} else {
    $empresa_lista = Empresa::read(id: $_SESSION['usuario']->id_empresa);
}
if ($get_data_inicial != '' || $get_data_final != '' || $get_custos != '' || $get_operacional != '') {
    $recebimentos = [];
    $pagamentos = [];
    foreach($empresa_lista as $empresa) {
    $recebimentos_parcelas = Rec02::read(
        id_empresa: $empresa->id,
        filtro_data_inicial: $get_data_inicial ?? null,
        filtro_data_final: $get_data_final ?? null,
        filtro_opcao: 'quitados',
        filtro_por: 'pagamento',
        filtro_custos: $get_custos,
    );
    
    foreach ($recebimentos_parcelas as $parcela) {
        $recebimentos[] = Rec01::read(id: $parcela->id_rec01)[0]->id;

    }


    $pagamentos_parcelas = Pag02::read(
        id_empresa: $empresa->id,
        filtro_data_inicial: $get_data_inicial,
        filtro_data_final: $get_data_final,
        filtro_opcao: 'quitados',
        filtro_por: 'pagamento',
        filtro_custos: $get_custos,
    );
    
    foreach ($pagamentos_parcelas as $parcela) {
        $pagamentos[] = Pag01::read(id: $parcela->id_pag01)[0]->id;

    }
}

    $recebimentos_e_pagamentos = array_merge($recebimentos, $pagamentos);

    $subtitulos = [];
    $titulos = [];
    foreach ($recebimentos_e_pagamentos as $item) {
        if (in_array($item, $recebimentos)) {
            $subtitulo = Rec01::read(id: $item)[0];
            $subtitulo = Con02::read($subtitulo->id_con02)[0];
            if (!in_array($subtitulo, $subtitulos)) {
                $subtitulos[] = $subtitulo;
            }
        } else if (in_array($item, $pagamentos)) {
            $subtitulo = Pag01::read(id: $item)[0];
            $subtitulo = Con02::read($subtitulo->id_con02)[0];
            if (!in_array($subtitulo, $subtitulos)) {
                $subtitulos[] = $subtitulo;
            }
        }
    }
    foreach($empresa_lista as $empresa) {
        foreach ($subtitulos as $subtitulo) {
            $titulo = Con01::read($subtitulo->id_con01, $empresa->id, ordenar_por: 'tipo', filtro_operacional:$get_operacional);
                            if ($titulo && isset($titulo[0]) && !in_array($titulo[0], $titulos)) {
                                $titulos[] = $titulo[0];
                            }
    }
}
}


?>


<!DOCTYPE html>




<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="../style/dre.css">
<link rel="stylesheet" href="../../choices/choices.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>


    <div class="main" id="container">
        
        <div class="row">
            <div class="col-md-12" style="padding: 0;">


                <div class="card">
                    <div class="card-header">
                        <button class="btn btn-primary btn-dre-selecionado dre-menu-btn" style="border-bottom: 2px solid #5856d6;" id="btn-sintetico">
                            <h3>DRE - Sintético</h3>
                        </button><!--
    --><button class="btn btn-primary dre-menu-btn" id="btn-analitico" onclick="window.location.href='analitico.php'">
                            <h3>DRE - Analitico</h3>
                        </button>
                    </div>

                    <div class="card-header-div">
                        <div class="card-header-borda">
                                <h5 class="card-title">Filtros</h5>
                                <form method="get" action="sintetico.php">

                                    <div class="inputs-dre">
                                        <div class="inputs-dre-text">
                                            <div class="data-dre">
                                                <div>
                                                    <label for="data_inicial" style="font-size:90%;">Data
                                                        Inicial:</label>
                                                    <input type="date" id="data_inicial" name="data_inicial"
                                                        value="<?= $get_data_inicial ?>" class="form-control"
                                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-top-left-radius: 0.25em; border-bottom-left-radius: 0.25em;">
                                                </div>

                                                <div>
                                                    <label for="data_final" style="font-size:90%;">Data Final:</label>
                                                    <input type="date" id="data_final" name="data_final"
                                                        value="<?= $get_data_final ?>" class="form-control"
                                                        style="border-radius: 0;">
                                                </div>
                                            </div>
                                             <div>
                                                <label for="data_final">Tipo:</label>
                                                <select class="form-control" name="filtro_operacional" style="height: 53%; border-radius: 0;">
                                                    <option value=""  <?php if($get_operacional == null)  echo 'selected' ?>>Todos</option>
                                                    <option value="1" <?php if($get_operacional == 1)  echo 'selected' ?> >Operacional</option>
                                                    <option value="2" <?php if($get_operacional == 2)  echo 'selected' ?>>Não Operacional</option>
                                                </select>
                                                </div>
                                                <div id="filtro-custos">
                                                <label for="data_final">C. Custos:</label>
                                                <select name="filtro_custos">
                                                    <option value="">Selecione</option>

                                                <?php foreach(CentroCustos::read(id_empresa:$_SESSION['usuario']->id_empresa) as $custo) { ?>
                                                    <option <?php if($get_custos == $custo->id){?>selected<?php } ?>  value="<?=$custo->id?>"><?=$custo->nome?></option>
                                                <?php } ?>
                                            </select>
                                                </div>
                                                <div class="d-flex flex-column align-items-center">
                                                <label for="data_final" id="input-label-todas-empresas" >Todas as Empresas:</label>
                                                <input <?php if($todas_empresas) echo 'checked' ?> type="checkbox" name="todas_empresas">
                                        </div>  
                                        

                                        </div>
                                        
                                        <div class="inputs-dre-btn">
                                            <div class="botoes-acao">
                                                <button type="submit" class="btn-sm btn" style="background-color: #5856d6; color: white; ">Filtrar</button>
                                                <a href="sintetico.php" class="btn btn-secondary btn-sm">Limpar</a>
                                            </div>
                                            <div class="botoes-gerar">
                                                <?php if (isset($get_data_inicial) || isset($get_data_inicial)) { ?>
                                                    <button type="button" class="btn-sm btn" id="botao-gerar-pdf" style=" "
                                                        onclick="prepararGeracao('pdf')">Gerar PDF</button>
                                                    <button type="button" class="btn-sm btn" id="botao-gerar-excel"
                                                        style=" " onclick="prepararGeracao('excel')">Gerar Excel</button>
                                                <?php } ?>
                                                    
                                                    
                                            </div>
                                            
                                        </div>
                                    </div>

                                </form>




                        </div>

                        </tbody>

                    </div>
                    <?php
                    if ($get_data_inicial != '' || $get_data_final != '' || $get_custos != '' || $get_operacional != '') {
                        
                        if (isset($titulos)) {
                            $total_geral = [];
                        $total_receitas = [];
                        $total_despesas = [];
                            foreach ($titulos as $i => $titulo) {
                                $subtitulos_filtrados = [];
                                foreach ($subtitulos as $subtitulo) {
                                    if ($subtitulo->id_con01 == $titulo->id) {
                                        $subtitulos_filtrados[] = $subtitulo;
                                    }
                                }

                                ?>

                                <div class="accordion custom-accordion avoid-page-break" style="border: 0;">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?= $i ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?= $i ?>" aria-expanded="false"
                                                aria-controls="collapse<?= $i ?>">
                                                <span style="color: #303640; font-size:25px; font-weight:500;">
                                                    <?php echo htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8'); ?> </span>
                                            </button>
                                        </h2>
                                        <div  id="collapse<?= $i ?>"
                                            class="accordion-collapse <?php if (!isset($con01) || $con01 != $titulo->id) { ?>collapse<?php } ?>"
                                            aria-labelledby="heading<?= $i ?>" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div class="inner-accordion">


                                                    <table class="table table-striped table-bordered">
                                                        <thead>
                                                            <tr class="tr-dre-sintetico">
                                                                <!-- <th style="width:25%;">Centro de Custos</th> -->
                                                                <th style="width:50%;">Subtitulo</th>
                                                                <th style="width:25%;">Receita</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $total_subtitulo = 0;
                                                            foreach ($subtitulos_filtrados as $subtitulo) {
                                                                $receita = 0;
                                                                if ($titulo->tipo == 'D') {

                                                                    $recebimentos_parcelas = Pag02::read(null, filtro_con02: $subtitulo->id, filtro_opcao: 'quitados', filtro_por: 'pagamento', filtro_data_inicial: $get_data_inicial, filtro_data_final: $get_data_final, filtro_custos: $get_custos);
                                                                   
                                                                } else {
                                                                    $recebimentos_parcelas = Rec02::read(null, filtro_con02: $subtitulo->id, filtro_opcao: 'quitados', filtro_por: 'pagamento', filtro_data_inicial: $get_data_inicial, filtro_data_final: $get_data_final, filtro_custos: $get_custos);
                                                                
                                                                }

                                                                

                                                                foreach ($recebimentos_parcelas as $rec) {
                                                                    if ($rec->id_con02 == $subtitulo->id) {
                                                                        $receita += $rec->valor_pag;
                                                                    }

                                                                }
                                                                
                                            
                                                                
                                                                if ($titulo->tipo == 'D') {
                                                                    $receita = $receita * (-1);
                                                                }
                                                                $total_subtitulo += $receita;
                                                                $receita_formatada = format_valor_alinhado($receita);
                                                                ?>

                                                                <tr class="tr-dre-sintetico">
                                                                    <td style="width:75%;"><?= htmlspecialchars($subtitulo->nome, ENT_QUOTES, 'UTF-8') ?></td>
                                                                    <td style="width:25%;" ><div class="valor-monetario"><div>R$</div> <div> <?=$receita_formatada?> </div></div></td>
                                                                </tr>

                                                            <?php
                                                            }
                                                            ?>
                                                            <tbody>
                                                            <tr class="tr-dre-total">
                                                                <!-- <td></td> -->
                                                                <td>Total do Titulo:</td>
                                                                <td id="total-dre-sintetico"><div>R$</div><div><?= number_format($total_subtitulo, 2, ',', '.') ?></div></td>
                                                            </tr>
                                                            </tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    <?php
                                    if ($titulo->tipo == 'D') {
                                        $total_despesas[] = $total_subtitulo;
                                    } else if ($titulo->tipo == 'C') {
                                        $total_receitas[] = $total_subtitulo;
                                    }

                                    $total_geral[] = $total_subtitulo;
                            } ?>
                      </div>
    <div class="card-footer" id="totais-dre">
                <?php
                $total_geral = array_sum($total_geral);
                $total_receitas = array_sum($total_receitas);
                $total_despesas = array_sum($total_despesas);
                ?>
                <div style="margin-top:2em;" id="total-receitas">Total receitas: <br> R$
                    <?= number_format($total_receitas, 2, ',', '.') ?> </div>
                <div style="margin-top:2em;" id="total-despesas">Total despesas: <br> R$
                    <?= number_format($total_despesas, 2, ',', '.') ?> </div>
                <div style="margin-top:2em;" id="total-dre">Saldo do DRE: <br> R$
                    <?= number_format($total_geral, 2, ',', '.') ?> </div>
            </div>
    </div> 
                <?php }  } ?>
    
</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../choices/choices.js"></script>

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

    function checarTitulo(resetSubtitulo = false) {
        var tituloSelect = document.getElementById('input-titulo');
        var tituloId = tituloSelect.value;
        var subtituloSelect = document.getElementById('subtitulo');
        let subtituloDiv = document.getElementById('subtitulo-dre');
        var options = subtituloSelect.querySelectorAll('option');
        let divText = document.querySelector('.inputs-dre-text');
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
            subtituloDiv.style.display = 'none';
        } else {
            subtituloDiv.style.display = 'block';
            divText.style.width = 'calc(55% + 1em)';
            divBtn.style.width = 'calc(40% + 1em)';
        }

        if (resetSubtitulo) {
            subtituloSelect.value = ""; // Só reseta se for troca de título
        }
    }

    document.getElementById('input-titulo').addEventListener('change', function () {
        checarTitulo();
    });
    checarTitulo()


//colocar data inicial e/ou final no cabeçalho do pdf
//colocar titulo e subtitulo no cabeçalho do pdf
function prepararGeracao(target) {
    let data_inicial = document.getElementById('data_inicial').value;
    let data_final = document.getElementById('data_final').value;
    let nomeEmpresa = document.querySelector('#nome-empresa h1').innerHTML
    let dataTexto = '';
    if (data_inicial !== '' && data_final !== '') {
        dataTexto = 'Período: ' + data_inicial + ' até ' + data_final;
    } else if (data_inicial !== '') {
        dataTexto = 'Data Inicial: ' + data_inicial;
    } else if (data_final !== '') {
        dataTexto = 'Data Final: ' + data_final;
    }
    if(target == 'pdf') {
        gerarpdf('sintetico', dataTexto, null, nomeEmpresa);
    } else if(target == 'excel') {
        gerarexcel('sintetico', dataTexto, null, nomeEmpresa);
    }
}


    // function checar() {
    //     let nome = document.querySelector('.input-nome input').value;
    //     let fantasia = document.querySelector('.input-fantasia input').value;
    //     let cpf = document.querySelector('.input-cpf input').value;
    //     let cnpj = document.querySelector('.input-cnpj input').value;
    //     let cep = document.querySelector('.input-cep input').value;
    //     let endereco = document.querySelector('.input-endereco input').value;
    //     let bairro = document.querySelector('.input-bairro input').value;
    //     let cidade = document.querySelector('.input-cidade input').value;
    //     let estado = document.querySelector('.input-estado input').value;
    //     let celular = document.querySelector('.input-celular input').value;
    //     let telefone = document.querySelector('.input-telefone input').value;
    //     let email = document.querySelector('.input-email input').value;




    //     if (nome !== '' && fantasia !== '' && cpf !== '' && cnpj !== '' && cep !== '' && endereco !== '' && bairro !== '' && cidade !== '' && estado !== '' && celular !== '' && telefone !== '' && email !== '') {
    //         document.querySelector('button[name="acao"]').disabled = false;
    //     } else {
    //         document.querySelector('button[name="acao"]').disabled = true;
    //     }
    // }

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