
<?php
if($acao == 'visualizar'){
$get_id = filter_input(INPUT_GET, 'id');
$ban02 = Ban02::read($get_id, $_SESSION['usuario']->id_empresa);

if(!isset($get_id) || !$ban02) {
    header('Location: movimentacao.php');
    exit;
}

$ban02 = $ban02[0];
if($ban02->id_original != null) {
$ban02 = Ban02::read(id_empresa:$_SESSION['usuario']->id_empresa, id_original: $ban02->id_original)[0];
}
$data_formatada = (new DateTime($ban02->data))->format('d / m / Y');
$quantidade = filter_input(INPUT_GET, 'quantidade_parcelas') ?? null; 
if($ban02->id_original != null) {
$desmembramentos = Ban02::read(id_empresa:$_SESSION['usuario']->id_empresa, id_original:$ban02->id_original, read_desmembramento:true);
} else {
    $desmembramentos = null;
}

foreach($desmembramentos as $desmembramento) {
    if($desmembramento->id_original == $desmembramento->id) {
        $ban02 = $desmembramento;
    }
}
}
?>
<div class="modal fade" id="modal_visualizar" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                <!-- Cabeçalho -->
                <div class="modal-header" style="display: flex; justify-content: center;">
                    <?php if($desmembramentos != null) {?><div class="card-header-borda"> <?php } ?>
                    <div class="d-flex flex-column w-100">
                        <div class="d-flex flex-row gap-2 w-100">

                            <div class="d-flex flex-column" style="width: calc(100%/3);">
                                <label>Documento:</label>
                                <p class="form-control" readonly value=""><?=$ban02->documento?></p>
                            </div>
                            <div class="d-flex flex-column" style="width: calc(100%/3);">
                                <label>Data:</label>
                                <p class="form-control" readonly value=""><?=$data_formatada?></p>
                            </div>
                            <div class="d-flex flex-column" style="width: calc(100%/3);">
                                <label>Valor:</label>
                                <p class="form-control" readonly value=""><?=$ban02->valor?></p>
                            </div>
                        </div>
                        <div class="d-flex flex-row gap-2">
                            <div class="d-flex flex-column w-50">
                                <label>Descrição:</label>
                                <p type="text" class="form-control"<?php if($ban02->descricao == '') echo 'style=background-color:#ccc'?>><?=$ban02->descricao == '' ? 'Sem Descrição' : $ban02->descricao?></p>
                            </div>
                            <div class="d-flex flex-column w-50">
                                <label>Descrição Complementar:</label>
                                <input id="desc_comp_original_origem2" type="text" class="form-control" style="height:55%" value="<?=$ban02->descricao_comp?>"></input>
                            </div>
                        </div>
                    </div>
                    <?php if($desmembramentos != null) {?></div><?php } ?>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <?php if($_SESSION['usuario']->processar === 1) { ?>
                    <form method="get" action="movimentacao.php">
                    <?php } ?>
                    
                    
                    <input type="hidden" name="id" value="<?=$ban02->id?>"> 
                    <input type="hidden" name="acao" value="desmembrar">
                    
                <?php if($_SESSION['usuario']->processar === 1) { ?>
                </form>
                <?php } ?>
                <?php if($_SESSION['usuario']->processar === 1) { ?>
                <form method="post" action="movimentacao_manager.php">
                    <?php } ?>
                    <input type="hidden" name="id" value="<?=$get_id?>">
                    <input type="hidden" name="valor" value="<?=$ban02->valor?>">
                    <input type="hidden" name="caminho" value="<?=$caminho?>">
                    <input id="desc_comp_original_destino2" type="hidden" name="descricao_comp_original" value="<?=$ban02->descricao_comp?>">
                <?php if($desmembramentos != null) { ?>
                
                    
                    <?php 
                    $i = 0;
                    foreach($desmembramentos as $ban02) {?>
                    
                    <input type="hidden" name="desmembramento_id[<?=$i?>]" value="<?=$ban02->id?>"></input>
                        <labe>Desmembramento <?=$i+1?></label>
                        
                        <div class=" gap-2 d-flex flex-row">
                            <div class="w-25">
                                <label>Valor:</label>
                                <input class="form-control" type="text" readonly value="<?=$ban02->valor?>"></input>
                            </div>
                            <div class="w-75">
                                <label>Descrição Complementar:</label>
                                <input type="text" class="form-control" name="descricao_comp[<?=$i?>]" value="<?=$ban02->descricao_comp?>"></input>
                            </div>
                        
                        </div>
                        
                    <?php
                    $i++;
                    } } ?>
                
                    
                    <!-- Botões -->
                    <?php if($_SESSION['usuario']->processar === 1) { ?>
                    <div class="d-flex flex-row" style="justify-content: space-between;">
                        <?php if($desmembramentos != null) {?>
                        <div>
                            <button type="submit" name="acao" value="cancelar_desmembramento" class="btn btn-danger">Cancelar Desmembramento</button>
                        </div>
                        <?php } ?>

                        
                        <div style="<?php if($desmembramentos == null) echo 'display:flex; justify-content: end; width:100%;'?>">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" name="acao" value="editar_desmembramento" class="btn btn-primary">Salvar</button>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($_SESSION['usuario']->processar === 1) { ?>
                    </form>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>

       <script>
        let origemInput2 = document.getElementById('desc_comp_original_origem2')
        let destinoInput2 = document.getElementById('desc_comp_original_destino2')
        if (origemInput2 && destinoInput2) {
            // Para selects, datas e textos
            origemInput2.addEventListener('input', function () {
                destinoInput2.value = origemInput2.value;
            });
        }
    </script>
