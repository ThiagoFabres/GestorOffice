<div class="modal fade" id="modal_adicionar_subgrupo" tabindex="-1" aria-labelledby="modalCadastroSubGrupoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastroSubGrupoLabel">Novo Subgrupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="cadastro_manager.php">
                <div class="modal-body">
                    <input type="hidden" name="target" value="subgrupo">
                    <input type="hidden" id="grupoSubGrupoId" name="grupo_id" value="">
                    <label for="nomeSubGrupo" class="form-label">Nome do subgrupo</label>
                    <input type="text" id="nomeSubGrupo" name="nome" class="form-control" maxlength="255" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" name="acao" value="adicionar" class="btn btn-primary">Salvar subgrupo</button>
                </div>
            </form>
        </div>
    </div>
</div>
