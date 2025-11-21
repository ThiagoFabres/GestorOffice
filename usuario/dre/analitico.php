<!DOCTYPE html>
<?php

require_once __DIR__ . '/../../db/base.php';
require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/categoria.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
require_once __DIR__ . '/../../db/entities/pagar.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';
session_start();
// Função para alinhar valores monetários

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

$get_data_final = filter_input(INPUT_GET, 'data_final', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_titulo = filter_input(INPUT_GET, 'titulo') ?: null;
$get_subtitulo = null;
$get_custos = filter_input(INPUT_GET, 'filtro_custos') ?? null;
if ($get_titulo != null)
    $get_subtitulo = filter_input(INPUT_GET, 'subtitulo') ?: null;

if ($get_data_final != '' || $get_data_inicial != '') {
    // Buscar receitas
    $recebimentos_parcelas = Rec02::read(
        null,
        $_SESSION['usuario']->id_empresa,
        filtro_data_inicial: $get_data_inicial,
        filtro_data_final: $get_data_final,
        filtro_opcao: 'quitados',
        filtro_por: 'pagamento'
    );
    $receitas_por_categoria = [];
    foreach ($recebimentos_parcelas as $parcela) {
        $rec01 = Rec01::read(id: $parcela->id_rec01)[0];
        $cadastro = Cadastro::read($rec01->id_cadastro)[0] ?? null;
        if ($cadastro) {
            $categoria = Categoria::read($cadastro->id_categoria)[0] ?? null;
            $cat_nome = $categoria ? $categoria->nome : 'Sem categoria';
            if (!isset($receitas_por_categoria[$cat_nome]))
                $receitas_por_categoria[$cat_nome] = [];
            $receitas_por_categoria[$cat_nome][] = [
                'data' => $parcela->data_pag,
                'descricao' => $rec01->descricao,
                'valor' => $parcela->valor_pag
            ];
        }
    }

    // Buscar despesas
    $pagamentos_parcelas = Pag02::read(
        null,
        $_SESSION['usuario']->id_empresa,
        filtro_data_inicial: $get_data_inicial,
        filtro_data_final: $get_data_final,
        filtro_opcao: 'quitados',
        filtro_por: 'pagamento'
    );
    $despesas_por_categoria = [];
    foreach ($pagamentos_parcelas as $parcela) {
        $pag01 = Pag01::read(id: $parcela->id_pag01)[0];
        $cadastro = Cadastro::read($pag01->id_cadastro)[0] ?? null;
        if ($cadastro) {
            $categoria = Categoria::read($cadastro->id_categoria)[0] ?? null;
            $cat_nome = $categoria ? $categoria->nome : 'Sem categoria';
            if (!isset($despesas_por_categoria[$cat_nome]))
                $despesas_por_categoria[$cat_nome] = [];
            $despesas_por_categoria[$cat_nome][] = [
                'data' => $parcela->data_pag,
                'descricao' => $pag01->descricao,
                'valor' => $parcela->valor_pag
            ];
        }
    }
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
                        <button class="btn btn-primary" id="btn-sintetico" onclick="window.location.href='sintetico.php'">
                            <h3>DRE - Sintético</h3>
                        </button><!--
    --><button class="btn btn-primary btn-dre-selecionado" style="border-bottom: 2px solid #5856d6;" id="btn-analitico">
                            <h3>DRE - Analitico</h3>
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
                                                <input type="date" id="data_inicial" name="data_inicial"
                                                    value="<?= $get_data_inicial ?>" class="form-control">
                                            </div>

                                            <div>
                                                <label for="data_final">Data Final:</label>
                                                <input type="date" id="data_final" name="data_final"
                                                    value="<?= $get_data_final ?>" class="form-control">
                                            </div>
                                        </div>

                                        <div id="filtro-custos">
                                            <label for="data_final">Centro de Custos:</label>
                                            <select name="filtro_custos">
                                                <option value="">Selecione</option>
                                                <?php foreach(CentroCUstos::read(id_empresa:$_SESSION['usuario']->id_empresa) as $custo) { ?>
                                                    <option <?php if($get_custos == $custo->id){?>selected<?php } ?>  value="<?=$custo->id?>"><?=$custo->nome?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <div class="titulos-dre">
                                        <div>
                                                <label for="titulo">Titulo:</label>
                                                <div class="input-select-titulo">
                                                    <select id="input-titulo" class="input-select-geral" name="titulo" onchange="this.form.submit()">
                                                        <option value="">Selecione</option>
                                                        <?php
                                                        foreach(Con01::read(null, $_SESSION['usuario']->id_empresa) as $titulo) {?>
                                                            <option <?php if($get_titulo == $titulo->id) { ?> selected <?php } ?> value="<?=$titulo->id?>"><?=$titulo->nome?></option>
                                                        <?php } ?>
                                                        </select>

                                                </div>
                                        </div>

                                        <div id="subtitulo-dre-div">
                                        <label for="subtitulo">Sub-Titulo</label>
                                            <div id="subtitulo-dre">
                                                <select id="input-subtitulo" class="input-select-geral" name="subtitulo" class="form-control" onchange="this.form.submit()">
                                                    <option value="">  Selecione</option>
                                                    <?php
                                                    if(isset($get_titulo)) {
                                                        foreach(Con02::read(null, $_SESSION['usuario']->id_empresa, con01_id: $get_titulo) as $subtitulo) {?>
                                                            <option <?php if($get_subtitulo == $subtitulo->id) { ?> selected <?php } ?> value="<?=$subtitulo->id?>"><?=$subtitulo->nome?></option>
                                                        <?php } } ?>
                                                                                                   
                                                </select>
                                            </div>
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

                        </tbody>

                    </div>

                    </tbody>
                    <?php
                    // Buscar títulos dinâmicos (baseados nos lançamentos pagos)
                    $titulos = [];
                    $subtitulos = [];
                    if ($get_data_final != '' || $get_data_inicial != '' || $get_titulo != '' || $get_custos != '') {
                        $recebimentos_parcelas = Rec02::read(
                            null,
                            $_SESSION['usuario']->id_empresa,
                            filtro_data_inicial: $get_data_inicial,
                            filtro_data_final: $get_data_final,
                            filtro_opcao: 'quitados',
                            filtro_por: 'pagamento',
                            filtro_con01: $get_titulo,
                            filtro_con02: $get_subtitulo,
                            filtro_custos: $get_custos
                        );
                        $pagamentos_parcelas = Pag02::read(
                            null,
                            $_SESSION['usuario']->id_empresa,
                            filtro_data_inicial: $get_data_inicial,
                            filtro_data_final: $get_data_final,
                            filtro_opcao: 'quitados',
                            filtro_por: 'pagamento',
                            filtro_con01: $get_titulo,
                            filtro_con02: $get_subtitulo,
                            filtro_custos: $get_custos

                        );
                        $recebimentos = [];
                        foreach ($recebimentos_parcelas as $parcela) {
                            $recebimento = Rec01::read(id: $parcela->id_rec01)[0]->id;
                            if (!in_array($recebimento, $recebimentos)) {
                                $recebimentos[] = $recebimento;
                            }
                        }
                        $pagamentos = [];
                        foreach ($pagamentos_parcelas as $parcela) {
                            $pagamento = Pag01::read(id: $parcela->id_pag01)[0]->id;
                            if (!in_array($pagamento, $pagamentos)) {
                                $pagamentos[] = $pagamento;
                            }
                        }

                        $recebimentos_e_pagamentos = array_merge($recebimentos, $pagamentos);
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
                        foreach ($subtitulos as $subtitulo) {
                            $titulo = Con01::read($subtitulo->id_con01, $_SESSION['usuario']->id_empresa, ordenar_por: 'tipo')[0];
                            if (!in_array($titulo, $titulos)) {
                                $titulos[] = $titulo;
                            }
                        }

                        // Exibir cada título como um accordion, e dentro dele, as contas separadas por categoria
// ...existing code...
                        if (isset($titulos)) {
                            echo '<div class="accordion custom-accordion"style="border:0;" id="accordionTitulos">';
                            $total_geral = [];
                            $total_receitas = [];
                            $total_despesas = [];
                            foreach ($titulos as $i => $titulo) {
                                $collapseId = 'tituloCollapse' . $i;
                                ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTitulo<?= $i ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#<?= $collapseId ?>" aria-expanded="false"
                                            aria-controls="<?= $collapseId ?>">
                                            <span
                                                style="color: #303640; font-size:1.1em; font-weight:500;"><?= htmlspecialchars($titulo->nome); ?></span>
                                        </button>
                                    </h2>
                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse"
                                        aria-labelledby="headingTitulo<?= $i ?>">
                                        <div class="accordion-body">
                                            <?php
                                            // Buscar todos os subtítulos (Con02) desse título
                                
                                            $totais_gerais = [];

                                            $sub_idx = 1;
                                            foreach ($subtitulos as $subtitulo) {
                                                // Buscar todos os pagamentos/recebimentos desse subtítulo
                                                $parcelas = [];
                                                if ($titulo->tipo == 'D') {
                                                    $parcelas = Pag02::read(
                                                        id_empresa: $_SESSION['usuario']->id_empresa,
                                                        filtro_data_inicial: $get_data_inicial,
                                                        filtro_data_final: $get_data_final,
                                                        filtro_opcao: 'quitados',
                                                        filtro_por: 'pagamento',
                                                        filtro_con01: $titulo->id,
                                                        filtro_con02: $subtitulo->id,
                                                        filtro_custos: $get_custos
                                                    );
                                                } else {
                                                    $parcelas = Rec02::read(
                                                        id_empresa: $_SESSION['usuario']->id_empresa,
                                                        filtro_data_inicial: $get_data_inicial,
                                                        filtro_data_final: $get_data_final,
                                                        filtro_opcao: 'quitados',
                                                        filtro_por: 'pagamento',
                                                        filtro_con01: $titulo->id,
                                                        filtro_con02: $subtitulo->id,
                                                        filtro_custos: $get_custos
                                                    );
                                                }
                                                if (count($parcelas) > 0) { $total_subtitulo = 0;?>
                                                <div class="avoid-page-break">
                                                <h5 class="avoid-page-break"> <?=htmlspecialchars($subtitulo->nome)?> </h5>
                                                    
                                                    
                                                    <table class="table table-striped table-bordered avoid-page-break" style="margin: none;">
                                                        <thead>
                                                            <tr class="tr-dre-analitico">
                                                                <th>Centro de custos</th>
                                                                <th>Descrição</th>
                                                                <th>Valor</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            
                                                            foreach ($parcelas as $rec) {
                                                                $centro_custos = 'N/A';
                                                                if ($titulo->tipo == 'D') {
                                                                    $obj = Pag01::read($rec->id_pag01)[0];
                                                                    $pagamento_metodo = TipoPagamento::read($rec->id_pgto)[0] ?? null;
                                                                } else {
                                                                    $obj = Rec01::read($rec->id_rec01)[0];
                                                                    $pagamento_metodo = TipoPagamento::read($rec->id_pgto)[0] ?? null;
                                                                }
                                                                    if($obj->centro_custos != null) {
                                                                        $centro_custos = CentroCustos::read($obj->centro_custos)[0]->nome;
                                                                    }
                                                            
                                                                $metodo_nome = $pagamento_metodo ? $pagamento_metodo->nome : 'Método não encontrado';
                                                                $data = $rec->data_pag ? (new DateTime($rec->data_pag))->format('d/m/Y') : '';
                                                                $descricao = $metodo_nome . ' - ' . $data;
                                                                $valor = ($titulo->tipo == 'D') ? $rec->valor_pag * -1 : $rec->valor_pag;
                                                                $total_subtitulo += $valor;
                                                                echo '<tr class="tr-dre-analitico">';
                                                                echo '<td style="width:7rem; text-overflow: ellipsis; white-space: nowrap;">' . htmlspecialchars($centro_custos) . '</td>';
                                                                echo '<td style="width:84rem;">' . htmlspecialchars($descricao) . '</td>';
                                                                echo '<td style="width:9rem;" class="valor-monetario"><div>R$</div> <div>' . format_valor_alinhado($valor) . '</div></td>';
                                                                echo '</tr>';
                                                            }
                                                            ?>
                                                            <tbody>
                                                                <tr class="tr-dre-total">
                                                                <td style="background-color:transparent; border:none;"></td>
                                                                <td>Saldo do subtitulo:</td>
                                                                <td id="total-dre-analitico"> <div>R$</div><div><?= number_format($total_subtitulo, 2, ',') ?></div></td>
                                                                </tr>
                                                            </tbody>
                                                            
                                                            
                                                            </tbody>
                                                            </table>
                                                            </div>
                                                        <?php    
                                                            $totais_gerais[] = $total_subtitulo;
                                                }
                                                $sub_idx++;
                                            }
                                            // Exibir total geral do título
                                            if (count($totais_gerais) > 0) {
                                                $total_titulo = array_sum($totais_gerais);
                                                // echo '<div style="font-size:1.2em; margin-top:2em;" id="total-titulo-'.$i.'">Total do Titulo: R$ ' . number_format($total_titulo, 2, ',', '.') . '</div>';
                                            }
                                            echo '</div></div></div>';
                                            if ($titulo->tipo == 'D') {
                                                $total_despesas[] = $total_titulo;
                                            } else if ($titulo->tipo == 'C') {
                                                $total_receitas[] = $total_titulo;
                                            }
                                            $total_geral[] = $total_titulo;

                            }
                            echo '</div>';
                        }
                        // ...existing code...
                    
                        ?>
                                            <?php if (empty($titulos)) { ?>
                                                <div id="div-registro-vazio-dre">
                                                    <h3>Nenhum registro encontrado</h3>
                                                </div>
                                            <?php } ?>

                                </div>

                            </div>

                        </div>
                        <?php if (!empty($titulos)) { ?>
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
                        <?php } ?>

                    </div>
                </div>

            <?php } ?>
            

</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../choices/choices.js"></script>

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
    let nomeEmpresa = document.querySelector('#nome-empresa h4').innerHTML

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