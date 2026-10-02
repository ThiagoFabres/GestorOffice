
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
                            
                            accept=".ofx, .xlsx, .csv" id="agencia" name="ofx"
                            class="form-control" placeholder="Agência"
                            >
                        </div>
                        <button type="submit" class="btn-sm btn btn-primary" style="float: right;">Importar Arquivo</button>
                    </form>
                    <?php } ?>

                    <?php if (!empty($_SESSION['ofx_transactions']['transactions'])): ?>
                    <form action="movimentacao_manager.php" method="post" id="form_adicionar">
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
                                        $valor = str_replace('.', '', $linha['valor']);
                                        $valor = str_replace(',', '.', $valor);
                                        $tipo = $valor > 0 ? 'Crédito' : 'Débito';
                                        ?>
                                        <tr>
                                            <td><?= $tipo ?></td>
                                            <td><?= htmlspecialchars($linha['data'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($linha['valor'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($novo_documento ?? '') ?></td>
                                            <td><?= htmlspecialchars($linha['descricao'] ?? '') ?></td>
                                            <td><input class="form-control" name="descricao_comp[<?=$id_linha?>]" value=""></td>
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
                        <button type="submit" id="btn_salvar" class="btn btn-success" style="background-color:#5856d6;border-color:#5856d6;">Salvar</button>
                    </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById('form_adicionar').addEventListener('submit', async function (e) {
    e.preventDefault();
    var btn = document.getElementById('btn_salvar');
    if (btn.disabled) return;
    btn.disabled = true;

    var TAM_LOTE = 100;
    var total = parseInt(this.querySelector('[name="total_linhas"]').value, 10);

    var comp = {};
    this.querySelectorAll('input[name^="descricao_comp["]').forEach(function (el) {
        var v = el.value.trim();
        if (v !== '') comp[el.name.match(/\[(\d+)\]/)[1]] = v;
    });

    async function enviar(fd) {
        var resp = await fetch('movimentacao_manager.php', { method: 'POST', body: fd });
        var txt = await resp.text();
        try { return JSON.parse(txt); }
        catch (e) { console.warn('Resposta não-JSON (HTTP ' + resp.status + '):', txt.substring(0, 300)); return null; }
    }

    try {
        for (var offset = 0; offset < total; offset += TAM_LOTE) {
            btn.textContent = 'Salvando ' + Math.min(offset + TAM_LOTE, total) + ' de ' + total + '...';

            var lote = {};
            for (var i = offset; i < Math.min(offset + TAM_LOTE, total); i++) {
                if (comp[i] !== undefined) lote[i] = comp[i];
            }

            var fd = new FormData();
            fd.append('acao', 'adicionar_lote');
            fd.append('offset', offset);
            fd.append('tam_lote', TAM_LOTE);
            fd.append('descricao_comp_json', JSON.stringify(lote));

            var json = await enviar(fd);
            // Só interrompe se a sessão se perdeu; qualquer outra coisa segue adiante
            if (json && json.ok === false && (json.erro === 'sessao_vazia' || json.erro === 'sessao_conta' || json.erro === 'lote_nao_iniciado')) {
                throw new Error('Sessão expirada. Gere o extrato novamente.');
            }
        }

        var fim = new FormData();
        fim.append('acao', 'adicionar_finalizar');
        await enviar(fim);
        window.location.href = 'movimentacao.php?sucesso=sucesso';
    } catch (err) {
        alert(err.message);
        btn.disabled = false;
        btn.textContent = 'Salvar';
    }
});
</script>