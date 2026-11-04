<div class="modal fade" id="modal_adicionar_componente" tabindex="-1" aria-labelledby="modalAdicionarComponenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdicionarComponenteLabel">Novo Componente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="cadastro_manager.php">
                <div class="modal-body">
                    <input type="hidden" name="target" value="componente">
                    <input type="hidden" id="componenteProdutoId" name="produto_id" value="">
                    <div class="mb-3">
                        <label for="nomeComponente" class="form-label">Nome do componente</label>
                        <input type="text" id="nomeComponente" name="item" class="form-control" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantidadeComponente" class="form-label">Quantidade</label>
                            <input type="number" id="quantidadeComponente" name="quantidade" class="form-control" min="0.001" step="0.001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="precoComponente" class="form-label">Preço por unidade</label>
                            <input type="number" id="precoComponente" name="preco_unidade" class="form-control" min="0" step="0.01" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" name="acao" value="adicionar" class="btn btn-primary">Salvar componente</button>
                </div>
            </form>
        </div>
    </div>
</div>
