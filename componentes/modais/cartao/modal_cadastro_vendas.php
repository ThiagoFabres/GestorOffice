<?php $erro = $erro ?? null ?>

<div class="modal fade" id="modal_cadastro_vendas" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Adicionar Novas Vendas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">

                <?php if(empty($_SESSION['vendas']) && empty($_SESSION['vendas_invalidas']) && $erro == null) {?>
                    <form method="post" enctype="multipart/form-data" action="venda_manager.php">
                        <input type="hidden" name="acao" value="processar"></input>
                        <div class="mb-3 gap-2">
                            <label for="nomeBanco" class="form-label">Operadora:</label>
                            <select class="form-select" name="operadora" required>
                                <option value="">Selecione</option>
                                <?php foreach(Ope01::read(null, $_SESSION['usuario']->id_empresa) as $ban01) { ?>
                                    <option
                                    <?php if(!empty($_SESSION['vendas'])) {?>
                                        <?php if($ban01->id == $_SESSION['vendas']['conta']) { ?> selected <?php } ?>
                                    <?php } ?> 
                                    value="<?=$ban01->id?>"><?=$ban01->descricao?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="d-flex flex-column ">
                            <label class="form-label">Tipo de arquivo:</label>
                            <div class="d-flex flex-row gap-3">
                                <div class="d-flex flex-column">
                                    <label for="tipo_arquivo_padrao" class="form-label me-2">Arquivo Operadora</label>
                                    <input checked type="radio" id="tipo_arquivo_padrao" name="tipo_arquivo" value="padrao">
                                </div>
                                <div class="d-flex flex-column">
                                    <label for="tipo_arquivo_personalizado" class="form-label me-2">Arquivo Gestor Office</label>
                                    <input type="radio" id="tipo_arquivo_personalizado" name="tipo_arquivo" value="personalizado">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 gap-2">
                            <label for="agencia" class="form-label">Arquivo Excel</label>
                            <input type="file"
                            onchange="this.form.submit()"
                            accept=".xlsx, .xls, .csv" id="agencia" name="vendas_excel"
                            class="form-control" placeholder="Agência"
                            >
                        </div>
                        <button type="submit" class="btn-sm btn btn-primary">Gerar</button>
                    </form>
                <?php } ?>
                <?php if (!empty($_SESSION['vendas_invalidas'])): ?>
                    <div class="">
                        <strong>Existem vendas com alguns atributos não cadastrados:</strong>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th>Data</th>
                                <th><div class="d-flex flex-row justify-content-between">
                                        <div class="w-50">
                                            Valor Bruto
                                        </div>
                                        <div class="w-50" style="text-align: end">
                                            Valor Liquido
                                        </div>
                                    </div>
                                </th>
                                <th>Parcela</th>
                                <th>Bandeira</th>
                                <th>Tipo</th>
                            </tr>
                        
                            <?php foreach ($_SESSION['vendas_invalidas'] as $transaction) { ?>
                                <tr>
                                    <td><?= htmlspecialchars((new DateTime($transaction['data']))->format('d/m/Y') ?? '') ?></td>
                                    <td><div class="d-flex flex-row justify-content-between w-100"><div class="w-50"><?= number_format(htmlspecialchars($transaction['valor_b'] ?? ''), 2, ',','.') . '</div> | <div class="w-50" style="text-align:end">' . ($transaction['valor_l'] === 0 ? 'Não Informado' : number_format(htmlspecialchars($transaction['valor_l'] ?? ''),2,',','.')) ?></div></div></td>
                                    <td <?php if(in_array('parcela', $transaction['motivo'])) { ?> class="text-danger" <?php } ?>><?= htmlspecialchars($transaction['parcela'] ?? '') ?></td>
                                    <td <?php if(in_array('bandeira', $transaction['motivo'])) { ?> class="text-danger" <?php } ?>><?= htmlspecialchars(ucfirst(strtolower($transaction['bandeira'])) ?? '') ?></td>
                                    <td <?php if(in_array('tipo', $transaction['motivo'])) { ?> class="text-danger" <?php } ?>><?= htmlspecialchars(ucfirst(strtolower($transaction['tipo'])) ?? '') ?></td>
                                </tr>
                            <?php } ?>
                            </table>
                    </div>
                <?php endif; ?>

                    <?php if ((!empty($_SESSION['vendas']['transactions']) || !empty($_SESSION['vendas']['cancelados'])) && empty($_SESSION['vendas_invlidas'])): ?>
                        <form method="post" action="receber_manager.php">
    <div class="gap-3">
        <?php if(!empty($_SESSION['vendas']['transactions']) && empty($_SESSION['vendas_invlidas'])) { ?>
            <input type="hidden" name="acao" value="adicionar"></input>
            <input type="hidden" name="operadora" value="<?=$_SESSION['vendas']['conta']?>"></input>
            
            <div class="mb-4">
                <h6>Pré-visualização do arquivo Excel:</h6>
                <!-- Adicionado max-height e overflow-y: auto aqui -->
                <div class="table-responsive" style="max-height: 20rem; overflow-y: auto;">
                    <table class="table table-bordered mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th>Parcela</th>
                                <th>Data</th>
                                <th>Valor Bruto</th>
                                <th>Valor Liquido</th>
                                <th>Bandeira</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 0;
                            foreach ($_SESSION['vendas']['transactions'] as $linha){ ?>
                                <input type="hidden" name="aprovada[bandeira_id][<?=$i?>]" value="<?=$linha['bandeira_id']?>">
                                <tr>
                                    <td><input class="form-control" readonly name="aprovada[parcela][<?=$i?>]" value="<?php echo htmlspecialchars($linha['parcela'] == '' ? 1 : $linha['parcela']) ?>"></td>
                                    <td><input class="form-control" readonly name="aprovada[data][<?=$i?>]" value="<?= (new DateTime(htmlspecialchars($linha['data'] ?? '')))->format(('d/m/Y')) ?>"></td>
                                    <td><input class="form-control" readonly name="aprovada[valor_b][<?=$i?>]" value="<?= number_format(htmlspecialchars($linha['valor_b'] ?? ''), 2, ',', '.') ?>"></td>
                                    <td><input class="form-control" readonly name="aprovada[valor_l][<?=$i?>]" value="<?= $linha['valor_l'] == 0 ? 'Não Informado' : number_format(htmlspecialchars($linha['valor_l']), 2, ',', '.') ?>"></td>
                                    <td><input class="form-control" readonly name="aprovada[bandeira][<?=$i?>]" value="<?= htmlspecialchars(ucfirst(strtolower($linha['bandeira'])) ?? '') ?>"></td>
                                    <td><input class="form-control" readonly name="aprovada[tipo][<?=$i?>]" value="<?= htmlspecialchars(ucfirst(strtolower($linha['tipo'])))?>"></td>
                                </tr>
                            <?php
                            $i++;
                            } 
                            ?>
                            <input type="hidden" name="total_linhas" value="<?=$i?>">
                        </tbody>
                    </table>
                </div>
            </div>          
        <?php } ?>

        <?php if(!empty($_SESSION['vendas']['cancelados']) && empty($_SESSION['vendas_invlidas'])) { ?>
            
            <div class="mb-4">
                <h6>Arquivos Cancelados Pela Operadora:</h6>
                <!-- Adicionado max-height e overflow-y: auto aqui -->
                <div class="table-responsive" style="max-height: 20rem; overflow-y: auto;">
                    <table class="table table-bordered mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th>Data</th>
                                <th>Bandeira</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Comprovante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 0;
                            foreach ($_SESSION['vendas']['cancelados'] as $linha){ ?>         
                                <tr>
                                    <td><input class="form-control" readonly name="cancelada[data][<?=$i?>]" value="<?= (new DateTime(htmlspecialchars($linha['data'] ?? '')))->format('d/m/Y')?>"></td>
                                    <td><input class="form-control" readonly name="cancelada[bandeira][<?=$i?>]" value="<?= ucfirst(htmlspecialchars($linha['bandeira'] ?? '')) ?>"></td>
                                    <td><input class="form-control" readonly name="cancelada[tipo][<?=$i?>]" value="<?= ucfirst(htmlspecialchars($linha['tipo'] ?? '')) ?>"></td>
                                    <td><input class="form-control" readonly name="cancelada[estado][<?=$i?>]" value="<?= ucfirst(htmlspecialchars($linha['status'] ?? '')) ?>"></td>
                                    <td><input class="form-control" readonly name="cancelada[valor][<?=$i?>]" value="<?= number_format(htmlspecialchars($linha['valor'] ?? ''), 2, ',', '.') ?>"></td>
                                    <td><input class="form-control" readonly name="cancelada[comprovante][<?=$i?>]" value="<?= htmlspecialchars(ucfirst(strtolower($linha['comprovante'])) ?? '') ?>"></td>
                                </tr>
                            <?php
                            $i++;
                            } 
                            ?>
                            <input type="hidden" name="total_linhas_canceladas" value="<?=$i?>">
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        <button type="submit" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
    </div>
</form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>