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

session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$recebimentos_pagos = Pag02::readPagos();

$ordenar_por = filter_input(INPUT_GET, 'ordenar');
$direcao = filter_input(INPUT_GET, 'direcao');

$view = filter_input(INPUT_GET, 'view');
$target = filter_input(INPUT_GET, 'target');
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



$parcela_paginas = Pag02::read(
    null,
    $_SESSION['usuario']->id_empresa,
    null,
    null,
    null,
    $get_filtro_data_inicial ?? null,
    $get_filtro_data_final ?? null,
    $get_filtro_nome ?? null,
    $get_filtro_opcao ?? null,
    $get_filtro_por ?? null,
    $get_filtro_pagamento ?? null,
    null,
    null,
    null,
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
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script type="module" src="node_modules/smart-webcomponents/source/modules/smart.combobox.js"></script>
<link rel="stylesheet" type="text/css" href="node_modules/smart-webcomponents/source/styles/smart.default.css" />



<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="../choices/choices.css"></link>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
        <div id="itens-menu">
            <div class="menu-item">
                <a href="index.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-layers"></i></div> Dashboard
                </a>
            </div>
            <?php if ($_SESSION['usuario']->processar == 1) { ?>
                <div class="menu-item accordion">

                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button"
                        aria-expanded="false" aria-controls="cadastrosMenu">
                        <i class="bi bi-person"></i> Cadastros
                    </a>
                    <div class="collapse" id="cadastrosMenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                            <li><a href="cadastrar.php?cadastro=cliente" class="link-light text-decoration-none"><i
                                        class="bi bi-person"></i>Cliente/Fornecedor</a></li>
                            <li><a href="cadastrar.php?cadastro=bairro" class="link-light text-decoration-none"><i
                                        class="bi bi-houses"></i>Bairro</a></li>
                            <li><a href="cadastrar.php?cadastro=cidade" class="link-light text-decoration-none"><i
                                        class="bi bi-buildings"></i>Cidade</a></li>
                            <li><a href="cadastrar.php?cadastro=pagamento" class="link-light text-decoration-none"><i
                                        class="bi bi-cash-coin"></i>Tipo Pagamento</a></li>
                            <li><a href="cadastrar.php?cadastro=categoria" class="link-light text-decoration-none"><i
                                        class="bi bi-tag"></i>Categoria</a></li>

                        </ul>
                    </div>
                </div>
            <?php } ?>

            <div class="menu-item">
                <a href="contas.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano
                    de Contas
                </a>
            </div>

            <div class="menu-item">
                <a href="receber.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber
                </a>
            </div>

            <div class="menu-item menu-item-atual">
                <a href="pagar.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a
                    Pagar
                </a>
            </div>

            <div class="menu-item">
                <a href="dre/sintetico.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-file-earmark-text"></i></div>DRE
                </a>
            </div>


        </div>
        </div>

    </nav>


    <div id="header">

        <button onclick="encolher()"
            style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer; z-index:1000;">
            <span class="btn bi bi-list"></span>
        </button>

        <div id="titulo-header">

            <a>Dashboard</a>
        </div>
        <div id="menu-superior">
            <a class="superior-item" href="/admin/">Dashboard</a>
        </div>
        <div class="conta-header" style="position:relative; float:right; margin-right:2em;">
            <button id="userBtn" type="button"
                style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer;">
                <span style="color:#181f2b;"><?= htmlspecialchars($_SESSION['usuario']->nome, ENT_QUOTES, 'UTF-8') ?>
                </span>
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
            <button data-bs-toggle="modal" data-bs-target="#modal_receber"
                class="btn btn-primary btn-lg botao-adm-adicionar">Novo Lançamento</button>
        </div>
        <div class="row">
            <div class="col-md-12" style="padding: 0;">


                <div class="card">
                    <div class="card-header">
                        <h3>Contas a Pagar</h3>
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
                                                    <div style="width: 25%; height: 3em;">
                                                        <label for="filtro_data_inicial" style="font-size:0.85em;">Data
                                                            Inicial:</label>
                                                        <input type="date" id="filtro_data_inicial"
                                                            name="filtro_data_inicial"
                                                            value="<?= $get_filtro_data_inicial; ?>"
                                                            class="form-control" style="border-top-right-radius: 0;">
                                                    </div>

                                                    <!-- Data final -->
                                                    <div style="width: 25%; height: 3em;">
                                                        <label for="filtro_data_final" style="font-size:0.85em;">Data
                                                            Final:</label>
                                                        <input type="date" id="filtro_data_final"
                                                            name="filtro_data_final"
                                                            value="<?= $get_filtro_data_final; ?>" class="form-control"
                                                            style="border-radius: 0;">
                                                    </div>

                                                    <!-- Documento -->
                                                    <div style="width: 20%;">
                                                        <label for="filtro_nome"
                                                            style="font-size:0.85em;">Documento:</label>
                                                        <input type="text" id="filtro_nome" name="filtro_nome"
                                                            class="form-control" value="<?= $get_filtro_nome; ?>"
                                                            placeholder="Documento" style="border-radius: 0;">
                                                    </div>

                                                    <!-- Tipo de pagamento -->
                                                    <div style="width: 20%;">
                                                        <label for="forma_pagamento" style="font-size:0.85em;">Pagamento:</label>
                                                        <select class="form-control" name="forma_pagamento" style="border-top-left-radius: 0; border-bottom-left-radius: 0;
                                                        border-top-right-radius: 0.25em; border-bottom-right-radius: 0.25em;">

                                                            <option value="">Selecione uma forma de pagamento</option>

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
                                                        <label for="forma_pagamento" style="font-size:0.85em;">Cliente /
                                                            Fornecedor:</label>
                                                        <select class="form-control" name="filtro_cadastro">
                                                            <option value="">Selecione um Cliente / Fornecedor</option>
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
                                                            style="font-size:0.85em;">Titulo:</label>
                                                        <select class="form-control" name="filtro_titulo"
                                                            id="titulo-filtro" onchange="filtroSubtitulo(true)">
                                                            <option value="">Selecione um Titulo</option>
                                                            <?php
                                                            foreach (Con01::read(null, $_SESSION['usuario']->id_empresa, 'D') as $titulo) { ?>
                                                                <option value="<?= $titulo->id ?>" <?php if ($get_filtro_titulo == $titulo->id) { ?> selected <?php } ?>>
                                                                    <?= $titulo->nome ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <div
                                                        style="display:flex; flex-direction: column;">
                                                        <label for="forma_pagamento"
                                                            style="font-size:0.85em;">Subtitulo:</label>
                                                        <select class="form-control" name="filtro_subtitulo"
                                                            id="subtitulo-filtro">
                                                            <option value="">Selecione um Subtitulo</option>
                                                            <?php
                                                            // Buscar todos os subtítulos da empresa
                                                            $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                                            foreach ($todosSubtitulos as $sub) { ?>
                                                                <option value="<?= $sub->id ?>"
                                                                    data-titulo-id="<?= $sub->id_con01 ?>" <?php if ($get_filtro_subtitulo == $sub->id) { ?> selected <?php } ?>>
                                                                    <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> <!-- fecha row -->
                                        </div> <!-- fecha inputs-pagamento-group -->

                                        <div class="selects-pagamento">

                                            <!-- Primeira linha de radios (opção) -->
                                            <div class="radio-pagamento">
                                                <div style="margin-right: 1em;">
                                                    <h5 style="font-size: 1em;">Opção:</h5>
                                                </div>

                                                <div class="form-check" style="margin-right: 1em;">
                                                    <input class="form-check-input" type="radio" id="todos"
                                                        name="opcao_filtro" value="" <?php if ($get_filtro_opcao == '' || empty($get_filtro_opcao)) { ?> checked <?php } ?>>
                                                    <label class="form-check-label" for="">Todos</label>
                                                </div>

                                                <div class="form-check" style="margin-right: 1em;">
                                                    <input class="form-check-input" type="radio" id="abertos"
                                                        name="opcao_filtro" value="abertos" <?php if ($get_filtro_opcao == 'abertos') { ?> checked <?php } ?>
                                                        value="abertos">
                                                    <label class="form-check-label" for="abertos">Abertos</label>
                                                </div>

                                                <div class="form-check" style="margin-right: 1em;">
                                                    <input class="form-check-input" type="radio" id="quitados"
                                                        name="opcao_filtro" value="quitados" <?php if ($get_filtro_opcao == 'quitados') { ?> checked <?php } ?>
                                                        value="quitados">
                                                    <label class="form-check-label" for="quitados">Quitados</label>
                                                </div>
                                            </div>


                                            <!-- Segunda linha de radios (filtro por) -->
                                            <div class="radio-pagamento" style="...">
                                                <div style="margin-right: 1em;">
                                                    <h5 style="font-size: 1em;">Filtro por:</h5>
                                                </div>

                                                <div class="form-check" style="margin-right: 1em;">
                                                    <input class="form-check-input" type="radio" id="lancamento"
                                                        name="filtro_por" <?php if ($get_filtro_por == 'lancamento' || empty($get_filtro_por)) { ?> checked <?php } ?>
                                                        value="lancamento">
                                                    <label class="form-check-label" for="lancamento">Lançamento</label>
                                                </div>

                                                <div class="form-check" style="margin-right: 1em;">
                                                    <input class="form-check-input" type="radio" id="vencimento"
                                                        name="filtro_por" <?php if ($get_filtro_por == 'vencimento') { ?>
                                                            checked <?php } ?> value="vencimento">
                                                    <label class="form-check-label" for="vencimento">Vencimento</label>
                                                </div>

                                                <div class="form-check" style="margin-right: 1em;">
                                                    <input class="form-check-input" type="radio" id="pagamento"
                                                        name="filtro_por" <?php if ($get_filtro_por == 'pagamento') { ?>
                                                            checked <?php } ?> value="pagamento">
                                                    <label class="form-check-label" for="pagamento">Pagamento</label>
                                                </div>
                                            </div>

                                        </div> <!-- fecha selects-pagamento -->





                                        <div
                                            style="width: 10%; display: flex; flex-direction: column; justify-content: space-evenly;">
                                            <button type="submit" class="btn btn-primary"
                                                style="background-color: #5856d6;">Filtrar</button>
                                            <a href="pagar.php" class="btn btn-secondary">Limpar</a>
                                        </div>

                                    </div>
                            </div>
                        </div>
                        </form>



                    </div>
                    <table class="table table-striped">
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
                                        href="<?= $caminho ?>?ordenar=valor&direcao=<?php echo ($ordenar_por === 'valor' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor</a><?php if ($ordenar_por == 'valor') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a>Parcela Geral</a></th>
                                <th><a>Parcela Atual</a></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor_parcela&direcao=<?php echo ($ordenar_por === 'valor_parcela' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor
                                        da Parcela</a><?php if ($ordenar_por == 'valor_parcela') {
                                            echo $seta;
                                        } ?></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_vencimento&direcao=<?php echo ($ordenar_por === 'data_vencimento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Vencimento</a><?php if ($ordenar_por == 'data_vencimento') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_pagamento&direcao=<?php echo ($ordenar_por === 'data_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Data
                                        de Pagamento</a><?php if ($ordenar_por == 'data_pagamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=valor_pagamento&direcao=<?php echo ($ordenar_por === 'valor_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Valor
                                        Pago</a><?php if ($ordenar_por == 'valor_pagamento') {
                                            echo $seta;
                                        } ?></th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=tipo_pagamento&direcao=<?php echo ($ordenar_por === 'tipo_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Tipo
                                        de Pagamento</a><?php if ($ordenar_por == 'tipo_pagamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th>Quitar</th>
                                <th>Estornar</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                direcao: $direcao
                            );
                            if (!empty($parcelas)) {
                                if (empty($recebimentos_pagos) || $recebimentos_pagos === null)
                                    $recebimentos_pagos = []; ?>

                                <?php foreach ($parcelas as $pag02) {

                                    $data_atual = new DateTime();
                                    $data_atual = $data_atual->format('Y-m-d');

                                    if (($pag02->vencimento == $data_atual) && $pag02->valor_pag != $pag02->valor_par) {
                                        $cor_parcela = 'parcela_cor_amarela';
                                    } else if (($pag02->vencimento < $data_atual) && $pag02->valor_pag != $pag02->valor_par) {
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
                                    $valor_total = number_format($pag01->valor, 2, ',', '');
                                    $valor_parcela = number_format($pag02->valor_par, 2, ',', '');
                                    $valor_pago = number_format($pag02->valor_pag, 2, ',', '');
                                    $link = 'pagar.php?view=pagar&acao=visualizar&id=' . $pag02->id;

                                    $ultima_parcela = null;
                                    if ($pag02->parcela == $pag01->parcelas)
                                        $ultima_parcela = true;

                                    ?>
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 3px solid #5856d6;<?php } ?> border-inline: 1px solid #5856d6;" -->
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 2px solid #5856d6;<?php } else if ($pag02->parcela == 1) { ?> border-top: 3px solid #5856d6; <?php } ?> border-inline: 2px solid #5856d6;" -->
                                    <tr class="tr-clientes <?= $cor_parcela ?>" onclick="">
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
                                            echo 'R$' . $valor_pago;
                                        } ?></td>
                                        <td><?= $pagamento->nome ?? '' ?></td>
                                        <td class="td-acoes">
                                            <?php $valor_restante = number_format($pag02->valor_par - $pag02->valor_pag, 2, ',', '') ?>
                                            <button class="btn btn-primary" data-bs-toggle="modal" <?php if ($pag02->valor_pag > 0) { ?> disabled <?php } ?> data-bs-target="#modal_quitar"
                                                data-id="<?= $pag02->id ?>" data-valor-restante="<?= $valor_restante ?>"><i
                                                    class="bi bi-cash-stack"></i></button>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if ($pag02->valor_pag == 0) { ?> disabled <?php } ?>
                                                onclick="window.location.href='cadastros_manager.php?view=pagar&pagar=1&target=parcela&acao=estornar&id=<?= $pag02->id ?>&caminho=<?= $caminho_get ?>&pagina=<?php if (empty($filtros)) {
                                                    echo '?pagina=' . $numero_pagina;
                                                } else {
                                                    echo '?pagina=' . $numero_pagina;
                                                } ?>&numero_exibido=<?= 'knumero_exibido=' . $numero_exibir ?>'"><i
                                                    class="bi bi-wallet2"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if (in_array($pag02->id_pag01, $recebimentos_pagos)) { ?> disabled <?php } ?>
                                                onclick="window.location.href='editar-pagar.php?id=<?= $pag01->id ?>&acao=editar'"><i
                                                    class="bi bi-pen-fill"></i></button>
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
                                    <td></td>
                                    <td></td>

                                </tr>
                            <?php } ?>

                        </tbody>

                </div>
                </table>
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

        <div class="relatorios-botoes">
            <button class="btn btn-primary btn-sm" id="botao-gerar-pdf" onclick="gerarpdf('pagar')">Gerar PDF</button>
            <button class="btn btn-primary btn-sm" id="botao-gerar-excel" onclick="gerarexcel('pagar')">Gerar
                Excel</button>
        </div>


        <div class="modal fade" id="modal_quitar" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Opções da conta</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="content" action="cadastros_manager.php"
                            onkeydown="return event.key != 'Enter';">
                            <input type="hidden" name="view" value="pagar">
                            <input type="hidden" name="acao" value="quitar">
                            <input type="hidden" name="target" value="parcela">
                            <input type="hidden" name="id" id="modal_quitar_id" value="">
                            <input type="hidden" name="caminho" value="<?= $caminho ?>">
                            <input type="hidden" name="pagar" value="1">
                            <?php if (!empty($filtros)) { ?>
                                <input type="hidden" name="pagina" value="&pagina=<?= $numero_pagina ?>">
                                <input type="hidden" name="numero_exibido" value="&numero_exibido=<?= $numero_exibir ?>">
                            <?php } else { ?>
                                <input type="hidden" name="pagina" value="?pagina=<?= $numero_pagina ?>">
                                <input type="hidden" name="numero_exibido" value="&numero_exibido=<?= $numero_exibir ?>">
                            <?php } ?>

                            <div class="valor-alvo">
                                <p id="modal_quitar_valor_restante" style="color: #00000096;"></p>
                            </div>
                            <label for="data">Data do pagamento</label>
                            <input class="form-control" type="date" placeholder="dd/mm/aa" name="data"
                                value="<?= (new DateTime())->format('Y-m-d') ?>">
                            <label for="valor">Valor pago</label>
                            <input class="form-control" type="text" name="valor" placeholder="Valor pago">
                            <label for="forma_pagamento">Forma de pagamento</label>
                            <select class="form-control" name="forma_pagamento">
                                <option value="">Selecione uma forma de pagamento</option>
                                <?php foreach (TipoPagamento::read(null, $_SESSION['usuario']->id_empresa) as $pagamento) { ?>
                                    <option value="<?= $pagamento->id ?>">
                                        <?= $pagamento->nome ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div style="margin-bottom: 3em;" class="footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                                <button type="submit" class="btn btn-primary">Pagar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade" id="modal_receber" tabindex="-1" role="dialog" aria-labelledby="modal_receber_title"
            aria-hidden="true">
            <div class=" modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 class="modal-title" id="modal_receber_long_title">Novo Lançamento</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">

                        <form method="post" id="content" action="editar-pagar.php"
                            onkeydown="return event.key != 'Enter';">
                            <input type="hidden" name="view" value="pagar">
                            <input type="hidden" name="pagar" value="1">

                            <label for="documento">Documento:</label>
                            <div class="input-documento-group">
                                <div class="input-documento input-form-adm">
                                    <!--Nome: -->

                                    <input type="text"  onchange="checar()" name="documento" id="documento"
                                        class="form-control" placeholder="Documento" value="" required>
                                </div>

                                <div class="input-documento-generator">
                                    <button type="button" class="form-control" id="btnBuscarDoc"><i
                                            class="bi bi-text-center"></i></button>
                                </div>
                            </div>
                            <label for="cadastro">Cliente / Fornecedor:</label>
                            <div class="input-documento-group">
                                <div class="input-documento input-form-adm">
                                    <!--Nome: -->

                                    <select name="cadastro" class="form-select" id="cadastro"
                                        style="border-top-right-radius:0; border-bottom-right-radius:0;">
                                        <option value="">Selecione</option>

                                        <?php
                                        $cadastros = Cadastro::read(null, null, $_SESSION['usuario']->id_empresa);
                                        foreach ($cadastros as $cadastro) { ?>
                                            <option value="<?= $cadastro->id_cadastro ?>">
                                                <?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="input-documento-generator">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro" type="button"
                                        class="form-control" id="btnModalCadastro"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>


                            <div class="input-valor input-form-adm">
                                <!--Nome: -->
                                <label for="valor">Valor:</label>
                                <input type="text" onchange="checar()" name="valor" class="form-control"
                                    placeholder="Valor" value="" required>
                            </div>

                            <div class="input-parcelas input-form-adm">
                                <!--Nome: -->
                                <label for="parcelas">Parcelas:</label>
                                <input type="number" onchange="checar()" name="parcelas" class="form-control"
                                    placeholder="Parcelas" value="" required>
                            </div>

                            <div class="input-descricao input-form-adm">
                                <!--Nome: -->
                                <label for="descricao">Descrição:</label>
                                <input type="text" onchange="checar()" name="descricao" class="form-control"
                                    placeholder="Descrição" value="" required>
                            </div>


                            <div class="titulos-receber" style="display:flex; flex-direction: row;">

                                <div class="input-titulo input-form-adm" style="width: 50%;">
                                    <!--Nome: -->
                                    <label for="titulo">Titulo</label>
                                    <select name="titulo" class="form-select" id="titulo"
                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                        <option value="">Selecione</option>

                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, 'D');
                                        foreach ($titulos as $titulo) { ?>
                                            <option value="<?= $titulo->id ?>">
                                                <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?></option>
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


                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button name="acao" value="adicionar" class="btn btn-success"
                                    style="float:right; background-color: #5856d6; border: #5856d6; "
                                    href="consulta_cliente.php">Salvar</button>

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

    <div class="modal fade" id="modal_cadastro" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Cadastrar</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="cadastro">
                        <input type="hidden" name="insta" value="pagar">
                        <input type="hidden" name="target" value="cliente">
                        <input type="hidden" name="id" value="">
                        <label>Informe os dados do Cliente ou Fornecedor</label>

                        <div class="input-nome input-form-adm" style="width:100%;">
                            <!-- Razão social / Nome: -->
                            <input type="text" onchange="checar()" name="nome" class="form-control"
                                placeholder="Razão social / Nome" value="" required>
                        </div>



                        <div class="input-fantasia input-form-adm">
                            <!--Nome fantasia-->
                            <input type="text" onchange="checar()" name="fantasia" class="form-control"
                                placeholder="Nome fantasia" value="" required>
                        </div>



                        <div class="input-cpf input-form-adm">
                            <!--CPF-->
                            <input type="text" onchange="checar()" name="cpf" class="form-control" placeholder="CPF"
                                value="" required>
                        </div>



                        <div class="input-cnpj input-form-adm">
                            <!--cnpj-->
                            <input type="text" onchange="checar()" name="cnpj" class="form-control" placeholder="CNPJ"
                                value="" required>
                        </div>



                        <div class="input-cep input-form-adm">
                            <input type="text" onchange="checar()" name="cep" class="form-control" placeholder="CEP"
                                value="" required>
                        </div>



                        <div class="input-endereco input-form-adm">
                            <input type="text" onchange="checar()" name="endereco" class="form-control"
                                placeholder="Endereço" value="" required>
                        </div>

                        <div class="input-form-adm-group input-form-adm">
                            <div class="input-bairro input-select-geral ">
                                <select id="bairro" name="bairro" class="form-control"
                                    style="border-bottom-right-radius:0; border-top-right-radius:0;" required>


                                    <option value="">Selecione um bairro</option>

                                    <?php foreach (Bairro::read(null, $_SESSION['usuario']->id_empresa) as $bairro) { ?>
                                        <option value="<?= $bairro->id ?>">
                                            <?= htmlspecialchars($bairro->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>



                            <div class="input-cidade input-select-geral ">
                                <select id="cidade" name="cidade" class="form-control" style="border-radius:0;"
                                    required>

                                    <option value="">Selecione uma cidade</option>
                                    <?php foreach (Cidade::read(null, $_SESSION['usuario']->id_empresa) as $cidade) { ?>


                                        <option value="<?= $cidade->id ?>">
                                            <?= htmlspecialchars($cidade->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>



                            <div class="input-estado input-select-geral ">
                                <select id="estado" name="estado" class="form-control"
                                    style="border-bottom-left-radius:0; border-top-left-radius:0;" required>

                                    <option value="">Selecione um estado</option>
                                    <?php foreach ($estadosLista as $sigla => $estado) { ?>
                                        <option value="<?= $sigla ?>">
                                            <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>
                        </div>


                        <div class="input-form-contato-adm-group input-form-adm">



                            <div class="input-celular">
                                <input type="text" onchange="checar()" name="celular" class="form-control"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                    placeholder="Celular" value="" required>
                            </div>



                            <div class="input-telefone">
                                <input type="text" onchange="checar()" name="fixo" class="form-control"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                    placeholder="Telefone Fixo" value="" required>
                            </div>

                        </div>

                        <div class="input-email input-form-adm">
                            <input type="text" onchange="checar()" name="email" class="form-control"
                                placeholder="E-mail" value="" required>
                        </div>



                        <div class="input-categoria" style="margin-bottom:1em;">
                            <select id="cidade" name="categoria" class="form-control" required>

                                <option value="">Selecione uma categoria</option>
                                <?php foreach (Categoria::read(null, $_SESSION['usuario']->id_empresa) as $categoria) { ?>
                                    <option value="<?= $categoria->id ?>">
                                        <?= htmlspecialchars($categoria->nome, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php } ?>

                            </select>
                        </div>




                        <div style="margin-bottom: 3em;" class="footer">

                            <button name="acao" value="adicionar" class="btn btn-success"
                                style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                href="consulta_cliente.php">Salvar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>


                    </form>


                </div>

            </div>
        </div>
    </div>


</body>

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
            console.log('ID:', id, 'Valor restante:', valorRestante); // Adicione esta linha
            document.getElementById('modal_quitar_id').value = id;
            document.getElementById('modal_quitar_valor_restante').textContent = "Valor restante da parcela: R$ " + valorRestante;
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

    
    
<?php if (isset($acao) && $acao == 'adicionar') { ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_receber');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = '<?=$caminho_get?>';
            });
        });
    </script>
<?php }
; ?>

</script>
<script src="gerar.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>



<script>
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
            subtituloFiltroChoice.clearChoices();
            subtituloFiltroChoice.setChoices(
                [{ value: '', label: 'Selecione um Subtítulo', disabled: false }],
                'value',
                'label',
                true
            );
            subtituloFiltroChoice.setChoiceByValue('');
        }

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
            subtituloModalChoice.setChoices(
                [{ value: '', label: 'Selecione', disabled: false }],
                'value',
                'label',
                true
            );
            subtituloModalChoice.setChoiceByValue('');
        }

        console.log('Choices.js inicializado em receber.php:', {
            tituloFiltro: !!tituloFiltroChoice,
            subtituloFiltro: !!subtituloFiltroChoice,
            tituloModal: !!tituloModalChoice,
            subtituloModal: !!subtituloModalChoice
        });

        // ========== FILTRO DE SUBTÍTULOS (FILTROS) ==========
        if (tituloFiltroElement && subtituloFiltroElement) {
            function carregarSubtitulosFiltro(tituloId, manterSelecao = false) {
                const valorAtual = subtituloFiltroElement.value;

                if (subtituloFiltroChoice) {
                    subtituloFiltroChoice.clearChoices();
                    subtituloFiltroChoice.setChoices(
                        [{ value: '', label: 'Selecione um Subtítulo', disabled: false }],
                        'value',
                        'label',
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
                        subtituloFiltroChoice.setChoiceByValue('');
                    }
                } else {
                    subtituloFiltroElement.innerHTML = '<option value="">Selecione um Subtítulo</option>';
                    
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
                    subtituloModalChoice.clearChoices();
                    subtituloModalChoice.setChoices(
                        [{ value: '', label: 'Selecione', disabled: false }],
                        'value',
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

        console.log('Filtros de título/subtítulo configurados em receber.php');
    }, 100);
});</script>

</html>