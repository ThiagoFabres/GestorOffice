<?php require_once __DIR__ . '/../../usuario/cartao/operadoras_suporte.php' ?>
<div class="modal fade" id="modal_footer_operadora" tabindex="-1" role="dialog" aria-labelledby="modalFaleConoscoLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastroCidadeLabel">Suporte por Operadoras</h5>
            </div>
            <div class="modal-body">
                    <div class="modal-content" style="gap: 3em;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Operadora</th>
                                    <th>XLSX</th>
                                    <th>XLS</th>
                                    <th>CSV</th>
                                    <th>Canceladas</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($operadoras_suporte as $nome => $ope) {
                                $sup_xlsx = false;
                                $sup_xls = false;
                                $sup_csv = false;
                                $sup_canceladas = false;
                                foreach($ope as $ext => $i) {
                                    // echo '<pre>';
                                    // print_r($i);
                                    // echo '</pre>';

                                    if($ext == 'xlsx') {
                                        $sup_xlsx = true;
                                    }
                                    if($ext == 'xls') {
                                        $sup_xls = true;
                                    }
                                    if($ext == 'csv') {
                                        $sup_csv = true;
                                    }
                                    if(isset($i['coluna_comprovante'])) {
                                        $sup_canceladas = true;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?=  strtoupper($nome) ?></td>
                                    <td><?= $sup_xlsx ? '✅' : '❌'?></td>
                                    <td><?= $sup_xls ? '✅' : '❌'?></td>
                                    <td><?= $sup_csv ? '✅' : '❌'?></td>
                                    <td><?= $sup_canceladas ? '✅' : '❌'?></td>
                                </tr>
                            <?php }?>
                            </tbody>
                        </table>
                        <div>
                            <h4>Formato XLSX Padrão Gestor Office:</h4>
                            <p>Obrigatório apenas 1 linha de cabeçalho</p>
                            <p>Formato de Exemplo:  </p>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Data de Lançamento</th>
                                        <th>Bandeira</th>
                                        <th>Tipo</th>
                                        <th>Parcelas</th>
                                        <th>Valor Bruto</th>
                                        <th>Valor Líquido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>14/10/2026</td>
                                        <td>Visa</td>
                                        <td>Crédito</td>
                                        <td>2</td>
                                        <td>R$ 1.000,00</td>
                                        <td>R$ 900,00</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p>Considerando que Visa Crédito com 2 parcelas está cadastrado e com prazo de 30 dias, Criará 2 Parcelas no Contas a receber:  </p>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Data De Vencimento</th>
                                        <th>Descrição</th>
                                        <th>Parcela</th>
                                        <th>Valor da Parcela</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="parcela_cor_verde">
                                        <td>13/11/2026</td>
                                        <td>Operadora - Visa - Crédito - 2x</td>
                                        <td>1/2</td>
                                        <td>R$ 450,00</td>
                                    </tr>
                                    <tr class="parcela_cor_verde">
                                        <td>13/12/2026</td>
                                        <td>Operadora - Visa - Crédito - 2x</td>
                                        <td>2/2</td>
                                        <td>R$ 450,00</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
