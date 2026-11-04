<div class="modal fade" id="modalExcluirMovimentacao" tabindex="-1"
    aria-labelledby="modalExcluirMovimentacaoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="movimentacao_manager.php">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" id="idMovimentacaoExcluir">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="modalExcluirMovimentacaoTitulo">Excluir movimentação</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Confirma a exclusão do movimento <strong id="documentoMovimentacaoExcluir"></strong>?
                    O estoque será recalculado com base nos itens registrados.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir movimentação</button>
                </div>
            </form>
        </div>
    </div>
</div>
