<div class="modal fade" id="modal_cadastro_bairro" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Nova Bairro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="cadastros_manager.php">
                    <input type="hidden" name="view" value="cadastro">
                    <input type="hidden" name="target" value="bairro">
                    <input type="hidden" name="insta" value="pagar">
                    <input type="hidden" name="id" value="">
                    
                    <div class="mb-3">
                        <label for="nomeBairro" class="form-label">Informe o nome do bairro</label>
                        <input type="text" id="nomeBairro" name="nome" class="form-control" placeholder="Nome" required>
                    </div>
                    
                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>