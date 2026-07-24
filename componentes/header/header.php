<?php
require_once __DIR__ . '/../../db/entities/empresas.php';
$empresa_atual_obj = Empresa::read($_SESSION['usuario']->id_empresa)[0];
$nome_empresa = $empresa_atual_obj->nom_fant;
if($_SESSION['usuario']->status == 0 && $_SESSION['usuario']->cargo == 3) {
    header('Location: /index.php?erro=usuario_inativo');
    exit;
}

$empresas_lista = Empresa::read(cnpj_principal: $empresa_atual_obj->cnpj_principal);
$actual_url = $_SERVER['REQUEST_URI'];
?>

<link rel="stylesheet" href="/componentes/header/header.css"> 
<link rel="stylesheet" href="/usuario/style/responsivo.css"> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>

<style>
    .menu-animado {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out, padding 0.4s ease-in-out;
    }

    .menu-animado.aberto {
        max-height: 400px;
        min-height: 400px; 
        opacity: 1;
        overflow-y:auto;
    }
</style>

<div id="header">
    <div id="titulo-header">
        <button class="btn mb-0" onclick="encolher()">
            <span class="btn bi bi-list mb-0"></span>
        </button>
    </div>

    <div id="nome-empresa" class="w-100">
        <?php if(isset($todas_empresas) && $todas_empresas) { ?>
            <h1>TODAS AS EMPRESAS</h1>
        <?php } else {
            if(count($empresas_lista) > 1) { ?>
                <div class="w-75 d-flex justify-content-center mx-auto">
                    <button type="button" 
                            class="btn btn-link text-decoration-none text-dark " 
                            onclick="toggleHeaderMenu()">
                        <?= $nome_empresa ?> <i class="bi bi-chevron-down small ms-1"></i>
                    </button>
                </div>
            <?php } else { ?>
                <h1><?=$nome_empresa?></h1>
        <?php } } ?>
    </div>

    <div id="conta-header">
        <a href="/" class="dropdown-item">
            <i class="bi bi-box-arrow-left"></i> Sair
        </a>
    </div>
</div>

<?php if(count($empresas_lista) > 1) { ?>
    <div class="menu-animado dragscroll d-flex flex-row gap-3 justify-content-evenly card-body card-contas-lista" 
     id="headerMenu" 
     style="position: absolute; z-index: 1000; overflow-x: auto;">
        <?php foreach($empresas_lista as $i_e => $empresa_obj) {

            $usuario_principal = Usuario::read(idempresa:$empresa_obj->id, principal:true)[0] ?? null;
            $usuario_empresa = $usuario_principal ?? Usuario::read(idempresa:$empresa_obj->id, cargo:3)[0] ?? null;
            if($usuario_empresa === null) {
                continue;
            }
        ?>
        <div class="card card-contas" style="min-width: 200px; width:24.25%;">
    <form method="post" action="/login.php" <?php if($empresa_obj->id == $empresa_atual_obj->id) { ?>style="background-color: #465168;"<?php }?>>
        <input type="hidden" name="target" value="<?= $empresa_obj->id ?>">
        <input type="hidden" name="acao" value="autenticacao_insta">
        <input type="hidden" name="url_atual" value="<?= htmlspecialchars($actual_url, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" style="background: none; border: none; padding: 0; width: 100%; text-align: inherit; color: inherit; cursor: pointer; ">
            <div class="card-body" >
                <p id="conta-nome" style="<?php if($empresa_obj->id == $empresa_atual_obj->id) { ?>color: #f1f3f6e8;<?php } else { ?>color: #3a3a3a;<?php } ?>"><?= strtoupper($empresa_obj->nom_fant) ?></p>
                <p <?php if($empresa_obj->id == $empresa_atual_obj->id) { ?>style="color: #f1f3f6e8;"<?php }?> ><?= $usuario_empresa->nome ?></p>
                <p <?php if($empresa_obj->id == $empresa_atual_obj->id) { ?>style="color: #f1f3f6e8;"<?php }?> ><?= $usuario_empresa->email ?></p>
            </div>
        </button>
    </form>
</div>
        <?php } ?>
    </div>
<?php } ?>

<script>
    function toggleHeaderMenu() {
        const menu = document.getElementById('headerMenu');
        if (menu) {
            menu.classList.toggle('aberto');
        }
    }

    function encolher(acao) {
        const barra = document.getElementById('barra-lateral');
        const container = document.getElementById('container');
        const superior = document.getElementById('header');

        if (barra.style.animationName != 'encolher-lateral'){
            if(document.querySelector('body').clientWidth >= 800) {                
                localStorage.setItem('tela', 'cheia');
                container.style.animationName = 'encolher-container';
                container.style.animationDuration = '0.5s';
                container.style.animationFillMode = 'forwards';
            }
            superior.style.animationName = 'encolher-header';
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'forwards';
            barra.style.animationName = 'encolher-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'forwards';
        } else if(barra.style.animationName == 'encolher-lateral') {
            if(document.querySelector('body').clientWidth >= 800) {
                localStorage.setItem('tela', 'normal');
                container.style.animationName = 'expandir-container';
                container.style.animationDuration = '0.5s';
                container.style.animationFillMode = 'backwards';
            }
            superior.style.animationName = 'expandir-header';
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'backwards';
            barra.style.animationName = 'expandir-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'backwards';
            return;
        } 
    }

    if(document.querySelector('body').clientWidth < 800) {
        if(localStorage.getItem('tela') == 'cheia') {
            setTimeout(() => { encolher('encolher'); }, 100);
        }
    }
</script>