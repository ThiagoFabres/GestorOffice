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
                        <input type="hidden" name="acao" value="processar">
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
                        <div class="d-flex flex-column">
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
                            class="form-control" placeholder="Agência">
                        </div>
                        <button type="submit" class="btn-sm btn btn-primary">Gerar</button>
                    </form>
                <?php } ?>

                <?php if (!empty($_SESSION['vendas_invalidas'])): ?>
                    <div>
                        <strong>Existem vendas com alguns atributos não cadastrados:</strong>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>
                                        <div class="d-flex flex-row justify-content-between">
                                            <div class="w-50">Valor Bruto</div>
                                            <div class="w-50" style="text-align: end">Valor Liquido</div>
                                        </div>
                                    </th>
                                    <th>Parcela</th>
                                    <th>Bandeira</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['vendas_invalidas'] as $transaction) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars((new DateTime($transaction['data']))->format('d/m/Y') ?? '') ?></td>
                                        <td>
                                            <div class="d-flex flex-row justify-content-between w-100">
                                                <div class="w-50"><?= number_format(htmlspecialchars($transaction['valor_b'] ?? ''), 2, ',','.') ?></div>
                                                <div class="w-50" style="text-align:end"><?= ($transaction['valor_l'] === 0 ? 'Não Informado' : number_format(htmlspecialchars($transaction['valor_l'] ?? ''),2,',','.')) ?></div>
                                            </div>
                                        </td>
                                        <td <?php if(in_array('parcela', $transaction['motivo'])) { ?> class="text-danger" <?php } ?>><?= htmlspecialchars($transaction['parcela'] ?? '') ?></td>
                                        <td <?php if(in_array('bandeira', $transaction['motivo'])) { ?> class="text-danger" <?php } ?>><?= htmlspecialchars(ucfirst(strtolower($transaction['bandeira'])) ?? '') ?></td>
                                        <td <?php if(in_array('tipo', $transaction['motivo'])) { ?> class="text-danger" <?php } ?>><?= htmlspecialchars(ucfirst(strtolower($transaction['tipo'])) ?? '') ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ((!empty($_SESSION['vendas']['transactions']) || !empty($_SESSION['vendas']['cancelados'])) && empty($_SESSION['vendas_invalidas'])): ?>
                    <form method="post" action="receber_manager.php" id="form_adicionar_vendas">
                        <input type="hidden" name="acao" value="adicionar">
                        <input type="hidden" name="operadora" value="<?=$_SESSION['vendas']['conta']?>">
                        
                        <!-- Dados estruturados enviados em um único parâmetro JSON -->
                        <input type="hidden" name="aprovadas_json" value="<?= htmlspecialchars(json_encode($_SESSION['vendas']['transactions'] ?? [])) ?>">
                        <input type="hidden" name="canceladas_json" value="<?= htmlspecialchars(json_encode($_SESSION['vendas']['cancelados'] ?? [])) ?>">

                        <div class="gap-3">
                            <?php if(!empty($_SESSION['vendas']['transactions']) && empty($_SESSION['vendas_invalidas'])) { ?>
                                <div class="mb-4">
                                    <h6>Pré-visualização do arquivo Excel:</h6>
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
                                                <?php foreach ($_SESSION['vendas']['transactions'] as $linha): ?>
                                                    <tr>
                                                        <td><input class="form-control" readonly value="<?= htmlspecialchars($linha['parcela'] == '' ? 1 : $linha['parcela']) ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= (new DateTime(htmlspecialchars($linha['data'] ?? '')))->format('d/m/Y') ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= number_format(htmlspecialchars($linha['valor_b'] ?? ''), 2, ',', '.') ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= $linha['valor_l'] == 0 ? 'Não Informado' : number_format(htmlspecialchars($linha['valor_l']), 2, ',', '.') ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= htmlspecialchars(ucfirst(strtolower($linha['bandeira'])) ?? '') ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= htmlspecialchars(ucfirst(strtolower($linha['tipo']))) ?>"></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>          
                            <?php } ?>

                            <?php if(!empty($_SESSION['vendas']['cancelados']) && empty($_SESSION['vendas_invalidas'])) { ?>
                                <div class="mb-4">
                                    <h6>Arquivos Cancelados Pela Operadora:</h6>
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
                                                <?php foreach ($_SESSION['vendas']['cancelados'] as $linha): ?>
                                                    <tr>
                                                        <td><input class="form-control" readonly value="<?= (new DateTime(htmlspecialchars($linha['data'] ?? '')))->format('d/m/Y') ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= ucfirst(htmlspecialchars($linha['bandeira'] ?? '')) ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= ucfirst(htmlspecialchars($linha['tipo'] ?? '')) ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= ucfirst(htmlspecialchars($linha['status'] ?? '')) ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= number_format(htmlspecialchars($linha['valor'] ?? ''), 2, ',', '.') ?>"></td>
                                                        <td><input class="form-control" readonly value="<?= htmlspecialchars(ucfirst(strtolower($linha['comprovante'])) ?? '') ?>"></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php } ?>

                                <script>
                                (function () {
                                    var form = document.getElementById('form_adicionar_vendas');
                                    if (!form) return;

                                    form.addEventListener('submit', async function (event) {
                                        event.preventDefault();

                                        var btn = form.querySelector('button[type="submit"]');
                                        if (btn.disabled) return;
                                        btn.disabled = true;

                                        try {
                                            var aprovadas = JSON.parse(form.querySelector('[name="aprovadas_json"]').value || '[]');
                                            var canceladas = JSON.parse(form.querySelector('[name="canceladas_json"]').value || '[]');
                                            var grupos = new Map();

                                            aprovadas.forEach(function (linha) {
                                                var bandeira = linha.tipo === 'Pix' ? 'Pix' : (linha.bandeira ?? '');
                                                var chave = JSON.stringify([
                                                    String(linha.data ?? ''),
                                                    String(bandeira),
                                                    String(linha.tipo ?? ''),
                                                    String(linha.parcela ?? 1)
                                                ]);
                                                if (!grupos.has(chave)) grupos.set(chave, []);
                                                grupos.get(chave).push(linha);
                                            });

                                            var itens = Array.from(grupos.values()).map(function (linhas) {
                                                return { tipo: 'aprovadas', linhas: linhas };
                                            });
                                            canceladas.forEach(function (linha) {
                                                itens.push({ tipo: 'cancelada', linha: linha });
                                            });

                                            var total = itens.length;
                                            var tamanhoLote = 100;

                                            async function enviar(formData) {
                                                var resposta = await fetch(form.action, { method: 'POST', body: formData });
                                                var texto = await resposta.text();
                                                var json;
                                                try {
                                                    json = JSON.parse(texto);
                                                } catch (error) {
                                                    throw new Error('Não foi possível confirmar o salvamento das vendas.');
                                                }
                                                if (!resposta.ok || !json || json.ok !== true) {
                                                    throw new Error('Ocorreu um erro ao salvar as vendas. Tente novamente.');
                                                }
                                            }

                                            for (var offset = 0; offset < total; offset += tamanhoLote) {
                                                btn.textContent = 'Salvando ' + Math.min(offset + tamanhoLote, total) + ' de ' + total + '...';
                                                var dadosLote = new FormData();
                                                dadosLote.append('acao', 'adicionar_lote');
                                                dadosLote.append('operadora', form.querySelector('[name="operadora"]').value);
                                                dadosLote.append('lote_json', JSON.stringify(itens.slice(offset, offset + tamanhoLote)));
                                                await enviar(dadosLote);
                                            }

                                            var finalizar = new FormData();
                                            finalizar.append('acao', 'adicionar_finalizar');
                                            await enviar(finalizar);
                                            window.location.href = '/usuario/cartao/cadastro_vendas.php?sucesso=1';
                                        } catch (error) {
                                            alert(error.message);
                                            btn.disabled = false;
                                            btn.textContent = 'Salvar';
                                        }
                                    });
                                })();
                                </script>
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