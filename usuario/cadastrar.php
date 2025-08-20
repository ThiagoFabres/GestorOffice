<?php
ob_start(); 
require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/bairro.php';
require_once __DIR__ . '/../db/entities/cidade.php';
require_once __DIR__ . '/../db/entities/categoria.php';
require_once __DIR__ . '/../db/entities/pagamento.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->processar != 1) {
    header('Location: /');
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

if(isset($_GET['id']) && (isset($_GET['acao']) && $_GET['acao'] == 'editar') && ($_GET['cadastro'] == 'cliente')) {
    if($_GET['cadastro'] == 'cliente') {
    $cadastros_empresa = Cadastro::read(null, null,$_SESSION['usuario']->id_empresa);
    $cadastro_target = Cadastro::read($_GET['id'])[0];
    } else if($_GET['cadastro'] == 'bairro') {
    $cadastro_target = Bairro::read($_GET['id'])[0];
    $cadastros_empresa = Bairro::read(null,$_SESSION['usuario']->id_empresa);
    } else if($_GET['cadastro'] == 'cidade') {
    $cadastro_target = Cidade::read($_GET['id'])[0];
    $cadastros_empresa = Cidade::read(null,$_SESSION['usuario']->id_empresa);
    } else if($_GET['cadastro'] == 'categoria') {
    $cadastro_target = Categoria::read($_GET['id'])[0];
    $cadastros_empresa = Categoria::read(null,$_SESSION['usuario']->id_empresa);
    } else if($_GET['cadastro'] == 'pagamento') {
    $cadastro_target = TipoPagamento::read($_GET['id'])[0];
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

        <div class="menu-item">
            <a href="index.php?view=contas"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano de Contas </a>
        </div>

        <div class="menu-item">
            <a href="index.php?view=receber"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber </a>
        </div>

        <div class="menu-item">
            <a href="index.php?view=pagar"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a Pagar </a>
        </div>

        <div class="menu-item accordion" >

        <a class="nav-link text-white" data-bs-toggle="collapse" href="#registrosMenu" role="button" aria-expanded="false" aria-controls="cadastrosMenu">
        <i class="bi bi-journal-bookmark"></i> Registros
      </a>

        <div class="collapse"  id="registrosMenu">
            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
            <li><a href="registros.php?registro=cadastros" class="link-light text-decoration-none"><i class="bi bi-list-task"></i>Cadastros</a></li>
            <li><a href="registros.php?registro=contas" class="link-light text-decoration-none"><i class="bi bi-journal-bookmark"></i>Plano de Contas</a></li>
            <li><a href="registros.php?registro=receber" class="link-light text-decoration-none"><i class="bi bi-wallet"></i>Contas a Receber</a></li>
            <li><a href="registros.php?registro=pagar" class="link-light text-decoration-none"><i class="bi bi-cash-stack"></i>Contas a Pagar</a></li>
            <li><a href="registros.php?registro=dre" class="link-light text-decoration-none"><i class="bi bi-file-earmark-text"></i>DRE</a></li>
            
            </ul>
        </div>
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



    

            
           
                <?php if($_GET['cadastro'] == 'cliente') {
                    if((!isset($_GET['acao'])) || $_GET['acao'] == 'adicionar' ) {
                    ?>
            
                    <div class="main" id="container">
                <div class="botao">
        <a href="cadastrar.php?cadastro=cliente&acao=adicionar" class="btn btn-primary btn-lg botao-adm-adicionar">Novo Cliente / Fornecedor</a>
    </div>

    <div class="tabela">

      <div class="tabela-borda">
        
            <div class="row">
                <div class="col-md-12">
                
                

                    <table class="table table-striped">
                        <div class="titulo-tabela"><h4>Clientes / Fornecedores</h4></div>
                        
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Endereco</th>
                                <th>Data de Registro</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            
                            $cadastros = Cadastro::read(null, null, $_SESSION['usuario']->id_empresa);
                            foreach ($cadastros as $cadastro) {

                                 $link = 'cadastrar.php?cadastro=cliente&acao=editar&id=' . $cadastro->id_cadastro;
                                 $cidade = Cidade::read($cadastro->id_cidade, $_SESSION['usuario']->id_empresa);            
                                 $bairro = Bairro::read($cadastro->id_bairro, $_SESSION['usuario']->id_empresa);
                                ?>
                                    
                                        <tr class="tr-clientes" onclick="window.location.href='<?= $link ?>'" style="cursor: pointer;">

                                            <td>
                                                <?=$cadastro->nom_fant?>
                                                <p>Razao social: <?=$cadastro->razao_soc?></p>
                                            </td>

                                            <td>
                                                <?=$cadastro->celular?>
                                                <p> Fixo: <?=$cadastro->fixo?></p>
                                            </td>

                                            <td>
                                                <?=$cadastro->rua . ', ' . $bairro[0]->nome. ' ' . $cidade[0]->nome . '- ' . $cadastro->estado ?>
                                                <p>CEP: <?= $cadastro->cep ?></p>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($cadastro->data_r)) ?>
                                            </td>
                                            <td>
                                                <a href="cadastros_manager.php?acao=excluir&target=cliente&id=<?= $cadastro->id_cadastro ?>" class="btn btn-danger btn-sm">Excluir</a>
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
<?php } ?>
<?php if (isset($_GET['acao']) && $_GET['acao'] == 'editar' && $_GET['cadastro'] == 'cliente'){ 
    
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
    <input type="hidden" name="id" value="<?=$_GET['id']?>">
    <input type="hidden" name="target" value="<?= $_GET['cadastro'] ?>">
    <input type="hidden" name="view" value="cadastro">

 
    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="nome" placeholder="Razao social / Nome" name="nome" value="<?= $cadastro->razao_soc ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="senha" placeholder="Nome fantasia" name="fantasia" value="<?= $cadastro->nom_fant ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cnpj" placeholder="CNPJ" name="cnpj" value="<?= $cadastro->cnpj ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cpf" placeholder="CPF" name="cpf" value="<?= $cadastro->cpf ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cep" placeholder="CEP" name="cep" value="<?= $cadastro->cep ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="endereco" placeholder="Endereço" name="endereco" value="<?= $cadastro->rua ?>" required>
    </div>

    <div class="input-form-adm-group input-form-adm">

    <div style="display:flex; flex-direction:row;" class="mb-3">
       <select style="border-top-right-radius: 0; border-bottom-right-radius: 0;" id="bairro" name="bairro" class="form-control"  style="border-radius:0;" required>

                            <?php foreach ($bairrolista as $bairro) { ?>
                                <option <?php if($bairro->id == $cadastro->id_bairro){?> selected <?php } ?> value="<?= $bairro->id ?>">
                            <?= $bairro->nome ?>
                            </option>
                            <?php } ?>
                        </select>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <select style="border-radius:0;" id="cidade" name="cidade" class="form-control"  style="border-radius:0;" required>

                            <?php foreach ($cidadelista as $cidade) { ?>
                                <option <?php if($cidade->id == $cadastro->id_cidade){?> selected <?php } ?> value="<?= $cidade->id ?>">
                            <?= $cidade->nome ?>
                            </option>
                            <?php } ?>
                        </select>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <select style="border-top-left-radius:0; border-bottom-left-radius:0;" id="estado" name="estado" class="form-control"  style="border-radius:0;" required>

                            <option value="" >Selecione um estado</option>
                            <?php foreach ($estadosLista as $sigla => $estado) { ?>
                                <option <?php if($sigla == $cadastro->estado){?> selected <?php } ?> value="<?= $sigla ?>">
                            <?= $estado ?>
                            </option>
                            <?php } ?>
                            
                        </select>
    </div>

    </div>

    <div class="input-form-contato-adm-group input-form-adm">

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input style="border-top-right-radius: 0; border-bottom-right-radius: 0;" type="text" class="form-control" id="celular" placeholder="Celular" name="celular" value="<?= $cadastro->celular ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input  style="border-top-left-radius: 0; border-bottom-left-radius: 0;" type="text" class="form-control" id="telefone" placeholder="Telefone fixo" name="fixo" value="<?= $cadastro->fixo ?>" required>
    </div>

    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="email" placeholder="E-mail" name="email" value="<?= $cadastro->email ?>" required>
    </div>

    <div class="input-categoria">
        <select style="margin-bottom:1em;" id="cidade" name="categoria" class="form-control"  style="border-radius:0;" required>

                <?php foreach ($categorialista as $categoria) { ?>
                    <option <?php if($categoria->id == $cadastro->id_categoria){?> selected <?php } ?> value="<?= $categoria->id ?>">
                     <?= $categoria->nome ?>
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
        $pagamentos = TipoPagamento::read(null, $_SESSION['usuario']->id_empresa);

     ?>

    <!-- Modal -->
    <div class="modal fade" id="modal_empresa" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                        <input type="hidden" name="target" value="<?= $_GET['cadastro'] ?>">
                        <input type="hidden" name="id" value="">
                        <label>Informe os dados do Cliente ou Fornecedor</label>

                        <div class="input-nome input-form-adm">
                            <!-- Razão social / Nome: -->
                            <input type="text" onchange="checar()" name="nome" class="form-control" placeholder="Razão social / Nome"
                                value="" required>
                        </div>

                        

                        <div class="input-fantasia input-form-adm">
                            <!--Nome fantasia-->
                            <input type="text" onchange="checar()" name="fantasia"
                                class="form-control" placeholder="Nome fantasia" value="" required>
                        </div>

                        

                        <div class="input-cpf input-form-adm">
                            <!--CPF-->
                            <input type="text" onchange="checar()" name="cpf" class="form-control"
                                placeholder="CPF" value="" required>
                        </div>

                        

                        <div class="input-cnpj input-form-adm">
                            <!--cnpj-->
                            <input type="text" onchange="checar()" name="cnpj" class="form-control"
                                placeholder="CNPJ" value="" required>
                        </div>

                        
                        
                        <div class="input-cep input-form-adm">
                            <input type="text" onchange="checar()" name="cep"
                                class="form-control" placeholder="CEP" value="" required>
                        </div>
                        
                        

                        <div class="input-endereco input-form-adm">
                            <input type="text" onchange="checar()" name="endereco" class="form-control"
                                placeholder="Endereço" value="" required>
                        </div>

                        <div class="input-form-adm-group input-form-adm">
                                <div class="input-bairro">
                    <select id="bairro" name="bairro" class="form-control"  style="border-radius:0;" required>


                            <option value="" >Selecione um bairro</option>

                            <?php foreach ($bairros as $bairro) { ?>
                                <option value="<?= $bairro->id ?>">
                            <?= $bairro->nome ?>
                            </option>
                            <?php } ?>
                        </select>
                                </div>

                                

                                <div class="input-cidade">
                        <select id="cidade" name="cidade" class="form-control"  style="border-radius:0;" required>

                            <option value="" >Selecione uma cidade</option>
                            <?php foreach ($cidades as $cidade) { ?>


                                <option value="<?= $cidade->id ?>">
                            <?= $cidade->nome ?>
                            </option>
                            <?php } ?>

                        </select>
                                </div>

                                

                                <div class="input-estado">
                            <select id="estado" name="estado" class="form-control"  style="border-radius:0;" required>

                            <option value="" >Selecione um estado</option>
                            <?php foreach ($estadosLista as $sigla => $estado) { ?>
                                <option value="<?= $sigla ?>">
                            <?= $estado ?>
                            </option>
                            <?php } ?>
                            
                        </select>
                                </div>
                        </div>
                        

                        <div class="input-form-contato-adm-group input-form-adm">

                        

                        <div class="input-celular">
                            <input type="text" onchange="checar()" name="celular" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                placeholder="Celular" value="" required>
                        </div>

                        

                        <div class="input-telefone">
                            <input type="text" onchange="checar()" name="fixo" class="form-control" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                placeholder="Telefone Fixo" value="" required>
                        </div>
                        
                        </div>

                        <div class="input-email input-form-adm">
                            <input type="text" onchange="checar()" name="email" class="form-control"
                                placeholder="E-mail" value="" required>
                        </div>



                        <div class="input-categoria" style="margin-bottom:1em;" >
                        <select id="cidade" name="categoria" class="form-control"  style="border-radius:0;" required>

                            <option value="" >Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria) { ?>
                                <option value="<?= $categoria->id ?>">
                            <?= $categoria->nome ?>
                            </option>
                            <?php } ?>

                        </select>
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
    </div>
    </div>
    </div>
    </div>



<?php if($_GET['cadastro'] == 'bairro' || $_GET['cadastro'] == 'cidade' || $_GET['cadastro'] == 'categoria' || $_GET['cadastro'] == 'pagamento') {
    if(!isset($_GET['acao']) || $_GET['acao'] == 'adicionar' ) {


    ?>
    <div class="main" id="container">
                <div class="botao">
        <a href="cadastrar.php?cadastro=<?= $_GET['cadastro'] ?>&acao=adicionar" class="btn btn-primary btn-lg botao-adm-adicionar"> <?php if ($_GET['cadastro'] == 'cidade') {echo 'Nova';} else { echo 'Novo';} ?> <?= ucfirst($_GET['cadastro']) ?></a>
    </div>

    <div class="tabela">

      <div class="tabela-borda">
        
            <div class="row">
                <div class="col-md-12">
                

                    <table class="table table-striped">
                        <div class="titulo-tabela"><h4><?= ucfirst($_GET['cadastro']) ?></h4></div>
                        
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Ação</th>
                            </tr>

                            
                                
                            
                        </thead>
                        <tbody>
                            <?php
                            if($_GET['cadastro'] == 'bairro') {$cadastros = Bairro::read(null, $_SESSION['usuario']->id_empresa);}
                            else if($_GET['cadastro'] == 'cidade') {$cadastros = Cidade::read(null, $_SESSION['usuario']->id_empresa);}
                            else if($_GET['cadastro'] == 'categoria') {$cadastros = Categoria::read(null, $_SESSION['usuario']->id_empresa);}
                            else if($_GET['cadastro'] == 'pagamento') {$cadastros = TipoPagamento::read(null, $_SESSION['usuario']->id_empresa);}

                            foreach ($cadastros as $cadastro) {
                            
                                 $link = 'cadastrar.php?cadastro='. $_GET['cadastro'] .'&acao=editar&id=' . $cadastro->id;
                                ?>
                                    
                                        <tr class="tr-clientes" onclick="window.location.href='<?= $link ?>'" style="cursor: pointer;">
                                            <td>
                                               <?=$cadastro->nome?>
                                            </td>
                                            <td>
                                                <a href="cadastros_manager.php?acao=excluir&target=<?= $_GET['cadastro'] ?>&id=<?= $cadastro->id ?>" class="btn btn-danger btn-sm">Excluir</a>

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



switch ($_GET['cadastro']) {
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
}

?>
    <!-- Modal -->
    <div class="modal fade" id="modal_cadastro" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><?= ucfirst($_GET['cadastro']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="target" value="<?= $_GET['cadastro']; ?>">
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
    </div>
    </div>
    </div>
    </div>
    </div>

<?php } } 

 if(isset($_GET['acao']) && $_GET['acao'] == 'editar' && ($_GET['cadastro'] == 'bairro' || $_GET['cadastro'] == 'cidade' || $_GET['cadastro'] == 'pagamento' || $_GET['cadastro'] == 'categoria')){  
    if($_GET['cadastro'] == 'bairro') {
    $cadastro_target = Bairro::read($_GET['id'])[0];
    $cadastros_empresa = Bairro::read(null,$_SESSION['usuario']->id_empresa);
    } else if($_GET['cadastro'] == 'cidade') {
    $cadastro_target = Cidade::read($_GET['id'])[0];
    $cadastros_empresa = Cidade::read(null,$_SESSION['usuario']->id_empresa);
    } else if($_GET['cadastro'] == 'categoria') {
    $cadastro_target = Categoria::read($_GET['id'])[0];
    $cadastros_empresa = Categoria::read(null,$_SESSION['usuario']->id_empresa);
    } else if($_GET['cadastro'] == 'pagamento') {
    $cadastro_target = TipoPagamento::read($_GET['id'])[0];
    $cadastros_empresa = TipoPagamento::read(null,$_SESSION['usuario']->id_empresa);
    }   

    $cadastro = checar_cadastro($cadastros_empresa[0], $cadastro_target);

    ?>

        

       <div class="main-edit">
    <div class="tabela-edit">
        <div class=" edit-card">
    <div class="card-body">

<div id="card-sub">Edite os dados do Cliente / Fornecedor</div>
<form action="cadastros_manager.php" method="post">
    <input type="hidden" name="id" value="<?=$_GET['id']?>">
    <input type="hidden" name="target" value="<?= $_GET['cadastro'] ?>">
 
    <form action="index.php" method="post">
 
    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="nome" placeholder="Razao social / Nome" name="nome" value="<?= $cadastro->nome ?>" required>
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
    //     let botao = document.querySelector('button[name="acao"]')



    //     switch($_GET['cadastro']) {
    //         case 'cliente':
    //                 if (nome !== '' && fantasia !== '' && cpf !== '' && cnpj !== '' && cep !== '' && endereco !== '' && bairro !== '' && cidade !== '' && estado !== '' && celular !== '' && telefone !== '' && email !== '') {
    //                 botao.disabled = false;
    //             } else {
    //                 botao.disabled = true;
    //                 }
    //             break;
    //         case 'bairro':
    //             if(nome !== '') {
    //                 botao.disabled = false
    //                 } else {
    //                 botao.disabled = true;
    //                 }
    //         break;
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
</script>

<?php if ( isset($_GET['acao']) && $_GET['acao'] == 'adicionar') { 
    if($_GET['cadastro'] == 'cliente'){ $modaltarget = 'modal_empresa';} else {$modaltarget = 'modal_cadastro';}
    ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            <?php if($_GET['cadastro'] == 'cliente') { ?>
            var modalEl = document.getElementById('modal_empresa');
            <?php } else { ?>
            var modalEl = document.getElementById('modal_cadastro');
            <?php } ?>
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastrar.php?cadastro=<?= $_GET['cadastro'] ?>';
            });
        });


    </script>
<?php }
; ?>



</html>


