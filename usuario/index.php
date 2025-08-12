<?php

require_once __DIR__ . '/../db/entities/usuarios.php';


session_start();

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
    <title>Calendario</title>
</head>

<body id="body" >


    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
    <div id="itens-menu">
                        <div class="menu-item">
            <a href="/usuario/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-layers"></i></div> Dashboard </a>
        </div>
    
        <div class="menu-item accordion" >

<a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button" aria-expanded="false" aria-controls="cadastrosMenu">
        <i class="bi bi-person"></i> Cadastros
      </a>
      <div class="collapse" id="cadastrosMenu">
        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
          <li><a href="clientes.php" class="link-light text-decoration-none"><i class="bi bi-person"></i>Cliente/Fornecedor</a></li>
          <li><a href="bairros.php" class="link-light text-decoration-none"><i class="bi bi-houses"></i>Bairro</a></li>
          <li><a href="produtos.php" class="link-light text-decoration-none"><i class="bi bi-buildings"></i>Cidade</a></li>
          <li><a href="produtos.php" class="link-light text-decoration-none"><i class="bi bi-cash-coin"></i>Tipo Pagamento</a></li>
          <li><a href="produtos.php" class="link-light text-decoration-none"><i class="bi bi-tag"></i>Categoria</a></li>
          
        </ul>
      </div>
        </div>

        <div class="menu-item">
            <a href="/usuario/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano de Contas </a>
        </div>

        <div class="menu-item">
            <a href="/usuario/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber </a>
        </div>

        <div class="menu-item">
            <a href="/usuario/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a Pagar </a>
        </div>

        <div class="menu-item accordion" >

        <a class="nav-link text-white" data-bs-toggle="collapse" href="#registrosMenu" role="button" aria-expanded="false" aria-controls="cadastrosMenu">
        <i class="bi bi-journal-bookmark"></i> Registros
      </a>

        <div class="collapse"  id="registrosMenu">
            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
            <li><a href="clientes.php" class="link-light text-decoration-none"><i class="bi bi-list-task"></i>Cadastros</a></li>
            <li><a href="fornecedores.php" class="link-light text-decoration-none"><i class="bi bi-journal-bookmark"></i>Plano de Contas</a></li>
            <li><a href="produtos.php" class="link-light text-decoration-none"><i class="bi bi-wallet"></i>Contas a Receber</a></li>
            <li><a href="produtos.php" class="link-light text-decoration-none"><i class="bi bi-cash-stack"></i>Contas a Pagar</a></li>
            <li><a href="produtos.php" class="link-light text-decoration-none"><i class="bi bi-file-earmark-text"></i>DRE</a></li>
            
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



    

            
            <div class="main" id="container">

                
    </div>


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



</html>0


