<?php $dre_target = $dre_target ?? null?>
<div class="card-header">
    <button class="btn btn-primary dre-menu-btn" <?php if($dre_target == 'sintetico') echo 'style="border-bottom: 2px solid #5856d6;"'; ?> onclick="window.location.href='sintetico.php'" id="btn-sintetico">
        <h3>DRE - Sintético</h3>
    </button>

    <button class="btn btn-primary dre-menu-btn" <?php if($dre_target == 'analitico') echo 'style="border-bottom: 2px solid #5856d6;"'; ?> onclick="window.location.href='analitico.php'" id="btn-analitico">
        <h3>DRE - Analitico</h3>
    </button>

    <button class="btn btn-primary dre-menu-btn" <?php if($dre_target == 'pagamento') echo 'style="border-bottom: 2px solid #5856d6;"'; ?> onclick="window.location.href='pagamento.php'" id="btn-grafico">
        <h3>Tipo de Pagamento</h3>
    </button>

    <button class="btn btn-primary dre-menu-btn" <?php if($dre_target == 'curva_abc') echo 'style="border-bottom: 2px solid #5856d6;"'; ?> onclick="window.location.href='curva_abc.php'"  id="btn-curva-abc">
        <h3>Curva ABC</h3>
    </button>

    <button class="btn btn-primary dre-menu-btn" <?php if($dre_target == 'anual') echo 'style="border-bottom: 2px solid #5856d6;"'; ?> onclick="window.location.href='anual.php'"  id="btn-anual">
        <h3>DRE - Anual</h3>
    </button>
</div>