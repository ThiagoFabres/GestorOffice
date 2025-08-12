<!DOCTYPE html>
<?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/empresas.php';
require_once __DIR__ . '/../db/entities/cargo.php';

session_start();


    
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 2) {
    header('Location: /');
}

if(isset($_POST['acao']) && $_POST['acao'] == 'editar') {



    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $status = isset($_POST['status']) ? 1 : 0;
    $consultar = isset($_POST['consultar']) ? 1 : 0;
    $processar = isset($_POST['processar']) ? 1 : 0;
    
$usuario = new Usuario(
    $_POST['id'],
    $_SESSION['usuario']->id_empresa,
    $nome,
    $email,
    null,
    $processar,
    $consultar,
    null,
    $status
);

    Usuario::update($usuario);
}

if (isset($_POST['acao']) && $_POST['acao'] == 'inserir_cliente') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $consultar = $_POST['consultar'];
    $processar = $_POST['processar'];

    function permissao() {
        // Exemplo: só permite se o usuário logado for admin
        return isset($_SESSION['usuario']) && $_SESSION['usuario']->cargo == Cargo::GESTOR;
    }

    function atribuirCargo($cargo) {
        // Verifica se o cargo é válido
        if (!in_array($cargo, [Cargo::ADMIN, Cargo::GESTOR, Cargo::USUARIO])) {
            throw new Exception('Cargo inválido.');
        }
        // Se o cargo a ser atribuído for ADMIN, a pessoa precisa ter permissão para isso
        if ($cargo == Cargo::GESTOR && !permissao()) {
            throw new Exception('Você não tem permissão para atribuir o cargo de GESTOR.');
        }
        return true;
    }



// Exemplo de criação de usuário com validação de cargo
$usuario = new Usuario(
    null, // id_usuario
    $_SESSION['usuario']->id_empresa, // id_empresa
    $nome,
    $email,
    null,
    $processar, // processar
    $consultar, // consultar
    Cargo::USUARIO, // cargo
    $status
);


// Atribuindo cargo
try {
    atribuirCargo($usuario->cargo);
    // O cargo foi atribuído com sucesso
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}



    Usuario::create($usuario);

    header('Location: index.php');
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
    <title>Calendario</title>
</head>

<body id="body" >


    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="100%" height="100%" src="/gestor-office.png" alt="Logo" class="logo">
        </div>

        <div id="Adicionar empresa-lateral" class="menu-item">
            <a href="/gestor/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-person"></i></div> Adicionar Usuarios</a>
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
        <a class="superior-item" href="/gestor/">Dashboard</a>
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
        <a href="index.php?acao=adicionar" class="btn btn-primary btn-lg botao-adm-adicionar">Novo Usuario</a>
    </div>

    
    <div class="tabela">

      <div class="tabela-borda">
        
            <div class="row">
                <div class="col-md-12">
                

                    <table class="table table-striped">
                        <div class="titulo-tabela"><h4>Usuarios</h4></div>
                        
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Modo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $usuarios = Usuario::read(false, false, $_SESSION['usuario']->id_empresa, 3);
                            foreach ($usuarios as $usuario) {

                                

                                $status = ($usuario->status == 0) ? 'INATIVO' : 'ATIVO';
                                 $link = 'index.php?acao=editar&&id=' . $usuario->id_usuario;
                                ?>
                                    
                                        <tr class="tr-clientes" onclick="window.location.href='<?= $link ?>'" style="cursor: pointer;">
                                            <td>
                                                <?php if($status == 'INATIVO') echo($status . ' - ') ?> <?=$usuario->nome?>
                                                <p>E-mail: <?=$usuario->email?></p>
                                            </td>
                                            <td>
                                                <?php if($usuario->processar == 1) {echo 'PROCESSAR';} else {echo 'CONSULTAR';} ?>
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
    </div>
<?php } else if($_GET['acao'] == 'editar') { ?>
    
    <?php 
    $id = $_GET['id']; 
    $usuario = Usuario::read($id);
    $usuario = $usuario[0];
     ?>
    
    <div class="main-edit">
    <div class="tabela-edit">
        <div class=" edit-card">
    <div class="card-body">

<div id="card-sub">Edite os dados do usuario</div>
<form action="index.php" method="post">

    <input type="hidden" name="id" value="<?=$usuario->id_usuario?>">
 
    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="input-nome" placeholder="Nome" name="nome" value="<?= $usuario->nome ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="input-email" placeholder="E-mail" name="email" value="<?= $usuario->email ?>" required>
    </div>

    

    <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
            <label for="status" style="margin-bottom:0;">Ativo</label>
            <input type="checkbox" <?php if($usuario->status == 1) {?> checked <?php }; ?> value="1" onchange="" name="status" class="form-check-input"
            value="">
    </div>

                            <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
                            <label for="consultar" style="margin-bottom:0;">Consultar</label>
                            <input type="checkbox" checked  onchange="checar()" name="consultar" class="form-check-input"
                                 value="1">
                        </div>

                        <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
                            <label for="processar" style="margin-bottom:0;">Processar</label>
                            <input type="checkbox" <?php if($usuario->processar == 1) {?> checked <?php } ?> onchange="" name="processar" class="form-check-input"
                                 value="1">
                        </div>

    <button name="acao" value="editar" type="submit" style="background-color:#5856d6; padding-inline:1.5em; " class="btn btn-primary">Salvar</button>
</form>

    </div>
</div>
</div>
</div>
    <?php } ?>
    <!-- Modal -->
    <div class="modal fade" id="modal_usuario" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Adicionar Usuario</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="index.php">
                        <input type="hidden" name="id" value="">
                        <label>Informe os dados do funcionario </label>

                        <div class="input-nome input-form-adm">
                            <!--Nome: -->
                            <input type="text" onchange="checar()" name="nome" class="form-control" placeholder="Nome"
                                value="" required>
                        </div>

                        

                        <div class="input-email input-form-adm">
                            <!--Nome email-->
                            <input type="text" onchange="checar()" name="email"
                                class="form-control" placeholder="E-mail" value="" required>
                        </div>

                        

                        <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
                            <label for="status" style="margin-bottom:0;">Ativo</label>
                            <input type="checkbox" checked onchange="" name="status" class="form-check-input"
                                 value="1">
                        </div>

                        <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
                            <label for="consultar" style="margin-bottom:0;">Consultar</label>
                            <input type="checkbox" checked  onchange=" checar()" name="consultar" class="form-check-input"
                                 value="1">
                        </div>

                        <div style="margin-left:1.25em; margin-top:0; align-self:center;" class="input-status input-form-adm">
                            <label for="processar" style="margin-bottom:0;">Processar</label>
                            <input type="checkbox"  onchange=" checar()" name="processar" class="form-check-input"
                                 value="1">
                        </div>


                        <div style="margin-bottom: 3em;" class="footer">

                        <button name="acao" value="inserir_cliente" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" disabled href="consulta_cliente.php">Salvar</button>
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
        var nome = document.querySelector('.input-nome input').value;
        var email = document.querySelector('.input-email input').value;
        let consultar = document.querySelector('input[name="consultar"]');
        let processar = document.querySelector('input[name="processar"]');
        



if (nome !== '' && email !== '' && (consultar.checked || processar.checked)) {
  document.querySelector('button[name="acao"]').disabled = false;
} else {
  document.querySelector('button[name="acao"]').disabled = true;
}

if (!consultar.checked) {
            processar.checked = false;
        }

        if (processar.checked) {
            consultar.checked = true;
        }

    }


</script>

<?php if (isset($_GET['acao']) && $_GET['acao'] == 'adicionar') { ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_usuario');
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