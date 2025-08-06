<!DOCTYPE html>
<?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/empresas.php';
require_once __DIR__ . '/../db/entities/cargo.php';

session_start();


if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 1) {
    header('Location: /');
}

if (isset($_POST['acao']) && $_POST['acao'] == 'inserir_cliente') {
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

    $empresa = new Empresa(
        null, // id
        $nome,
        $fantasia,
        $endereco,
        $bairro,
        $cidade,
        $estado,
        $cnpj,
        $email,
        $celular,
        $telefone,
        '', // contato
        $status
    );

// Exemplo de criação de usuário com validação de cargo
$gestor = new Usuario(
    null, // id_usuario
    null, // id_empresa
    $nome,
    $email,
    password_hash('123456', PASSWORD_DEFAULT),
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

    $empresaId = Empresa::create($empresa);
    $gestor->id_empresa = $empresaId;
    Usuario::create($gestor);

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

<body >


    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="100%" height="100%" src="/gestor-office.png" alt="Logo" class="logo">
        </div>

        <div id="Adicionar empresa-lateral" class="menu-item">
            <a href="/admin/"> <div style="width:10%; height:10%; align-items:center;"><i class="bi bi-building"></i></div> Adicionar Empresas</a>
        </div>

        
        </div>

    </nav>


    <div id="header">
    <div id="titulo-header"><a>Dashboard</a></div>
    <div id="menu-superior">
        <a class="superior-item" href="/admin/">Dashboard</a>
    </div>
    <div class="conta-header" style="position:relative; float:right; margin-right:2em;">
        <button id="userBtn" type="button" style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer;">
            <span style="color:#181f2b;">Admin</span>
        </button>
        <div id="userMenu" style="right:0;">
            <a href="/" class="dropdown-item">
                <i class="bi bi-box-arrow-left"></i> Logout
        </a>
        </div>
    </div>
    </div>



    <div class="container">
            <a href="index.php?acao=adicionar" style="width: 10em; margin-bottom: 2em;" type="button"
                class="btn btn-primary">
                Adicionar Empresa
            </a>
    </div>

    <?php
    
    echo '<pre>';
    print_r($_POST);  
    echo '</pre>';

    ?>


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
                                 value="" required>
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
        var fantasia = document.querySelector('.input-fantasia input').value;
        var cpf = document.querySelector('.input-cpf input').value;
        var cnpj = document.querySelector('.input-cnpj input').value;
        var cep = document.querySelector('.input-cep input').value;
        var endereco = document.querySelector('.input-endereco input').value;
        var bairro = document.querySelector('.input-bairro input').value;
        var cidade = document.querySelector('.input-cidade input').value;
        var estado = document.querySelector('.input-estado input').value;
        var celular = document.querySelector('.input-celular input').value;
        var telefone = document.querySelector('.input-telefone input').value;
        var email = document.querySelector('.input-email input').value;




        if (nome !== '' && fantasia !== '' && cpf !== '' && cnpj !== '' && cep !== '' && endereco !== '' && bairro !== '' && cidade !== '' && estado !== '' && celular !== '' && telefone !== '' && email !== '') {
            document.querySelector('button[name="acao"]').disabled = false;
        } else {
            document.querySelector('button[name="acao"]').disabled = true;
        }
    }
</script>

<?php if (isset($_GET['acao']) && $_GET['acao'] == 'editar' || isset($_GET['acao']) && $_GET['acao'] == 'adicionar') { ?>
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