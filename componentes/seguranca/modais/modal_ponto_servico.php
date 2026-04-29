<div class="modal fade" id="modal_ponto_servico" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered" style="max-width:90%; max-height: 90%;">
                
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Novo Ponto de Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="seguranca_manager.php">
                        <div class="d-flex flex-column">
                            <div class="p-2">
                                <div class="d-flex flex-column">
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-column w-25">
                                            <label for="cliente">Cliente:</label>
                                            <select class="form-control" name="cliente">
                                                <option>Selecione</option>
                                                <?php foreach($clientes as $cliente) {
                                                    echo '<option value="' . $cliente->id_cadastro . '">' . $cliente->nom_fant . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="contato">Contato:</label>
                                            <input type="text" class="form-control" name="contato" placeholder="Contato" required>                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="telefone">Telefone:</label>
                                            <input type="text" onkeypress="return /[0-9,]/.test(event.key)" placeholder="+00 (00) 00000-0000" class="form-control numero-telefone" name="telefone" value="+55" required>                                    
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-column w-25">
                                            <label for="endereco">Endereço:</label>
                                            <input type="text" class="form-control" name="endereco" placeholder="Endereço" required>                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="bairro">Bairro:</label>
                                            <input type="text" class="form-control" name="bairro" placeholder="Bairro" required>                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="cidade">Cidade:</label>
                                            <input type="text" class="form-control" name="cidade" placeholder="Cidade" required>                                    
                                        </div>
                                        
                                    </div>
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-column w-25">
                                            <label for="cliente">Estado:</label>
                                            <select class="form-control" name="estado">
                                                <option>Selecione</option>
                                                <?php foreach($estadosLista as $sigla => $estado) {
                                                    echo '<option value="' . $sigla . '">' . $estado . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="referencia">Referência:</label>
                                            <input type="text" class="form-control" name="referencia" placeholder="Referência" required>                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="descricao">Descrição:</label>
                                            <input type="text" class="form-control" name="descricao" placeholder="Descrição" required>                                    
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="p-2">
                                <h2>Contatos:</h2>
                                <div class="d-flex flex-column">
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-column w-25">
                                            <label for="nome1">Nome 1:</label>
                                            <input type="text" class="form-control" name="nome1" required placeholder="Nome">                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="nome2">Nome 2:</label>
                                            <input type="text" class="form-control" name="nome2" required placeholder="Nome">                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="nome3">Nome 3:</label>
                                            <input type="text" class="form-control" name="nome3" required placeholder="Nome">                                    
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-column w-25">
                                            <label for="celular1">Celular 1:</label>
                                            <input type="text" class="form-control numero-telefone" name="celular1" required placeholder="+00 (00) 00000-0000" value="55">                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="celular2">Celular 2:</label>
                                            <input type="text" class="form-control numero-telefone" name="celular2" required placeholder="+00 (00) 00000-0000" value="55">                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="celular3">Celular 3:</label>
                                            <input type="text" class="form-control numero-telefone" name="celular3" required placeholder="+00 (00) 00000-0000" value="55">                                    
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-column w-25">
                                            <label for="cargo1">Cargo 1:</label>
                                            <input type="text" class="form-control" name="cargo1" required placeholder="Cargo">                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="cargo2">Cargo 2:</label>
                                            <input type="text" class="form-control" name="cargo2" required placeholder="Cargo">                                    
                                        </div>
                                        <div class="d-flex flex-column w-25">
                                            <label for="cargo3">Cargo 3:</label>
                                            <input type="text" class="form-control" name="cargo3" required placeholder="Cargo">                                    
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="float: right; margin-top: 10px;">
                            <input type="hidden" name="target" value="ponto_servico">
                            <input type="hidden" name="acao" value="adicionar">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>