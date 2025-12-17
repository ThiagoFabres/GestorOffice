<?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/contas.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/pagar.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
require_once __DIR__ . '/../db/entities/cidade.php';
require_once __DIR__ . '/../db/entities/bairro.php';
require_once __DIR__ . '/../db/entities/estados.php';
require_once __DIR__ . '/../db/entities/categoria.php';
require_once __DIR__ . '/../db/entities/centrocustos.php';
require_once __DIR__ . '/../db/entities/banco02.php';
require_once __DIR__ . '/../db/entities/banco01.php';

session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}



$recebimentos_pagos = Pag02::readPagos();

$pagar = true;
$lateral_target = 'pagar';

$ordenar_por = filter_input(INPUT_GET, 'ordenar') ?? filter_input(INPUT_POST, 'ordenar');
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

if(!isset($ordenar_por) || $ordenar_por === null){
    if($get_filtro_por != null) {
        switch($get_filtro_por) {
            case 'lancamento':
                $ordenar_por = 'data_lancamento';
                break;
            case 'vencimento':
                $ordenar_por = 'data_vencimento';
                break;
            case 'pagamento':
                $ordenar_por = 'data_pagamento';

                break;
        }
    }
}


$parcela_paginas = Pag02::read(
    id_empresa: $_SESSION['usuario']->id_empresa,
    filtro_data_inicial: $get_filtro_data_inicial ?? null,
    filtro_data_final: $get_filtro_data_final ?? null,
    filtro_documento: $get_filtro_nome ?? null,
    filtro_opcao: $get_filtro_opcao ?? null,
    filtro_por: $get_filtro_por ?? null,
    filtro_pagamento: $get_filtro_pagamento ?? null,
    filtro_cadastro:$get_filtro_cadastro ?? null, 
    filtro_custos: $get_filtro_custo ?? null,
    read_paginas: true
);



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
if ($get_filtro_por != '' && $get_filtro_por != 'lancamento')
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

if ($filtros != []) {   
    $caminho = 'pagar.php?' . implode('&', $filtros);
    $caminho_get = urlencode('pagar.php?' . implode('&', $filtros));
} else {
    $caminho = 'pagar.php';
    $caminho_get = urlencode('pagar.php');
}



?>


<!DOCTYPE html>




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

<?php if($acao == 'visualizar') {?> 
<style>
    .choices div{
    background-color: #eaeaea;
}

.form-select-titulo {
            border-radius: 0;
            height: 100%;
        }
</style>
<?php }?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../componentes/header/header.php' ?>

    <div class="main" id="container">

            
        <div class="row">
            <div class="col-md-12" style="padding: 0;">


                <div class="card">
                    <div class="card-header">
                        <div class="card-header-lancamento">
                        <h3>Contas a Pagar</h3>

                    <div>
                        <button data-bs-toggle="modal" data-bs-target="#modal_receber"
                        class="btn btn-primary btn-lg">Novo Lançamento</button>
                    </div>
                        
                        </div>
                    </div>

                    <div class="card-header-div">

                        <div class="card-header-borda">
                            <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                aria-labelledby="vendas-tab">
                                <h5 class="card-title">Filtros</h5>
                                <form method="get" action="pagar.php">
                                    <?php if ($numero_exibir != 10) { ?> <input type="hidden" value="<?= $numero_exibir ?>"
                                            name="numero_exibido" /> <?php } ?>
                                    <div class="form-pagamento">
                                        <div class="inputs-pagamento-group">
                                            <div class="row">
                                                <div class="inputs-pagamento-text">

                                                    <!-- Data inicial -->
                                                    <div style="width: 25%;">
                                                        <label for="filtro_data_inicial">Data
                                                            Inicial:</label>
                                                        <input type="date" id="filtro_data_inicial"
                                                            name="filtro_data_inicial"
                                                            value="<?= $get_filtro_data_inicial; ?>"
                                                            class="form-control" style="border-top-right-radius: 0;">
                                                    </div>

                                                    <!-- Data final -->
                                                    <div style="width: 25%;">
                                                        <label for="filtro_data_final">Data
                                                            Final:</label>
                                                        <input type="date" id="filtro_data_final"
                                                            name="filtro_data_final"
                                                            value="<?= $get_filtro_data_final; ?>" class="form-control"
                                                            style="border-radius: 0;">
                                                    </div>

                                                    <!-- Documento -->
                                                    <div style="width: 25%;">
                                                        <label for="filtro_nome"
                                                        >Documento:</label>
                                                        <input type="text" id="filtro_nome" name="filtro_nome"
                                                            class="form-control" value="<?= $get_filtro_nome; ?>"
                                                            placeholder="Documento" style="border-radius: 0;">
                                                    </div>

                                                    <!-- Tipo de pagamento -->
                                                
                                                    <div style="width: 25%;">
                                                        <label for="forma_pagamento">Pagamento:</label>
                                                        <select class="form-control" name="forma_pagamento" style="border-top-left-radius: 0; border-bottom-left-radius: 0;
                                                        border-top-right-radius: 0.25em; border-bottom-right-radius: 0.25em;">

                                                            <option value="">Selecione</option>

                                                            <?php foreach (TipoPagamento::read(null, $_SESSION['usuario']->id_empresa) as $pagamento) { ?>
                                                                <option value="<?= $pagamento->id ?>" <?php if ($get_filtro_pagamento == $pagamento->id) { ?> selected
                                                                    <?php } ?>>
                                                                    <?= $pagamento->nome ?>
                                                                </option>
                                                            <?php } ?>

                                                        </select>
                                                    </div>

                                                </div> <!-- fecha inputs-pagamento-text -->
                                                <div class="inputs-pagamento-text inputs-pagamento-select input-select-geral ">

                                                    <div style="display:flex; flex-direction: column;">
                                                        <label for="forma_pagamento">Cliente /
                                                            Fornecedor:</label>
                                                        <select class="form-control" name="filtro_cadastro">
                                                            <option value="">Selecione</option>
                                                            <?php
                                                            foreach (Cadastro::read(null, null, $_SESSION['usuario']->id_empresa) as $cadastro) { ?>
                                                                <option value="<?= $cadastro->id_cadastro ?>" <?php if ($get_filtro_cadastro == $cadastro->id_cadastro) { ?>
                                                                        selected <?php } ?>>
                                                                    <?= $cadastro->nom_fant ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    

                                                    <div
                                                        style="display:flex; flex-direction: column;">
                                                        <label for="forma_pagamento"
                                                        >Titulo:</label>
                                                        <select class="form-control" name="filtro_titulo" 
                                                            id="titulo-filtro" onchange="filtroSubtitulo(true)">
                                                            <option value="">Selecione</option>
                                                            <?php
                                                            foreach (Con01::read(null, $_SESSION['usuario']->id_empresa, 'D') as $titulo) { ?>
                                                                <option value="<?= $titulo->id ?>" <?php if ($get_filtro_titulo == $titulo->id) { ?> selected <?php } ?> >
                                                                    <?= $titulo->nome ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <div style="display:flex; flex-direction: column;">
                                                        <label for="subtitulo-filtro">Subtitulo:</label>
                                                        <select class="form-control" name="filtro_subtitulo"
                                                            id="subtitulo-filtro">
                                                            <?php
                                                            $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                                            foreach ($todosSubtitulos as $sub) { ?>
                                                                <option value="<?= $sub->id ?>"
                                                                    data-titulo-id="<?= $sub->id_con01 ?>" <?php if ($get_filtro_subtitulo == $sub->id) { ?> selected <?php } ?>>
                                                                    <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <div style="display:flex; flex-direction: column;">
                                                        <label for="centro-custos-filtro">Centro de custos:</label>
                                                        <select class="form-control" name="filtro_custo" id="custo-filtro">
                                                            <option value="">Selecione</option>
                                                            <?php
                                                            $centro_custos = CentroCustos::read(null, $_SESSION['usuario']->id_empresa);
                                                            foreach ($centro_custos as $custo) { ?>
                                                                <option value="<?= $custo->id ?>" <?php if ($get_filtro_custo == $custo->id) { ?> selected <?php } ?>>
                                                                    <?= htmlspecialchars($custo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> <!-- fecha row -->
                                        </div> <!-- fecha inputs-pagamento-group -->

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





                                        <div class="btn-filtro">
                                            <button type="submit" class="btn btn-primary"
                                                style="background-color: #5856d6;">Filtrar</button>
                                            <a href="pagar.php" class="btn btn-secondary">Limpar</a>
                                        </div>

                                    </div>
                            </div>
                        </div>
                        </form>



                    </div>
                <div class="tabela-lancamento dragscroll avoid-page-break">
                    <table class="table table-striped avoid-page-break">
                        <thead>
                            <?php
                            if ($direcao == 'ASC') {
                                $seta = '▲';
                            } else if ($direcao == 'DESC') {
                                $seta = '▼';
                            } else {
                                $seta = '';
                            }
                            ?>
                            <tr class="tr-clientes-header">
                                <th>Centro de custos</th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=documento&direcao=<?php echo ($ordenar_por === 'documento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Documento</a><?php if ($ordenar_por == 'documento') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_lancamento&direcao=<?php echo ($ordenar_por === 'data_lancamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Data
                                        de Lançamento</a><?php if ($ordenar_por == 'data_lancamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=nome&direcao=<?php echo ($ordenar_por === 'nome' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Nome</a><?php if ($ordenar_por == 'nome') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a>Descrição</a></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor</a><?php if ($ordenar_por == 'valor') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a>Parcela Geral</a></th>
                                <th><a>Parcela Atual</a></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor_parcela&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor_parcela' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor
                                        da Parcela</a><?php if ($ordenar_por == 'valor_parcela') {
                                            echo $seta;
                                        } ?></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_vencimento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'data_vencimento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Vencimento</a><?php if ($ordenar_por == 'data_vencimento') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'data_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Data
                                        de Pagamento</a><?php if ($ordenar_por == 'data_pagamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor
                                        Pago</a><?php if ($ordenar_por == 'valor_pagamento') {
                                            echo $seta;
                                        } ?></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=tipo_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'tipo_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Tipo
                                        de Pagamento</a><?php if ($ordenar_por == 'tipo_pagamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th>OBS</th>
                                <th>Quitar</th>
                                <th>Estornar</th>
                                <th>Editar</th>
                                <th>Visualizar</th>
                            </tr>
                        </thead>
                        <tbody class="avoid-page-break">
                            <?php
                            $parcelas = Pag02::read(
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
                                numero_exibir: $numero_exibir,
                                numero_pagina: $numero_pagina,
                                ordenar_por: $ordenar_por,
                                direcao: $direcao,
                                filtro_custos: $get_filtro_custo
                            );
                            if (!empty($parcelas)) {
                        
                                $total_valor_pago = 0;
                                $total_valor_par = 0;
                                if (empty($recebimentos_pagos) || $recebimentos_pagos === null)
                                    $recebimentos_pagos = []; 
                                    
                                ?>
                                <?php foreach ($parcelas as $pag02) {


                                    $data_atual = new DateTime();
                                    $data_atual = $data_atual->format('Y-m-d');

                                    if (($pag02->vencimento == $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_amarela';
                                    } else if (($pag02->vencimento < $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_vermelha';
                                    } else if (($pag02->vencimento > $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_azul';
                                    } else if ($pag02->valor_pag > 0) {
                                        $cor_parcela = 'parcela_cor_verde';
                                    }

                                    $pag01 = Pag01::read($pag02->id_pag01, $_SESSION['usuario']->id_empresa)[0];

                                    if ($pag02->id_pgto != null) {
                                        $pagamento = TipoPagamento::read($pag02->id_pgto)[0];
                                    } else {
                                        $pagamento = null;
                                    }
                                    ;

                                    $data_pag = new DateTime($pag02->data_pag);
                                    $data_pag = $data_pag->format('d-m-Y');

                                    $data_venc = new DateTime($pag02->vencimento);
                                    $data_venc = $data_venc->format('d-m-Y');

                                    $data_lanc = new DateTime($pag01->data_lanc);
                                    $data_lanc = $data_lanc->format('d-m-Y');

                                    $cadastro = Cadastro::read($pag01->id_cadastro)[0];
                                    $valor_total = number_format($pag01->valor, 2, ',', '.');
                                    $valor_parcela = number_format($pag02->valor_par, 2, ',', '.');
                                    $valor_pago = number_format($pag02->valor_pag, 2, ',', '.');

                                    $centro_custos = '';
                                    if($pag01->centro_custos != null) {
                                    $centro_custos = CentroCustos::read($pag01->centro_custos, $_SESSION['usuario']->id_empresa)[0]->nome ?? '';
                                    }

                                    $link = 'pagar.php?view=pagar&acao=visualizar&id=' . $pag02->id;

                                    $ultima_parcela = null;
                                    if ($pag02->parcela == $pag01->parcelas)
                                        $ultima_parcela = true;

                                    ?>
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 3px solid #5856d6;<?php } ?> border-inline: 1px solid #5856d6;" -->
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 2px solid #5856d6;<?php } else if ($pag02->parcela == 1) { ?> border-top: 3px solid #5856d6; <?php } ?> border-inline: 2px solid #5856d6;" -->
                                    <tr class="tr-clientes <?= $cor_parcela ?> avoid-page-break context-menu-row"
                                        
                                         >
                                        <td><?=$centro_custos?></td>
                                        <td><?= $pag01->documento; ?> </td>
                                        <td><?= $data_lanc; ?> </td>
                                        <td><?= $cadastro->razao_soc; ?> </td>
                                        <td><?= $pag01->descricao; ?></td>
                                        <td>R$ <?= $valor_total ?></td>
                                        <td><?= $pag01->parcelas ?></td>
                                        <td><?= $pag02->parcela ?></td>
                                        <td>R$ <?= $valor_parcela ?></td>
                                        <td><?= $data_venc ?></td>
                                        <td><?php if ($pag02->valor_pag == 0) {
                                            echo 'Não foi pago';
                                        } else {
                                            echo $data_pag ?? 'Não foi pago';
                                        } ?>
                                        </td>
                                        <td><?php if ($pag02->valor_pag == 0) {
                                            echo '';
                                        } else {
                                            echo 'R$ ' . $valor_pago;
                                        } ?></td>
                                        <td><?= $pagamento->nome ?? '' ?></td>
                                        <td><?= $pag02->obs ?></td>
                                        <td class="td-acoes">
                                            <?php $valor_restante = number_format($pag02->valor_par - $pag02->valor_pag, 2, ',', '.') ?>
                                            <button class="btn btn-primary" data-bs-toggle="modal" <?php if ($pag02->valor_pag > 0) { ?> disabled <?php } ?> data-bs-target="#modal_quitar"
                                                data-id="<?= $pag02->id ?>"  data-valor-restante="<?= $valor_restante ?>"
                                                data-parcela-atual="<?= $pag02->parcela ?>"
                                                data-parcela-geral="<?= $pag01->parcelas ?>"
                                                data-vencimento="<?= $data_venc ?>"
                                                data-documento="<?= htmlspecialchars($pag01->documento, ENT_QUOTES, 'UTF-8') ?>"
                                            ><i class="bi bi-cash-stack"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if ($pag02->valor_pag == 0) { ?> disabled <?php } ?>
                                                onclick="window.location.href='cadastros_manager.php?view=pagar&pagar=1&target=parcela&acao=estornar&id=<?= $pag02->id ?>&caminho=<?= $caminho_get ?>&pagina=<?php if (empty($filtros)) {?>
                                                     <?='?pagina=' . $numero_pagina;?>
                                                <?php } else { ?>
                                                     <?='?pagina=' . $numero_pagina;?>
                                                <?php } ?>&numero_exibido=<?= 'knumero_exibido=' . $numero_exibir ?>'"><i
                                                    class="bi bi-wallet2"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if (in_array($pag02->id_pag01, $recebimentos_pagos)) { ?> disabled <?php } ?>
                                                onclick="window.location.href='pagar.php?id=<?= $pag01->id ?>&acao=editar'"><i
                                                    class="bi bi-pen-fill"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary"
                                                onclick="window.location.href='pagar.php?id=<?= $pag01->id ?>&acao=visualizar'"><i class="bi bi-eye"></i></button>
                                        </td>
                                        
                                    </tr>



                                                    
                                
                                <?php 
                                $total_valor_pago += $pag02->valor_pag;
                                $total_valor_par += $pag02->valor_par;
                                }  
                                ?> 
                                <tr id="tr-totais">
                                    <td style="text-align: end; font-size: 100%;">Totais:</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: end; font-size: 100%;">R$</td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_par, '2', ',', '.')?></td>
                                    <td></td>
                                    <td style="text-align: end; font-size: 100%;">R$</td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_pago, '2', ',', '.')?></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php }  else { ?>
                                <tr >
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
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                </tr>
                            <?php } ?>
                        
                        </tbody>

                
                </table>
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
                                foreach ($recebimentos_pagos as $i => $id_pag01) { ?>

                                    <input type="hidden" name="recebimentos_pagos[<?= $i ?>]" value="<?= $id_pag01 ?>">
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
                        $total_parcelas = 0;
                        $total_pagamentos = 0;
                        $parcelas_totais = Pag02::read(
                        id_empresa: $_SESSION['usuario']->id_empresa, 
                        filtro_data_inicial: $get_filtro_data_inicial, 
                        filtro_data_final: $get_filtro_data_final,
                        filtro_por: $get_filtro_por,
                        filtro_opcao: $get_filtro_opcao,
                    );
                        
                        foreach ($parcelas_totais as $pt) {
                            $total_parcelas += $pt->valor_par;
                            $total_pagamentos += $pt->valor_pag;
                        }
                    ?>


                        <div id="totais-lancamento">          

                            <?php if($get_filtro_opcao == null ||  $get_filtro_opcao == 'todos' || $get_filtro_opcao == 'abertos')  {?>
                                <div id="total-vencido">Total das Parcelas: R$
                                    <?= number_format($total_parcelas, 2, ',', '.') ?> </div>
                                </div>
                            <?php } ?>  
                            <?php if($get_filtro_opcao == 'quitados')  {?>
                                <div id="total-vencido">Total Pago: R$
                                    <?= number_format($total_pagamentos, 2, ',', '.') ?> </div>
                                </div>
                            <?php } ?>



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
                
            </div>
        </div>

        <div class="relatorios-botoes w-100" style="float: left;">
            <button class="btn btn-primary btn-sm" id="botao-gerar-pdf" onclick="gerarpdf('pagar', document.querySelector('#nome-empresa h1').innerHTML)">Gerar PDF</button>
            <button class="btn btn-primary btn-sm" id="botao-gerar-excel" onclick="gerarexcel('pagar', document.querySelector('#nome-empresa h1').innerHTML)">Gerar Excel</button>
        </div>

        <div class="tabela-lancamento dragscroll avoid-page-break"style="display:none;">
                <table class="tabela-lancamento1 table table-striped avoid-page-break" id="tabela-pdf" style="width:297px">
                        <thead>
                            <?php
                            if ($direcao == 'ASC') {
                                $seta = '▲';
                            } else if ($direcao == 'DESC') {
                                $seta = '▼';
                            } else {
                                $seta = '';
                            }
                            ?>
                            <tr class="tr-clientes-header">
                                <th>Centro de custos</th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=documento&direcao=<?php echo ($ordenar_por === 'documento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Documento</a><?php if ($ordenar_por == 'documento') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_lancamento&direcao=<?php echo ($ordenar_por === 'data_lancamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Data
                                        de Lançamento</a><?php if ($ordenar_por == 'data_lancamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=nome&direcao=<?php echo ($ordenar_por === 'nome' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Nome</a><?php if ($ordenar_por == 'nome') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a>Descrição</a></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor</a><?php if ($ordenar_por == 'valor') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a>Parcela Geral</a></th>
                                <th><a>Parcela Atual</a></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor_parcela&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor_parcela' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor
                                        da Parcela</a><?php if ($ordenar_por == 'valor_parcela') {
                                            echo $seta;
                                        } ?></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_vencimento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'data_vencimento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Vencimento</a><?php if ($ordenar_por == 'data_vencimento') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'data_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Data
                                        de Pagamento</a><?php if ($ordenar_por == 'data_pagamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor
                                        Pago</a><?php if ($ordenar_por == 'valor_pagamento') {
                                            echo $seta;
                                        } ?></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=tipo_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'tipo_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Tipo
                                        de Pagamento</a><?php if ($ordenar_por == 'tipo_pagamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th>OBS</th>
                                <th>Quitar</th>
                                <th>Estornar</th>
                                <th>Editar</th>
                                <th>Visualizar</th>
                            </tr>
                        </thead>
<tbody class="avoid-page-break">
                            <?php
                            $parcelas = Pag02::read(
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
                                ordenar_por: $ordenar_por,
                                direcao: $direcao,
                                filtro_custos: $get_filtro_custo
                            );
                            if (!empty($parcelas)) {
                        
                                $total_valor_pago = 0;
                                $total_valor_par = 0;
                                if (empty($recebimentos_pagos) || $recebimentos_pagos === null)
                                    $recebimentos_pagos = []; 
                                    
                                ?>
                                <?php foreach ($parcelas as $pag02) {


                                    $data_atual = new DateTime();
                                    $data_atual = $data_atual->format('Y-m-d');

                                    if (($pag02->vencimento == $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_amarela';
                                    } else if (($pag02->vencimento < $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_vermelha';
                                    } else if (($pag02->vencimento > $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_azul';
                                    } else if ($pag02->valor_pag > 0) {
                                        $cor_parcela = 'parcela_cor_verde';
                                    }

                                    $pag01 = Pag01::read($pag02->id_pag01, $_SESSION['usuario']->id_empresa)[0];

                                    if ($pag02->id_pgto != null) {
                                        $pagamento = TipoPagamento::read($pag02->id_pgto)[0];
                                    } else {
                                        $pagamento = null;
                                    }
                                    ;

                                    $data_pag = new DateTime($pag02->data_pag);
                                    $data_pag = $data_pag->format('d-m-Y');

                                    $data_venc = new DateTime($pag02->vencimento);
                                    $data_venc = $data_venc->format('d-m-Y');

                                    $data_lanc = new DateTime($pag01->data_lanc);
                                    $data_lanc = $data_lanc->format('d-m-Y');

                                    $cadastro = Cadastro::read($pag01->id_cadastro)[0];
                                    $valor_total = number_format($pag01->valor, 2, ',', '.');
                                    $valor_parcela = number_format($pag02->valor_par, 2, ',', '.');
                                    $valor_pago = number_format($pag02->valor_pag, 2, ',', '.');

                                    $centro_custos = '';
                                    if($pag01->centro_custos != null) {
                                    $centro_custos = CentroCustos::read($pag01->centro_custos, $_SESSION['usuario']->id_empresa)[0]->nome ?? '';
                                    }

                                    $link = 'pagar.php?view=pagar&acao=visualizar&id=' . $pag02->id;

                                    $ultima_parcela = null;
                                    if ($pag02->parcela == $pag01->parcelas)
                                        $ultima_parcela = true;

                                    ?>
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 3px solid #5856d6;<?php } ?> border-inline: 1px solid #5856d6;" -->
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 2px solid #5856d6;<?php } else if ($pag02->parcela == 1) { ?> border-top: 3px solid #5856d6; <?php } ?> border-inline: 2px solid #5856d6;" -->
                                    
                                     <tr class="tr-clientes <?= $cor_parcela ?> avoid-page-break context-menu-row"
                                         data-id="<?= $pag02->id ?>"
                                         data-id-pag01="<?= $pag01->id ?>"
                                         data-valor-pag="<?= $pag02->valor_pag ?>"
                                         data-valor-restante="<?= number_format($pag02->valor_par - $pag02->valor_pag, 2, ',', '.') ?>"
                                         data-parcela-atual="<?= $pag02->parcela ?>"
                                         data-parcela-geral="<?= $pag01->parcelas ?>"
                                         data-vencimento="<?= $data_venc ?>"
                                         data-documento="<?= htmlspecialchars($pag01->documento, ENT_QUOTES, 'UTF-8') ?>"
                                         data-id-pag01-recebido="<?= in_array($pag02->id_pag01, $recebimentos_pagos) ? '1' : '0' ?>"
                                         onclick="">
                                        

                                        <td><?=$centro_custos?></td>
                                        <td><?= $pag01->documento; ?> </td>
                                        <td><?= $data_lanc; ?> </td>
                                        <td><?= $cadastro->razao_soc; ?> </td>
                                        <td><?= $pag01->descricao; ?></td>
                                        <td>R$ <?= $valor_total ?></td>
                                        <td><?= $pag01->parcelas ?></td>
                                        <td><?= $pag02->parcela ?></td>
                                        <td>R$ <?= $valor_parcela ?></td>
                                        <td><?= $data_venc ?></td>
                                        <td><?php if ($pag02->valor_pag == 0) {
                                            echo 'Não foi pago';
                                        } else {
                                            echo $data_pag ?? 'Não foi pago';
                                        } ?>
                                        </td>
                                        <td><?php if ($pag02->valor_pag == 0) {
                                            echo '';
                                        } else {
                                            echo 'R$ ' . $valor_pago;
                                        } ?></td>
                                        <td><?= $pagamento->nome ?? '' ?></td>
                                        <td><?= $pag02->obs ?></td>
                                        <td class="td-acoes">
                                            <?php $valor_restante = number_format($pag02->valor_par - $pag02->valor_pag, 2, ',', '.') ?>
                                            <button class="btn btn-primary" data-bs-toggle="modal" <?php if ($pag02->valor_pag > 0) { ?> disabled <?php } ?> data-bs-target="#modal_quitar"
                                                data-id="<?= $pag02->id ?>"  data-valor-restante="<?= $valor_restante ?>"
                                                data-parcela-atual="<?= $pag02->parcela ?>"
                                                data-parcela-geral="<?= $pag01->parcelas ?>"
                                                data-vencimento="<?= $data_venc ?>"
                                                data-documento="<?= htmlspecialchars($pag01->documento, ENT_QUOTES, 'UTF-8') ?>"
                                            ><i class="bi bi-cash-stack"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if ($pag02->valor_pag == 0) { ?> disabled <?php } ?>
                                                onclick="window.location.href='cadastros_manager.php?view=pagar&pagar=1&target=parcela&acao=estornar&id=<?= $pag02->id ?>&caminho=<?= $caminho_get ?>&pagina=<?php if (empty($filtros)) {?>
                                                     <?='?pagina=' . $numero_pagina;?>
                                                <?php } else { ?>
                                                     <?='?pagina=' . $numero_pagina;?>
                                                <?php } ?>&numero_exibido=<?= 'knumero_exibido=' . $numero_exibir ?>'"><i
                                                    class="bi bi-wallet2"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if (in_array($pag02->id_pag01, $recebimentos_pagos)) { ?> disabled <?php } ?>
                                                onclick="window.location.href='pagar.php?id=<?= $pag01->id ?>&acao=editar'"><i
                                                    class="bi bi-pen-fill"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary"
                                                onclick="window.location.href='pagar.php?id=<?= $pag01->id ?>&acao=visualizar'"><i class="bi bi-eye"></i></button>
                                        </td>
                                        
                                    </tr>



                                                    
                                
                                <?php 
                                $total_valor_pago += $pag02->valor_pag;
                                $total_valor_par += $pag02->valor_par;
                                
                                }  
                                ?> 
                                <tr id="tr-totais">
                                    <td style="text-align: end; font-size: 100%;">Totais:</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: end; font-size: 100%;">R$</td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_par, '2', ',', '.')?></td>
                                    <td></td>
                                    <td style="text-align: end; font-size: 100%;">R$</td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_pago, '2', ',', '.')?></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php }  else { ?>
                                <tr >
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
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                </tr>
                            <?php } ?>
                        
                        </tbody>

                
                </table>
                </div>

                <!-- Context menu for table rows -->
                <div id="custom-context-menu" style="display:none; position:absolute; z-index:9999; background:#fff; border:1px solid #ccc; box-shadow:0 2px 8px rgba(0,0,0,0.2); min-width:180px; border-radius:6px; overflow:hidden;">
                    <button id="menu-quitar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-cash-stack"></i> Quitar</button>
                    <button id="menu-estornar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-wallet2"></i> Estornar</button>
                    <button id="menu-editar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-pen-fill"></i> Editar</button>
                    <button id="menu-visualizar" class="dropdown-item btn btn-light w-100 text-start" type="button"><i class="bi bi-eye"></i> Visualizar</button>
                </div>

    </div>                                 
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_quitar.php'; ?>
    </div>                          
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_cadastro_pagamento.php'; ?>
    </div>  
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_titulo.php'; ?>
    </div>  
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_subtitulo.php'; ?>
    </div>
    </div>                 
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_cadastro_cidade.php'; ?>
    </div>
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_cadastro_bairro.php'; ?>        
    </div>
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_cadastro_categoria.php'; ?>
    </div>  
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_cadastro.php'; ?>
    </div>  
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_cadastro_custos.php'; ?>
    </div>  
    <?php require_once __DIR__ . '/../componentes/modais/lancamentos/pagar/modal_receber.php'; ?>


    

    

</body>
<script>
// Custom context menu behavior
;(function(){
    const menu = document.getElementById('custom-context-menu');
    let currentRow = null;

    document.addEventListener('contextmenu', function(e){
        // target any table data row (including clicks inside cells or buttons)
        let row = e.target.closest('.tr-clientes');
        // ignore header rows or other non-data rows
        if (row && !row.classList.contains('tr-clientes-header')) {
            // stop browser menu and any propagation; run in capture to beat other handlers
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();
            console.debug('custom contextmenu intercept', row);
            currentRow = row;
            // compute menu size and position to keep within viewport
            const menuWidth = menu.offsetWidth || 220;
            const menuHeight = menu.offsetHeight || 160;
            let x = e.pageX - 260;
            let y = e.pageY - 70;
           
            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
            menu.style.display = 'block';
            // enable/disable items based on data attrs
            const valorPag = parseFloat(row.getAttribute('data-valor-pag') || '0');
            const recebido = row.getAttribute('data-id-pag01-recebido') === '1';
            const btnQ = document.getElementById('menu-quitar');
            const btnE = document.getElementById('menu-estornar');
            const btnEd = document.getElementById('menu-editar');
            if (btnQ) btnQ.disabled = valorPag > 0;
            if (btnE) btnE.disabled = valorPag == 0;
            if (btnEd) btnEd.disabled = recebido;
        } else {
            // allow browser context menu when not on a data row
            menu.style.display = 'none';
        }
    }, true);

    document.addEventListener('click', function(e){
        if (!menu.contains(e.target)) menu.style.display = 'none';
    });
    window.addEventListener('scroll', ()=> menu.style.display = 'none');

    // actions
    document.getElementById('menu-quitar').addEventListener('click', function(){
        if (!currentRow) return;
        const id = currentRow.getAttribute('data-id');
        const valorRestante = currentRow.getAttribute('data-valor-restante');
        const parcelaAtual = currentRow.getAttribute('data-parcela-atual');
        const parcelaGeral = currentRow.getAttribute('data-parcela-geral');
        const vencimento = currentRow.getAttribute('data-vencimento');
        const documento = currentRow.getAttribute('data-documento');
        const modalEl = document.getElementById('modal_quitar');
        if (modalEl) {
            document.getElementById('modal_quitar_id').value = id;
            const vr = document.getElementById('modal_quitar_valor_restante'); if (vr) vr.textContent = 'Valor restante da parcela: R$ ' + valorRestante;
            const pa = document.getElementById('modal_quitar_parcela_atual'); if (pa) pa.textContent = parcelaAtual || '';
            const pg = document.getElementById('modal_quitar_parcela_geral'); if (pg) pg.textContent = parcelaGeral || '';
            const vv = document.getElementById('modal_quitar_vencimento'); if (vv) vv.textContent = vencimento || '';
            const doc = document.getElementById('modal_quitar_documento'); if (doc) doc.textContent = documento || '';
            const modalVal = document.getElementById('modal_quitar_valor'); if (modalVal) modalVal.placeholder = valorRestante || '';
            const bsModal = new bootstrap.Modal(modalEl); bsModal.show();
        }
        menu.style.display = 'none';
    });

    document.getElementById('menu-estornar').addEventListener('click', function(){
        if (!currentRow) return;
        const id = currentRow.getAttribute('data-id');
        const url = 'cadastros_manager.php?view=pagar&pagar=1&target=parcela&acao=estornar&id=' + id + '&caminho=<?= $caminho_get ?>&pagina=<?= $numero_pagina ?>&numero_exibido=knumero_exibido=<?= $numero_exibir ?>';
        window.location.href = url;
    });

    document.getElementById('menu-editar').addEventListener('click', function(){
        if (!currentRow) return;
        const idPag01 = currentRow.getAttribute('data-id-pag01');
        window.location.href = 'pagar.php?id=' + idPag01 + '&acao=editar';
    });

    document.getElementById('menu-visualizar').addEventListener('click', function(){
        if (!currentRow) return;
        const idPag01 = currentRow.getAttribute('data-id-pag01');
        window.location.href = 'pagar.php?id=' + idPag01 + '&acao=visualizar';
    });

})();
</script>
<script src="gerar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
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
    
        // set the modal id and the displayed remaining value when the page was opened with ?quitar_id=
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
                window.location.href = 'pagar.php';
            });
        });
    <?php } ?>

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
    
    
    ;}, 100);
});
<?php } ?>
<?php if (isset($acao) && ($acao == 'adicionar' || $acao == 'visualizar')) { 
    if($acao == 'visualizar') {?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'pagar.php';
            });
        });
    <?php } else if(!isset($target) || $target == 'cadastro') {?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'pagar.php';
            });
        });
    <?php } else if($target == 'quitar') { ?>

        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_quitar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'pagar.php';
            });
        });
    <?php } else if (isset($target) && $target != 'cadastro' && $target != 'quitar'){ ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_cadastro');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'pagar.php';
            });
        });
<?php }}?>

<?php if(isset($acao) && $acao == 'editar') { ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'pagar.php';
            });
        });
<?php } ?>
</script>





</html>