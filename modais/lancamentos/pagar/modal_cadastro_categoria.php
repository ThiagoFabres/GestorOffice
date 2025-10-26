<div class="modal fade" id="modal_cadastro_categoria" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCategoriaLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="cadastros_manager.php">
                    <input type="hidden" name="view" value="cadastro">
                    <input type="hidden" name="target" value="categoria">
                    <input type="hidden" name="insta" value="pagar">
                    <input type="hidden" name="id" value="">
                    
                    <div class="mb-3">
                        <label for="nomeCidade" class="form-label">Informe o nome da categoria</label>
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
    </div>
    