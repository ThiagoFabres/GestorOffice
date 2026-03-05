
<div class="modal fade" id="modal_adicionar_lancamento" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Importar Lançamentos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <?php if (!empty($_SESSION['excel_transactions']['transactions'])): ?>
                        <form action="financeiro_manager.php" method="post">
                    <input type="hidden" name="acao" value="adicionar"></input>
                    <input type="hidden" name="tipo" value="<?=$_SESSION['excel_transactions']['tipo_lancamento']?>"></input>
                    <input type="hidden" name="cadastro" value="<?=$_SESSION['excel_transactions']['cadastro']?>"></input>
                    <input type="hidden" name="custos" value="<?=$_SESSION['excel_transactions']['custos']?>"></input>
                    <input type="hidden" name="titulo" value="<?=$_SESSION['excel_transactions']['titulo']?>"></input>
                    <input type="hidden" name="subtitulo" value="<?=$_SESSION['excel_transactions']['subtitulo']?>"></input>

                    <div class="mb-3 mt-3" style="max-height:30rem; overflow: auto;">
                        <h6>Pré-visualização do arquivo Excel:</h6>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead style="position:sticky;">
                                    <tr style="position:sticky;">
                                        <th>Importar</th>
                                        <th>Data</th>
                                        <th>Valor</th>
                                        <th>Vencimento</th>
                                        <th>Descricao</th>
                                        <th>Data De pagamento</th>
                                        <th>Valor Pago</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $id_linha = 0;
                                    foreach ($_SESSION['excel_transactions']['transactions'] as $linha){ 
                                        $valor = str_replace('.', '', $linha['valor']);
                                        $valor = str_replace(',', '.', $valor);
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" name="importar[<?=$id_linha?>]" checked></td>
                                            <td><input class="form-control" onkeypress="return false;" name="data[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['data'] ?? '') ?>"></input></td>
                                            <td><input class="form-control" onkeypress="return false;" name="valor[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['valor'] ?? '') ?>"></input></td>
                                            <td><input class="form-control" onkeypress="return false;" name="vencimento[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['vencimento'] ?? '')?>"></input></td>
                                            <td><input class="form-control" onkeypress="return false;" name="descricao[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['documento'] . ' - ' . $linha['descricao'] ?? '') ?>"></input></td>
                                            <td><input class="form-control" onkeypress="return false;" name="data_pag[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['data_pag'] ?? '')?>"></input></td>
                                            <td><input class="form-control" onkeypress="return false;" name="valor_pag[<?=$id_linha?>]" value="<?= htmlspecialchars($linha['valor_pag'] == 0 ? '' : number_format($linha['valor_pag'], 2, ',', '.'))?>"></input></td>
                                        
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