<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/cargo.php';
require_once __DIR__ . '/../../db/entities/logo.php';
require_once __DIR__ . '/../../db/entities/controle.php';
session_start();
$env = parse_ini_file(__DIR__ . '/../../.env');
    
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 2) {
    header('Location: /');
    exit();
}
$id_empresa = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($id_empresa)[0];
$nomeEmpresa = $empresa_usuario_obj->nom_fant;
$erro = filter_input(INPUT_GET, 'erro');
$logo_image = null;
$logo_blob = null;
    $logos = Logo::read(null, $id_empresa);
    if ($logos && isset($logos[0]->foto) && !empty($logos[0]->foto)) {
        $logo_blob = $logos[0]->foto;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($finfo, $logo_blob);
        unset($finfo);
        if (!$mime_type) {
            $mime_type = 'image/png';
        }
        $logo_image = 'data:' . $mime_type . ';base64,' . base64_encode($logo_blob);
    }
$bot_username = $env['TELEGRAM_BOT_USERNAME'];
$link_vinculo_1 = "https://web.telegram.org/#/im?tgaddr=tg%3A%2F%2Fresolve%3Fdomain%3D{$bot_username}%26start%3D{$id_empresa}_1"; 
$link_vinculo_2 = "https://web.telegram.org/#/im?tgaddr=tg%3A%2F%2Fresolve%3Fdomain%3D{$bot_username}%26start%3D{$id_empresa}_2";
$get_acao = filter_input(INPUT_GET, 'acao');
$controleEdicao = null;
if ($get_acao === 'editar') {
    $controleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    foreach (Controle::read($id_empresa) as $controle) {
        if ((int) $controle->id === (int) $controleId) {
            $controleEdicao = $controle;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<head>

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
    <link rel="stylesheet" href="/../usuario/style/responsivo.css"> 
    <link rel="stylesheet" href="/../components/lateral/lateral.css">
    <title>Gestor Office Control</title>
</head>

<body id="body" >


    <div id="barra-lateral">
        <div id="logo-container">
            <?php  if($logo_image): ?>
            <img width="220px" height="220px" src="<?= $logo_image ?>" alt="Logo" class="logo">
            <?php else: ?>
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
            <?php endif; ?>
        </div>
    <div id="itens-menu" style="height: 100%;">

        <div class="menu-item ">
            <a href="/gestor/"> <div ><i class="bi bi-person"></i></div> Adicionar Usuario</a>
        </div>

        <?php if($empresa_usuario_obj->permissao_seguranca === 1) { ?>
            <div class="menu-item">
                <a href="/gestor/procedimentos/procedimentos.php"> <div ><i class="bi bi-envelope-open"></i></div> Procedimentos</a>
            </div>
            <div class="menu-item menu-item-atual">
                <a href="controle/controle.php"> <div ><i class="bi bi-clock"></i></div> Controle</a>
            </div>
        <?php } ?>
            <!-- quero que isso fique embaixo -->
        <div class="d-flex flex-column" style="margin-top: auto;">
            <div class="menu-item">
                <div class="d-flex flex-column">
                    <?php if($empresa_usuario_obj->celular1_atividade === null) { ?>
                        <label style="color: red; white-space: wrap;">
                            Telegram 1 Não Vinculado
                        </label>
                    
                        <a href="<?= $link_vinculo_1 ?>" target="_blank" style="padding: 0;"> <div><i></i></div> Vincular Telegram (1)</a>
                    <?php } else {?>
                        <a href="<?= $link_vinculo_1 ?>" target="_blank" style="padding: 0;"> <div><i></i></div> Revincular Telegram (1)</a>
                    <?php } ?>
                    
                </div>
            </div>
            <div class="menu-item" style="margin-top: 2em; margin-bottom: 1em;">
                <div class="d-flex flex-column">
                    <?php if($empresa_usuario_obj->celular2_atividade === null) { ?>
                        <label style="color: red; white-space: wrap;">
                            Telegram 2 Não Vinculado
                        </label>
                    
                        <a href="<?= $link_vinculo_2 ?>" target="_blank" style="padding: 0;"> <div><i></i></div> Vincular Telegram (2)</a>
                    <?php } else {?>
                        <a href="<?= $link_vinculo_2 ?>" target="_blank" style="padding: 0;"> <div><i></i></div> Revincular Telegram (2)</a>
                    <?php } ?>
                    
                </div>
            </div>
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

            
            <div class="main" id="container">
                 <div class="botao">
         <button data-bs-toggle="modal" data-bs-target="#modal_controle" class="btn btn-primary btn-lg botao-adm-adicionar">Novo Controle</button>
    </div>
            
                <div class="card mb-4">
        <div class="card-header">
            <h3>Pontos De Controle</h3>
        </div>

            <div class="card-body tab-content" id="relatorioTabsContent" style=" padding: 0;">

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Horario</th>
                        <th>Tolerância</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (Controle::read($id_empresa) as $controle) { ?>
                    <tr>
                        <td><?= htmlspecialchars($controle->hora, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $controle->tolerancia ?> minutos</td>
                        <td>
                            <a class="btn btn-primary btn-sm" href="controle.php?acao=editar&id=<?= (int) $controle->id ?>">Editar</a>
                            <form method="post" action="controle_manager.php" style="display:inline;" onsubmit="return confirm('Deseja excluir este controle?');">
                                <input type="hidden" name="id" value="<?= (int) $controle->id ?>">
                                <button type="submit" name="acao" value="deletar" class="btn btn-danger btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../componentes/modais/gestor/modal_controle.php'; ?>

</body>

<?php if ($get_acao === 'editar' && $controleEdicao !== null) { ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('modal_controle');
    if (modalElement) {
        new bootstrap.Modal(modalElement).show();
    }
});
</script>
<?php } ?>


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
const consultar = document.querySelector('input[name="consultar"]');

const processar = document.querySelector('input[name="processar"]');

const seguranca = document.querySelector('input[name="seguranca"]');
document.addEventListener('change', function() {
    console.log('Segurança ativada');
    if(seguranca.checked) {
        processar.checked = false;
        consultar.checked = false;
        processar.disabled = true;
        consultar.disabled = true;
    } else {
        consultar.checked = true;
        processar.disabled = false;
        consultar.disabled = false;
    }
});
console.log(seguranca.checked);
if(seguranca.checked) {
    
    processar.checked = false;
    consultar.checked = false;
    processar.disabled = true;
    consultar.disabled = true;
} else {
    processar.disabled = false;
    consultar.disabled = false;
}

if (!consultar.checked) {
            processar.checked = false;
        }

        if (processar.checked) {
            consultar.checked = true;
        }

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

        
        
        function encolher(acao) {
        const barra = document.getElementById('barra-lateral');
        const container = document.getElementById('container');
        const superior = document.getElementById('header');

        



        if ( barra.style.animationName != 'encolher-lateral'){
            

            if(document.querySelector('body').clientWidth >= 800) {                
            localStorage.setItem('tela', 'cheia')
           

            container.style.animationName = 'encolher-container'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'forwards';

            }
            superior.style.animationName = 'encolher-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'forwards';
            barra.style.animationName = 'encolher-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'forwards';
        } else if( barra.style.animationName == 'encolher-lateral') {
            if(document.querySelector('body').clientWidth >= 800) {
                localStorage.setItem('tela', 'normal')
            
            container.style.animationName = 'expandir-container'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'backwards';

            }
            superior.style.animationName = 'expandir-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'backwards';
            barra.style.animationName = 'expandir-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'backwards';

            

            return;
        } 
    }
    if(!document.querySelector('body').clientWidth >= 800) {
        if(localStorage.getItem('tela') == 'cheia') {
        setTimeout(
            () => {
                encolher('encolher')
            }, 100
        )
        
    }
    }


</script>



</html>