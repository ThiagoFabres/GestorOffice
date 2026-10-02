

<?php 
$acao = $acao ?? null;
$caminho = $caminho ?? null;
$id = $id ?? null;
if($acao == 'vincular') {
  $id = filter_input(INPUT_GET, 'id');
}
?>
<div class="modal fade" id="modal_vincular" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Vincular Lançamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <e id="mensagem-erro"></e>
                    <form method="post" action="movimentacao_manager.php" id="form-conciliar">
                    <input type="hidden" name="caminho" value="<?=$caminho?>">
                    <input id="vincular-id" type="hidden" name="id" value="<?=$id?>">

                    <div class="d-flex fd-row gap-3" >
                    <div class="modal-input-group w-100">
                        <label for="cadastro">Cadastro</label>
                        <div class="input-cadastro" style="width:100%;">
                            <select name="cadastro" class="form-control form-select-cadastro" id="cadastro" style="width:100%;">
                                <option value="">Selecione</option>
                                <?php $cadastros = Cadastro::read(id_empresa: $_SESSION['usuario']->id_empresa);
                                    foreach ($cadastros as $cadastro) { ?>
                                        <option value="<?= $cadastro->id_cadastro ?>">
                                            <?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                <?php } ?>
                            </select>
                        </div>                   
                    </div>   
                </div>
                <div class="d-flex flex-row justify-content-between">
                    <div class="d-flex justify-content-end gap-2 w-100" style="margin-top: 1em">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="acao" value="vincular" class="btn btn-primary" id="conciliar-btn">Vincular</button>
                    </div>
                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


