<div class="modal fade" id="modal_adicionar_produto" tabindex="-1" aria-labelledby="modalAdicionarProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdicionarProdutoLabel">Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="cadastro_manager.php">
                <div class="modal-body">
                    <input type="hidden" name="target" value="produto">
                    <input type="hidden" id="produtoSubgrupoId" name="subgrupo_id" value="">
                    <div class="mb-3">
                        <label for="nomeProduto" class="form-label">Nome do produto</label>
                        <input type="text" id="nomeProduto" name="nome" class="form-control" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unidadeProduto" class="form-label">Unidade</label>
                            <input type="text" id="unidadeProduto" name="unidade" class="form-control" maxlength="20" placeholder="UN" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="codigoBarrasProduto" class="form-label">Código de barras</label>
                            <input type="text" id="codigoBarrasProduto" name="cod_barra" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="custoProduto" class="form-label">Preço de custo</label>
                            <input type="number" id="custoProduto" name="preco_custo" class="form-control" min="0" step="0.01" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="markupProduto" class="form-label">Markup (%)</label>
                            <input type="number" id="markupProduto" name="markup" class="form-control" min="0" step="0.01" value="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vendaProduto" class="form-label">Preço de venda</label>
                            <input type="number" id="vendaProduto" name="preco_venda" class="form-control" min="0" step="0.01" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estoqueProduto" class="form-label">Estoque inicial</label>
                            <input type="number" id="estoqueProduto" name="estoque" class="form-control" min="0" step="0.001" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" name="acao" value="adicionar" class="btn btn-primary">Salvar produto</button>
                </div>
            </form>
        </div>
    </div>
</div>
