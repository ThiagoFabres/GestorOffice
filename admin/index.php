<!DOCTYPE html>
<?php

require_once __DIR__ . '/../db/entities/usuarios.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 1) {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../db/entities/empresas.php';
require_once __DIR__ . '/../db/entities/cargo.php';




function permissao() {
        // Exemplo: só permite se o usuário logado for admin
        return isset($_SESSION['usuario']) && $_SESSION['usuario']->cargo == Cargo::ADMIN;
    }

    function atribuirCargo($cargo) {
        // Verifica se o cargo é válido
        if (!in_array($cargo, [Cargo::ADMIN, Cargo::GESTOR, Cargo::USUARIO])) {
            throw new Exception('Cargo inválido.');
        }
        // Se o cargo a ser atribuído for ADMIN, a pessoa precisa ter permissão para isso
        if ($cargo == Cargo::ADMIN && !permissao()) {
            throw new Exception('Você não tem permissão para atribuir o cargo de ADMIN.');
        }
        return true;
    }


if (isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $fantasia = $_POST['fantasia'];
    $cnpj = $_POST['cnpj'];
    $cpf = $_POST['cpf'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $celular = $_POST['celular'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $status = isset($_POST['status']) ? 1 : 0;
    $data_r = $_POST['data_r'];
    if(strlen($estado) == 2){$estado = mb_strtoupper($estado);};

    $empresa = new Empresa(
        $id, // id
        $nome,
        $fantasia,
        $endereco,
        $bairro,
        $cidade,
        $estado,
        $cpf,
        $cnpj,
        $email,
        $celular,
        $telefone,
        $status,
        $data_r,
        $cep
);



$gestor = new Usuario(
    null, // id_usuario
    $id, // id_empresa
    $nome,
    $email,
    null,
    0, // processar
    0, // consultar
    Cargo::GESTOR
);

// Atribuindo cargo
try {
    atribuirCargo($gestor->cargo);
    // O cargo foi atribuído com sucesso
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}

Empresa::update($empresa);
Usuario::update($gestor);





}

if (isset($_POST['acao']) && $_POST['acao'] == 'adicionar') {

    $nome = $_POST['nome'];
    $fantasia = $_POST['fantasia'];
    $cnpj = $_POST['cnpj'];
    $cpf = $_POST['cpf'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $celular = $_POST['celular'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $status = isset($_POST['status']) ? 1 : 0;


    

    $empresa = new Empresa(
        null, // id
        $nome,
        $fantasia,
        $endereco,
        $bairro,
        $cidade,
        $estado,
        $cpf,
        $cnpj,
        $email,
        $celular,
        $telefone,
        $status,
        date('Y-m-d H:i:s'), // data_r
        $cep

    );


// Exemplo de criação de usuário com validação de cargo
$gestor = new Usuario(
    null, // id_usuario
    null, // id_empresa
    $nome,
    $email,
    '123456',
    0, // processar
    0, // consultar
    Cargo::GESTOR,
    $status
);

// Atribuindo cargo
try {
    atribuirCargo($gestor->cargo);
    // O cargo foi atribuído com sucesso
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}

if(!Empresa::read(null, $email) && !Usuario::read(null, $email)) { 

    Empresa::create($empresa);
    $empresacriada = Empresa::read(null, $email);
    $empresaid = $empresacriada[0];
    $gestor->id_empresa = $empresaid->id;
    Usuario::create($gestor);
}

} else {
    $error = "Dados inválidos.";

}

?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js" integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
            <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">

    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="../choices/choices.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
    <title>Gestor Office Control</title>
</head>

<body id="body" >




    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="100%" height="100%" src="/gestor-office.png" alt="Logo" class="logo">
        </div>

        <div id="Adicionar empresa-lateral" class="menu-item">
            <a href="/admin/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-building"></i></div> Adicionar Empresas</a>
        </div>

        
        </div>

    </nav>

    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
    <div id="itens-menu">

        <div class="menu-item">
            <a href="/admin/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-building"></i></div> Adicionar Empresas </a>
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



    
        <?php if(!isset($_GET['acao']) || $_GET['acao'] !== 'editar') { ?>
            
            <div class="main" id="container">

                <div class="botao">
         <button data-bs-toggle="modal" data-bs-target="#modal_empresa" class="btn btn-primary btn-lg botao-adm-adicionar">Nova Empresa</button>
    </div>

    
        

                <div class="col-md-12" style="padding: 0;">
                        <div class="row">
                    <?php $empresas = Empresa::read(); ?>
                

                    <div class="card">
                        <div class="card-header">
                            <h3>Empresas</h3>
                        </div>

                        <div class="card-header-div">
        <div class="card-header-borda">
            <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
                <h5 class="card-title">Filtros</h5>
                
                <form class="row g-3 align-items-end mb-3" method="get" action="index.php">
                    <input type="hidden" name="registro" value="cadastros">

                        <div class="col-md-2" style="width: 15%;"   >
                        <label for="nome" class="form-label">Nome:</label>
                        <div class="input-group">
                            <input name="nome" value="<?= $_GET['nome'] ?? "" ?>" type="Nome" class="form-control" id="nome" placeholder="Nome">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 15%;"   >
                        <label for="dataInicio" class="form-label">Data inicial:</label>
                        <div class="input-group">
                            <input name="dataInicial" value="<?= $_GET['dataInicial'] ?? "" ?>" type="date" class="form-control" id="dataInicio" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 15%;">
                        <label for="dataFinal" class="form-label">Data final:</label>
                        <div class="input-group">
                            <input name="dataFinal"  value="<?= $_GET['dataFinal'] ?? "" ?>" type="date" class="form-control" id="dataFinal" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>

                    <div class="col-md-1" style="width: 10%;">
                        <label for="estado" class="form-label">Estado:</label>
                        <select name="estado" class="form-select" id="estado">

                        
                            <option value="" <?php if(!isset($_GET['estado'])) {?> selected <?php } ?> >Selecione</option>
                            <?php

                            $lista_estados = [];

                            foreach ($empresas as $empresa) {
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
                                <option value="<?= $estado ; ?>"  <?php if(isset($_GET['estado']) && $estado == $_GET['estado']) {?> selected <?php } ?>  ><?= $estado ?></option>
                            <?php }; ?>
                        </select>
                    </div>
                
                    <div class="col-md-1" style="width: 10%; ">
                        <label for="cidade" class="form-label">Cidade:</label>
                        <select name="cidade" class="form-select" id="cidade">
                        
                            <option value="" <?php if(!isset($_GET['cidade'])) {?> selected <?php } ?> >Selecione</option>

                            <?php
                            
                            $lista_cidades = [];
                            foreach ($empresas as $empresa) {
                                if (!in_array(mb_strtolower($empresa->cidade), $lista_cidades)) {
                                    $lista_cidades[] = $empresa->cidade;
                                }
                            }
                            $cidades_unicas = [];
                            foreach ($lista_cidades as $cidade) {
                                $chave = mb_strtolower(trim($cidade), 'UTF-8');
                                $cidades_unicas[$chave] = ucfirst(mb_strtolower($cidade, 'UTF-8'));
                            }
                            ?>
                        
                            <?php foreach ($cidades_unicas as $cidade) { ?>
                                <option value="<?php echo $cidade; ?>"  <?php if(isset($_GET['cidade']) && $cidade == $_GET['cidade']) {?> selected <?php } ?>  ><?php echo $cidade; ?></option>
                            <?php }; ?>
                        </select>
                    </div>

                    <div class="col-md-1" style="width: 10%;">
                        <label for="bairro" class="form-label">Bairro:</label>
                        <select name="bairro" class="form-select" id="bairro">
                        
                            <option value="" <?php if(!isset($_GET['bairro'])) {?> selected <?php } ?> >Selecione</option>

                            <?php

                            $lista_bairros = [];

                            foreach ($empresas as $empresa) {
                                if (!in_array($empresa->bairro, $lista_bairros)) {
                                    $lista_bairros[] = $empresa->bairro;
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
                            
                                <option value="<?php echo $bairro; ?>"  <?php if(isset($_GET['bairro']) && $bairro == $_GET['bairro']) {?> selected <?php } ?>  ><?php echo $bairro; ?></option>
                            <?php }; ?>
                        </select>
                    </div>

                    <div class="botoes-admin">
                        <div>
                            <button type="submit" style="background-color: #5856d6; border: 0;" class="btn btn-primary">Buscar</button>
                        </div>
                        <div>
                            <a type="button" style="background-color: #5856d6; border: 0;" href="index.php?registro=cadastros" class="btn btn-secondary">Limpar</a>
                        </div>
                    </div>
                    
                </form>
                </div>
                </div>
</div>
                    <div class="tabela-lancamento">
                        <table style="margin-top:1em;" class="table table-striped">

                        
                        
                        
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Endereco</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $empresas = Empresa::read(null, null, $_GET['nome'] ?? null, $_GET['dataInicial'] ?? null, $_GET['dataFinal'] ?? null, $_GET['estado'] ?? null, $_GET['cidade'] ?? null, $_GET['bairro'] ?? null);
                            foreach ($empresas as $empresa) {
                                $status = ($empresa->status == 0) ? 'INATIVO' : 'ATIVO';
                                 $link = 'index.php?acao=editar&id=' . $empresa->id;
                                ?>
                                    
                                        <tr class="tr-clientes" onclick="window.location.href='<?= $link ?>'" style="cursor: pointer;">
                                            <td>
                                                <?php if($status == 'INATIVO') echo($status . ' - ') ?><?=$empresa->nom_fant?>
                                                <p>Razao social: <?=$empresa->razao_soc?></p>
                                            </td>
                                            <td>
                                                <?=$empresa->celular?>
                                                <p> Fixo:<?=$empresa->fixo?></p>
                                            </td>

                                            <td>
                                                <?=$empresa->rua . ', ' . $empresa->bairro . ' ' . $empresa->cidade . '-' . $empresa->estado ?>
                                                <p>CEP: <?= $empresa->cep ?></p>
                                            </td>
                                            <td>
                                                <?=$status?>
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
<?php } else if($_GET['acao'] == 'editar') { ?>
    
    <?php 
    $id = $_GET['id']; 
    $empresa = Empresa::read($id);
    print_r($empresa); 
    $empresa = $empresa[0];
     ?>
    
    <div class="main-edit">
    <div class="tabela-edit">
        <div class=" edit-card">
    <div class="card-body">

<div id="card-sub">Edite os dados do cliente</div>
<form action="index.php" method="post">

    <input type="hidden" name="data_r" value="<?=$empresa->data_r?>">
    <input type="hidden" name="id" value="<?=$empresa->id?>">
 
    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="nome" placeholder="Razao social / Nome" name="nome" value="<?= $empresa->razao_soc ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="senha" placeholder="Nome fantasia" name="fantasia" value="<?= $empresa->nom_fant ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cnpj" placeholder="CNPJ" name="cnpj" value="<?= $empresa->cpf ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cpf" placeholder="CPF" name="cpf" value="<?= $empresa->cnpj ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="cep" placeholder="CEP" name="cep" value="<?= $empresa->cep ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="endereco" placeholder="Endereço" name="endereco" value="<?= $empresa->rua ?>" required>
    </div>

    <div class="input-form-adm-group input-form-adm">

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input style="border-top-right-radius: 0; border-bottom-right-radius: 0;" type="text" class="form-control" id="bairro" placeholder="Bairro" name="bairro" value="<?= $empresa->bairro?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input style=" border-radius: 0;" type="text" class="form-control" id="cidade" placeholder="Cidade" name="cidade" value="<?= $empresa->cidade ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input style="border-top-left-radius: 0; border-bottom-left-radius: 0;" type="text" class="form-control" id="estado" placeholder="Estado" name="estado" value="<?= $empresa->estado ?>" required>
    </div>

    </div>

    <div class="input-form-contato-adm-group input-form-adm">

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input style="border-top-right-radius: 0; border-bottom-right-radius: 0;" type="text" class="form-control" id="celular" placeholder="Celular" name="celular" value="<?= $empresa->celular ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input  style="border-top-left-radius: 0; border-bottom-left-radius: 0;" type="text" class="form-control" id="telefone" placeholder="Telefone fixo" name="telefone" value="<?= $empresa->fixo ?>" required>
    </div>

    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="email" placeholder="E-mail" name="email" value="<?= $empresa->email ?>" required>
    </div>

    <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
            <label for="status" style="margin-bottom:0;">Ativo</label>
            <input type="checkbox" <?php if($empresa->status == 1) {?> checked <?php }; ?> onchange="" name="status" class="form-check-input"
            value="">
    </div>

    <button name="acao" value="editar" type="submit" style="background-color:#5856d6; padding-inline:1.5em; " class="btn btn-primary">Salvar</button>
</form>

    </div>
</div>
</div>
</div>
    <?php } ?>

    <!-- Modal -->
    <div class="modal fade" id="modal_empresa" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Adicionar Empresa</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="index.php">
                        <input type="hidden" name="id" value="">
                        <label>Informe os dados da empresa </label>

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
                                    <input type="text" onchange="checar()" name="bairro" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                        placeholder="Bairro" value="" required>
                                </div>

                                

                                <div class="input-cidade">
                                    <input type="text" onchange="checar()" name="cidade" class="form-control" style=" border-radius: 0;"
                                        placeholder="Cidade" value="" required>
                                </div>

                                

                                <div class="input-estado">
                                    <input type="text" onchange="checar()" name="estado" class="form-control" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                        placeholder="Estado" value="" required>
                                </div>
                        </div>
                        

                        <div class="input-form-contato-adm-group input-form-adm">

                        

                        <div class="input-celular">
                            <input type="text" onchange="checar()" name="celular" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                placeholder="Celular" value="" required>
                        </div>

                        

                        <div class="input-telefone">
                            <input type="text" onchange="checar()" name="telefone" class="form-control" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                placeholder="Telefone Fixo" value="" required>
                        </div>
                        
                        </div>

                        <div class="input-email input-form-adm">
                            <input type="text" onchange="checar()" name="email" class="form-control"
                                placeholder="E-mail" value="" required>
                        </div>

                        

                        <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
                            <label for="status" style="margin-bottom:0;">Ativo</label>
                            <input type="checkbox" checked onchange="" name="status" class="form-check-input"
                                 value="">
                        </div>

                        

                        <div style="margin-bottom: 3em;" class="footer">

                        <button name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" disabled href="consulta_cliente.php">Salvar</button>
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



</body>

<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script> -->
<script src="../choices/choices.js"></script>

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
    }}
</script>

<?php if ( isset($_GET['acao']) && $_GET['acao'] == 'adicionar') { ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_empresa');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'index.php';
            });
        });


    </script>
<?php }
; ?>



</html>


