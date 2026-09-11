<div class="modal fade" id="modal_controle" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class=" modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?= $controleEdicao !== null ? 'Editar Controle' : 'Novo Controle' ?></h5>
            </div>
            <div class="modal-body">
                <form method="post" id="content" action="controle_manager.php">
                    <input type="hidden" name="id" value="<?= $controleEdicao !== null ? (int) $controleEdicao->id : '' ?>">
                    <div class="d-flex flex-row w-100">
                        <div class="d-flex flex-column w-50">
                            <label>Hora</label>
                            <div class="input-nome input-form-adm">
                                <input type="time" name="hora" class="form-control" value="<?= $controleEdicao !== null ? htmlspecialchars($controleEdicao->hora, ENT_QUOTES, 'UTF-8') : '' ?>" required>
                            </div>
                        </div>
                        <div class="d-flex flex-column w-50">
                            <label>Tolerância (Minutos)</label>
                            <div class="input-nome input-form-adm">
                                <input type="number" name="tolerancia" class="form-control" min="0" step="1" value="<?= $controleEdicao !== null ? (int) $controleEdicao->tolerancia : '' ?>" required>
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom: 3em;" class="footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                        <button name="acao" value="<?= $controleEdicao !== null ? 'editar' : 'adicionar' ?>" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;">Salvar</button>
                        
                    </div>
                </form>
            </div>
         </div>
    </div>
</div>