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
require_once __DIR__ . '/../../db/entities/empresas.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$lateral_target = 'dre';

function format_valor_alinhado($valor) {
    $formatado = number_format($valor, 2, ',', '.');
    $formatado = str_pad($formatado, 12, ' ', STR_PAD_LEFT);
    return $formatado;
}

$get_data_final = filter_input(INPUT_GET, 'data_final', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_custos = filter_input(INPUT_GET, 'filtro_custos') ?? null;
$get_titulo = filter_input(INPUT_GET, 'filtro_titulo') ?? null;
$get_subtitulo = filter_input(INPUT_GET, 'filtro_subtitulo') ?? null;
$get_descricao = filter_input(INPUT_GET, 'filtro_descricao') ?? null;
$todas_empresas = filter_input(INPUT_GET, 'todas_empresas') == 'on' ? 1 : 0;

if($todas_empresas) {
    $empresa = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0];
    $empresa_lista = Empresa::read(cnpj_principal: $empresa->cnpj_principal);
} else {
    $empresa_lista = Empresa::read(id: $_SESSION['usuario']->id_empresa);
}

// Buscar totais por tipo de pagamento apenas do Contas a Receber (Rec)
$totais_tipo_pagamento = [];
    foreach($empresa_lista as $empresa) {
        $recebimentos_quitados = Rec02::read(
            id_empresa: $empresa->id,
            filtro_data_inicial: $get_data_inicial,
            filtro_data_final: $get_data_final,
            filtro_por: 'pagamento',
            filtro_opcao: 'quitados',
            filtro_custos: $get_custos,
            filtro_descricao: $get_descricao
        );
        // Processar apenas recebimentos
        foreach($recebimentos_quitados as $rec) {
            if(!$rec->id_pgto) continue;
            if($get_titulo || $get_subtitulo) {
                $obj_principal = Rec01::read($rec->id_rec01)[0];
                $id_titulo = $obj_principal->id_con01 ?? 0;
                $id_subtitulo = $obj_principal->id_con02 ?? 0;
                if (($get_titulo && $id_titulo != $get_titulo) || ($get_subtitulo && $id_subtitulo != $get_subtitulo)) {
                    continue;
                }
            }
            if (!isset($totais_tipo_pagamento[$rec->id_pgto])) {
                $totais_tipo_pagamento[$rec->id_pgto] = ['total' => 0];
            }
            $totais_tipo_pagamento[$rec->id_pgto]['total'] += $rec->valor_pag;
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
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.0.2/jspdf.plugin.autotable.min.js" integrity="sha512-JizZOUNesiGhMcp9fsA/9W31FOat6QysBM8hSj6ir8iIANIUJ2mhko7Lo1+j0ErftmJ8SebMZLm9iielKjeIEQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="../style/dre.css">

<link rel="stylesheet" href="../../choices/choices.css">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office - Tipo de Pagamento</title>
</head>

<body id="body">
    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>
    
    <div class="main" id="container">
        <div class="col-md-12" style="padding: 0;">
            <div class="card">
                <div class="card-header">
                    <button class="btn btn-primary dre-menu-btn" id="btn-sintetico" onclick="window.location.href='sintetico.php'">
                        <h3>DRE - Sintético</h3>
                    </button>
                    <button class="btn btn-primary dre-menu-btn" id="btn-analitico" onclick="window.location.href='analitico.php'">
                        <h3>DRE - Analitico</h3>
                    </button>
                    <button class="btn btn-primary dre-menu-btn btn-dre-selecionado" id="btn-pagamento" style="border-bottom: 2px solid #5856d6;">
                        <h3>Tipo de Pagamento</h3>
                    </button>
                </div>

                <div class="card-header-div">
                    <div class="card-header-borda">
                        <h5 class="card-title">Filtros</h5>
                        <form method="get" action="pagamento.php">
                            <div class="inputs-dre">
                                <div class="inputs-dre-text">
                                    <div class="d-flex flex-row">
                                        <div class="w-100">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex flex-row">
                                                    <div>
                                                        <label for="data_inicial" style="font-size:90%;">Data Inicial:</label>
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
                                                
                                                    <div id="filtro-custos">
                                                        <label for="filtro_custos">C. Custos:</label>
                                                        <select name="filtro_custos" class="form-control">
                                                            <option value="">Selecione</option>
                                                            <?php foreach(CentroCustos::read(id_empresa:$_SESSION['usuario']->id_empresa) as $custo) { ?>
                                                                <option <?php if($get_custos == $custo->id){?>selected<?php } ?>  value="<?=$custo->id?>"><?=$custo->nome?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-row">
                                                <div>
                                                    <label for="filtro_titulo">Título:</label>
                                                    <select name="filtro_titulo" class="form-control" onchange="this.form.submit()">
                                                        <option value="">Selecione</option>
                                                        <?php foreach(Con01::read(idempresa:$_SESSION['usuario']->id_empresa, tipo: 'C') as $titulo) { ?>
                                                            <option <?php if($get_titulo == $titulo->id){?>selected<?php } ?> value="<?=$titulo->id?>"><?=$titulo->nome?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="filtro_subtitulo">Subtítulo:</label>
                                                    <select name="filtro_subtitulo" class="form-control" onchange="this.form.submit()">
                                                        <option value="">Selecione</option>
                                                        <?php if($get_titulo) { foreach(Con02::read(idempresa:$_SESSION['usuario']->id_empresa, con01_id:$get_titulo) as $subtitulo) { ?>
                                                            <option <?php if($get_subtitulo == $subtitulo->id){?>selected<?php } ?> value="<?=$subtitulo->id?>"><?=$subtitulo->nome?></option>
                                                        <?php }} ?>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="filtro_descricao">Descrição:</label>
                                                    <input type="text" name="filtro_descricao" class="form-control rounded-0" placeholder="Descrição" value="<?= $get_descricao ?>">
                                                </div>
                                                </div>
                                            </div>
                                        
                                        </div>
                                        <div class="d-flex flex-column align-items-center" style="margin-left: 1em;">
                                            <label for="data_final" id="input-label-todas-empresas">Todas as Empresas:</label>
                                            <input <?php if($todas_empresas) echo 'checked' ?> type="checkbox" name="todas_empresas">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="inputs-dre-btn">
                                    <div class="botoes-acao">
                                        <button type="submit" class="btn-sm btn" style="background-color: #5856d6; color: white;">Filtrar</button>
                                        <a href="pagamento.php" class="btn btn-secondary btn-sm">Limpar</a>
                                    </div>
                                    <div class="botoes-gerar">
                                        <?php if (!empty($totais_tipo_pagamento)) { ?>
                                            <button type="button" class="btn-sm btn" id="botao-gerar-pdf" onclick="prepararGeracao('pdf')">Gerar PDF</button>
                                            <button type="button" class="btn-sm btn" id="botao-gerar-excel" onclick="prepararGeracao('excel')">Gerar Excel</button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabela de Totais por Tipo de Pagamento (Contas a Receber) -->
                <?php
$total_geral = 0;

if (!empty($totais_tipo_pagamento)) {
    ?>
    <table class="table table-striped table-bordered" id="table-pagamento-pdf">
        <thead>
            <tr>
                <th>Tipo de Pagamento</th>
                <th>Total Recebido</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($totais_tipo_pagamento as $id_tipo_pag => $totais) {
                $tipo_pag_obj = TipoPagamento::read($id_tipo_pag)[0];
                $total = $totais['total'];
                
                $total_geral += $total;
                ?>
                <tr>
                    <td><?= htmlspecialchars($tipo_pag_obj->nome) ?></td>
                    <td>
                        <div class="valor-monetario">
                            <div>R$</div>
                            <div><?= format_valor_alinhado($total) ?></div>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align:right;"><strong>Total Geral:</strong></td>
                <td>
                    <div class="valor-monetario">
                        <div>R$</div>
                        <div><?= format_valor_alinhado($total_geral) ?></div>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
    <?php
}
                ?>
            </div>
            
            <?php if (!empty($totais_tipo_pagamento)) { ?>
            <div class="card-footer" id="totais-dre">
                <div style="margin-top:2em;" id="total-dre">Total Recebido: <br> R$
                    <?= number_format($total_geral, 2, ',', '.') ?> </div>
            </div>
            <?php }  ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?>
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

    function prepararGeracao(target) {
        let data_inicial = document.getElementById('data_inicial').value;
        let data_final = document.getElementById('data_final').value;
        let nomeEmpresa = document.querySelector('#nome-empresa h1').innerHTML;
        let dataTexto = '';
        
        if (data_inicial !== '' && data_final !== '') {
            dataTexto = 'Período: ' + data_inicial + ' até ' + data_final;
        } else if (data_inicial !== '') {
            dataTexto = 'Data Inicial: ' + data_inicial;
        } else if (data_final !== '') {
            dataTexto = 'Data Final: ' + data_final;
        }
        
        if(target == 'pdf'){
            gerarpdf('pagamento', dataTexto, 'Tipo de Pagamento', nomeEmpresa);
        } else if(target == 'excel'){
            gerarexcel('pagamento', dataTexto, 'Tipo de Pagamento', nomeEmpresa);
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="gerar.js"></script>

</html>