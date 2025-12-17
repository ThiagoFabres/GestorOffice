<!DOCTYPE html>
<?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/empresas.php';
require_once __DIR__ . '/../db/entities/cargo.php';

session_start();


    
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 2) {
    header('Location: /');
    exit();
}
$nomeEmpresa = Empresa::read($_SESSION['usuario']->id_empresa)[0]->nom_fant;
require_once __DIR__ . '/gestor.php';
$erro = filter_input(INPUT_GET, 'erro');

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
    <link rel="stylesheet" href="/../components/header/header.css"> 
    <link rel="stylesheet" href="/../components/lateral/lateral.css">
    <title>Gestor Office Control</title>
</head>

<body id="body" >


    <div id="barra-lateral" style="">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
    <div id="itens-menu">

        <div class="menu-item">
            <a href="/gestor/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-person"></i></div> Adicionar Usuario </a>
        </div>

        </div>

    </div>


    <div id="header" style="right:10em">
        
        <button onclick="encolher()" style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer; z-index:1000;">
            <span class="btn bi bi-list"></span>
        </button>
        
    <div id="nome-empresa">
            <h4><?=$nomeEmpresa?></h4>
        </div>

    <div class="conta-header" style="position:relative; float:right; margin-right:2em;">

            <a href="/" class="dropdown-item">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>

    </div>
    </div>



    
        <?php if(!isset($get_acao) || $get_acao !== 'editar') { ?>
            
            <div class="main" id="container">
                 <div class="botao">
         <button data-bs-toggle="modal" data-bs-target="#modal_usuario" class="btn btn-primary btn-lg botao-adm-adicionar">Novo Usuario</button>
    </div>
            
                <div class="card mb-4">
        <div class="card-header">
            <h3>Usuarios</h3>
        </div>

        <div class="card-header-div">
        <div class="card-header-borda">
            <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
                <h5 class="card-title">Filtros</h5>
                
                <form class="row g-3 align-items-end mb-3" method="get" action="index.php">
                    <input type="hidden" name="registro" value="cadastros">
                    <div class="col-md-2" style="width: 20%;">
                        <label for="nome" class="form-label">Nome:</label>
                        <div class="input-group">
                            <input name="nome" value="<?= htmlspecialchars($get_nome, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="text" class="form-control" id="dataInicio" placeholder="Nome">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 20%;">
                        <label for="dataInicio" class="form-label">Data inicial:</label>
                        <div class="input-group">
                            <input name="dataInicial" value="<?= htmlspecialchars($get_data_inicial, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="date" class="form-control" id="dataInicio" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 20%;">
                        <label for="dataFinal" class="form-label">Data final:</label>
                        <div class="input-group">
                            <input name="dataFinal"  value="<?= htmlspecialchars($get_data_final, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="date" class="form-control" id="dataFinal" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>

                    <div class="botoes-gestor">
                        <div>
                            <button type="submit" style="background-color: #5856d6; border: 0;" class="btn btn-primary">Filtrar</button>
                        </div>

                        <div>
                            <a type="button" style="border: 0;" href="index.php" class="btn btn-secondary">Limpar</a>
                        </div>
                    </div>
                    
                </form>
                </div>
                </div>
            </div>

            <div class="card-body tab-content" id="relatorioTabsContent" style=" padding: 0;">

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Modo</th>
                        <th>Status</th>

                    </tr>
                </thead>
                <tbody>
            <?php 
            $cadastros_reg = Usuario::read(null,
            null,
            $_SESSION['usuario']->id_empresa,
            Cargo::USUARIO,
            $get_nome,
            $get_data_inicial,
            $get_data_final);

            if (!empty($cadastros_reg)) { ?>
                        <?php foreach ($cadastros_reg as $cadastro) {?>


                            
                                                            <tr onclick="window.location.href='index.php?acao=editar&id=<?=$cadastro->id_usuario?>'" data-id="<?= htmlspecialchars($cadastro->id, ENT_QUOTES, 'UTF-8') ?>" style="cursor: pointer;">
                                <td>
                                                <?=htmlspecialchars($cadastro->nome, ENT_QUOTES, 'UTF-8')?>
                                                <p>E-mail: <?=htmlspecialchars($cadastro->email, ENT_QUOTES, 'UTF-8')?></p>
                                            </td>

                                            <td>
                                                <?php if($cadastro->processar == 1) {echo 'PROCESSAR';} else {echo 'CONSULTAR';} ?>
                                            </td>
                                            <td>
                                                <?php if($cadastro->status == 1) {echo 'ATIVO';} else {echo 'INATIVO';} ?>
                                                <p>Data de registro: <?= date('d/m/Y', strtotime(htmlspecialchars($cadastro->data_r, ENT_QUOTES, 'UTF-8'))) ?></p>
                                            </td>
                                
                            
                                
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td>Nenhum usuario encontrado</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>


                        </tr>
                    <?php } ?>
            
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php } else if($get_acao == 'editar') { ?>
    
    <?php 
    
    $usuario = Usuario::read($get_id)[0];
    
     ?>
    
    <div class="main-edit">
    <div class="tabela-edit">
        <div class=" edit-card">
    <div class="card-body">

<div id="card-sub">Edite os dados do usuario</div>

<form action="index.php" method="post">

    <input type="hidden" name="id" value="<?=$usuario->id_usuario?>">
 
    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="input-nome" placeholder="Nome" name="nome" value="<?= htmlspecialchars($usuario->nome, ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div style="display:flex; flex-direction:row;" class="mb-3">
        <input type="text" class="form-control" id="input-email" placeholder="E-mail" name="email" value="<?= htmlspecialchars($usuario->email, ENT_QUOTES, 'UTF-8') ?>" required>
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

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                        <button name="acao" value="inserir_cliente" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" href="consulta_cliente.php">Salvar</button>
                        
                            

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



//     function checar() {
//         var nome = document.querySelector('.input-nome input').value;
//         var email = document.querySelector('.input-email input').value;
//         let consultar = document.querySelector('input[name="consultar"]');
//         let processar = document.querySelector('input[name="processar"]');
        



// if (nome !== '' && email !== '' && (consultar.checked || processar.checked)) {
//   document.querySelector('button[name="acao"]').disabled = false;
// } else {
//   document.querySelector('button[name="acao"]').disabled = true;
// }

if (!consultar.checked) {
            processar.checked = false;
        }

        if (processar.checked) {
            consultar.checked = true;
        }

    // }
<?php if (isset($get_acao) && $get_acao == 'adicionar') { ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_usuario');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'index.php';
            });
        });
<?php } if(isset($erro) && $erro == 'usado') { ?>
                alert('Não é possível adicionar esse usuario, pois já existe um usuario ou gestor com esse e-mail');
                window.location.href = 'index.php';
<?php } ?>

</script>



</html>