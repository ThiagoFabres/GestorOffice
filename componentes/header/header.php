<?php
require_once __DIR__ . '/../../db/entities/empresas.php';
$empresa_atual_obj = Empresa::read($_SESSION['usuario']->id_empresa)[0];
$nome_empresa = $empresa_atual_obj->nom_fant;

if ($_SESSION['usuario']->status == 0 && $_SESSION['usuario']->cargo == 3) {
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
    #headerMenuWrapper {
        position: relative;
        width: 100%;
        z-index: 1050;
    }

    /* Estado Fechado: Invisível e Sem Espaço */
    .menu-animado {
        position: absolute !important;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        
        display: flex !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        max-height: 0 !important;
        overflow: hidden !important;

        background-color: #212631;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        transition: max-height 0.35s ease-in-out, opacity 0.3s ease-in-out, visibility 0.35s;
    }

    /* Estado Aberto: Visível e com Altura Limite */
    .menu-animado.aberto {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        max-height: 500px !important; /* Valor alto suficiente para revelar todos os cards */
        overflow-x: auto !important;
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }
</style>

<div id="header">
    <div id="titulo-header">
        <button type="button" class="btn mb-0" onclick="encolher()">
            <span class="btn bi bi-list mb-0"></span>
        </button>
    </div>

    <div id="nome-empresa" class="w-100">
        <?php if (isset($todas_empresas) && $todas_empresas): ?>
            <h1>TODAS AS EMPRESAS</h1>
        <?php elseif (count($empresas_lista) > 1): ?>
            <div class="w-75 d-flex justify-content-center mx-auto">
                <button type="button" 
                        class="btn btn-link text-decoration-none text-dark" 
                        onclick="toggleHeaderMenu()">
                    <?= htmlspecialchars($nome_empresa) ?> <i class="bi bi-chevron-down small ms-1"></i>
                </button>
            </div>
        <?php else: ?>
            <h1><?= htmlspecialchars($nome_empresa) ?></h1>
        <?php endif; ?>
    </div>

    <div id="conta-header">
        <a href="/" class="dropdown-item">
            <i class="bi bi-box-arrow-left"></i> Sair
        </a>
    </div>
</div>

<?php if (count($empresas_lista) > 1): ?>
<div id="headerMenuWrapper">
    <div class="menu-animado dragscroll flex-row gap-3 justify-content-evenly card-body card-contas-lista" id="headerMenu">
        <?php foreach ($empresas_lista as $empresa_obj): 
            $usuario_principal = Usuario::read(idempresa: $empresa_obj->id, principal: true)[0] ?? null;
            $usuario_empresa = $usuario_principal ?? Usuario::read(idempresa: $empresa_obj->id, cargo: 3)[0] ?? null;
            
            if ($usuario_empresa === null) continue;
            
            $is_atual = ($empresa_obj->id == $empresa_atual_obj->id);
        ?>
            <div class="card card-contas" style="min-width: 200px; width:24.25%; <?php if($is_atual) { ?>background-color: #465168;<?php } ?>">
                <a href="/login.php?acao=autenticacao_insta&target=<?= $empresa_obj->id ?>&url_atual=<?= urlencode($actual_url) ?>" 
                   class="text-decoration-none color-inherit d-block style-link-card">
                    <div class="card-body">
                        <p id="conta-nome" style="<?php if($is_atual) { ?>color: #f1f3f6e8;<?php } else { ?>color: #3a3a3a;<?php } ?>">
                            <?= strtoupper(htmlspecialchars($empresa_obj->nom_fant)) ?>
                        </p>
                        <p <?php if($is_atual) { ?>style="color: #f1f3f6e8;"<?php }?>>
                            <?= htmlspecialchars($usuario_empresa->nome) ?>
                        </p>
                        <p <?php if($is_atual) { ?>style="color: #f1f3f6e8;"<?php }?>>
                            <?= htmlspecialchars($usuario_empresa->email) ?>
                        </p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

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

        if (barra && barra.style.animationName != 'encolher-lateral') {
            if (document.querySelector('body').clientWidth >= 800) {                
                localStorage.setItem('tela', 'cheia');
                if (container) {
                    container.style.animationName = 'encolher-container';
                    container.style.animationDuration = '0.5s';
                    container.style.animationFillMode = 'forwards';
                }
            }
            if (superior) {
                superior.style.animationName = 'encolher-header';
                superior.style.animationDuration = '0.5s';
                superior.style.animationFillMode = 'forwards';
            }
            barra.style.animationName = 'encolher-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'forwards';
        } else if (barra && barra.style.animationName == 'encolher-lateral') {
            if (document.querySelector('body').clientWidth >= 800) {
                localStorage.setItem('tela', 'normal');
                if (container) {
                    container.style.animationName = 'expandir-container';
                    container.style.animationDuration = '0.5s';
                    container.style.animationFillMode = 'backwards';
                }
            }
            if (superior) {
                superior.style.animationName = 'expandir-header';
                superior.style.animationDuration = '0.5s';
                superior.style.animationFillMode = 'backwards';
            }
            barra.style.animationName = 'expandir-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'backwards';
        } 
    }

    if (document.querySelector('body').clientWidth < 800) {
        if (localStorage.getItem('tela') == 'cheia') {
            setTimeout(() => { encolher('encolher'); }, 100);
        }
    }
</script>