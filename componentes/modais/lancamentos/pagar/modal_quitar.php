
<div class="modal fade" id="modal_quitar" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Opções da conta</h5>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="content" action="cadastros_manager.php"
                            onkeydown="return event.key != 'Enter';">
                            <input type="hidden" name="view" value="pagar">
                            <input type="hidden" name="acao" value="quitar">
                            <input type="hidden" name="target" value="parcela">
                            <input type="hidden" name="id" id="modal_quitar_id" value="">
                            <input type="hidden" name="caminho" value="<?= $caminho ?>">
                            <input type="hidden" name="pagar" value="1">
                            <?php if (!empty($filtros)) { ?>
                                <input type="hidden" name="pagina" value="&pagina=<?= $numero_pagina ?>">
                                <input type="hidden" name="numero_exibido" value="&numero_exibido=<?= $numero_exibir ?>">
                            <?php } else { ?>
                                <input type="hidden" name="pagina" value="?pagina=<?= $numero_pagina ?>">
                                <input type="hidden" name="numero_exibido" value="&numero_exibido=<?= $numero_exibir ?>">
                            <?php } ?>

                            <div class="valor-alvo">
                                <p id="modal_quitar_valor_restante" style="color: #00000096;"></p>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label>Documento</label>
                                    <p id="modal_quitar_documento" style="color: #00000096; margin-bottom: 0;"></p>
                                </div>
                                <div class="col-6">
                                    <label>Vencimento</label>
                                    <p id="modal_quitar_vencimento" style="color: #00000096; margin-bottom: 0;"></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label>Parcela geral</label>
                                    <p id="modal_quitar_parcela_geral" style="color: #00000096; margin-bottom: 0;"></p>
                                </div>
                                <div class="col-6">
                                    <label>Parcela atual</label>
                                    <p id="modal_quitar_parcela_atual" style="color: #00000096; margin-bottom: 0;"></p>
                                </div>
                            </div>
                            
                            <label for="data">Data do pagamento</label>
                            <input class="form-control" type="date" placeholder="dd/mm/aa" name="data"
                                value="<?= (new DateTime())->format('Y-m-d') ?>">
                            <label for="valor">Valor pago</label>
                            <input class="form-control" type="text" id="modal_quitar_valor" name="valor" placeholder="Valor pago">
                            <label for="forma_pagamento">Forma de pagamento</label>
                            <div class="input-pagamento-group">
                                <div class="input-pagamento-div" style="width:100%">
                                    <select class="form-control" name="forma_pagamento" required>
                                        <option value="">Selecione</option>
                                        <?php foreach (TipoPagamento::read(null, $_SESSION['usuario']->id_empresa) as $pagamento) { ?>
                                            <option value="<?= $pagamento->id ?>">
                                                <?= $pagamento->nome ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="input-pagamento-btn">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_pagamento" type="button"
                                        class="form-control" id="btnModalPagamento"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>
                            <div style="margin-bottom: 3em;" class="footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                                <button type="submit" class="btn btn-primary">Pagar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<script>
// Função para preencher os campos extras do modal_quitar ao abrir (exibindo como texto)
document.addEventListener('DOMContentLoaded', function () {
    var modalQuitar = document.getElementById('modal_quitar');
    if (!modalQuitar) return;
    modalQuitar.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        // Se não houver um elemento relacionado (aberto via JS), não sobrescreve valores já preenchidos
        if (!button) return;
        // Os dados podem ser passados via data-* attributes no botão que abre o modal
        var parcelaAtual = button.getAttribute('data-parcela-atual') || '';
        var parcelaGeral = button.getAttribute('data-parcela-geral') || '';
        var vencimento = button.getAttribute('data-vencimento') || '';
        var documento = button.getAttribute('data-documento') || '';
        var valorRestante = button.getAttribute('data-valor') || '';
        var elParAtual = document.getElementById('modal_quitar_parcela_atual'); if (elParAtual) elParAtual.textContent = parcelaAtual;
        var elParGeral = document.getElementById('modal_quitar_parcela_geral'); if (elParGeral) elParGeral.textContent = parcelaGeral;
        var elVenc = document.getElementById('modal_quitar_vencimento'); if (elVenc) elVenc.textContent = vencimento;
        var elDoc = document.getElementById('modal_quitar_documento'); if (elDoc) elDoc.textContent = documento;
        var elVal = document.getElementById('modal_quitar_valor'); if (elVal) elVal.placeholder = valorRestante;
    });
});
</script>
