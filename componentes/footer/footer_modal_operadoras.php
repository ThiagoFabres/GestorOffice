<?php require_once __DIR__ . '/../../usuario/cartao/operadoras_suporte.php' ?>
<div class="modal fade" id="modal_footer_operadora" tabindex="-1" role="dialog" aria-labelledby="modalFaleConoscoLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastroCidadeLabel">Suporte por Operadoras</h5>
            </div>
            <div class="modal-body">
                    <div class="modal-content">
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
                    </div>
            </div>
        </div>
    </div>
</div>
