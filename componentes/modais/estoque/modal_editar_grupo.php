<div class="modal fade" id="modal_editar_grupo" tabindex="-1" aria-labelledby="modalEditarGrupoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarGrupoLabel">Editar Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditarGrupo" method="post" action="cadastro_manager.php">
                <div class="modal-body">
                    <input type="hidden" name="target" value="grupo">
                    <input type="hidden" name="acao" value="atualizar">
                    <input type="hidden" id="editarGrupoId" name="id">
                    <label for="editarNomeGrupo" class="form-label">Nome do grupo</label>
                    <input type="text" id="editarNomeGrupo" name="nome" class="form-control" maxlength="255" required>
                </div>
            </form>
            <div class="modal-footer justify-content-between">
                <form method="post" action="cadastro_manager.php" onsubmit="return confirm('Deseja excluir este grupo?');">
                    <input type="hidden" name="target" value="grupo">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" id="excluirGrupoId" name="id">
                    <button type="submit" class="btn btn-outline-danger">Excluir</button>
                </form>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" form="formEditarGrupo" class="btn btn-primary">Salvar alterações</button>
                </div>
            </div>
        </div>
    </div>
</div>
