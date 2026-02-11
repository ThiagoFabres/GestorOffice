<link rel="stylesheet" href="/componentes/footer/footer.css">
<div id="footer">
    
    <button type="button" data-bs-toggle="modal" data-bs-target="#modal_fale_conosco" id="footer-btn" > <i class="bi bi-telephone"></i> Fale Conosco</button>
    <?php if(isset($lateral_cartao) && $lateral_cartao == true) {?>
    <button type="button" data-bs-toggle="modal" data-bs-target="#modal_footer_operadora" id="footer-btn" style="margin-right:20px;"> 
        <i class="bi bi-info-circle"></i>
        Operadoras Disponíveis
    </button>
    <?php } ?>
</div>
<?php require_once __DIR__ . '/footer_modal_suporte.php' ?>
<?php require_once __DIR__ . '/footer_modal_operadoras.php' ?>
