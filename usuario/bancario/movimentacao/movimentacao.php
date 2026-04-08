<?php 
require_once __DIR__ . '/../../../db/entities/usuarios.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
require_once __DIR__ . '/../../../db/entities/banco01.php';
require_once __DIR__ . '/../../../db/entities/banco02.php';
require_once __DIR__ . '/../../../db/entities/palavra_chave.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../../db/entities/pagar.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/buscar_documento.php';
require_once __DIR__ . '/../../../db/buscar_documento_rec.php';
require_once __DIR__ . '/../../../db/buscar_documento_pag.php';

$numero_exibir = filter_input(INPUT_POST, 'numero_exibido') ?? filter_input(INPUT_GET, 'numero_exibido') ?? 10;
$numero_pagina = filter_input(INPUT_POST, 'pagina') ?? filter_input(INPUT_GET, 'pagina') ?? 1;

$get_filtro_data_inicial = filter_input(INPUT_GET, 'filtro_data_inicial');
if($get_filtro_data_inicial == '') $get_filtro_data_inicial = null;

$get_filtro_data_final = filter_input(INPUT_GET, 'filtro_data_final');
if($get_filtro_data_final == '') $get_filtro_data_final = null;

$get_filtro_titulo = filter_input(INPUT_GET, 'filtro_titulo');
if($get_filtro_titulo == '') $get_filtro_titulo = null;

$get_filtro_subtitulo = filter_input(INPUT_GET, 'filtro_subtitulo');
if($get_filtro_subtitulo == '') $get_filtro_subtitulo = null;

$get_filtro_tipo = filter_input(INPUT_GET, 'filtro_tipo');
if($get_filtro_tipo == '') $get_filtro_tipo = null;

$get_filtro_conta = filter_input(INPUT_GET, 'filtro_conta');
if($get_filtro_conta == '') $get_filtro_conta = null;

$get_filtro_conciliado = filter_input(INPUT_GET, 'filtro_conciliado') == 'on' ? true : false;
if(!$get_filtro_conciliado) {
    $get_filtro_conciliado = filter_input(INPUT_POST, 'filtro_conciliado') == 'on' ? true : false;
}
$get_filtro_descricao = filter_input(INPUT_GET, 'descricao');
if($get_filtro_descricao === '') {
    $get_filtro_descricao = null;
}
$erro = filter_input(INPUT_GET, 'erro');
$get_pdf = filter_input(INPUT_GET, 'pdf') == 1 ? true : false;
$get_excel = filter_input(INPUT_GET, 'excel') == 1 ? true : false;


$numero_pagina = intval($numero_pagina);
$numero_exibir = intval($numero_exibir);

$bancario_paginas = Ban02::read(
    id:null,
    id_empresa:$_SESSION['usuario']->id_empresa,
    read_paginas:true,
    filtro_data_inicial: $get_filtro_data_inicial  ?? null,
    filtro_data_final: $get_filtro_data_final ?? null,
    filtro_conciliado: $get_filtro_conciliado ?? null,
    filtro_titulo: $get_filtro_titulo ?? null,
    filtro_subtitulo: $get_filtro_subtitulo ?? null,
    filtro_conta: $get_filtro_conta ?? null,
    filtro_tipo: $get_filtro_tipo,
    filtro_descricao: $get_filtro_descricao,
); 

$total_paginas = ceil($bancario_paginas / $numero_exibir);

$novo_documento = buscarDocumento();

$lateral_target = 'movimentacao';
$lateral_bancario = true;
$acao = filter_input(INPUT_GET, 'acao') ?? null;
if($acao == null) $acao = filter_input(INPUT_POST, 'acao') ?? null;
$ofx = filter_input(INPUT_GET, 'ofx', FILTER_SANITIZE_NUMBER_INT);

$filtros = [];
$filtros_get = [];
if ($get_filtro_data_inicial != '')
    $filtros[] = 'filtro_data_inicial=' . $get_filtro_data_inicial;
    $filtros_get['filtro_data_incial']  = $get_filtro_data_inicial;
if ($get_filtro_data_final != '')
    $filtros[] = 'filtro_data_final=' . $get_filtro_data_final;
    $filtros_get['filtro_data_final']  = $get_filtro_data_final;
if ($get_filtro_conciliado)
    $filtros[] = 'filtro_conciliado=on';
    $filtros_get['filtro_conciliado']  = 'on';
if($get_filtro_tipo != '')
    $filtros[] = 'filtro_tipo=' . $get_filtro_tipo;
    $filtros_get['filtro_tipo']  = $get_filtro_tipo;
if($get_filtro_titulo != '' ) 
    $filtros[] = 'filtro_titulo=' . $get_filtro_titulo;
    $filtros_get['filtro_titulo']  = $get_filtro_titulo;
if($get_filtro_subtitulo != '') 
    $filtros[] = 'filtro_subtitulo=' . $get_filtro_subtitulo;
    $filtros_get['filtro_subtitulo']  = $get_filtro_subtitulo;
if($get_filtro_conta != '')
    $filtros[] = 'filtro_conta=' . $get_filtro_conta;
    $filtros_get['filtro_conta']  = $get_filtro_conta;
if($numero_exibir != 10) 
    $filtros[] = 'numero_exibido=' . $numero_exibir;
    $filtros_get['numero_exibir']  = $numero_exibir;
if($get_filtro_descricao != '')
    $filtros[] = 'descricao=' . $get_filtro_descricao;
    $filtros_get['descricao']  = $get_filtro_descricao;

$caminho_exibir = 'movimentacao.php?' . implode('&', $filtros) . '&';
if($numero_pagina != 1) 
    $filtros[] = 'pagina=' . $numero_pagina;
    $filtros_get['numero_pagina']  = $numero_pagina;

if ($filtros != []) {
    
    $caminho = 'movimentacao.php?' . implode('&', $filtros) . '&';
    $caminho_get = urlencode('movimentacao.php?' . implode('&', $filtros) . '&');
    $caminho_sem_pag = $filtros;
    array_pop($caminho_sem_pag);
    $caminho_sem_pag = 'movimentacao.php?' . implode('&', $caminho_sem_pag) . '&';
} else {
    $caminho = 'movimentacao.php?';
    $caminho_get = urlencode('movimentacao.php?');
    $caminho_sem_pag = 'movimentacao.php?';
}
if($get_filtro_conta != null){
    $saldo_geral = Ban02::read(
    filtro_data_inicial: $get_filtro_data_inicial ??null,
    filtro_data_final: $get_filtro_data_final ?? null,
    id_empresa:$_SESSION['usuario']->id_empresa,
    filtro_conta:$get_filtro_conta,
    read_total: true,
);
} else {
    $saldo_geral = Ban02::read(
        id_empresa:$_SESSION['usuario']->id_empresa,
        read_total: true,
    );
}
$movimentacoes_pdf = Ban02::read(
                            id_empresa: $_SESSION['usuario']->id_empresa,
                            filtro_data_inicial: $get_filtro_data_inicial ??null,
                            filtro_data_final: $get_filtro_data_final ?? null,
                            filtro_conciliado:$get_filtro_conciliado,
                            filtro_titulo: $get_filtro_titulo ?? null,
                            filtro_subtitulo: $get_filtro_subtitulo ?? null,
                            filtro_conta: $get_filtro_conta ?? null,
                            filtro_tipo: $get_filtro_tipo ?? null,
                            ordenar_por: 'data',
                            filtro_descricao: $get_filtro_descricao,
                        );
$movimentacoes_totais = $movimentacoes_pdf;

$saldo = 0;
$saldo_inicial = 0;
$saldo_final = 0;


$saldo = Ban02::read(
    id_empresa: $_SESSION['usuario']->id_empresa,
    filtro_data_inicial: $get_filtro_data_inicial,
    filtro_data_final: $get_filtro_data_final,
    filtro_conciliado: $get_filtro_conciliado,
    filtro_titulo: $get_filtro_titulo,
    filtro_subtitulo: $get_filtro_subtitulo,
    filtro_conta: $get_filtro_conta,
    filtro_tipo: $get_filtro_tipo,
    filtro_descricao: $get_filtro_descricao,
    read_total: true
);

$saldo_geral = Ban02::read(
    id_empresa: $_SESSION['usuario']->id_empresa,
    filtro_data_final: $get_filtro_data_final,
    filtro_conta: $get_filtro_conta,
    read_total: true
);

if ($get_filtro_conta != null) {

    $conta = Ban01::read(
        id: $get_filtro_conta,
        id_empresa: $_SESSION['usuario']->id_empresa
    )[0];

    $saldo_geral += $conta->valor;
} else {

    $contas = Ban01::read(id_empresa: $_SESSION['usuario']->id_empresa);

    foreach ($contas as $conta) {
        $saldo_geral += $conta->valor;
    }
}


?>
<!DOCTYPE html>
<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>

<script type="module" src="node_modules/smart-webcomponents/source/modules/smart.combobox.js"></script>
<link rel="stylesheet" type="text/css" href="node_modules/smart-webcomponents/source/styles/smart.default.css" />



<link rel="stylesheet" href="/style.css">

<link rel="stylesheet" href="/componentes/modais/lancamentos/modais.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="/../../../choices/choices.css"></link>
<link rel="stylesheet" href="movimentacao.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>



<body id="body">


    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>
    <div class="main" id="container">
        <?php if($_SESSION['usuario']->processar === 1) { ?>
        <button  data-bs-toggle="modal" data-bs-target="#modal_cadastro_bancario" class="btn btn-primary" >Upload Arquivo OFX / Excel</button>
        <?php } ?>
        <div class="row">
        <div class="card">
            <form method="get" action="movimentacao.php">
                <input type="hidden" name="numero_exibido" value="<?=$numero_exibir?>">
            <div class="card-header d-flex fd-row  align-items-center"> 
                    <!-- <div class="card-header-borda d-flex flex-row align-items-center" style="justify-content: space-between;"> -->
                        <div style=" display: flex; flex-direction: column;" class="input-movimentacao-text-group">    
                            <div class="inputs-pagamento-text">
                                
                                <!-- Data inicial -->
                                    <div>
                                        <label style="display:block; height:17px">Data Inicial:</label>
                                        <input type="date" id="filtro_data_inicial"
                                            name="filtro_data_inicial"
                                            value="<?=$get_filtro_data_inicial?>"
                                            class="form-control" style="border-top-right-radius: 0; height: 3.6em; padding-block: 7.5px; margin:0;">
                                    </div>

                                    <!-- Data final -->
                                    <div>
                                        <label style="display:block; height:17px">Data Final:</label>
                                        <input type="date" id="filtro_data_final"
                                            name="filtro_data_final"
                                            value="<?=$get_filtro_data_final?>" class="form-control"
                                            style="border-top-right-radius: 0; height: 3.6em; padding-block: 7.5px; margin:0;">
                                    </div>

                                    <div class="justify-content-between">
                                        <label >Tipo:</label>
                                        <select id="filtro_tipo" name="filtro_tipo">
                                            <option value="">Selecione</option>
                                            <option <?php if($get_filtro_tipo == 'C') echo 'selected' ?> value="C">Crédito</option>
                                            <option <?php if($get_filtro_tipo == 'D') echo 'selected' ?> value="D">Débito</option>
                                        </select>
                                    </div>
                                    
                                    
                                </div>
                                <div class="inputs-pagamento-text ai-start">
                                
                                    <div class="w-25">
                                        <label for="filtro_documento">Conta:</label>
                                        <select id="conta-filtro" name="filtro_conta">
                                            <option value="">Selecione</option>
                                            <?php
                                    // Buscar todos os subtítulos da empresa
                                    $todasContas = Ban01::read(null, $_SESSION['usuario']->id_empresa);
                                    foreach ($todasContas as $ban01) { ?>
                                        <option value="<?= $ban01->id ?>" <?php if($ban01->id == $get_filtro_conta) echo 'selected' ?> >
                                            <?= htmlspecialchars($ban01->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                            
                                        </select>
                                    </div>

                                    <div class="w-25">
                                        <label for="filtro_data_final">Titulo:</label>
                                        <select id="titulo-filtro" name="filtro_titulo">
                                            <option value="">Selecione</option>
                                            <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, $ban02_tipo);
                                        foreach ($titulos as $titulo) { ?>
                                            <option value="<?= $titulo->id ?>" <?php if($titulo->id == $get_filtro_titulo) echo 'selected' ?> >
                                                <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php } ?>
                                        </select>
                                    </div>

                                    <div class="w-25">
                                        <label for="filtro_documento">Subtitulo:</label>
                                        <select id="subtitulo-filtro" name="filtro_subtitulo">
                                            <?php
                                    // Buscar todos os subtítulos da empresa
                                    $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                    foreach ($todosSubtitulos as $sub) { ?>
                                        <option value="<?= $sub->id ?>"
                                             <?php if($sub->id == $get_filtro_subtitulo) echo 'selected' ?>
                                            data-titulo-id="<?= $sub->id_con01 ?>">
                                            <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                            
                                        </select>
                                    </div>
                                    <div class="d-flex flex-column w-25">
                                        <label for="filtro_documento">Descrição:</label>
                                        <div class="h-100" style="top:1em; position: block;">
                                        <input name="descricao" class="form-control" value="<?=$get_filtro_descricao?>" style="height:100%; top:1em; margin:0; padding:0;" placeholder="Descrição">
                                        </div>
                                    </div>

                                    

                                    
                                    
                                    
                                </div>
                                
                        </div>
                    
                        
                            
                            <div class="inputs-pagamento-btn d-flex fd-column ai-start justify-content-space-between" style="width: 30%; justify-content: space-evenly; gap: 10%;">
                                <div class="form-check">
                                        <label style="font-size: 100%; overflow: visible; white-space: nowrap;" for="filtro_documento">Não Conciliados:</label>
                                        <input type="checkbox" <?php if($get_filtro_conciliado) echo 'checked' ?> name="filtro_conciliado">
                                </div>
                                <div style=" display:flex; flex-direction:row; gap: 3%;">
                                    <div class="d-flex flex-direction-column" style="flex-direction: column; justify-content: center;">
                                        <div class="d-flex flex-direction-row" style="flex-direction: row; justify-content: space-between; gap:0.5em;">
                                            <button class="btn btn-primary btn" type="submit">Buscar</button>
                                            <a class="btn btn-secondary btn" href="movimentacao.php">Limpar</a>
                                        </div>
                                        <button type="button" class="btn btn-success btn" data-bs-toggle="modal" data-bs-target="#modal_conciliar_palavra">Conciliar</button>
                                    </div>
                                     
                                </div>
                            </div>
                            <!-- </div> -->
                            </div> 
                        </form>
                                       
                            
                            
                                    
                                    


                    
                
            
            <div class="card-body dragscroll" style="padding:0;">
                <table class="table table-hover tabela-bancario" id="tabela-bancario">
                    <thead>
                        <tr class="tr-header">
                            <th></th>
                            <th>Documento</th>
                            <th>Data de Lançamento</th>
                            <th>Tipo de Lançamento</th>
                            <th>Valor</th>
                            <th>Conta</th>
                            <th>Título</th>
                            <th>Subtítulo</th>
                            <th>Descrição</th>
                            <th>Descrição Complementar</th>
                            <?php if($_SESSION['usuario']->processar === 1) { ?>
                            <th class="td-acoes">Conciliar</th>
                            <th class="td-acoes">Desmembrar</th>
                            <?php } ?>
                            <?php if($_SESSION['usuario']->processar === 1) { ?>
                            <th class="td-acoes">Editar</th>
                            <?php } else{ ?>
                            <th class="td-acoes">Visualizar</th>
                            <?php } ?>
                            <?php if($_SESSION['usuario']->processar === 1) { ?>
                            <th class="td-acoes">Quitar</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody >
                        <?php

                        
                        $movimentacoes = Ban02::read(
                            id_empresa: $_SESSION['usuario']->id_empresa,
                            numero_exibir: $numero_exibir,
                            numero_pagina: $numero_pagina,
                            filtro_data_inicial: $get_filtro_data_inicial ??null,
                            filtro_data_final: $get_filtro_data_final ?? null,
                            filtro_conciliado:$get_filtro_conciliado,
                            filtro_titulo: $get_filtro_titulo ?? null,
                            filtro_subtitulo: $get_filtro_subtitulo ?? null,
                            filtro_conta: $get_filtro_conta ?? null,
                            filtro_tipo: $get_filtro_tipo,
                            filtro_descricao: $get_filtro_descricao,
                        );

                         if(!empty($movimentacoes)) {
                            
                            foreach($movimentacoes as $i => $movimentacao) {
                                
                                // echo '<pre>';
                                // print_r($movimentacao);
                                // echo '</pre>';
                                if(empty($filtros)) {
                                $link = $caminho . '?';
                            } else {$link = $caminho . '&';}
                                $link .= 'acao=conciliar&id=' . $movimentacao->id;
                            
                            if($movimentacao->id_con01 != null) {
                                $con01 = Con01::read($movimentacao->id_con01, $_SESSION['usuario']->id_empresa)[0];
                            } else {
                                $con01 = null;
                            }
                            if($movimentacao->id_con02 != null) {
                                $con02 = Con02::read($movimentacao->id_con02, $_SESSION['usuario']->id_empresa)[0];
                            } else {
                                $con02 = null;
                            }
                            if($movimentacao->id_con01 != null && $movimentacao->id_con02 != null )   {
                                $cor_parcela = 'parcela_cor_verde';
                            } else {
                                $cor_parcela = 'parcela_cor_vermelha';
                            }
                                $tipo = $movimentacao->valor < 0 ? 'Débito' : 'Crédito';
                                $caminho_quitar = $movimentacao->valor < 0 ? '/usuario/pagar.php?filtro_data_inicial='.$movimentacao->data.'&filtro_data_final='.$movimentacao->data.'&opcao_filtro=abertos&filtro_por=lancamento' : '/usuario/receber.php?filtro_data_inicial='.$movimentacao->data.'&filtro_data_final='.$movimentacao->data.'&opcao_filtro=abertos&filtro_por=lancamento';
                                $data_lancamento = DateTime::createFromFormat('Y-m-d', $movimentacao->data)->format('d/m/Y');
                                $conta_nome = Ban01::read($movimentacao->id_ban01, $_SESSION['usuario']->id_empresa)[0]->nome;
                         {?>
                         <tr class="<?=$cor_parcela?> tr-bancario" 
                             oncontextmenu="return window.openCustomContextMenu ? window.openCustomContextMenu(event, this) : false;"
                             data-id="<?= $movimentacao->id ?>"
                             data-valor="<?= $movimentacao->valor ?>"
                             data-id-original="<?= $movimentacao->id_original ?? '' ?>"
                             data-id-con01="<?= $movimentacao->id_con01 ?? '' ?>"
                             data-id-con02="<?= $movimentacao->id_con02 ?? '' ?>"
                         >
                         <td style=""><input type="checkbox" data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>"  name="id_check[<?=$movimentacao->id?>]" data-id="<?=$movimentacao->id?>"></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?=$movimentacao->documento?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?=$data_lancamento?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?=$tipo?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>">R$ <?=number_format($movimentacao->valor, 2, ',', '.', )?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?=$conta_nome?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?= isset($con01) ? $con01->nome : ''?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?= isset($con02) ? $con02->nome : ''?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?=$movimentacao->descricao?></td>
                            <td <?php if($_SESSION['usuario']->processar === 1) {?> data-bs-toggle="modal" data-bs-target="#modal_conciliar" <?php } ?>data-tipo="<?=$movimentacao->valor > 0 ? 'C' : 'D'?>" data-id="<?=$movimentacao->id?>"><?=$movimentacao->descricao_comp?></td>
                            <?php if($_SESSION['usuario']->processar === 1) { ?>
                                <td class="td-acoes">
                                    <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#modal_conciliar" data-id="<?=$movimentacao->id?>">
                                        <i class="bi bi-clipboard-check"></i>
                                    </button>
                                </td>
                                <td class="td-acoes">
                                    <button class="btn" type="button" 
                                    <?php if($movimentacao->id_original != null ) echo 'disabled'?> 
                                    onclick="window.location.href='<?php if(empty($filtros)) {echo $caminho . '?';} else {echo $caminho . '&';}?>acao=desmembrar&id=<?= $movimentacao->id ?>'">
                                        <i class="bi bi-code-slash"></i>
                                    </button>
                                </td>
                            <?php } ?>
                                <td class="td-acoes"><button class="btn" type="button" onclick="window.location.href='<?php if(empty($filtros)) {echo $caminho . '?';} else {echo $caminho . '&';}?>acao=visualizar&id=<?= $movimentacao->id ?>'"><i class="bi 
                                <?php if($_SESSION['usuario']->processar === 1) { ?>
                                bi-pen-fill
                                <?php } else { ?>
                                bi-eye-fill
                                <?php } ?>
                                "></i></button></td>
                            <?php if($_SESSION['usuario']->processar === 1) { ?>
                                <td class="td-acoes">
                                    <button class="btn" type="button" <?php if($movimentacao->id_con01 == null || $movimentacao->id_con02 == null) echo 'disabled'?> onclick="window.location.href='<?php if(empty($filtros)) {echo $caminho . '?';} else {echo $caminho . '&';}?>acao=quitar_bancario&id=<?=$movimentacao->id?>'" >
                                        <i class="bi bi-arrow-90deg-up" ></i>
                                    </button>
                                </td>
                            <?php } ?>
                        </tr>
                        <?php } } }?>
                    </tbody>
                </table>

            </div>
            <div class="card-footer">
                    <div class="card-select-pagina">
                        <?php
                        ?>
                        <form method="post" action="<?=$caminho?>">
                            <?php if ($numero_exibir != 10) { ?> <input type="hidden" value="<?= $numero_exibir ?>"
                                    name="numero_exibido" /> <?php } ?>



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

                    <div id="totais-lancamento" class="d-flex flex-row">          

                                <div id="total-parcela">Saldo Filtro: R$
                                    <?= number_format($saldo, 2, ',', '.') ?> 
                                </div>

                                <?php if($get_filtro_conta != null) { ?>
                                <div style="margin-inline:10px;">

                                    |

                                </div>

                                    <div id="total-parcela">Saldo Conta: R$
                                        <?= number_format($saldo_geral, 2, ',', '.') ?> 
                                    </div>

                                <?php } ?>
                            
                        </div>

                    <div class="card-select-numero">
                        <div>
                            <form method="post" action="<?=$caminho_exibir?>">

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
            
        </div>

        
        <div id="custom-context-menu" style="display:none; position:absolute; z-index:9999; background:#fff; border:1px solid #ccc; box-shadow:0 2px 8px rgba(0,0,0,0.2); min-width:200px; border-radius:6px; overflow:hidden;">
            <?php if($_SESSION['usuario']->processar === 1) { ?>
                    <button id="menu-conciliar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-clipboard-check"></i> Conciliar</button>
                    <button id="menu-desmembrar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-code-slash"></i> Desmembrar</button>
            <?php } ?>
                    <button id="menu-editar-bancario" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi 
                    <?php if($_SESSION['usuario']->processar === 1) { ?>
                        bi-pen-fill
                        <?php } else { ?>
                        bi-eye-fill
                        <?php } ?>
                        "></i> 
                        <?php if($_SESSION['usuario']->processar === 1) { ?>
                        Editar
                        <?php } else { ?>
                        Visualizar
                        <?php } ?>
                    </button>
            <?php if($_SESSION['usuario']->processar === 1) { ?>
                    <button id="menu-quitar-bancario" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-arrow-90deg-up"></i> Quitar</button>
            <?php } ?>
        </div>
        <div class="relatorios-botoes" style="float:left; width:100%">
            <button class="btn btn-primary btn-sm" id="botao-gerar-pdf" onclick="<?php if($get_pdf) {echo "gerarpdf('movimentacao', document.querySelector('#nome-empresa h1').innerHTML)";} else {?>window.location.href='<?=$caminho?>pdf=1'<?php } ?>">Gerar PDF</button>           
            <button class="btn btn-primary btn-sm" id="botao-gerar-excel" onclick="<?php if($get_excel) {echo "gerarexcel('movimentacao', document.querySelector('#nome-empresa h1').innerHTML)";} else {?>window.location.href='<?=$caminho?>excel=1'<?php } ?>">Gerar Excel</button>           
        </div>



        <div id="totais-lancamento-pdf" style="display:none;">
            <?php
            
            ?>
            <div class="total-parcela" id="saldo-inicial-pdf">
                <?= number_format($saldo_inicial, 2, ',', '.') ?> 
            </div>
            <div class="total-parcela" id="saldo-final-pdf"> <strong>Saldo Final: R$
                <?= number_format($saldo, 2, ',', '.') ?> </strong>
            </div>
        </div>
        <?php if($get_pdf || $get_excel) {?>
        <div style="display:none;">
            <?php require_once __DIR__ . '/../../../componentes/tabelas/pdf/tabela_pdf_mov.php'; ?>
        </div>
        <?php } ?>
    

    <?php 
    
    


    ?>
    </div>
    <?php 
    
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_cadastro_bancario.php';
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_conciliar.php';
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_conciliar_palavra.php';
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_desmembrar.php';
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_visualizar.php';
    
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_quitar_bancario.php';
    
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_quitar.php';
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_quitar_adicionar.php';

        if (isset($_SESSION['ofx_transactions'])) {
        unset($_SESSION['ofx_transactions']);
    }
    if (isset($_SESSION['ofx_conta'])) {
        unset($_SESSION['ofx_conta']);
    }
    if (isset($_SESSION['file_name'])) {
        unset($_SESSION['file_name']);
    }
    if (isset($_SESSION['dias_usados'])) {
        unset($_SESSION['dias_usados']);
    }
    ?>
<?php require_once __DIR__ . '/../../../componentes/footer/footer.php' ?> 
</body>



<script>


        
// Custom context menu for movimentacao table — initialize after DOM
;(function(){
    console.debug('init movimentacao contextmenu script');
    // shared state for inline handler
    window._mov_ctx_row = null;

    // global handler callable from oncontextmenu attribute on rows
    // window.openCustomContextMenu = function(e, row) {
    //     try {
    //         const menu = document.getElementById('custom-context-menu');
    //         if (!menu) return false;
    //         if (!row || !row.classList.contains('tr-bancario')) return false;
    //         e.preventDefault();
    //         e.stopPropagation();
    //         if (e.stopImmediatePropagation) e.stopImmediatePropagation();
    //         console.debug('openCustomContextMenu called', row);
    //         window._mov_ctx_row = row;
    //         const menuWidth = menu.offsetWidth || 220;
    //         const menuHeight = menu.offsetHeight || 160;
    //         let x = e.pageX - 260;
    //         let y = e.pageY - 70;

    //         menu.style.left = x + 'px';
    //         menu.style.top = y + 'px';
    //         menu.style.display = 'block';
    //         const idOriginal = row.getAttribute('data-id-original') || '';
    //         const idCon01 = row.getAttribute('data-id-con01') || '';
    //         const idCon02 = row.getAttribute('data-id-con02') || '';
    //         const btnConc = document.getElementById('menu-conciliar');
    //         const btnDes = document.getElementById('menu-desmembrar');
    //         const btnEd = document.getElementById('menu-editar-bancario');
    //         const btnQ = document.getElementById('menu-quitar-bancario');

    //         row.classList.add('context-menu-open');
            
    //         return false;
    //     } catch (err) { console.error(err); return false; }
    // };

    document.addEventListener('DOMContentLoaded', function(){
        
        const menu = document.getElementById('custom-context-menu');
        if (!menu) {
            console.warn('custom-context-menu not found');
            return;
        }
        const base = '<?php if(empty($filtros)) {echo $caminho . '?';} else {echo $caminho . '&';}?>';

        function openMenuForEvent(e) {
            try {
                let row = e.target.closest('.tr-bancario');
                if (row && !row.classList.contains('tr-header')) {
                    
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.stopImmediatePropagation) e.stopImmediatePropagation();
                    // console.debug('movimentacao contextmenu intercepted', row);
                    window._mov_ctx_row = row;
                    const menuWidth = menu.offsetWidth || 220;
                    const menuHeight = menu.offsetHeight || 160;
                    let x = e.pageX - 260;
                    let y = e.pageY - 70;
                    menu.style.left = x + 'px';
                    menu.style.top = y + 'px';
                    menu.style.display = 'block';

                    // enable/disable based on row attributes
                    const idOriginal = row.getAttribute('data-id-original') || '';
                    const idCon01 = row.getAttribute('data-id-con01') || '';
                    const idCon02 = row.getAttribute('data-id-con02') || '';
                    const btnConc = document.getElementById('menu-conciliar');
                    const btnDes = document.getElementById('menu-desmembrar');
                    const btnEd = document.getElementById('menu-editar-bancario');
                    const btnQ = document.getElementById('menu-quitar-bancario');
                    // visual highlight
                    if(row.getAttribute('data-id-con01') == "" || row.getAttribute('data-id-con02') == "") {
                        btnQ.disabled = 'true';

                    } else {
                        btnQ.removeAttribute('disabled');
                    }
                    
                    row.classList.add('context-menu-open');
                } else {
                    menu.style.display = 'none';
                    if (window._mov_ctx_row) window._mov_ctx_row.classList.remove('context-menu-open');
                    window._mov_ctx_row = null;
                }
            } catch (err) { console.error('contextmenu handler error', err); }
        }

        // capture-phase listener to beat other handlers
        document.addEventListener('contextmenu', openMenuForEvent, true);

        // fallback assignment for environments that may not respect capture listeners
        document.body.oncontextmenu = function(e) {
            // try our handler; if it chooses to open menu it will prevent default
            openMenuForEvent(e);
            // if menu was opened, ensure default prevented
            if (menu.style.display === 'block') return false;
            return true;
        };

        document.addEventListener('click', function(e){ if (!menu.contains(e.target)) { menu.style.display = 'none'; if (window._mov_ctx_row) window._mov_ctx_row.classList.remove('context-menu-open'); window._mov_ctx_row = null; } });
        window.addEventListener('scroll', ()=> { menu.style.display = 'none'; if (window._mov_ctx_row) window._mov_ctx_row.classList.remove('context-menu-open'); window._mov_ctx_row = null; });

        function navigateTo(action, id) { window.location.href = base + 'acao=' + action + '&id=' + id; }

        document.getElementById('menu-conciliar').addEventListener('click', function(){
        document.getElementById('conciliar-id').value = window._mov_ctx_row.getAttribute('data-id')
        document.getElementById('custom-context-menu').style.display = 'none';
        Modal = new bootstrap.Modal(document.getElementById('modal_conciliar'));
        Modal.show();
         });
        document.getElementById('menu-desmembrar').addEventListener('click', function(){ if (!window._mov_ctx_row) return; navigateTo('desmembrar', window._mov_ctx_row.getAttribute('data-id')); });
        document.getElementById('menu-editar-bancario').addEventListener('click', function(){ if (!window._mov_ctx_row) return; navigateTo('visualizar', window._mov_ctx_row.getAttribute('data-id')); });
        document.getElementById('menu-quitar-bancario').addEventListener('click', function(){ if (!window._mov_ctx_row) return; navigateTo('quitar_bancario', window._mov_ctx_row.getAttribute('data-id')); });

    });
})();

    // let origemInput = document.getElementById('desc_comp_original_origem')
    //     let destinoInput = document.getElementById('desc_comp_original_destino')
    //     if (origemInput && destinoInput) {
    //         // Para selects, datas e textos
    //         origemInput.addEventListener('input', function () {
    //             destinoInput.value = origemInput.value;
    //         });
    //     }
        
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

    



    document.getElementById("btnBuscarDoc").addEventListener("click", function () {
        document.getElementById("documento").placeholder = 'Buscando...';
        fetch("/../db/buscar_documento_pag.php")
            .then(response => response.json())
            .then(data => {

                if (data.sucesso) {
                    document.getElementById("documento").value = data.numero;
                } else {
                    alert("Nenhum documento disponível encontrado.");
                }
            })
            .catch(err => console.error("Erro:", err));
    });

    
    


</script>


<script src="gerar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/../../../choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>




<?php if($ofx == 1) {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_cadastro_bancario');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho?>';
            });
        });
    </script>
<?php } ?>

<?php if($acao == 'conciliar') {?>
    <script>
        document.getElementById('modal_conciliar').addEventListener('shown.bs.modal', function () {
    // Inicialize Choices.js aqui para os selects do modal
    initTituloSubtitulo('titulo', 'subtitulo', null);
});
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_conciliar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho?>';
            });
        });
    </script>
<?php } ?>
<?php if($acao == 'desmembrar') {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_desmembrar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho?>';
            });
        });
    </script>
<?php } ?>
<?php if($acao == 'visualizar') {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_visualizar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho?>';
            });
        });
    </script>
<?php } ?>
<?php if($acao == 'quitar_bancario') {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_quitar_bancario');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho?>';
            });
        });
    </script>
<?php } ?>
<?php if($acao == 'quitar') {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_quitar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho?>';
            });
        });
    </script>
<?php } ?>
<?php if($acao == 'quitar_adicionar') {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('quitar_adicionar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho?>';
            });
        });
    </script>
<?php } ?>
<script>
<?php if($get_pdf) {?>
        gerarpdf('pagar', document.querySelector('#nome-empresa h1').innerHTML);
        window.location.href='<?=$caminho?>'
    <?php } ?>  
    <?php if($get_excel) {?>
        gerarexcel('pagar', document.querySelector('#nome-empresa h1').innerHTML);
        window.location.href='<?=$caminho?>'
    <?php } ?>  

        // ========== INICIALIZAÇÃO DO CHOICES.JS ==========
        const tituloFiltroElement = document.querySelector('#titulo-filtro');
        const subtituloFiltroElement = document.querySelector('#subtitulo-filtro');
        tituloModalElement = document.querySelector('#titulo');
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

        <?php if($acao != 'visualizar'){?>

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
            subtituloModalChoice.setChoiceByValue('');

        }

        console.log('Choices.js inicializado em receber.php:', {
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
        
document.addEventListener('DOMContentLoaded', function () {

    const modalConciliar = document.getElementById('modal_conciliar');
    if (!modalConciliar || !tituloModalChoice) return;

    modalConciliar.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;
        if (!button) return;

        const tipo = button.getAttribute('data-tipo'); // 'C' ou 'D'
        if (!tipo) return;

        // 🔴 LIMPA COMPLETAMENTE O CHOICES
        tituloModalChoice.clearStore();
        tituloModalChoice.clearChoices();

        // 🔵 opção padrão

        // 🔎 filtra títulos por tipo
        const titulosFiltrados = todosTitulosModal.filter(t =>
            t.value === '' || t.tipo === tipo
        );

        if (titulosFiltrados.length) {
            tituloModalChoice.setChoices(
                titulosFiltrados.filter(t => t.value !== ''),
                'value',
                'label',
                false
            );
        }

        // 🔁 reseta seleção
        tituloModalChoice.setChoiceByValue('');
    });

});

  
</script>
<?php if($erro == 'valor') { ?>
    <script>
        alert('Um ou mais valores não são aceitos ou são maiores do que o valor inicial')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>
<?php if($erro == 'valor_total') { ?>
    <script>
        alert('O valor total é maior do que o valor inicial')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>
<?php if($erro == 'permissao') { ?>
    <script>
        alert('Você não tem permissão para realizar essa ação')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>
<?php if($erro == 'uso') { ?>
    <script>
        alert('Movimentação já cadastrada')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>
<?php if($erro == 'documento') { ?>
    <script>
        alert('O número do documento já existe')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>
<?php if($erro == 'data_conta') { ?>
    <script>
        alert('As datas importadas são menores do que a data da conta bancária. Por favor, corrija a data da conta e tente novamente.')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>
<?php if($erro == 'cadastrado') { ?>
    <script>
        alert('Movimentação vazia ou já cadastrada')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>
<?php if($erro == 'erro_palavra') { ?>
    <script>
        alert('Não existe nenhuma palavra chave cadastrada')
        window.location.href="<?=$caminho?>"
    </script>
<?php } ?>


</html>