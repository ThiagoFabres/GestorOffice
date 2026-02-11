
<div class="modal fade" id="modal_cadastro_bancario" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Adicionar Nova Conta Bancária</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <?php 


                    ?>
                    <?php if(empty($_SESSION['ofx_transactions'])) {?>
                    <form method="post" enctype="multipart/form-data" action="movimentacao_manager.php">
                        <input type="hidden" name="acao" value="processar"></input>
                        <div class="mb-3 gap-2">
                            <label for="nomeBanco" class="form-label">Conta Bancária:</label>
                            <select class="form-select" name="conta" required>
                                <option value="">Selecione</option>
                                <?php foreach(Ban01::read(null, $_SESSION['usuario']->id_empresa) as $ban01) { ?>
                                    <option
                                    <?php if(!empty($_SESSION['ofx_transactions'])) {?>
                                        <?php if($ban01->id == $_SESSION['ofx_transactions']['ofx_conta']) { ?> selected <?php } ?>
                                    <?php } ?> 
                                    value="<?=$ban01->id?>"><?=$ban01->nome?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3 gap-2">
                            <label for="agencia" class="form-label">Arquivo OFX / Excel</label>
                            <input type="file"
                            onchange="this.form.submit()"
                            accept=".ofx, .xlsx" id="agencia" name="ofx"
                            class="form-control" placeholder="Agência"
                            >
                        </div>
                        <button type="submit" class="btn-sm btn btn-primary">Gerar</button>
                    </form>
                    <?php } ?>

                    <?php if (!empty($_SESSION['ofx_transactions']['transactions'])): ?>
                        <form action="movimentacao_manager.php" method="post">
                    <input type="hidden" name="acao" value="adicionar"></input>
                    <input type="hidden" name="conta" value="<?=$_SESSION['ofx_transactions']['ofx_conta']?>"></input>


                    <div class="mb-3 mt-3" style="max-height:30rem; overflow: auto;">
                        <h6>Pré-visualização do arquivo OFX:</h6>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead style="position:sticky;">
                                    <tr style="position:sticky;">
                                        <th style="width: 9%;">Tipo</th>
                                        <th style="width: 11%;">Data do Depósito</th>
                                        <th style="width: 14%;">Valor</th>
                                        <th style="width: 9%;">Documento</th>
                                        <th>Descrição</th>
                                        <th>Descrição complementar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $id_linha = 0;
                                    foreach ($_SESSION['ofx_transactions']['transactions'] as $linha){ 
                                        
                                        ?>
                                        <tr>
                                            <td><input class="form-control" readonly name="tipo[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['tipo'] ?? $linha['valor'] > 0 ? 'Crédito' : 'Débito') ?>"></input></td>
                                            <td><input class="form-control" readonly name="data[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['data'] ?? '') ?>"></input></td>
                                            <td><input class="form-control" readonly name="valor[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['valor'] ?? '') ?>"></input></td>
                                            <td><input class="form-control" readonly name="documento[<?=$id_linha?>]" value="<?= htmlspecialchars($novo_documento ?? '')?>"></input></td>
                                            <td><input class="form-control" readonly name="descricao[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['descricao'] ?? '') ?>"></input></td>
                                            <td><input class="form-control" name="descricao_comp[<?=$id_linha?>]" value=""></input></td>
                                        </tr>
                                    
                                    <?php
                                     $id_linha++;
                                     $novo_documento++;
                                 } 
                                 ?>
                                 <input type="hidden" name="total_linhas" value="<?=$id_linha?>"></input>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    

                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                    </div>
                    <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>