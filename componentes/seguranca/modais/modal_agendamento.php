<div class="modal fade" id="modal_agendamento" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered" style="max-width:40%; max-height: 90%;">
                
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
                                    <div class="d-flex flex-row justify-content-between gap-3">

                                        <div class="d-flex flex-column w-50">
                                            <label for="cliente">Usuario:</label>
                                            <select class="form-control" name="id_usuario_prev">
                                                <option>Selecione</option>
                                                <?php foreach($usuarios_lista as $usuario) { ?>
                                                    <option value="<?= $usuario->id ?>" style="color:black;"><?= $usuario->nome ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="d-flex flex-column w-50">
                                                <label for="obs">Observação:</label>
                                                <input type="text" class="form-control" name="obs" placeholder="Observação" required style="border-radius:0; height:2.85em">                                    
                                        </div>


                                    </div>
                                    <hr>
                                    <div class="d-flex flex-row justify-content-between gap-3">
                                        <div class="d-flex flex-column w-50">
                                            <label for="contato">Data Inicial:</label>
                                            <input type="date" class="form-control" name="data_ini_prev" placeholder="Data" required style="border-radius:0;">                                    
                                        </div>
                                        <div class="d-flex flex-column w-50">
                                            <label for="telefone">Hora Inicial:</label>
                                            <input type="time" class="form-control" name="hora_ini_prev" required style="border-radius:0;">                                    
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex flex-row justify-content-between gap-3">
                                        <div class="d-flex flex-column w-50">
                                            <label for="contato">Data Final:</label>
                                            <input type="date" class="form-control" name="data_fim_prev" placeholder="Data" required style="border-radius:0;">                                    
                                        </div>
                                        <div class="d-flex flex-column w-50">
                                            <label for="telefone">Hora Final:</label>
                                            <input type="time" class="form-control" name="hora_fim_prev" required style="border-radius:0;">                                    
                                        </div>
                                    </div>
                                
                                </div>
                                
                            </div>
                        </div>
                        <div style="float: right; margin-top: 10px;">
                            <input type="hidden" name="target" value="agendamento">
                            <input type="hidden" name="acao" value="adicionar">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>