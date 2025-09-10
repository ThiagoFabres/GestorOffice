
       <?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/contas.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/pagar.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$view = filter_input(INPUT_GET, 'view');
$target = filter_input(INPUT_GET, 'target');
$con01 = filter_input(INPUT_GET, 'con01id');
$acao = filter_input(INPUT_GET, 'acao');
$get_opcao = filter_input(INPUT_GET, 'opcao');
$get_id = filter_input(INPUT_GET, 'id');

$get_filtro_data_inicial = filter_input(INPUT_GET, 'filtro_data_inicial');
$get_filtro_data_final = filter_input(INPUT_GET, 'filtro_data_final');
$get_filtro_nome = filter_input(INPUT_GET, 'filtro_nome');
$get_filtro_opcao = filter_input(INPUT_GET, 'opcao_filtro');
$get_filtro_por = filter_input(INPUT_GET, 'filtro_por');
$get_filtro_pagamento = filter_input(INPUT_GET, 'filtro_pagamento');







?>

<!DOCTYPE html>




<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js" integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
            <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
    <title>Gestor Office Control</title>
</head>

<body id="body" >


    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
    <div id="itens-menu">
                        <div class="menu-item">
            <a href="index.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-layers"></i></div> Dashboard </a>
        </div>
    <?php if($_SESSION['usuario']->processar == 1) { ?>
        <div class="menu-item accordion" >

<a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button" aria-expanded="false" aria-controls="cadastrosMenu">
        <i class="bi bi-person"></i> Cadastros
      </a>
      <div class="collapse" id="cadastrosMenu">
        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
          <li><a href="cadastrar.php?cadastro=cliente" class="link-light text-decoration-none"><i class="bi bi-person"></i>Cliente/Fornecedor</a></li>
          <li><a href="cadastrar.php?cadastro=bairro" class="link-light text-decoration-none"><i class="bi bi-houses"></i>Bairro</a></li>
          <li><a href="cadastrar.php?cadastro=cidade" class="link-light text-decoration-none"><i class="bi bi-buildings"></i>Cidade</a></li>
          <li><a href="cadastrar.php?cadastro=pagamento" class="link-light text-decoration-none"><i class="bi bi-cash-coin"></i>Tipo Pagamento</a></li>
          <li><a href="cadastrar.php?cadastro=categoria" class="link-light text-decoration-none"><i class="bi bi-tag"></i>Categoria</a></li>
          
        </ul>
      </div>
        </div>
        <?php } ?>

        <div class="menu-item">
            <a href="contas.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano de Contas </a>
        </div>

        <div class="menu-item">
            <a href="receber.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber </a>
        </div>

        <div class="menu-item">
            <a href="pagar.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a Pagar </a>
        </div>

        <div class="menu-item">
            <a href="dre.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-file-earmark-text"></i></div>DRE</a>
        </div>


        </div>
        </div>

    </nav>


    <div id="header">
        
        <button onclick="encolher()" style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer; z-index:1000;">
            <span class="btn bi bi-list"></span>
        </button>
        
    <div id="titulo-header">
        
        <a>Dashboard</a>
    </div>
    <div id="menu-superior">
        <a class="superior-item" href="/admin/">Dashboard</a>
    </div>
    <div class="conta-header" style="position:relative; float:right; margin-right:2em;">
        <button id="userBtn" type="button" style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer;">
            <span style="color:#181f2b;"><?= htmlspecialchars($_SESSION['usuario']->nome, ENT_QUOTES, 'UTF-8') ?> </span>
        </button>
        <div id="userMenu" style="right:0; z-index: 1000000;">
            <a href="/" class="dropdown-item">
                <i class="bi bi-box-arrow-left"></i> Logout
        </a>
        </div>
    </div>
    </div> 
    
    <div class="main" id="container">

                <div class="botao">
        <button data-bs-toggle="modal" data-bs-target="#modal_receber" class="btn btn-primary btn-lg botao-adm-adicionar">Novo Lançamento</button>
    </div>

    

    
    <div class="tabela">
    

      
        
            <div class="row">
                <div class="col-md-12" style="padding: 0;">
                

                    <div class="card">
                        <div class="card-header"><h3>Contas a Pagar</h3></div>

                        <div class="card-header-div">
        <div class="card-header-borda">
            <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
                <h5 class="card-title">Filtros</h5>
                <form method="get" action="pagar.php">
                    <div class="row">
                        <div class="inputs-pagamento-text" style="display: flex; flex-direction: row; width: 40%;">
                        
                        <div style="width: 30%;">
                            <label for="filtro_data_inicial" style="font-size:0.85em;">Data Inicial:</label>
                            <input type="date" id="filtro_data_inicial" name="filtro_data_inicial" value="<?=$get_filtro_data_inicial;?>" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-top-left-radius: 0.25em; border-bottom-left-radius: 0.25em;">
                        </div>

                        <div style="width: 30%;">
                            <label for="filtro_data_final" style="font-size:0.85em;">Data Final:</label>
                            <input type="date" id="filtro_data_final" name="filtro_data_final" value="<?=$get_filtro_data_final;?>" class="form-control" style="border-radius: 0;">
                        </div>

                        <div style="width: 20%;">
                            <label for="filtro_nome" style="font-size:0.85em;">Nome:</label>
                            <input type="text" id="filtro_nome" name="filtro_nome" class="form-control" value="<?=$get_filtro_nome;?>" placeholder="Nome">
                        </div>

                        <div style="width: 30%;">
                            <label for="filtro_nome" style="font-size:0.85em;">Tipo de Pagamento:</label>
                            <select class="form-control" name="filtro_pagamento">
                        <option value="">Selecione uma forma de pagamento</option>
                        <?php foreach(TipoPagamento::read(null, $_SESSION['usuario']->id_empresa) as $pagamento) { ?>
                            <option <?php if($get_filtro_pagamento == $pagamento->id) {?> selected <?php } ?> value="<?= $pagamento->id ?>">
                                <?= $pagamento->nome ?>
                            </option>
                        <?php } ?>
                    </select>
                        </div>
                        
                        
                    </div>

                    <div class="selects-pagamento" style="width:50%; display: flex; flex-direction: column; justify-content: space-between;">
                        <div class="radio-pagamento" style="width: 100%; max-height: 50%; display: flex; flex-direction: row;">
                            <div style="margin-right: 1em;"><h5 style="font-size: 1em;">Opção:</h5></div>
                            <div class="form-check" style="margin-right: 1em;">
                                
                            <input class="form-check-input" type="radio" id="todos" name="opcao_filtro" <?php if($get_filtro_opcao == '' || empty($get_filtro_opcao)) {?> checked <? } ?> value="" style="height: 65%;" checked>
                            <label class="form-check-label"for="todos">Todos</label>
                            </div>

                            <div class="form-check" style="margin-right: 1em;">
                            <input class="form-check-input" type="radio" id="abertos" name="opcao_filtro" <?php if($get_filtro_opcao == 'abertos' ) {?> checked <? } ?> value="abertos" style="height: 65%;">
                            <label class="form-check-label" for="abertos">Abertos</label>
                            </div>
                            
                            <div class="form-check" style="margin-right: 1em;">
                            <input class="form-check-input"type="radio" id="quitados" name="opcao_filtro" <?php if($get_filtro_opcao == 'quitados' ) {?> checked <? } ?> value="quitados" style="height: 65%;"> 
                            <label class="form-check-label" for="quitados">Quitados</label>
                            </div>
                            
                          
                        </div>


                        
                        <div class="radio-pagamento" style="width: 100%; max-height: 50%; display: flex; flex-direction: row;">
                            <div style="margin-right: 1em;"><h5 style="font-size: 1em;">Filtro por:</h5></div>
                            <div class="form-check"style="margin-right: 1em;">
                            <input class="form-check-input" type="radio" id="lancamento" name="filtro_por" <?php if($get_filtro_por == 'lancamento' || empty($get_filtro_por)) {?> checked <? } ?> value="lancamento" style="height: 65%;">
                            <label class="form-check-label"for="lancamento">Lançamento</label>
                            </div>

                            <div class="form-check"style="margin-right: 1em;">
                            <input class="form-check-input" type="radio" id="vencimento" name="filtro_por" <?php if($get_filtro_por == 'vencimento') {?> checked <? } ?> value="vencimento" style="height: 65%;">
                            <label class="form-check-label" for="vencimento">Vencimento</label>
                            </div>
                            
                            <div class="form-check"style="margin-right: 1em;">
                            <input class="form-check-input"type="radio" id="pagamento" name="filtro_por" <?php if($get_filtro_por == 'pagamento') {?> checked <? } ?> value="pagamento" style="height: 65%;"> 
                            <label class="form-check-label" for="pagamento">Pagamento</label>
                            </div>
                          
                        
                    </div>
                
                
                    </div>

                    
                    
                        <div style="width: 10%; display: flex; flex-direction: column;">
                            <button type="submit" class="btn btn-primary" style="background-color: #5856d6; height: 50%;">Filtrar</button>
                            <a href="pagar.php" class="btn btn-secondary"style="height: 50%;">Limpar</a>
                        </div>
                        
                    </div>
                </form>
                

                    
                </div>
                </div>
</div>
                        <table class="table table-striped">
                <thead>
                    <tr class="tr-clientes">
                        <th>Documento</th>
                        <th>Data de Lançamento</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Parcela Geral</th>
                        <th>Parcela Atual</th>
                        <th>Valor da Parcela</th>
                        <th>Vencimento</th>
                        <th>Data de Pagamento</th>
                        <th>Valor Pago</th>
                        <th>Tipo de Pagamento</th>
                        <th style="width: 20%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
            <?php
                        $parcelas = Pag02::read(null, $_SESSION['usuario']->id_empresa,null, null, null,
            $get_filtro_data_inicial ?? null, $get_filtro_data_final ?? null, $get_filtro_nome ?? null,
            $get_filtro_opcao ?? null, $get_filtro_por ?? null, $get_filtro_pagamento ?? null );
            if (!empty($parcelas)) {
                $recebimentos_pagos = [];?>
                
                <?php foreach ($parcelas as $pag02 ){
                    $parcelas_pagas = false;
                                if(!in_array($pag02->id_pag01, $recebimentos_pagos)){
                                if($pag02->valor_pag != 0) {
                                    $recebimentos_pagos[$pag02->id] = $pag02->id_pag01;
                                } 
                            }
                }?>
            
                        <?php foreach ($parcelas as $pag02) {

                        $data_atual = new DateTime();
                        $data_atual = $data_atual->format('Y-m-d');

                        if(($pag02->vencimento == $data_atual) && $pag02->valor_pag != $pag02->valor_par) {
                            $cor_parcela = 'parcela_cor_amarela';
                        } else if(($pag02->vencimento < $data_atual) && $pag02->valor_pag != $pag02->valor_par) {
                            $cor_parcela = 'parcela_cor_vermelha';
                        } else if(($pag02->vencimento > $data_atual) && $pag02->valor_pag != $pag02->valor_par) {
                            $cor_parcela = 'parcela_cor_azul';
                        } else if($pag02->valor_pag == $pag02->valor_par) {
                            $cor_parcela = 'parcela_cor_verde';
                        }

                        $pag01 = Pag01::read($pag02->id_pag01, $_SESSION['usuario']->id_empresa )[0];

                            if($pag02->id_pgto != null){ 
                                $pagamento = TipoPagamento::read($pag02->id_pgto)[0];
                            } else {
                                $pagamento = null;
                            }; 

                            $data_pag = new DateTime($pag02->data_pag);
                            $data_pag = $data_pag->format('d-m-Y');

                            $data_venc = new DateTime($pag02->vencimento);
                            $data_venc = $data_venc->format('d-m-Y');

                            $cadastro = Cadastro::read($pag01->id_cadastro)[0];
                            $valor_total = number_format($pag01->valor,2 , ',', '');
                            $valor_parcela = number_format($pag02->valor_par,2 , ',', '');
                            $valor_pago = number_format($pag02->valor_pag,2 , ',', '');
                            $link = 'receber.php?view=receber&acao=visualizar&id=' . $pag02->id;
                            
                                $parcelas_pagas = false;
                                if(!in_array($pag02->id_pag01, $recebimentos_pagos)){
                                if($pag02->valor_pag != 0) {
                                    $parcelas_pagas = true;
                                    
                                } else {
                                    $parcelas_pagas = false;
                                }
                            } else {
                                $parcelas_pagas = true;
                            }
                           
                            $ultima_parcela = null;
                            if($pag02->parcela == $pag01->parcelas) $ultima_parcela = true;
                            
                             ?>
                            <!-- style="<?php if($ultima_parcela) {?>border-bottom: 3px solid #5856d6;<?php } ?> border-inline: 1px solid #5856d6;" -->

                            <tr class="tr-clientes <?=$cor_parcela?>" onclick="" 
                            
                             >
                                <td><?=$pag01->documento;?> </td> 
                                <td><?=$pag01->data_lanc;?> </td>
                                <td><?=$cadastro->razao_soc;?> </td>
                                <td><?=$pag01->descricao;?></td>
                                <td>R$ <?=$valor_total?></td>
                                <td><?=$pag01->parcelas?></td>
                                <td><?=$pag02->parcela?></td>
                                <td>R$ <?=$valor_parcela?></td>
                                <td><?=$data_venc ?></td>
                                <td><?php if($pag02->valor_pag == 0){echo 'Não foi pago';} else {echo $data_pag ?? 'Não foi pago';}?></td>
                                <td><?php if($pag02->valor_pag == 0){echo '';} else {echo 'R$' . $valor_pago;}?></td>
                                <td><?=$pagamento->nome ?? ''?></td>
                                <td>

                                 <?php $valor_restante = number_format($pag02->valor_par - $pag02->valor_pag, 2 ,',', '') ?>


                                    <button class="btn btn-primary" data-bs-toggle="modal" <?php if($pag02->valor_pag == $pag02->valor_par){ ?> disabled <?php } ?> data-bs-target="#modal_quitar" data-id="<?=$pag02->id?>"  data-valor-restante="<?= $valor_restante ?>">Quitar</button>
                                    <button class="btn btn-primary" <?php if ($pag02->valor_pag == 0) { ?> disabled <?php } ?> onclick="window.location.href='cadastros_manager.php?view=receber&target=parcela&acao=estornar&pagar=1&id=<?=$pag02->id?>'">Estornar</button>
                                    <button class="btn btn-primary" <?php if($parcelas_pagas == true){ ?> disabled <?php } ?> onclick="window.location.href='editar-pagar.php?acao=editar&id=<?=$pag01->id?>'">Editar</button>
                                    
                                </td>
                            </tr>
                            
                                
                        
                            

                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td>Nenhum Lançamento encontrado</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>


                        </tr>
                    <?php } ?>
            
                </tbody>
            
                </div></table>
    </div>
    </div> 
    </div>
    </div>
        </div>

<div class="modal fade" id="modal_quitar" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Opções da conta</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="content" action="cadastros_manager.php">
                    <input type="hidden" name="view" value="receber">
                    <input type="hidden" name="acao" value="quitar">
                    <input type="hidden" name="target" value="parcela">
                    <input type="hidden" name="pagar" value="1">
                    <input type="hidden" name="id" id="modal_quitar_id" value="">
                    <div class="valor-alvo">
                       
                        <p id="modal_quitar_valor_restante" style="color: #00000096;"></p>
                    </div>
                    <label for="data">Data do pagamento</label>
                    <input class="form-control" type="date" placeholder="dd/mm/aaaa" name="data" value="<?= (new DateTime())->format('Y-m-d') ?>">
                    <label for="valor">Valor pago</label>
                    <input class="form-control" type="text" name="valor" placeholder="Valor pago">
                    <label for="forma_pagamento">Forma de pagamento</label>
                    <select class="form-control" name="forma_pagamento">
                        <option value="">Selecione uma forma de pagamento</option>
                        <?php foreach(TipoPagamento::read(null, $_SESSION['usuario']->id_empresa) as $pagamento) { ?>
                            <option value="<?= $pagamento->id ?>">
                                <?= $pagamento->nome ?>
                            </option>
                        <?php } ?>
                    </select>
                    <div style="margin-bottom: 3em;" class="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary" style="float:right;">Pagar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    

        <div class="modal fade" id="modal_receber" tabindex="-1" role="dialog"
        aria-labelledby="modal_receber_title" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">

                    <h5 class="modal-title" id="modal_receber_long_title">Novo Lançamento</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="editar-pagar.php">
                        <?php $data_lanc = new DateTime();
                        $data_lanc = $data_lanc->format('Y-m-d');?>
                        <input type="hidden" name="view" value="receber">
                        <input type="hidden" name="data_lanc" value="<?= $data_lanc ; ?>">

                        

                        <label for="documento">Documento:</label>
                    <div class="input-documento-group">
                        <div class="input-documento input-form-adm">
                            <!--Nome: -->
                            
                            <input type="text" onchange="checar()" name="documento" id="documento" class="form-control" placeholder="Documento" value="" required>
                        </div>
                            
                        <div class="input-documento-generator">
                            <button type="button" class="form-control" id="btnBuscarDoc"><i class="bi bi-text-center"></i></button>
                        </div>
                    </div>

                        <div class="input-cadastro input-form-adm">
                            <!--Nome: -->
                            <label for="cadastro">Cliente / Fornecedor:</label>
                            <select name="cadastro" class="form-select" id="cadastro">
                                <option value="">Selecione</option>
                                
                                <?php 
                                $cadastros = Cadastro::read(null, null, $_SESSION['usuario']->id_empresa);  
                                foreach ($cadastros as $cadastro) { ?>
                                    <option value="<?= $cadastro->id_cadastro?>"><?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8')?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="input-valor input-form-adm">
                            <!--Nome: -->
                            <label for="valor">Valor:</label>
                            <input type="text" onchange="checar()" name="valor" class="form-control" placeholder="Valor" value="" required>
                        </div>

                        <div class="input-parcelas input-form-adm">
                            <!--Nome: -->
                            <label for="parcelas">Parcelas:</label>
                            <input type="number" onchange="checar()" name="parcelas" class="form-control" placeholder="Parcelas" value="" required>
                        </div>

                        <div class="input-descricao input-form-adm">
                            <!--Nome: -->
                            <label for="descricao">Descrição:</label>
                            <input type="text" onchange="checar()" name="descricao" class="form-control" placeholder="Descrição" value="" required>
                        </div>

                        
                        <div class="titulos-receber" style="display:flex; flex-direction: row;">

                        <div class="input-titulo input-form-adm" style="width: 50%;" >
                            <!--Nome: -->
                            <label for="titulo">Titulo</label>
                            <select name="titulo" class="form-select" id="titulo" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <option value="">Selecione</option>
                                
                                <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa); 
                                foreach ($titulos as $titulo) { ?>
                                    <option value="<?= $titulo->id ?>"><?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="input-subtitulo input-form-adm" style="width: 50%;">
                            <!--Nome: -->
                            <label for="subtitulo">Sub-Titulo</label>
                            <select id="subtitulo" name="subtitulo" class="form-control">
                                <option value="">Selecione</option>
                                <?php
                                // Buscar todos os subtítulos da empresa
                                $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                foreach ($todosSubtitulos as $sub) { ?>
                                    <option value="<?= $sub->id ?>" data-titulo-id="<?= $sub->id_con01 ?>">
                                        <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    
                        <div style="margin-bottom: 3em;" class="footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" >Fechar</button>
                        <button name="acao" value="adicionar" class="btn btn-success" style="float:right; background-color: #5856d6; border: #5856d6;" href="consulta_cliente.php">Salvar</button>                       

                    </form>


                </div>

            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>



</body>

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
document.getElementById('titulo').addEventListener('change', function() {
    var tituloId = this.value;
    var subtituloSelect = document.getElementById('subtitulo');
    var options = subtituloSelect.querySelectorAll('option');

    options.forEach(function(option) {
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
    }}

    document.addEventListener('DOMContentLoaded', function() {
    var modalQuitar = document.getElementById('modal_quitar');
    modalQuitar.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var valorRestante = button.getAttribute('data-valor-restante');
            console.log('ID:', id, 'Valor restante:', valorRestante); // Adicione esta linha
        document.getElementById('modal_quitar_id').value = id;
        document.getElementById('modal_quitar_valor_restante').textContent = "Valor restante da parcela: R$ " + valorRestante;
    });
});

document.getElementById("btnBuscarDoc").addEventListener("click", function() {
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




</html>


