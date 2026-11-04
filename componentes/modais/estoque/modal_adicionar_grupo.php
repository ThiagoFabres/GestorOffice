<div class="modal fade" id="modal_adicionar_grupo" tabindex="-1" aria-labelledby="modalCadastroGrupoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastroGrupoLabel">Novo Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="cadastro_manager.php">
                <div class="modal-body">
                    <input type="hidden" name="target" value="grupo">
                    <label for="nomeGrupo" class="form-label">Nome do grupo</label>
                    <input type="text" id="nomeGrupo" name="nome" class="form-control" maxlength="255" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" name="acao" value="adicionar" class="btn btn-primary">Salvar grupo</button>
                </div>
            </form>
        </div>
    </div>
</div>
