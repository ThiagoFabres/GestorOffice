<?php

require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
/* Removido: recebimentos/pagamento/pagar - agora usamos apenas Ban02 */
require_once __DIR__ . '/../../../db/entities/banco02.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/centrocustos.php';

session_start();
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

$get_data_final = filter_input(INPUT_GET, 'data_final') ?? null;
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial') ?? null;
$get_titulo = filter_input(INPUT_GET, 'titulo') ?: null;
$get_subtitulo = null;
$get_custos = filter_input(INPUT_GET, 'filtro_custos') ?: null;
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

$lancamentos = [];
$subtitulos = [];
    $subtitulos_ids = [];
foreach($empresa_lista as $empresa) {


if ($get_data_final != '' || $get_data_inicial != '') {
    $lancamentos_empresa = Ban02::read(
    id_empresa: $empresa->id,
    filtro_data_inicial: $get_data_inicial ?? null,
    filtro_data_final: $get_data_final ?? null,
    filtro_titulo: $get_titulo ?? null,
    filtro_subtitulo: $get_subtitulo ?? null,
    dre_read: true,
);
$lancamentos[] = $lancamentos_empresa;
}
}
    // Obter subtítulos únicos (con02) usados nos lançamentos
    
    if (!empty($lancamentos)) {
        foreach ($lancamentos as $lancamentos_lista) {
            foreach($lancamentos_lista as $lan) {
            if (!empty($lan->id_con02) && !in_array($lan->id_con02, $subtitulos_ids)) {
                $subtitulos_ids[] = $lan->id_con02;
            }
        }
    }

        // carregar objetos Con02
        foreach ($subtitulos_ids as $id_con02) {
            $c2 = Con02::read($id_con02);
            if ($c2 && isset($c2[0])) {
                $subtitulos[] = $c2[0];
            }
        }
    }
    
$titulos = [];
    $titulos_ids = [];
    foreach($empresa_lista as $empresa) {


    // Obter títulos (con01) a partir dos subtítulos
    
        foreach ($subtitulos as $sub) {
            $c1 = Con01::read(
                $sub->id_con01,
                $empresa->id,
                ordenar_por: 'tipo',
                filtro_operacional: $get_operacional
            );


            if ($c1 && isset($c1[0]) && !in_array($c1[0]->id, $titulos_ids)) {
                $titulos_ids[] = $c1[0]->id;
                $titulos[] = $c1[0];
            }
        }
    }


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/../../style/dre.css">

<link rel="stylesheet" href="../../choices/choices.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>


    <div class="main" id="container">
            <div class="col-md-12" style="padding: 0;">


                <div class="card">
                    <div class="card-header">
                        <button class="btn btn-primary btn-dre-selecionado dre-menu-btn" style="border-bottom: 2px solid #5856d6;" id="btn-sintetico">
                            <h3>DRE - Sintético</h3>
                        </button>

                        <button class="btn btn-primary dre-menu-btn"  onclick="window.location.href='analitico.php'" id="btn-analitico">
                            <h3>DRE - Analitico</h3>
                        </button>

                        <button class="btn btn-primary dre-menu-btn"  onclick="window.location.href='grafico.php'" id="btn-grafico">
                            <h3>Gráfico</h3>
                        </button>

                        <button class="btn btn-primary dre-menu-btn" onclick="window.location.href='grafico_periodo.php'"  id="btn-grafico-periodo">
                            <h3>Gráfico por período</h3>
                        </button>
                    </div>
                    <div class="card-header-div">
                        <div class="card-header-borda">
                            <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                aria-labelledby="vendas-tab">
                                <h5 class="card-title">Filtros</h5>
                                <form method="get" action="sintetico.php">

                                    <div class="inputs-dre d-flex flex-row">
                                        <div class="inputs-dre-text d-flex flex-row">
                                            <div class="data-dre d-flex flex-row">
                                                <div>
                                                    <label for="data_inicial" style="font-size:90%;">Data Inicial:</label>
                                                    <input type="date" id="data_inicial" name="data_inicial"
                                                        value="<?= htmlspecialchars($get_data_inicial) ?>" class="form-control"
                                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-top-left-radius: 0.25em; border-bottom-left-radius: 0.25em;">
                                                </div>

                                                <div>
                                                    <label for="data_final" style="font-size:90%;">Data Final:</label>
                                                    <input type="date" id="data_final" name="data_final"
                                                        value="<?= htmlspecialchars($get_data_final)?>" class="form-control"
                                                        style="border-radius: 0;">
                                                </div>

                                                <div>
                                                <label for="data_final">Tipo:</label>
                                                <select class="form-control" name="filtro_operacional" style="height: 50%; border-radius: 0;">
                                                    <option value=""  <?php if($get_operacional == null)  echo 'selected' ?>>Todos</option>
                                                    <option value="1" <?php if($get_operacional == 1)  echo 'selected' ?> >Operacional</option>
                                                    <option value="2" <?php if($get_operacional == 2)  echo 'selected' ?>>Não Operacional</option>
                                                </select>
                                                </div>

                                                <div class="d-flex flex-column align-items-center">
                                                <label for="data_final">Todas as Empresas:</label>
                                                <input <?php if($todas_empresas) echo 'checked' ?> type="checkbox" name="todas_empresas">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="inputs-dre-btn" id="inputs-btn-sintetico">
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
                        </div>

                        </tbody>

                    </div>

                    <?php
                    if (($get_data_inicial != '' || $get_data_final != '') && isset($titulos) && !empty($titulos)) {
                        $total_geral = [];
                        $total_receitas = [];
                        $total_despesas = [];

                        foreach ($titulos as $i => $titulo) {
                            // filtrar subtítulos pertencentes a este título
                            $subtitulos_filtrados = [];
                            foreach ($subtitulos as $subtitulo) {
                                if ($subtitulo->id_con01 == $titulo->id) {
                                    $subtitulos_filtrados[] = $subtitulo;
                                }
                            }
                            ?>

                            <div class="accordion custom-accordion avoid-page-break" style="border: 0;">
                                <div class="accordion-item ">
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
                                            <div class="inner-accordion avoid-page-break">

                                                <table class="table table-striped table-bordered">
                                                    <thead class="avoid-page-break">
                                                        <tr class="tr-dre-sintetico avoid-page-break avoid-page-break">
                                                            <!-- <th style="width:25%;">Centro de Custos</th> -->
                                                            <th style="width:50%;">Subtitulo</th>
                                                            <th style="width:25%;">Receita</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="avoid-page-break">
                                                        <?php
                                                        $total_subtitulo = 0;
                                                        foreach ($subtitulos_filtrados as $subtitulo) {
                                                            $receita = 0;

                                                            // calcular soma absoluta dos valores dos lançamentos que têm esse id_con02
                                                            $soma_abs = 0.0;
                                                            if (!empty($lancamentos)) {
                                                                foreach ($lancamentos as $lancamentos_lista) {
                                                                    foreach($lancamentos_lista as $lan) {
                                                                    if ($lan->id_con02 == $subtitulo->id) {
                                                                        $soma_abs += abs(floatval($lan->valor));
                                                                    }
                                                                }
                                                                }
                                                            }

                                                            // garantir sinal conforme o tipo do título:
                                                            // se título é Despesa (D) exibimos número negativo; se Receita (C) exibimos positivo.
                                                            if ($titulo->tipo == 'D') {
                                                                $receita = -1 * $soma_abs;
                                                            } else {
                                                                $receita = $soma_abs;
                                                            }

                                                            $total_subtitulo += $receita;
                                                            $receita_formatada = format_valor_alinhado($receita);
                                                            ?>

                                                            <tr class="tr-dre-sintetico avoid-page-break">
                                                                <!-- <td style="width:25%;"><?=$centro_custos?></td> -->
                                                                <td style="width: 75%;"><?= htmlspecialchars($subtitulo->nome, ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td style="width: 25%;"><div class="valor-monetario d-flex flex-row justify-content-between"><div>R$</div> <div> <?= $receita_formatada?> </div></div></td>
                                                            </tr>

                                                        <?php
                                                        } // foreach subtitulos_filtrados
                                                        ?>
                                                    <tbody>
                                                    <tr class="tr-dre-total avoid-page-break">
                                                        <!-- <td></td> -->
                                                        <td style="background-color:transparent">Total do Titulo:</td>
                                                        <td id="total-dre-sintetico" style="background-color:transparent; justify-content:space-between" class="d-flex flex-row total-dre-sintetico"><div>R$ </div><div><?=number_format($total_subtitulo, 2, ',', '.') ?></div></td>
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
                        } // foreach titulos
                        ?>

                        </div>
                        <div class="card-footer avoid-page-break" style="break-inside:avoid;"id="totais-dre">
                            <?php
                            $total_geral = array_sum($total_geral);
                            $total_receitas = array_sum($total_receitas);
                            $total_despesas = array_sum($total_despesas);
                            ?>
                            <div style="margin-top:2em;" class="avoid-page-break" id="total-receitas">Total receitas: <br> R$
                                <?= number_format($total_receitas, 2, ',', '.') ?> </div>
                            <div style="margin-top:2em;" class="avoid-page-break" id="total-despesas">Total despesas: <br> R$
                                <?= number_format($total_despesas, 2, ',', '.') ?> </div>
                            <div style="margin-top:2em;" class="avoid-page-break" id="total-dre">Saldo do DRE: <br> R$
                                <?= number_format($total_geral, 2, ',', '.') ?> </div>
                        </div>

                    <?php }  ?>
                </div>
                </div> <!-- card -->
        </div>
    </div>

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
        if (!tituloSelect) return;
        var tituloId = tituloSelect.value;
        var subtituloSelect = document.getElementById('subtitulo');
        let subtituloDiv = document.getElementById('subtitulo-dre');
        var options = subtituloSelect ? subtituloSelect.querySelectorAll('option') : [];
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
            if (subtituloDiv) subtituloDiv.style.display = 'none';
        } else {
            if (subtituloDiv) subtituloDiv.style.display = 'block';
            if (divText) divText.style.width = 'calc(55% + 1em)';
            if (divBtn) divBtn.style.width = 'calc(40% + 1em)';
        }

        if (resetSubtitulo && subtituloSelect) {
            subtituloSelect.value = ""; // Só reseta se for troca de título
        }
    }

    var inputTitulo = document.getElementById('input-titulo');
    if (inputTitulo) {
        inputTitulo.addEventListener('change', function () {
            checarTitulo();
        });
        checarTitulo();
    }

    //colocar data inicial e/ou final no cabeçalho do pdf
    //colocar titulo e subtitulo no cabeçalho do pdf
    function prepararGeracao(target) {
        let data_inicial = document.getElementById('data_inicial').value;
        let data_final = document.getElementById('data_final').value;
        let nomeEmpresa = document.querySelector('#nome-empresa h1') ? document.querySelector('#nome-empresa h1').innerHTML : '';
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

  

</script>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="gerar.js"></script>

</html>
