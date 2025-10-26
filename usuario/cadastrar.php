<?php
ob_start(); 
require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/bairro.php';
require_once __DIR__ . '/../db/entities/cidade.php';
require_once __DIR__ . '/../db/entities/categoria.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
require_once __DIR__ . '/../db/entities/centrocontas.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->status != 1) {
    header('Location: /');
    exit();
}

if($_SESSION['usuario']->status != 1) {
    header('Location: index.php');
    exit();
}
$acao = filter_input(INPUT_GET, 'acao');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$get_cadastro = filter_input(INPUT_GET, 'cadastro');
$get_nome = filter_input(INPUT_GET, 'nome');
$get_estado = filter_input(INPUT_GET, 'estado');
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial');
$get_data_final = filter_input(INPUT_GET, 'data_final');
$erro = filter_input(INPUT_GET, 'erro');

if($get_cadastro != 'cliente' && $get_cadastro != 'pagamento' && $get_cadastro != 'categoria' && $get_cadastro != 'bairro' && $get_cadastro != 'cidade' && $get_cadastro != 'custo') {
    header('Location: index.php');
    exit();
}

    function checar_cadastro($cadastro_empresa, $cadastro_target) {

        if (isset($cadastro_empresa->id_empresa) && isset($cadastro_target->id_empresa)) {

                if($cadastro_empresa->id_empresa == $cadastro_target->id_empresa) {
            $cadastro = $cadastro_target;
        } else {
            $cadastro = null;
        }

        return $cadastro;
    } else {
    header('Location: index.php');
    exit;
    }

    }

if(isset($id) && (isset($acao) && $acao == 'editar')) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if($get_cadastro == 'cliente') {
    $cadastros_empresa = Cadastro::read(null, null,$_SESSION['usuario']->id_empresa);
    $cadastro_target = Cadastro::read($id)[0];
    } else if($get_cadastro == 'bairro') {
    $cadastro_target = Bairro::read($id)[0];
    $cadastros_empresa = Bairro::read(null,$_SESSION['usuario']->id_empresa);
    } else if($get_cadastro == 'cidade') {
    $cadastro_target = Cidade::read($id)[0];
    $cadastros_empresa = Cidade::read(null,$_SESSION['usuario']->id_empresa);
    } else if($get_cadastro == 'categoria') {
    $cadastro_target = Categoria::read($id)[0];
    $cadastros_empresa = Categoria::read(null,$_SESSION['usuario']->id_empresa);
    } else if($get_cadastro == 'pagamento') {
    $cadastro_target = TipoPagamento::read($id)[0];
    $cadastros_empresa = TipoPagamento::read(null,$_SESSION['usuario']->id_empresa);
    }   

    foreach($cadastros_empresa as $cadastro_empresa) {
       
        
        $cadastro = checar_cadastro($cadastro_empresa, $cadastro_target);
    }
        }



$estadosLista = [
    "AC" => "Acre",
    "AL" => "Alagoas",
    "AP" => "Amapá",
    "AM" => "Amazonas",
    "BA" => "Bahia",
    "CE" => "Ceará",
    "ES" => "Espírito Santo",
    "GO" => "Goiás",
    "MA" => "Maranhão",
    "MT" => "Mato Grosso",
    "MS" => "Mato Grosso do Sul",
    "MG" => "Minas Gerais",
    "PA" => "Pará",
    "PB" => "Paraíba",
    "PR" => "Paraná",
    "PE" => "Pernambuco",
    "PI" => "Piauí",
    "RJ" => "Rio de Janeiro",
    "RN" => "Rio Grande do Norte",
    "RS" => "Rio Grande do Sul",
    "RO" => "Rondônia",
    "RR" => "Roraima",
    "SC" => "Santa Catarina",
    "SP" => "São Paulo",
    "SE" => "Sergipe",
    "TO" => "Tocantins"
];

?>

<!DOCTYPE html>




<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js" integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

            <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
    
    <link rel="stylesheet" href="/style.css">
    
    <link rel="stylesheet" href="../choices/choices.css"></link>

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
    
        <div class="menu-item menu-item-atual accordion" >

<a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button" aria-expanded="false" aria-controls="cadastrosMenu">
        <i class="bi bi-person"></i> Cadastros
      </a>
      <div class="" id="cadastrosMenu">
        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
          <li><a href="cadastrar.php?cadastro=cliente" class="link-light text-decoration-none"><i class="bi bi-person"></i>Cliente/Fornecedor</a></li>
          <li><a href="cadastrar.php?cadastro=bairro" class="link-light text-decoration-none"><i class="bi bi-houses"></i>Bairro</a></li>
          <li><a href="cadastrar.php?cadastro=cidade" class="link-light text-decoration-none"><i class="bi bi-buildings"></i>Cidade</a></li>
          <li><a href="cadastrar.php?cadastro=pagamento" class="link-light text-decoration-none"><i class="bi bi-cash-coin"></i>Tipo Pagamento</a></li>
          <li><a href="cadastrar.php?cadastro=categoria" class="link-light text-decoration-none"><i class="bi bi-tag"></i>Categoria</a></li>
          <li><a href="cadastrar.php?cadastro=custo" class="link-light text-decoration-none"><i class="bi bi-bank"></i>Centro de custos</a></li>
          
        </ul>
      </div>
        </div>

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
            <a href="dre/sintetico.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-file-earmark-text"></i></div>DRE</a>
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
            <span style="color:#181f2b;"><?= $_SESSION['usuario']->nome ?> </span>
        </button>
        <div id="userMenu" style="right:0; z-index: 1000000;">
            <a href="/" class="dropdown-item">
                <i class="bi bi-box-arrow-left"></i> Logout
        </a>
        </div>
    </div>
    </div>



    

            
           
                <?php if($get_cadastro == 'cliente') {
                    if((!isset($acao)) || $acao == 'adicionar' ) {
                    ?>
            
<div class="main" id="container">

                <div class="botao">
        <button data-bs-toggle="modal" data-bs-target="#modal_empresa" class="btn btn-primary btn-lg botao-adm-adicionar">Novo Cadastro</button>
    </div>

    

    
    <div class="tabela">
    

      
        
            <div class="row">
                <div class="col-md-12" style="padding: 0;">
                

                    <div class="card"   >
                        <div class="card-header"><h3>Clientes / Fornecedores</h3></div>

                        <div class="card-header-div">
        <div class="card-header-borda">
            <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
                <h5 class="card-title">Filtros</h5>
                
                <form class="row g-3 align-items-end mb-3" method="get" action="cadastrar.php">
                    <input type="hidden" name="cadastro" value="cliente">

                
                    <div class="col-md-2" style="width: 15%;">
                        <label for="nome" class="form-label">Nome:</label>
                        <div class="input-group">
                            <input name="nome" value="<?= $get_nome ?? "" ?>" type="Nome" class="form-control" id="nome" placeholder="Nome" style="border-radius: 0.25em;">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 15%;">
                        <label for="dataInicio" class="form-label">Data inicial:</label>
                        <div class="input-group">
                            <input name="dataInicial" value="<?= $get_data_inicial ?? "" ?>" type="date" class="form-control" id="dataInicio" placeholder="dd/mm/aaaa" style="border-radius: 0.25em;">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 15%;">
                        <label for="dataFinal" class="form-label">Data final:</label>
                        <div class="input-group">
                            <input name="dataFinal"  value="<?= $get_data_inicial ?? "" ?>" type="date" class="form-control" id="dataFinal" placeholder="dd/mm/aaaa" style="border-radius: 0.25em;">
                            
                        </div>
                    </div>

                    <div class="col-md-1" style="width: 15%;">
                        <label for="estado" class="form-label">Estado:</label>
                        <select name="estado" class="form-select" id="estado">

                        
                            <option value="" <?php if(!isset($get_estado)) {?> selected <?php } ?> >Selecione um Estado</option>
                            <?php
                            $cadastrosl = Cadastro::read(null, null, $_SESSION['usuario']->id_empresa, $get_nome ?? null, $get_data_inicial ?? null, $get_data_final ?? null, $get_estado ?? null, $get_cidade ?? null, $get_bairro ?? null);
                            $cadastros = Cadastro::read(null, null, $_SESSION['usuario']->id_empresa);

                            $lista_estados = [];

                            foreach ($cadastros as $empresa) {
                                if (!in_array($empresa->estado, $lista_estados)) {
                                    $lista_estados[] = $empresa->estado;
                                }
                            }
                            $estados_unicos = [];
                            foreach ($lista_estados as $estado) {
                                $chave = mb_strtolower(trim($estado), 'UTF-8');
                                if(strlen($chave) != 2) {
                                    $estados_unicos[$chave] = ucfirst(mb_strtolower($estado, 'UTF-8'));
                                } else {
                                    $estados_unicos[$chave] = mb_strtoupper($estado, 'UTF-8');
                                }

                                
                            }

                            

                            ?>
                            <?php foreach ($estados_unicos as $estado) { ?>
                                <option value="<?= $estado ; ?>"  <?php if(isset($get_estado) && $estado == $get_estado) {?> selected <?php } ?>  ><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php }; ?>
                        </select>
                    </div>
                
                    <div class="col-md-1" style="width: 15%; ">
                        <label for="cidade" class="form-label">Cidade:</label>
                        <select name="cidade" class="form-select" id="cidade">
                        
                            <option value="" <?php if(!isset($get_cidade)) {?> selected <?php } ?> >Selecione uma cidade</option>

                            <?php
                            
                            $lista_cidades = [];
                            foreach ($cadastros as $empresa) {
                                $cidade = Cidade::read($empresa->id_cidade, $_SESSION['usuario']->id_empresa)[0];
                                
                                if (!in_array($cidade, $lista_cidades)) {
                                    $lista_cidades[] = $cidade;
                                }
                            
                            }
                            ?>
                        
                            <?php foreach ($lista_cidades as $cidade) { ?>
                                <option value="<?php echo $cidade->id; ?>"  <?php if(isset($get_cidade) && $cidade->id == $get_cidade) {?> selected <?php } ?>  ><?php echo htmlspecialchars($cidade->nome, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php }; ?>
                        </select>
                    </div>

                    <div class="col-md-1" style="width: 15%;">
                        <label for="bairro" class="form-label">Bairro:</label>
                        <select name="bairro" class="form-select" id="bairro">
                        
                            <option value="" <?php if(!isset($get_bairro)) {?> selected <?php } ?> >Selecione um bairro</option>

                            <?php

                            $lista_bairros = [];

                            foreach ($cadastros as $empresa) {
                                $bairro = Bairro::read( $empresa->id_bairro, $_SESSION['usuario']->id_empresa)[0]->nome;
                                if (!in_array($bairro, $lista_bairros)) {
                                    $lista_bairros[] = $bairro;
                                }
                            }

                            $bairros_unicos = [];
                            foreach ($lista_bairros as $bairro) {
                                $chave = mb_strtolower(trim($bairro), 'UTF-8');
                                $bairros_unicos[$chave] = ucfirst(mb_strtolower($bairro, 'UTF-8'));
                            }

                            ?>
                        
                        
                            <?php foreach ($bairros_unicos as $bairro) { 
                                ?>
                            
                                <option value="<?php echo $bairro; ?>"  <?php if(isset($get_bairro) && $bairro == $get_bairro) {?> selected <?php } ?>  ><?php echo htmlspecialchars($bairro, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php }; ?>
                        </select>
                    </div>

                    
                    <div class="btn-filtro">
                        <button type="submit" style="background-color: #5856d6; border: 0;" class="btn btn-primary">Filtrar</button>
                        <a type="button" style="background-color: #5856d6; border: 0;" href="cadastrar.php?cadastro=cliente" class="btn btn-secondary">Limpar</a>
                    </div>
                </form>
                </div>
                </div>
</div>
                        <table class="table table-striped table-cadastro ">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Estado</th>
                        <th>Bairro</th>
                        <th>Data de registro</th>
                    </tr>
                </thead>
                <tbody>
            <?php if (!empty($cadastrosl)) { ?>
                        <?php foreach ($cadastrosl as $cadastro) {
                            $cidade = Cidade::read($cadastro->id_cidade)[0];
                            $bairro = Bairro::read($cadastro->id_bairro)[0];
                            $link = 'cadastrar.php?cadastro=cliente&acao=editar&id=' . $cadastro->id_cadastro;
                             ?>


                            <tr class="tr-clientes" onclick="window.location.href='<?= $link ?>'" style="cursor: pointer;">
                                <td>
                                                <?=htmlspecialchars($cadastro->razao_soc, ENT_QUOTES, 'UTF-8')?>
                                                <?php if($cadastro->nom_fant != '') {?>
                                                    <p>Nome Fantasia: <?=htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8')?></p> 
                                                <?php } ?>    
                                                    
                                               
                                            </td>

                                            <td>
                                                <?=htmlspecialchars($cadastro->celular, ENT_QUOTES, 'UTF-8')?>
                                                <br>
                                                <?php if($cadastro->fixo != ''){?> 
                                                    <p> Fixo: <?=htmlspecialchars($cadastro->fixo, ENT_QUOTES, 'UTF-8')?></p>
                                                <?php } ?>    
                                                    
                                                
                                            </td>

                                            <td>
                                                <?php 
                                                if($cadastro->estado != null) {
                                                    echo htmlspecialchars($cadastro->estado, ENT_QUOTES, 'UTF-8') 
                                                ;} 
                                                ?>
                                                
                                                <?php
                                                
                                                if($cadastro->estado != null && $cadastro->id_cidade != null) {
                                                    echo '-';
                                                 }   
                                                ?>
                                                
                                                <?php 
                                                if($cadastro->id_cidade != null) { 
                                                    echo htmlspecialchars($cidade->nome, ENT_QUOTES, 'UTF-8') 
                                                ;}
                                                    
                                                    
                                                ?>
                                                <br>
                                                 <?php if($cadastro->cep != '') {?>
                                                    <p>CEP: <?= htmlspecialchars($cadastro->cep, ENT_QUOTES, 'UTF-8') ?></p>
                                                 <?php } ?>         
                                            </td>
                                            <td>
                                                <?php if($cadastro->id_bairro != null) {?>
                                                    <?= htmlspecialchars($bairro->nome, ENT_QUOTES, 'UTF-8') ?>
                                                 <?php } ?>    
                                                    
                                                
                                                <br>
                                                <?php if($cadastro->rua != '') {?>
                                                    <p>Rua: <?= htmlspecialchars($cadastro->rua, ENT_QUOTES, 'UTF-8') ?></p>
                                                <?php } ?>  

                                                    
                                                
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime(htmlspecialchars($cadastro->data_r, ENT_QUOTES, 'UTF-8'), )) ?>
                                            </td>
                                
                            </tr>
                                

                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td>Nenhum cliente encontrado</td>
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
<?php } ?>
<?php if (isset($acao) && $acao == 'editar' && $get_cadastro == 'cliente'){ 
    
    $cidadelista = Cidade::read(null, $_SESSION['usuario']->id_empresa);
    $bairrolista = Bairro::read(null, $_SESSION['usuario']->id_empresa);
    $categorialista = Categoria::read(null, $_SESSION['usuario']->id_empresa);

    ?>

        

                        <div class="main-edit">
    <div class="tabela-edit">
        <div class=" edit-card">
    <div class="card-body">

<div id="card-sub">Edite os dados do Cliente / Fornecedor</div>
<form action="cadastros_manager.php" method="post">
    <input type="hidden" name="id" value="<?=$id?>">
    <input type="hidden" name="target" value="<?= $get_cadastro ?>">
    <input type="hidden" name="view" value="cadastro">

 
    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="nome" placeholder="Razao social / Nome" name="nome" value="<?= htmlspecialchars($cadastro->razao_soc, ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="senha" placeholder="Nome fantasia" name="fantasia" value="<?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cnpj" placeholder="CNPJ" name="cnpj" value="<?= htmlspecialchars($cadastro->cnpj, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cpf" placeholder="CPF" name="cpf" value="<?= htmlspecialchars($cadastro->cpf, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cep" placeholder="CEP" name="cep" value="<?= htmlspecialchars($cadastro->cep, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="endereco" placeholder="Endereço" name="endereco" value="<?= htmlspecialchars($cadastro->rua, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    <div class="input-form-adm-group input-form-adm">

    <div style="display:flex; flex-direction:row;" class="mb-3">
       <select style="border-top-right-radius: 0; border-bottom-right-radius: 0;" id="bairro" name="bairro" class="form-control"  style="border-radius:0;" >

                            <?php foreach ($bairrolista as $bairro) { ?>
                                <option <?php if($bairro->id == $cadastro->id_bairro){?> selected <?php } ?> value="<?= htmlspecialchars($bairro->id, ENT_QUOTES, 'UTF-8') ?>">
                            <?= $bairro->nome ?>
                            </option>
                            <?php } ?>
                        </select>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <select style="border-radius:0;" id="cidade" name="cidade" class="form-control"  style="border-radius:0;" >

                            <?php foreach ($cidadelista as $cidade) { ?>
                                <option <?php if($cidade->id == $cadastro->id_cidade){?> selected <?php } ?> value="<?= htmlspecialchars($cidade->id, ENT_QUOTES, 'UTF-8') ?>">
                            <?= $cidade->nome ?>
                            </option>
                            <?php } ?>
                        </select>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <select style="border-top-left-radius:0; border-bottom-left-radius:0;" id="estado" name="estado" class="form-control"  style="border-radius:0;" >

                            <option value="" >Selecione um estado</option>
                            <?php foreach ($estadosLista as $sigla => $estado) { ?>
                                <option <?php if($sigla == $cadastro->estado){?> selected <?php } ?> value="<?= htmlspecialchars($sigla, ENT_QUOTES, 'UTF-8') ?>">
                            <?= $estado ?>
                            </option>
                            <?php } ?>
                            
                        </select>
    </div>

    </div>

    <div class="input-form-contato-adm-group input-form-adm">

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input style="border-top-right-radius: 0; border-bottom-right-radius: 0;" type="text" class="form-control" id="celular" placeholder="Celular" name="celular" value="<?= htmlspecialchars($cadastro->celular, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input  style="border-top-left-radius: 0; border-bottom-left-radius: 0;" type="text" class="form-control" id="telefone" placeholder="Telefone fixo" name="fixo" value="<?= htmlspecialchars($cadastro->fixo, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="email" placeholder="E-mail" name="email" value="<?= htmlspecialchars($cadastro->email, ENT_QUOTES, 'UTF-8') ?>" >
    </div>

    <div class="input-categoria">
        <select style="margin-bottom:1em;" id="cidade" name="categoria" class="form-control"  style="border-radius:0;" >

                <?php foreach ($categorialista as $categoria) { ?>
                    <option <?php if($categoria->id == $cadastro->id_categoria){?> selected <?php } ?> value="<?= $categoria->id ?>">
                     <?= htmlspecialchars($categoria->nome, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php } ?>

        </select>
    </div>

    <button name="acao" value="editar" type="submit" style="background-color:#5856d6; padding-inline:1.5em; " class="btn btn-primary">Salvar</button>
</form>


    </div>
</div>
</div>
</div>
        <?php }; 
        }; ?>
    
    <?php 

        $bairros = Bairro::read(null, $_SESSION['usuario']->id_empresa);
        $cidades = Cidade::read(null, $_SESSION['usuario']->id_empresa);
        $categorias = Categoria::read(null, $_SESSION['usuario']->id_empresa);
       
     ?>

    <!-- Modal -->
    <div class="modal fade" id="modal_empresa" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Cadastrar</h5>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="cadastro">
                        <input type="hidden" name="target" value="cliente">
                        <input type="hidden" name="insta" value="cadastro">
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
                                value="" >
                        </div>



                        <div class="input-cnpj input-form-adm">
                            <!--cnpj-->
                            <input type="text" onchange="checar()" name="cnpj" class="form-control" placeholder="CNPJ"
                                value="" >
                        </div>



                        <div class="input-cep input-form-adm">
                            <input type="text" onchange="checar()" name="cep" class="form-control" placeholder="CEP"
                                value="" >
                        </div>



                        <div class="input-endereco input-form-adm">
                            <input type="text" onchange="checar()" name="endereco" class="form-control"
                                placeholder="Endereço" value="" >
                        </div>

                        <div class="input-form-adm-group input-form-adm">
                            <div class="input-bairro input-select-geral ">
                                <div class="input-modal-div-select">
                                    <select id="bairro" name="bairro" class="form-control" >


                                    <option value="">Bairro</option>

                                    <?php foreach (Bairro::read(null, $_SESSION['usuario']->id_empresa) as $bairro) { ?>
                                        <option value="<?= $bairro->id ?>">
                                            <?= htmlspecialchars($bairro->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                </div>
                                <div class="input-modal-div-btn">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_bairro" type="button"
                                        class="form-control" id="btnModalCadastro"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>



                            <div class="input-cidade input-select-geral ">
                                <div class="input-modal-div-select">
                                    <select id="cidade" name="cidade" class="form-control"
                                    >

                                    <option value="">Cidade</option>
                                    <?php foreach (Cidade::read(null, $_SESSION['usuario']->id_empresa) as $cidade) { ?>


                                        <option value="<?= $cidade->id ?>">
                                            <?= htmlspecialchars($cidade->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>

                                </select>
                                </div>
                                <div class="input-modal-div-btn">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_cidade" type="button"
                                        class="form-control" id="btnModalCadastro"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>


                                
                            <div class="input-estado input-select-geral-estado">
                                
                                <div class="input-modal-div-select-estado">
                                <select id="estado" name="estado" class="form-control" >

                                    <option value="">Estado</option>
                                    <?php foreach ($estadosLista as $sigla => $estado) { ?>
                                        <option value="<?= $sigla ?>">
                                            <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                        </div>
                        </div>

                        <div class="input-form-contato-adm-group input-form-adm">



                            <div class="input-celular">
                                <input type="text" onchange="checar()" name="celular" class="form-control"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                    placeholder="Celular" value="" >
                            </div>



                            <div class="input-telefone">
                                <input type="text" onchange="checar()" name="fixo" class="form-control"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                    placeholder="Telefone Fixo" value="" >
                            </div>

                        </div>

                        <div class="input-email input-form-adm">
                            <input type="text" onchange="checar()" name="email" class="form-control"
                                placeholder="E-mail" value="" >
                        </div>

                         <div class="input-categoria input-select-geral">
                                <div class="input-modal-div-select-categoria">
                                    <select id="categoria" name="categoria" class="form-control"
                                    >

                                    <option value="">Categoria</option>
                                    <?php foreach (Categoria::read(null, $_SESSION['usuario']->id_empresa) as $categoria) { ?>


                                        <option value="<?= $categoria->id ?>">
                                            <?= htmlspecialchars($categoria->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>

                                </select>
                                </div>
                                <div class="input-modal-div-btn-categoria">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_categoria" type="button"
                                        class="form-control" id="btnModalCadastro"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
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
    </div>
    </div>
    </div>
    </div>
    </div>

    <div class="modal fade" id="modal_cadastro_bairro" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Novo bairro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="cadastro">
                        <input type="hidden" name="target" value="bairro">
                        <input type="hidden" name="insta" value="cadastro">
                        <input type="hidden" name="id" value="">
                        <label>Informe o nome do bairro </label>

                        <div class="input-nome input-form-adm">
                            <!-- Nome: -->
                            <input type="text"  name="nome" class="form-control" placeholder="Nome"
                                value=""  required>
                        </div>

                        <div style="margin-bottom: 3em;" class="footer">

                        <button name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" href="consulta_cliente.php">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                            

                    </form>


                </div>

            </div>
        </div>
    </div>
    </div>                                
            <!-- Modal Cadastro Cidade -->
    <div class="modal fade" id="modal_cadastro_cidade" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Nova Cidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="cadastros_manager.php">
                    <input type="hidden" name="view" value="cadastro">
                    <input type="hidden" name="target" value="cidade">
                    <input type="hidden" name="insta" value="cadastro">
                    <input type="hidden" name="id" value="">
                    
                    <div class="mb-3">
                        <label for="nomeCidade" class="form-label">Informe o nome da cidade</label>
                        <input type="text" id="nomeCidade" name="nome" class="form-control" placeholder="Nome" required>
                    </div>
                    
                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                    </form>
                </div>
                
                </div>
            </div>
    </div>
    </div>
    
    <div class="modal fade" id="modal_cadastro_categoria" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCategoriaLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="cadastros_manager.php">
                    <input type="hidden" name="view" value="cadastro">
                    <input type="hidden" name="target" value="categoria">
                    <input type="hidden" name="insta" value="cadastro">
                    <input type="hidden" name="id" value="">
                    
                    <div class="mb-3">
                        <label for="nomeCidade" class="form-label">Informe o nome da categoria</label>
                        <input type="text" id="nomeCidade" name="nome" class="form-control" placeholder="Nome" required>
                    </div>
                    
                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                    </form>
                </div>
                
                </div>
            </div>
    </div>


<?php if($get_cadastro == 'bairro' || $get_cadastro == 'cidade' || $get_cadastro == 'categoria' || $get_cadastro == 'pagamento' || $get_cadastro == 'custo') {
    if(!isset($acao) || $acao == 'adicionar' ) {


    ?>
    <div class="main" id="container">
                <div class="botao">
        <a href="cadastrar.php?cadastro=<?= $get_cadastro ?>&acao=adicionar" class="btn btn-primary btn-lg botao-adm-adicionar"> <?php if ($get_cadastro == 'cidade') {echo 'Nova';} else { echo 'Novo';} ?> <?= ucfirst($get_cadastro) ?></a>
    </div>

     <div class="tabela">
    

      
        
            <div class="row">
                <div class="col-md-12" style="padding: 0;">
                

                    <div class="card">
                        <div class="card-header"><h3><?= ucfirst($get_cadastro) . 's' ?></h3></div>
                

                    <table class="table table-striped table-cadastro">
                        
                        <thead>
                            <tr>
                                <th style="width:95%;">Nome</th>
                                <th>Ação</th>
                            </tr>

                            
                                
                            
                        </thead>
                        <tbody>
                            <?php
                            if($get_cadastro == 'bairro') {$cadastros = Bairro::read(null, $_SESSION['usuario']->id_empresa);}
                            else if($get_cadastro == 'cidade') {$cadastros = Cidade::read(null, $_SESSION['usuario']->id_empresa);}
                            else if($get_cadastro == 'categoria') {$cadastros = Categoria::read(null, $_SESSION['usuario']->id_empresa);}
                            else if($get_cadastro == 'pagamento') {$cadastros = TipoPagamento::read(null, $_SESSION['usuario']->id_empresa);}
                            else if($get_cadastro == 'custo') {$cadastros = CentroContas::read(null, $_SESSION['usuario']->id_empresa);}
                            foreach ($cadastros as $cadastro) {
                                
                            
                                 $link = 'cadastrar.php?cadastro='. $get_cadastro .'&acao=editar&id=' . $cadastro->id;
                                ?>
                                    
                                        <tr class="tr-clientes" onclick="window.location.href='<?= $link ?>'" style="cursor: pointer;">
                                            <td>
                                               <?=htmlspecialchars($cadastro->nome, ENT_QUOTES, 'UTF-8')?>
                                            </td>
                                            <td>
                                               <a class="btn btn-danger btn-sm" href="cadastros_manager.php?view=cadastro&target=<?=$get_cadastro?>&acao=excluir&id=<?=$cadastro->id?>">Excluir</a>
                                            </td>
                                            
                                        </tr>

                            <?php }
                            ?>
                        </tbody>
                    </table>
                </div>
    </div>
    </div> 
    </div>
                
    </div>
                                
    
<?php



switch ($get_cadastro) {
    case 'bairro':
        $labelModal = 'do bairro';
    break;

    case 'cidade':
        $labelModal = 'da cidade';
    break;

    case 'categoria':
        $labelModal = 'da categoria';
        
    break;

    case 'pagamento':
        $labelModal = 'do tipo de pagamento';
    break;

    case 'custo':
        $labelModal = 'do centro de custos';
    break;
}

?>
    <!-- Modal -->
    <div class="modal fade" id="modal_cadastro" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><?= ucfirst($get_cadastro) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="cadastro">
                        <input type="hidden" name="target" value="<?= $get_cadastro; ?>">
                        <input type="hidden" name="id" value="">
                        <label>Informe o nome <?= $labelModal ?> </label>

                        <div class="input-nome input-form-adm">
                            <!-- Nome: -->
                            <input type="text"  name="nome" class="form-control" placeholder="Nome"
                                value="" onchange="checar();" required>
                        </div>

                        <div style="margin-bottom: 3em;" class="footer">

                        <button name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" href="consulta_cliente.php">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                            

                    </form>


                </div>

            </div>
        </div>
    </div>

<?php } } 

 if(isset($acao) && $acao == 'editar' && ($get_cadastro == 'bairro' || $get_cadastro == 'cidade' || $get_cadastro == 'pagamento' || $get_cadastro == 'categoria')){  
 

    $cadastro = checar_cadastro($cadastros_empresa[0], $cadastro_target);
    if($get_cadastro == 'bairro'){$cadastro_titulo = 'o bairro';}
    else if($get_cadastro == 'cidade'){$cadastro_titulo = 'a cidade';}
    else if($get_cadastro == 'pagamento'){$cadastro_titulo = 'o pagamento';}
    else if($get_cadastro == 'categoria'){$cadastro_titulo = 'a categoria';}
    ?>

        

       <div class="main-edit">
    <div class="tabela-edit">
        <div class=" edit-card">
    <div class="card-body">

<div id="card-sub">Edite os dados d<?=$cadastro_titulo?></div>
<form action="cadastros_manager.php" method="post">
    <input type="hidden" name="view" value="cadastro">
    <input type="hidden" name="id" value="<?=$id?>">
    <input type="hidden" name="target" value="<?= $get_cadastro ?>">
 
    <form action="index.php" method="post">
 
    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="nome" placeholder="Nome" name="nome" value="<?= htmlspecialchars($cadastro->nome, ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <button name="acao" value="editar" type="submit" style="background-color:#5856d6; padding-inline:1.5em; " class="btn btn-primary">Salvar</button>
</form>


    </div>
</div>
</div>
</div>
<?php } ?>

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
        let botao = document.querySelector('button[name="acao"]')



        switch($get_cadastro) {
            case 'cliente':
                    if (nome !== '' && fantasia !== '' && cpf !== '' && cnpj !== '' && cep !== '' && endereco !== '' && bairro !== '' && cidade !== '' && estado !== '' && celular !== '' && telefone !== '' && email !== '') {
                    botao.disabled = false;
                } else {
                    botao.disabled = true;
                    }
                break;
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
    }}
</script>

<?php if ( isset($acao) && $acao == 'adicionar') { 
    if($get_cadastro == 'cliente'){ $modaltarget = 'modal_empresa';} else {$modaltarget = 'modal_cadastro';}
    ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            <?php if($get_cadastro == 'cliente') { ?>
            var modalEl = document.getElementById('modal_empresa');
            <?php } else { ?>
            var modalEl = document.getElementById('modal_cadastro');
            <?php } ?>
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastrar.php?cadastro=<?= $get_cadastro ?>';
            });
        });


    </script>
<?php }
; ?>

<?php if (isset($erro) && $erro == 'usado') {
    if($get_cadastro == 'cliente') { ?>
    
            <script>
                alert('Não é possível adicionar esse cadastro, pois já existe um cadastro com esse e-mail');
                window.location.href = 'cadastrar.php?cadastro=<?= $get_cadastro ?>';
            </script>
   
<?php } else if($get_cadastro == 'pagamento') { ?> 
    
            <script>
                alert('Não é possivel apagar esse tipo de pagamento, pois existe um recebimento ou pagamento com esse método');
                window.location.href = 'cadastrar.php?cadastro=<?= $get_cadastro ?>';
            </script>
            
    <?php } else if ($get_cadastro == 'categoria'){ ?>
            <script>
                alert('Não é possivel apagar essa categoria, pois existe um cliente ou fornecedor com essa categoria');
                window.location.href = 'cadastrar.php?cadastro=<?= $get_cadastro ?>';
            </script>
    <?php } else if ($get_cadastro == 'bairro'){ ?>
            <script>
                alert('Não é possivel apagar essa bairro, pois existe um cliente ou fornecedor com esse bairro');
                window.location.href = 'cadastrar.php?cadastro=<?= $get_cadastro ?>';
            </script>
    <?php } else if ($get_cadastro == 'cidade'){?>
            <script>
                alert('Não é possivel apagar essa cidade, pois existe um cliente ou fornecedor com essa cidade');
                window.location.href = 'cadastrar.php?cadastro=<?= $get_cadastro ?>';
            </script>
    <?php } }?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../choices/choices.js"></script>

</html>


