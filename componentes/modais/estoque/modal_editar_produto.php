<div class="modal fade" id="modal_editar_produto" tabindex="-1" aria-labelledby="modalEditarProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarProdutoLabel">Editar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditarProduto" method="post" action="cadastro_manager.php">
                <div class="modal-body">
                    <input type="hidden" name="target" value="produto">
                    <input type="hidden" name="acao" value="atualizar">
                    <input type="hidden" id="editarProdutoId" name="id">
                    <div class="mb-3">
                        <label for="editarNomeProduto" class="form-label">Nome do produto</label>
                        <input type="text" id="editarNomeProduto" name="nome" class="form-control" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editarUnidadeProduto" class="form-label">Unidade</label>
                            <input type="text" id="editarUnidadeProduto" name="unidade" class="form-control" maxlength="20" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editarCodigoBarrasProduto" class="form-label">Código de barras</label>
                            <input type="text" id="editarCodigoBarrasProduto" name="cod_barra" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editarCustoProduto" class="form-label">Preço de custo</label>
                            <input type="number" id="editarCustoProduto" name="preco_custo" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editarMarkupProduto" class="form-label">Markup (%)</label>
                            <input type="number" id="editarMarkupProduto" name="markup" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editarVendaProduto" class="form-label">Preço de venda</label>
                            <input type="number" id="editarVendaProduto" name="preco_venda" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editarEstoqueProduto" class="form-label">Estoque</label>
                            <input type="number" id="editarEstoqueProduto" name="estoque" class="form-control" min="0" step="0.001" required>
                        </div>
                    </div>
                </div>
            </form>
            <div class="modal-footer justify-content-between">
                <form method="post" action="estoque_manager.php" onsubmit="return confirm('Deseja excluir este produto e seus componentes?');">
                    <input type="hidden" name="target" value="produto">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" id="excluirProdutoId" name="id">
                    <button type="submit" class="btn btn-outline-danger">Excluir</button>
                </form>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" form="formEditarProduto" class="btn btn-primary">Salvar alterações</button>
                </div>
            </div>
        </div>
    </div>
</div>
