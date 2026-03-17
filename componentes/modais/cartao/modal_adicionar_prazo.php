<?php 
if($acao == 'editar' && $target == 'prazo') {
    $id = filter_input(INPUT_GET, 'id_pra01');
    $pra01 = Pra01::read($id, id_empresa:$_SESSION['usuario']->id_empresa)[0];
}
?>
<div class="modal fade" id="modal_adicionar_prazo" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Nova Tarifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="cartao_manager.php" onkeydown="return event.key != 'Enter';">
                    <input type="hidden" name="target" value="prazo">
                    <input type="hidden" id="id_operadora" name="operadora_id" value="">
                    <input type="hidden" id="id_bandeira" name="bandeira_id" value="">
                    <?php if($acao == 'editar' && $target == 'prazo') { ?>
                    <input type="hidden" id="id_prazo" name="id" value="<?=$pra01->id?>">
                    <?php } ?>
                    <div class="mb-3 d-flex flex-row">
                        <div>
                            <label for="nomeOperadora" class="form-label">Parcela</label>
                            <input style="border-radius:0;" type="text" id="parcelaOperadora" name="parcela" class="form-control" placeholder="Parcela" value="<?=$pra01->parcela ?? ''?>" required>
                        </div>
                        <div>
                            <label for="nomeOperadora" class="form-label">Prazo</label>
                            <input style="border-radius:0;" type="text" id="prazoOperadora" name="prazo" class="form-control" placeholder="Prazo" value="<?=$pra01->prazo ?? '' ?>" required>
                        </div>
                        <div>
                            <label for="nomeOperadora" class="form-label">Taxa</label>
                            <input style="border-radius:0;" onkeypress="return /[0-9,]/.test(event.key)"  type="text" id="taxaOperadora" name="taxa" class="form-control" placeholder="Taxa" value="<?= isset($pra01) ? number_format($pra01->taxa, 2, ',', '') : '' ?>" required>
                        </div>
                    </div>
                    
                    <!-- Botões -->
                    <div class="d-flex <?php if($acao == 'editar') echo 'justify-content-between'; else echo 'justify-content-end'; ?> gap-2">
                        <?php if($acao == 'editar') { ?>
                        <div>
                            <button type="submit" name="acao" value="excluir" class="btn btn-danger">Excluir</button>
                        </div>
                        <?php } ?>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" name="acao" value="<?php if($acao == 'editar') echo 'editar'; else echo 'adicionar'; ?>" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    var modalAdicionarPrazo = document.getElementById('modal_adicionar_prazo');
    modalAdicionarPrazo.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var operadoraId = button.getAttribute('data-operadora-id');
        var bandeiraId = button.getAttribute('data-bandeira-id');
        var inputOperadoraId = modalAdicionarPrazo.querySelector('#id_operadora');
        var inputBandeiraId = modalAdicionarPrazo.querySelector('#id_bandeira');
        inputOperadoraId.value = operadoraId;
        inputBandeiraId.value = bandeiraId;
    });
    </script>