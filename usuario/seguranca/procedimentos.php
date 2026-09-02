<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/cargo.php';
require_once __DIR__ . '/../../db/entities/seguranca/procedimento.php';

session_start();


    
$empresa_usuario_obj = Empresa::read($_SESSION['usuario']->id_empresa)[0];
$nomeEmpresa = $empresa_usuario_obj->nom_fant;

if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->permissao_seguranca != 1 || $empresa_usuario_obj->permissao_seguranca != 1) {
    header('Location: /');
    exit();
}

$erro = filter_input(INPUT_GET, 'erro');
$lateral_seguranca = true;
$lateral_target = 'procedimentos';

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


        <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'; ?>
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