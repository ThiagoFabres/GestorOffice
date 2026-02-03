<?php 
if($acao == 'editar' && $target == 'bandeira') {
    $id = filter_input(INPUT_GET, 'id_band01');
    $band01 = Band01::read($id, $_SESSION['usuario']->id_empresa)[0];
}
?>
<div class="modal fade" id="modal_adicionar_bandeira" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Nova Bandeira</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">

                    <form method="post" action="cartao_manager.php" onkeydown="return event.key != 'Enter';">
                    <input type="hidden" name="target" value="bandeira">
                    <input type="hidden" id="id_operadora" name="operadora_id" value="">
                    <?php if($acao == 'editar' && $target == 'bandeira') { ?>
                    <input type="hidden" id="id_bandeira" name="id" value="<?php if($acao == 'editar') {echo $band01->id;} ?>">
                    <?php } ?>
                    <div class="mb-3 d-flex flex-row">
                        <div class="w-50">
                        <label for="nomeBandeira" class="form-label">Informe o nome da Bandeira</label>
                        <input style="border-radius: 0;" type="text" id="nomeBandeira" name="nome" class="form-control" placeholder="Nome" value="<?=$band01->descricao ?? ''?>" required>
                        </div>
                        <div class="w-50">
                        <label for="TipoBandeira" class="form-label">Informe o tipo da Bandeira</label>
                        <input style="border-radius: 0;" type="text" id="TipoBandeira" name="tipo_bandeira" class="form-control" placeholder="Tipo" value="<?=$band01->tipo ?? ''?>" required>
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
                            <button type="submit" name="acao" value="<?php if($acao == 'editar') echo $acao; else echo 'adicionar'; ?>" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
    var modalAdicionarBandeira = document.getElementById('modal_adicionar_bandeira');
    modalAdicionarBandeira.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var operadoraId = button.getAttribute('data-operadora-id');
        var inputOperadoraId = modalAdicionarBandeira.querySelector('#id_operadora');
        inputOperadoraId.value = operadoraId;
    });
</script>
