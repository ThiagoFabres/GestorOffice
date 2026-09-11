<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/cargo.php';
require_once __DIR__ . '/../../db/entities/seguranca/procedimento.php';
require_once __DIR__ . '/../../db/entities/logo.php';

session_start();


$id_empresa = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($_SESSION['usuario']->id_empresa)[0];
$nomeEmpresa = $empresa_usuario_obj->nom_fant;

if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 2 || $empresa_usuario_obj->permissao_seguranca != 1) {
    header('Location: /');
    exit();
}

$erro = filter_input(INPUT_GET, 'erro');
$lateral_seguranca = true;
$lateral_target = 'procedimentos';
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

$texto_empresa = Procedimento::read($_SESSION['usuario']->id_empresa)[0]->texto ?? '';

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
    <link rel="stylesheet" href="/../components/lateral/lateral.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="../choices/choices.css"></link>

    <title>Gestor Office Control</title>
</head>

<body id="body" data-nome-empresa="<?= htmlspecialchars($nomeEmpresa) ?>">


        <div id="barra-lateral">
        <div id="logo-container">
            <?php  if($logo_image): ?>
            <img width="220px" height="220px" src="<?= $logo_image ?>" alt="Logo" class="logo">
            <?php else: ?>
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
            <?php endif; ?>
        </div>
    <div id="itens-menu" style="height: 100%;">

        <div class="menu-item">
            <a href="/gestor/"> <div ><i class="bi bi-person"></i></div> Adicionar Controle</a>
        </div>

        <?php if($empresa_usuario_obj->permissao_seguranca === 1) { ?>
            <div class="menu-item menu-item-atual">
                <a href="procedimentos.php"> <div ><i class="bi bi-envelope-open"></i></div> Procedimentos</a>
            </div>
            <div class="menu-item">
                <a href="/gestor/controle/controle.php"> <div ><i class="bi bi-clock"></i></div> Controle</a>
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
        <?php require_once __DIR__ . '/../../componentes/header/header.php'; ?>

                
    <div class="main" id="container">
        Texto de Procedimento Visivel no Aplicativo:

        <div class="w-75 d-flex flex-column">
            <form action="procedimentos_manager.php" method="POST">
                <textarea id="texto_procedimento" name="texto_procedimento" style="width: 100%; height: 200px; font-size: 16px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
<?= htmlspecialchars($texto_empresa) ?>
</textarea>
                <button type="submit" class="btn btn-primary mt-2 w-100">Salvar</button>
            </form>
        </div>
    </div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>
<script src="gerar.js"></script>

<script>


</script>



</html>