<?php
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
require_once __DIR__ . '/../../db/entities/ope01.php';
require_once __DIR__ . '/../../db/entities/band01.php';
require_once __DIR__ . '/../../db/entities/pra01.php';
require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
require_once __DIR__ . '/../../db/entities/cidade.php';
require_once __DIR__ . '/../../db/entities/bairro.php';
require_once __DIR__ . '/../../db/entities/estados.php';
require_once __DIR__ . '/../../db/entities/categoria.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../db/entities/banco02.php';
require_once __DIR__ . '/../../db/entities/banco01.php';
require_once __DIR__ . '/../../db/base.php';
// if(isset($_SESSION['vendas_invalidas'])) {
//     unset($_SESSION['vendas_invalidas']);
// }
// if(isset($_SESSION['vendas'])) {
//     unset($_SESSION['vendas']);
// }

if(isset($_SESSION['vendas_invalidas']) && isset($_SESSION['vendas']['transactions'])) {
    unset($_SESSION['vendas']['transactions']);
}
$lateral_cartao = true;
$lateral_target = 'cartao_vendas';

$acao = filter_input(INPUT_GET, 'acao');
filter_input(INPUT_GET, 'vendas_invalidas') == 1 ? $vendas_invalidas = true : $vendas_invalidas = false;
filter_input(INPUT_GET, 'vendas_enviadas') == 1 ? $vendas_enviadas = true : $vendas_enviadas = false;
$erro = filter_input(INPUT_GET, 'erro');
$ordenar_por = filter_input(INPUT_GET, 'ordenar') ?? filter_input(INPUT_POST, 'ordenar') ?? null;
$direcao = filter_input(INPUT_GET, 'direcao') ?? filter_input(INPUT_POST, 'direcao');

$view = filter_input(INPUT_GET, 'view');
$target = filter_input(INPUT_GET, 'target');

$modal_quitar_id = filter_input(INPUT_GET, 'quitar_id') ?? null;
$id_ban = filter_input(INPUT_GET, 'id_ban') ?? null;

$con01 = filter_input(INPUT_GET, 'con01id');

$acao = filter_input(INPUT_GET, 'acao');

$get_opcao = filter_input(INPUT_GET, 'opcao');
$get_id = filter_input(INPUT_GET, 'id');
$numero_exibir = filter_input(INPUT_POST, 'numero_exibido') ?? filter_input(INPUT_GET, 'numero_exibido') ?? 10;
$numero_pagina = filter_input(INPUT_POST, 'pagina') ?? filter_input(INPUT_GET, 'pagina') ?? 1;
$numero_pagina = intval($numero_pagina);
$direcao_var = $direcao;
$get_filtro_data_inicial = filter_input(INPUT_GET, 'filtro_data_inicial') ?? null;
$get_filtro_data_final = filter_input(INPUT_GET, 'filtro_data_final') ?? null;

$get_filtro_nome = filter_input(INPUT_GET, 'filtro_nome') ?? null;
$get_filtro_opcao = filter_input(INPUT_GET, 'opcao_filtro') ?? null;
$get_filtro_por = filter_input(INPUT_GET, 'filtro_por') ?? null;
$get_filtro_pagamento = filter_input(INPUT_GET, 'forma_pagamento') ?? null;
$get_filtro_cadastro = filter_input(INPUT_GET, 'filtro_cadastro') ?? null;
$get_filtro_titulo = filter_input(INPUT_GET, 'filtro_titulo') ?? null;
$get_filtro_subtitulo = filter_input(INPUT_GET, 'filtro_subtitulo') ?? null;
$get_filtro_custo = filter_input(INPUT_GET, 'filtro_custo') ?? null;

$exibir_detalhes = filter_input(INPUT_GET, 'filtro_detalhes') == 'on' ? true : false;
$exibir_diferencas = filter_input(INPUT_GET, 'filtro_diferencas') == 'on' ? true : false;
if($exibir_diferencas === true) {
    $exibir_detalhes = true;
}
if($exibir_detalhes) {
    $get_filtro_opcao = null;
    $get_filtro_por = null;
}

if($exibir_detalhes === false){
    $parcela_paginas = Rec02::read(
        id_empresa: $_SESSION['usuario']->id_empresa,
        filtro_data_inicial: $get_filtro_data_inicial ?? null,
        filtro_data_final: $get_filtro_data_final ?? null,
        filtro_documento: $get_filtro_nome ?? null,
        filtro_opcao: $get_filtro_opcao ?? null,
        filtro_por: $get_filtro_por ?? null,
        read_vendas:true,
        filtro_pagamento: $get_filtro_pagamento ?? null,
        filtro_cadastro:$get_filtro_cadastro ?? null, 
        filtro_custos: $get_filtro_custo ?? null,
        read_paginas: true
    );
} else {
    $parcela_paginas = Rec01::read(
        id_empresa: $_SESSION['usuario']->id_empresa,
        read_vendas: true,
        read_diferencas: $exibir_diferencas,
        read_paginas:$exibir_detalhes,
        filtro_data_inicial: $get_filtro_data_inicial ?? null,
        filtro_data_final: $get_filtro_data_final ?? null,
        id_cadastro: $get_filtro_cadastro,
        filtro_custos: $get_filtro_custo
    );
}
$total_paginas = ceil($parcela_paginas / $numero_exibir);




$filtros = [];
if ($get_filtro_data_inicial != '')
    $filtros[] = 'filtro_data_inicial=' . $get_filtro_data_inicial;
if ($get_filtro_data_final != '')
    $filtros[] = 'filtro_data_final=' . $get_filtro_data_final;
if ($get_filtro_nome != '')
    $filtros[] = 'filtro_nome=' . $get_filtro_nome;
if ($get_filtro_opcao != '')
    $filtros[] = 'opcao_filtro=' . $get_filtro_opcao;
if ($get_filtro_por != '')
    $filtros[] = 'filtro_por=' . $get_filtro_por;
if ($get_filtro_pagamento != '')
    $filtros[] = 'forma_pagamento=' . $get_filtro_pagamento;
if ($get_filtro_titulo != '')
    $filtros[] = 'filtro_titulo=' . $get_filtro_titulo;
if ($get_filtro_subtitulo != '')
    $filtros[] = 'filtro_subtitulo=' . $get_filtro_subtitulo;
if ($get_filtro_cadastro != '')
    $filtros[] = 'filtro_cadastro=' . $get_filtro_cadastro;
if ($get_filtro_custo != '')
    $filtros[] = 'filtro_custo=' . $get_filtro_custo;
if ($exibir_detalhes)
    $filtros[] = 'filtro_detalhes=on';
if($exibir_diferencas)
    $filtros[] = 'filtro_diferencas=on';
if ($filtros != []) {   
    $caminho = 'cadastro_vendas.php?' . implode('&', $filtros);
    $caminho_get = urlencode('cadastro_vendas.php?' . implode('&', $filtros));
} else {
    $caminho = 'cadastro_vendas.php';
    $caminho_get = urlencode('cadastro_vendas.php');
}

?>
<!DOCTYPE html>

<head>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>



<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/componentes/modais/lancamentos/modais.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="../choices/choices.css"></link>




<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>
<body id="body">


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>

    <div class="main" id="container">


        <div class="col-md-12" style="padding: 0;">


                <div class="card">
                    <div class="card-header">
                        <div class="card-header-lancamento">
                        <h3>Vendas (Contas a Receber)</h3>

                    <div>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_cadastro_vendas">Adicionar Vendas</button>
                    </div>
                        
                        </div>
                    </div>

                    <div class="card-header-div">

                        <div class="card-header-borda" style="width:90%;">
                            <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                aria-labelledby="vendas-tab">
                                <h5 class="card-title">Filtros</h5>
                                <form method="get" action="cadastro_vendas.php">
                                    <?php if ($numero_exibir != 10) { ?> <input type="hidden" value="<?= $numero_exibir ?>"
                                            name="numero_exibido" /> <?php } ?>
                                    <div class="form-pagamento">
                                        <div class="inputs-pagamento-group">
                                            <div class="row">
                                                <div class="inputs-pagamento-text" id="inputs-text">
                                                    <div class="r-inputs-data">
                                                        <!-- Data inicial -->
                                                        <div style="width: 50%;">
                                                            <label for="filtro_data_inicial">Data
                                                                Inicial:</label>
                                                            <input type="date" id="filtro_data_inicial"
                                                                name="filtro_data_inicial"
                                                                value="<?= $get_filtro_data_inicial; ?>"
                                                                class="form-control" style="border-radius: 0;">
                                                        </div>

                                                        <!-- Data final -->
                                                        <div style="width: 50%;">
                                                            <label for="filtro_data_final">Data
                                                                Final:</label>
                                                            <input type="date" id="filtro_data_final"
                                                                name="filtro_data_final"
                                                                value="<?= $get_filtro_data_final; ?>" class="form-control"
                                                                style="border-radius: 0;">
                                                        </div>
                                                    </div>


                                                   
                                                </div>
                                                <div class="inputs-pagamento-text inputs-pagamento-select input-select-geral" id="inputs-select">

                                                <div class="r-inputs-data" style="width:100%;">
                                                    <div style="display:flex; flex-direction: column; width:100%;" >
                                                        <label for="forma_pagamento">Cliente /
                                                            Fornecedor:</label>
                                                        <select class="form-control" name="filtro_cadastro">
                                                            <option value="">Selecione</option>
                                                            <?php
                                                            foreach (Cadastro::read(null, null, $_SESSION['usuario']->id_empresa) as $cadastro) { ?>
                                                                <option value="<?= $cadastro->id_cadastro ?>" <?= $get_filtro_cadastro == $cadastro->id_cadastro ? 'selected' : ''?>>
                                                                    <?= $cadastro->nom_fant ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <div style="display:flex; flex-direction: column; width:100%;" >
                                                        <label for="centro-custos-filtro">Centro de custos:</label>
                                                        <select class="form-control" name="filtro_custo" id="custo-filtro">
                                                            <option value="">Selecione</option>
                                                            <?php
                                                            $centro_custos = CentroCustos::read(null, $_SESSION['usuario']->id_empresa);
                                                            foreach ($centro_custos as $custo) { ?>
                                                                <option value="<?= $custo->id ?>" <?= $get_filtro_custo == $custo->id ? 'selected' : ''?>>
                                                                    <?= htmlspecialchars($custo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>                           
                                                </div>


                                                    
                                                </div>
                                            </div> <!-- fecha row -->
                                        </div> <!-- fecha inputs-pagamento-group -->
                                        <div class="checkbox-group" style="margin-right:1.5em;display:flex; justify-content: center; align-itens:center;">
                                            <div style="display:flex; justify-content: center; align-itens:center; flex-direction:column">
                                                <div class="form-check">
                                                    <label style="font-size: 100%; overflow: visible; white-space: nowrap;" for="filtro_documento">Auditoria:</label>
                                                    <input type="checkbox" name="filtro_detalhes" <?= $exibir_detalhes === true ? 'checked' : '' ?>>
                                                    <label style="font-size: 100%; overflow: visible; white-space: nowrap;" for="filtro_documento">Exibir Apenas <br> Diferenças:</label>
                                                    <input type="checkbox" name="filtro_diferencas" <?= $exibir_diferencas=== true ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if($exibir_detalhes === false) {?>
                                        <div class="selects-pagamento" style="padding-top:3%;">
                                            <div style="display: flex; flex-direction: row;">
                                            <div style="width: 15%;">
                                                <h5 style="font-size: 75%;">Opção:</h5>
                                            </div>
                                            <!-- Primeira linha de radios (opção) -->
                                            <div class="radio-pagamento">
                                                
                                                <div>                    
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="todos">Todos</label>
                                                        <input class="form-check-input" type="radio" id="todos"
                                                            name="opcao_filtro" value="" <?php if ($get_filtro_opcao == '' || empty($get_filtro_opcao)) { ?> checked <?php } ?>>
                                                        
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="abertos">Abertos</label>
                                                        <input class="form-check-input" type="radio" id="abertos"
                                                            name="opcao_filtro" value="abertos" <?php if ($get_filtro_opcao == 'abertos') { ?> checked <?php } ?>
                                                            value="abertos">
                                                        
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-check">
                                                        <label class="form-check-label" style="font-size: 90%;" for="quitados">Quitados</label>
                                                        <input class="form-check-input" type="radio" id="quitados"
                                                            name="opcao_filtro" value="quitados" <?php if ($get_filtro_opcao == 'quitados') { ?> checked <?php } ?>
                                                            value="quitados">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                

                                            <!-- Segunda linha de radios (filtro por) -->
                                        <div style="display: flex; flex-direction: row;">                      
                                                <div style="width: 15%;">
                                                    <h5 style="font-size: 75%;">Filtro:</h5>
                                                </div>
                                            <div class="radio-pagamento">
                                                
                                                <div >                        
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="lancamento">Lançamento</label>
                                                        <input class="form-check-input" type="radio" id="lancamento"
                                                            name="filtro_por" <?php if ($get_filtro_por == 'lancamento' || empty($get_filtro_por)) { ?> checked <?php } ?>
                                                            value="lancamento">
                                                        
                                                    </div>
                                                </div>
                                                <div >
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="vencimento">Vencimento</label>
                                                        <input class="form-check-input" type="radio" id="vencimento"
                                                            name="filtro_por" <?php if ($get_filtro_por == 'vencimento') { ?>
                                                                checked <?php } ?> value="vencimento">
                                                        
                                                    </div>
                                                </div>
                                                <div >                    
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="pagamento">Pagamento</label>
                                                        <input class="form-check-input" type="radio" id="pagamento"
                                                            name="filtro_por" <?php if ($get_filtro_por == 'pagamento') { ?>
                                                                checked <?php } ?> value="pagamento">
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  

                                        </div> <!-- fecha selects-pagamento -->
                                    <?php } ?>




                                        <div class="btn-filtro">
                                            <button type="submit" class="btn btn-primary"
                                                style="background-color: #5856d6;">Filtrar</button>
                                            <a href="cadastro_vendas.php" class="btn btn-secondary">Limpar</a>
                                        </div>

                                    </div>
                            </div>
                        </div>
                        </form>



                    </div>
                <div class="tabela-lancamento dragscroll avoid-page-break">
                    <?php 
                    if($exibir_detalhes === true){
                        require_once __DIR__ . '/tabelas/tabela_detalhada.php'; 
                    } else if($exibir_detalhes === false) {
                        require_once __DIR__ . '/tabelas/tabela_comum.php'; 
                    }
                    ?>
                </div>
                <div class="card-footer">
                    <div class="card-select-pagina">
                        <?php
                        ?>
                        <form method="post" action="<?= $caminho ?>">
                            <?php if ($ordenar_por != '') { ?> <input type="hidden" value="<?= $ordenar_por ?>"
                                    name="ordenar" /><?php } ?>
                            <?php if ($numero_exibir != 10) { ?> <input type="hidden" value="<?= $numero_exibir ?>"
                                    name="numero_exibido" /> <?php } ?>
                            <?php if ($direcao != '') { ?> <input type="hidden" value="<?= $direcao ?>"
                                    name="direcao" /><?php } ?>

                            <?php if (!empty($recebimentos_pagos)) {
                                foreach ($recebimentos_pagos as $i => $id_rec01) { ?>

                                    <input type="hidden" name="recebimentos_pagos[<?= $i ?>]" value="<?= $id_rec01 ?>">
                                <?php }
                            } ?>

                            <?php for ($i = 1; $i <= $total_paginas; $i++) {
                                if ((($i == ($numero_pagina + 4) || $i == ($numero_pagina - 4)) && $i != 1) && $i != $total_paginas) { ?>
                                    ...

                                    <?php continue;
                                }
                                if ((($i > ($numero_pagina + 4) || $i < ($numero_pagina - 4)) && $i < $total_paginas) && $i != 1) {
                                    continue;
                                }
                                ?>

                                <button type="submit" name="pagina" class="form-control" <?php if ($numero_pagina == $i) { ?>
                                        disabled <?php } ?> value="<?= $i ?>"><?= $i ?></button>
                            <?php } ?>
                        </form>
                    </div>
                    <?php
                        if($exibir_detalhes === false){
                            $total_parcelas = 0;
                            $total_pagamentos = 0;
                            $parcelas_totais = Rec02::read(
                                    id_empresa: $_SESSION['usuario']->id_empresa,
                                    filtro_data_inicial: $get_filtro_data_inicial,
                                    filtro_data_final: $get_filtro_data_final,
                                    filtro_documento: $get_filtro_nome,
                                    filtro_opcao: $get_filtro_opcao,
                                    filtro_por: $get_filtro_por,
                                    filtro_pagamento: $get_filtro_pagamento,
                                    filtro_cadastro: $get_filtro_cadastro,
                                    filtro_con01: $get_filtro_titulo,
                                    filtro_con02: $get_filtro_subtitulo,
                                    filtro_custos: $get_filtro_custo,
                                    read_totais:true,
                                    read_vendas:true,
                            );
                            
                            foreach ($parcelas_totais as $pt) {
                                $total_parcelas += $pt->valor_par;
                                $total_pagamentos += $pt->valor_pag;
                            }   
                        } else {
                            $total_parcelas = 0;
                            $recebimentos = Rec01::read(
                                id_empresa: $_SESSION['usuario']->id_empresa,
                                read_vendas: $exibir_detalhes,
                                read_diferencas: $exibir_diferencas,
                                filtro_data_inicial: $get_filtro_data_inicial,
                                filtro_data_final: $get_filtro_data_final,
                                id_cadastro: $get_filtro_cadastro,
                                filtro_custos: $get_filtro_custo,
                            );
                            foreach($recebimentos as $rec) {
                                $total_parcelas += $rec->valor;
                            }
                        }
                    
                    ?>


                        <div id="totais-lancamento">          
                            <?php if($exibir_detalhes === false) {?>
                                <?php if($get_filtro_opcao == null ||  $get_filtro_opcao == 'todos' || $get_filtro_opcao == 'abertos')  {?>
                                    <div id="total-parcela"><div class="r-total-lanc">
                                        <div>
                                            Total das Parcelas: 
                                        </div>
                                        <div>
                                            &nbsp  R$ <?= number_format($total_parcelas, 2, ',', '.') ?> 
                                        </div>
                                        </div>
                                    </div>
                                <?php } ?>  
                                <?php if($get_filtro_opcao == 'quitados')  {?>
                                    <div id="total-parcela">Total Pago: R$
                                        <?= number_format($total_pagamentos, 2, ',', '.') ?>
                                    </div>
                                <?php } ?>
                            <?php } else if($exibir_detalhes) { ?>
                                <div>
                                    Valor Liquido Total:  R$ <?= number_format($total_parcelas, 2, ',', '.') ?> 
 
                                </div>
                            <?php } ?>
                        </div>


                    <div class="card-select-numero">
                        <div>
                            <form method="post" action="<?= $caminho ?>">
                                <?php if ($ordenar_por != '') { ?> <input type="hidden" value="<?= $ordenar_por ?>"
                                        name="ordenar" /><?php } ?>
                                <?php if ($direcao != '') { ?> <input type="hidden" value="<?= $direcao ?>"
                                        name="direcao" /><?php } ?>

                                <select class="form-control" onchange="this.form.submit()" name="numero_exibido">
                                    <option <?php if ($numero_exibir == 5) { ?> selected <?php } ?> value="5">5</option>
                                    <option <?php if ($numero_exibir == 10) { ?> selected <?php } ?> value="10">10</option>
                                    <option <?php if ($numero_exibir == 20) { ?> selected <?php } ?> value="20">20</option>
                                    <option <?php if ($numero_exibir == 30) { ?> selected <?php } ?> value="30">30</option>
                                    <option <?php if ($numero_exibir == 40) { ?> selected <?php } ?> value="40">40</option>
                                    <option <?php if ($numero_exibir == 50) { ?> selected <?php } ?> value="50">50</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <div class="relatorios-botoes" style="float:left; width:100%">
            <button type="button" class="btn btn-primary btn-sm" id="botao-gerar-pdf" onclick="gerarpdf('movimentacao', document.querySelector('#nome-empresa h1').innerHTML)">Gerar PDF</button>
            <button type="button" class="btn btn-primary btn-sm" id="botao-gerar-excel" onclick="gerarexcel('movimentacao', document.querySelector('#nome-empresa h1').innerHTML)">Gerar Excel</button>
        </div>
    </div>
</div>
           
<div style="display:none">
    <?php 
    if($exibir_detalhes){
        require_once __DIR__ . '/tabelas/tabela_detalhada_pdf.php';
    } else if($exibir_detalhes === false) {
        require_once __DIR__ . '/tabelas/tabela_comum_pdf.php';
    }
    ?>
</div>
    
    <div id="custom-context-menu" style="display:none; position:absolute; z-index:9999; background:#fff; border:1px solid #ccc; box-shadow:0 2px 8px rgba(0,0,0,0.2); min-width:180px; border-radius:6px; overflow:hidden;">
                    <?php if($_SESSION['usuario']->processar === 1) {?>
                    <button id="menu-quitar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-cash-stack"></i> Quitar</button>
                    <button id="menu-estornar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-wallet2"></i> Estornar</button>
                    <button id="menu-editar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-pen-fill"></i> Editar</button>
                    <?php } ?>
                    <button id="menu-visualizar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-eye"></i> Visualizar</button>
    </div>

    <?php require_once __DIR__ . '/../../componentes/modais/cartao/modal_cadastro_vendas.php' ?>

    <?php
    if(isset($_SESSION['vendas_invalidas'])) {
        unset($_SESSION['vendas_invalidas']);
    }
    if(isset($_SESSION['vendas'])) {
        unset($_SESSION['vendas']);
    }
    ?>
<?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?> 
</body>
<script>

</script>
<script src="gerar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>



<script>
    <?php if($vendas_invalidas || $vendas_enviadas) {?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_cadastro_vendas');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
    <?php } ?>
//     ;(function(){
//     const menu = document.getElementById('custom-context-menu');
//     let currentRow = null;

//     document.addEventListener('contextmenu', function(e){
//         // target any table data row (including clicks inside cells or buttons)
//         let row = e.target.closest('.tr-clientes');
//         // ignore header rows or other non-data rows
//         if (row && !row.classList.contains('tr-clientes-header')) {
//             // stop browser menu and any propagation; run in capture to beat other handlers
//             e.preventDefault();
//             e.stopPropagation();
//             if (e.stopImmediatePropagation) e.stopImmediatePropagation();
//             console.debug('custom contextmenu intercept', row);
//             currentRow = row;
//             // compute menu size and position to keep within viewport
//             const menuWidth = menu.offsetWidth || 220;
//             const menuHeight = menu.offsetHeight || 160;
//             let x = e.pageX - 260;
//             let y = e.pageY - 70;
           
//             menu.style.left = x + 'px';
//             menu.style.top = y + 'px';
//             menu.style.display = 'block';
//             // enable/disable items based on data attrs
//             const valorPag = parseFloat(row.getAttribute('data-valor-pag') || '0');
//             const recebido = row.getAttribute('data-id-rec01-recebido') === '1';
//             const btnQ = document.getElementById('menu-quitar');
//             const btnE = document.getElementById('menu-estornar');
//             const btnEd = document.getElementById('menu-editar');
//             if (btnQ) btnQ.disabled = valorPag > 0;
//             if (btnE) btnE.disabled = valorPag == 0;
//             if (btnEd) btnEd.disabled = recebido;
//         } else {
//             // allow browser context menu when not on a data row
//             menu.style.display = 'none';
//         }
//     }, true);

//     document.addEventListener('click', function(e){
//         if (!menu.contains(e.target)) menu.style.display = 'none';
//     });
//     window.addEventListener('scroll', ()=> menu.style.display = 'none');

//     // actions
//     document.getElementById('menu-quitar').addEventListener('click', function(){
//         if (!currentRow) return;
//         const id = currentRow.getAttribute('data-id');
//         const valorRestante = currentRow.getAttribute('data-valor-restante');
//         const parcelaAtual = currentRow.getAttribute('data-parcela-atual');
//         const parcelaGeral = currentRow.getAttribute('data-parcela-geral');
//         const vencimento = currentRow.getAttribute('data-vencimento');
//         const documento = currentRow.getAttribute('data-documento');
//         const modalEl = document.getElementById('modal_quitar');
//         if (modalEl) {
//             document.getElementById('modal_quitar_id').value = id;
//             const vr = document.getElementById('modal_quitar_valor_restante'); if (vr) vr.textContent = 'Valor restante da parcela: R$ ' + valorRestante;
//             const pa = document.getElementById('modal_quitar_parcela_atual'); if (pa) pa.textContent = parcelaAtual || '';
//             const pg = document.getElementById('modal_quitar_parcela_geral'); if (pg) pg.textContent = parcelaGeral || '';
//             const vv = document.getElementById('modal_quitar_vencimento'); if (vv) vv.textContent = vencimento || '';
//             const doc = document.getElementById('modal_quitar_documento'); if (doc) doc.textContent = documento || '';
//             const modalVal = document.getElementById('modal_quitar_valor'); if (modalVal) modalVal.placeholder = valorRestante || '';
//             const bsModal = new bootstrap.Modal(modalEl); bsModal.show();
//         }
//         menu.style.display = 'none';
//     });

//     document.getElementById('menu-estornar').addEventListener('click', function(){
//         if (!currentRow) return;
//         const id = currentRow.getAttribute('data-id');
//         const url = 'cadastros_manager.php?view=receber&target=parcela&acao=estornar&id=' + id + '&caminho=<?= $caminho_get ?>&pagina=<?php if (empty($filtros)) {?>
//                                                      <?='?pagina=' . $numero_pagina;?>
//                                                 <?php } else { ?>
//                                                      <?='?pagina=' . $numero_pagina;?>
//                                                 <?php } ?>&numero_exibido=<?= 'knumero_exibido=' . $numero_exibir ?>';
        
//                                                 window.location.href = url;
//     });

//     document.getElementById('menu-editar').addEventListener('click', function(){
//         if (!currentRow) return;
//         const idRec01 = currentRow.getAttribute('data-id-rec01');
//         window.location.href = 'cadastro_vendas.php?id=' + idRec01 + '&acao=editar';
//     });

//     document.getElementById('menu-visualizar').addEventListener('click', function(){
//         if (!currentRow) return;
//         const idRec01 = currentRow.getAttribute('data-id-rec01');
//         window.location.href = 'cadastro_vendas.php?id=' + idRec01 + '&acao=visualizar';
//     });

// })();
//     window.addEventListener('DOMContentLoaded', function () {
//             var modalEl = document.getElementById('modal_cadastro_vendas');
//             var Modal = new bootstrap.Modal(modalEl);
//             Modal.show();
//             modalEl.addEventListener('hidden.bs.modal', function () {
//                 window.location.href = 'cadastro_vendas.php';
//             });
//         });
        <?php if(isset($modal_quitar_id)){
    // try to load the parcela to compute the remaining value so modal shows it when opened via GET
    $parcela_for_modal = null;
    try {
        $parcela_for_modal = Rec02::read($modal_quitar_id, $_SESSION['usuario']->id_empresa)[0] ?? Rec02::read($modal_quitar_id)[0] ?? null;
    } catch (Exception $e) {
        $parcela_for_modal = Rec02::read($modal_quitar_id)[0] ?? null;
    }
    $valor_restante_js = '';
    if ($parcela_for_modal) {
        $valor_restante_js = number_format($parcela_for_modal->valor_par - $parcela_for_modal->valor_pag, 2, ',', '');
    }
?>
    
        set the modal id and the displayed remaining value when the page was opened with ?quitar_id=
        var modalQInput = document.getElementById('modal_quitar_id');
        if (modalQInput) modalQInput.value = <?= $modal_quitar_id ?>;
        var vrEl = document.getElementById('modal_quitar_valor_restante');
        if (vrEl) vrEl.textContent = "Valor restante da parcela: R$ <?= $valor_restante_js ?>";

<?php }?>
<?php if($acao == 'visualizar') {?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
    <?php } ?>
    <?php if(isset($acao) && $acao == 'editar') { ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
<?php } ?>


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
    document.getElementById('titulo').addEventListener('change', function () {
        var tituloId = this.value;
        var subtituloSelect = document.getElementById('subtitulo');
        var options = subtituloSelect.querySelectorAll('option');

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

        subtituloSelect.value = ""; // Reseta seleção
    });

    function filtroSubtitulo(resetSubtitulo = true) {
        var tituloId = document.querySelector('select[name=filtro_titulo]').value;
        var subtituloSelect = document.querySelector('select[name=filtro_subtitulo]');
        var options = subtituloSelect.querySelectorAll('option');

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

        if (resetSubtitulo) {
            subtituloSelect.value = ""; // Só reseta se for troca de título
        }
    }

    document.querySelector('select[name=filtro_titulo]').addEventListener('change', function () {
        filtroSubtitulo()
    });
    filtroSubtitulo()

    var posicao = localStorage.getItem('posicaoScroll');

    /* Se existir uma opção salva seta o scroll nela */
    if (posicao) {
        /* Timeout necessário para funcionar no Chrome */
        setTimeout(function () {
            window.scrollTo(0, posicao);
        }, 1);
    }

    /* Verifica mudanças no Scroll e salva no localStorage a posição */
    window.onscroll = function (e) {
        posicao = window.scrollY;
        localStorage.setItem('posicaoScroll', JSON.stringify(posicao));
    }

    

    document.addEventListener('DOMContentLoaded', function () {
        var modalQuitar = document.getElementById('modal_quitar');
        modalQuitar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var valorRestante = button.getAttribute('data-valor-restante');
            var parcelaAtual = button.getAttribute('data-parcela-atual');
            var parcelaGeral = button.getAttribute('data-parcela-geral');
            var vencimento = button.getAttribute('data-vencimento');
            var documento = button.getAttribute('data-documento');
            document.getElementById('modal_quitar_id').value = id;
            document.getElementById('modal_quitar_valor_restante').textContent = "Valor restante da parcela: R$ " + valorRestante;
            document.getElementById('modal_quitar_parcela_atual').textContent = parcelaAtual || '';
            document.getElementById('modal_quitar_parcela_geral').textContent = parcelaGeral || '';
            document.getElementById('modal_quitar_vencimento').textContent = vencimento || '';
            document.getElementById('modal_quitar_documento').textContent = documento || '';
            document.getElementById('modal_quitar_valor').placeholder = valorRestante || '';
        });
    });

    



    

    
    


    <?php if($acao != 'visualizar') {?>
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function() {
        // ========== INICIALIZAÇÃO DO CHOICES.JS ==========
        const tituloFiltroElement = document.querySelector('#titulo-filtro');
        const subtituloFiltroElement = document.querySelector('#subtitulo-filtro');
        const tituloModalElement = document.querySelector('#titulo'); 
        const subtituloModalElement = document.querySelector('#subtitulo'); 

        // IMPORTANTE: Guarda os subtítulos ANTES de inicializar Choices.js
        const todosSubtitulos = subtituloFiltroElement ? 
            Array.from(subtituloFiltroElement.querySelectorAll('option')).map(opt => ({
                value: opt.value,
                label: opt.textContent.trim(),
                tituloId: opt.getAttribute('data-titulo-id')
            })) : [];

        const todosSubtitulosModal = subtituloModalElement ?
            Array.from(subtituloModalElement.querySelectorAll('option')).map(opt => ({
                value: opt.value,
                label: opt.textContent.trim(),
                tituloId: opt.getAttribute('data-titulo-id')
            })) : [];

        // Inicializa Choices.js nos elementos de filtro
        let tituloFiltroChoice = null;
        let subtituloFiltroChoice = null;
        
        if (tituloFiltroElement && typeof Choices !== 'undefined') {
            tituloFiltroChoice = new Choices(tituloFiltroElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
        }

        if (subtituloFiltroElement && typeof Choices !== 'undefined') {
            subtituloFiltroChoice = new Choices(subtituloFiltroElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
            
            // LIMPA IMEDIATAMENTE após inicializar
            subtituloFiltroChoice.clearStore();
            subtituloFiltroChoice.clearChoices();
            subtituloFiltroChoice.setChoices(
                [{ value: '', placeholder: 'Selecione', disabled: false }],
                'value',
                'placeholder',
                true
            );
            subtituloFiltroChoice.setChoiceByValue('');
        }
<?php if($id_ban == null) { ?>
        // Inicializa Choices.js nos elementos do modal
        let tituloModalChoice = null;
        let subtituloModalChoice = null;
        
        if (tituloModalElement && typeof Choices !== 'undefined') {
            tituloModalChoice = new Choices(tituloModalElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
        }

        if (subtituloModalElement && typeof Choices !== 'undefined') {
            subtituloModalChoice = new Choices(subtituloModalElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
            
            // LIMPA IMEDIATAMENTE após inicializar
            subtituloModalChoice.clearChoices();
            subtituloModalChoice.clearStore();


        }

        console.log('Choices.js inicializado em cadastro_vendas.php:', {
            tituloFiltro: !!tituloFiltroChoice,
            subtituloFiltro: !!subtituloFiltroChoice,
            tituloModal: !!tituloModalChoice,
            subtituloModal: !!subtituloModalChoice
        });
        <?php } ?>

        // ========== FILTRO DE SUBTÍTULOS (FILTROS) ==========
        if (tituloFiltroElement && subtituloFiltroElement) {
            function carregarSubtitulosFiltro(tituloId, manterSelecao = false) {
                const valorAtual = subtituloFiltroElement.value;

                if (subtituloFiltroChoice) {
                    subtituloFiltroChoice.clearStore();
                    subtituloFiltroChoice.clearChoices();
                    subtituloFiltroChoice.setChoices(
                        [{ value: '', placeholder: 'Selecione', disabled: false }],
                        'value',
                        'placeholder',
                        true
                    );

                    if (!tituloId) {
                        subtituloFiltroChoice.setChoiceByValue('');
                        return;
                    }

                    const subtitulosFiltrados = todosSubtitulos.filter(sub => sub.tituloId === tituloId);
                    if (subtitulosFiltrados.length > 0) {
                        subtituloFiltroChoice.setChoices(subtitulosFiltrados, 'value', 'label', false);
                    }

                    if (manterSelecao && valorAtual && subtitulosFiltrados.some(sub => sub.value === valorAtual)) {
                        setTimeout(() => {
                            subtituloFiltroChoice.setChoiceByValue(valorAtual);
                        }, 50);
                    } else {
                        subtituloFiltroChoice.setChoiceByValue('<?= $get_filtro_subtitulo; ?>');
                    }
                } else {
                    subtituloFiltroElement.innerHTML = '<option value="">Selecione</option>';
                    
                    if (!tituloId) return;

                    todosSubtitulos
                        .filter(sub => sub.tituloId === tituloId)
                        .forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.value;
                            option.textContent = sub.label;
                            option.setAttribute('data-titulo-id', sub.tituloId);
                            subtituloFiltroElement.appendChild(option);
                        });

                    if (manterSelecao && valorAtual) {
                        subtituloFiltroElement.value = valorAtual;
                    } else {
                        subtituloFiltroElement.value = '';
                    }
                }

            }

            tituloFiltroElement.addEventListener('change', function(e) {
                const valor = e.detail ? e.detail.value : e.target.value;
                carregarSubtitulosFiltro(valor, false);
            });

            const tituloInicial = tituloFiltroElement.value;
            if (tituloInicial) {
                carregarSubtitulosFiltro(tituloInicial, true);
            }
        }

        // ========== FILTRO DE SUBTÍTULOS (MODAL) ==========
        if (tituloModalElement && subtituloModalElement) {
            function carregarSubtitulosModal(tituloId) {
                if (subtituloModalChoice) {
                    subtituloModalChoice.clearStore();
                    subtituloModalChoice.clearChoices();
                    subtituloModalChoice.setChoices(
                        [{ value: '', placeholder: 'Selecione', disabled: false }],
                        'placeholder',
                        'label',
                        true
                    );

                    if (!tituloId) {
                        subtituloModalChoice.setChoiceByValue('');
                        return;
                    }

                    const subtitulosFiltrados = todosSubtitulosModal.filter(sub => sub.tituloId === tituloId);
                    if (subtitulosFiltrados.length > 0) {
                        subtituloModalChoice.setChoices(subtitulosFiltrados, 'value', 'label', false);
                    }
                    
                    subtituloModalChoice.setChoiceByValue('');
                } else {
                    subtituloModalElement.innerHTML = '<option value="">Selecione</option>';
                    
                    if (!tituloId) return;

                    todosSubtitulosModal
                        .filter(sub => sub.tituloId === tituloId)
                        .forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.value;
                            option.textContent = sub.label;
                            option.setAttribute('data-titulo-id', sub.tituloId);
                            subtituloModalElement.appendChild(option);
                        });
                    
                    subtituloModalElement.value = '';
                }
            }

            tituloModalElement.addEventListener('change', function(e) {
                const valor = e.detail ? e.detail.value : e.target.value;
                carregarSubtitulosModal(valor);
            });

            const tituloInicialModal = tituloModalElement.value;
            if (tituloInicialModal) {
                carregarSubtitulosModal(tituloInicialModal);
            }
        }
    
    
    ;}, 100);
});
<?php } ?>
<?php if (isset($acao) && ($acao == 'adicionar' || $acao == 'visualizar' || $acao == 'editar')) { 
    if($acao == 'visualizar') {?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
    <?php } else if(!isset($target) || $target == 'cadastro') {?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
    <?php } else if($target == 'quitar') { ?>

        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_quitar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
    <?php } else if (isset($target) && $target != 'cadastro' && $target != 'quitar'){ ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_cadastro');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
<?php }}?>

<?php if(isset($acao) && $acao == 'editar') { ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_vendas.php';
            });
        });
<?php } ?>
</script>

<?php if($erro != null) {?>
    <?php if($erro == 'operadora'){?>
    <script>
        alert('Operadora não Selecionada')
        window.location.href = 'cadastro_vendas.php'
    </script>
    <?php } ?>
    <?php if($erro == 'arquivo'){?>
    <script>
        alert('Tipo de Arquivo Inválido')
        window.location.href = 'cadastro_vendas.php'
    </script>
    <?php } ?>
    <?php if($erro == 'suporte'){?>
    <script>
        alert('Operadora não suportada, entre em contato com a gestor office para adicionar um suporte')
        window.location.href = 'cadastro_vendas.php'
    </script>
    <?php } ?>
    <?php if($erro == 'cadastrado'){?>
    <script>
        alert('Arquivo vazio ou já cadastrado')
        window.location.href = 'cadastro_vendas.php'
    </script>
    <?php } ?>
<?php } ?>




</html>