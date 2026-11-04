<div class="modal fade" id="modal_editar_componente" tabindex="-1" aria-labelledby="modalEditarComponenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarComponenteLabel">Editar Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditarComponente" method="post" action="cadastro_manager.php">
                <div class="modal-body">
                    <input type="hidden" name="target" value="componente">
                    <input type="hidden" name="acao" value="atualizar">
                    <input type="hidden" id="editarComponenteId" name="id">
                    <div class="mb-3">
                        <label for="editarNomeComponente" class="form-label">Nome do item</label>
                        <input type="text" id="editarNomeComponente" name="item" class="form-control" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editarQuantidadeComponente" class="form-label">Quantidade</label>
                            <input type="number" id="editarQuantidadeComponente" name="quantidade" class="form-control" min="0.001" step="0.001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editarPrecoComponente" class="form-label">Preço por unidade</label>
                            <input type="number" id="editarPrecoComponente" name="preco_unidade" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                </div>
            </form>
            <div class="modal-footer justify-content-between">
                <form method="post" action="estoque_manager.php" onsubmit="return confirm('Deseja excluir este item?');">
                    <input type="hidden" name="target" value="componente">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" id="excluirComponenteId" name="id">
                    <button type="submit" class="btn btn-outline-danger">Excluir</button>
                </form>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" form="formEditarComponente" class="btn btn-primary">Salvar alterações</button>
                </div>
            </div>
        </div>
    </div>
</div>
