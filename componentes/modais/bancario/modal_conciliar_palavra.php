<div class="modal fade" id="modal_conciliar_palavra" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Conciliar Lançamento por Palavra Chave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="movimentacao_manager.php">

                    <div class="d-flex fd-row gap-3" >
                        <div class="modal-input-group" style="width:100%">
                            <label for="titulo">Palavra</label>
                            <div class="titulo-group">
                                <div class="input-titulo w-100 mb-3" > 
                                <select name="palavra_chave" class="form-control form-select-titulo" id="palavra"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0; ">
                                    <option value="">Selecione</option>

                                    <?php $palavras = Pal01::read(null, $_SESSION['usuario']->id_empresa);
                                    foreach ($palavras as $palavra) { ?>
                                        <option value="<?= $palavra->id ?>">
                                            <?= htmlspecialchars($palavra->palavra, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>                             
                    <div class="d-flex flex-direction-row" style="justify-content:space-between;">
                        <div class="d-flex justify-content-start gap-2">
                            <button type="submit" name="acao" value="conciliar_todas" class="btn btn-success" >Conciliar Todas Palavras</button>      
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" name="acao" value="conciliar_palavra" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                            
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>