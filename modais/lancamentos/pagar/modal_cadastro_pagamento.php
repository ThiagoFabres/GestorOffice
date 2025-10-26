<div class="modal fade" id="modal_cadastro_pagamento" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCategoriaLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Novo tipo de pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="cadastros_manager.php">
                    <input type="hidden" name="view" value="cadastro">
                    <input type="hidden" name="target" value="pagamento">
                    <!-- these have IDs so JS can set them when opened from modal_quitar -->
                    <input type="hidden" id="modal_cadastro_pagamento_insta" name="insta" value="pagar">
                    <input type="hidden" id="modal_cadastro_pagamento_id" name="id" value="">
                    
                    <div class="mb-3">
                        <label for="nomeCidade" class="form-label">Informe o nome do tipo de pagamento</label>
                        <input type="text" id="nomeCidade" name="nome" class="form-control" placeholder="Nome" required>
                    </div>
                    
                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                    </form>
                </div>
                
                </div>
            </div>
    </div>

<script>
    // Copy modal_quitar id into this form when opened from the quitar modal
    document.addEventListener('DOMContentLoaded', function () {
        var pagamentoModal = document.getElementById('modal_cadastro_pagamento');
        if (!pagamentoModal) return;

        pagamentoModal.addEventListener('show.bs.modal', function (event) {
            try {
                var quitarInput = document.getElementById('modal_quitar_id');
                var destIdInput = document.getElementById('modal_cadastro_pagamento_id');
                var instaInput = document.getElementById('modal_cadastro_pagamento_insta');
                if (quitarInput && destIdInput) {
                    // copy the quitar id into the payment form's id field
                    destIdInput.value = quitarInput.value || '';
                    // when opened from quitar, set insta so server redirects back to receber/pagar correctly
                    if (instaInput) instaInput.value = 'pagar';
                } else {
                    // ensure fields are cleared when not opened from quitar
                    if (destIdInput) destIdInput.value = '';
                }
            } catch (e) {
                console.error('Erro ao sincronizar modal_quitar id:', e);
            }
        });
    });
</script>