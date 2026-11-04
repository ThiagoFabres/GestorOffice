<div class="modal fade" id="modalNovaMovimentacao" tabindex="-1"
                aria-labelledby="modalNovaMovimentacaoTitulo" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <form method="post" action="movimentacao_manager.php" id="formNovaMovimentacao">
                            <div class="modal-header">
                                <div>
                                    <h2 class="modal-title fs-5" id="modalNovaMovimentacaoTitulo">Nova movimentação</h2>
                                    <p class="text-muted small mb-0">Um movimento (mov01) pode conter vários produtos (mov02).</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="tipoMovimentacao" class="form-label">Tipo</label>
                                        <select id="tipoMovimentacao" name="tipo" class="form-select" required>
                                            <option value="entrada">Entrada</option>
                                            <option value="saida">Saída</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="dataMovimentacao" class="form-label">Data</label>
                                        <input type="date" id="dataMovimentacao" name="data" class="form-control"
                                            value="<?= escaparMovimentacao($hoje->format('Y-m-d')) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="cadastroMovimentacao" class="form-label">Cliente/fornecedor</label>
                                        <select id="cadastroMovimentacao" name="cadastro" class="form-select" required>
                                            <option value="">Selecione um cliente/fornecedor</option>
                                            <?php foreach ($cadastros as $cadastro) {
                                                $nomeCadastro = trim((string) ($cadastro->nom_fant ?: $cadastro->razao_soc));
                                                ?>
                                                <option value="<?= (int) $cadastro->id_cadastro ?>">
                                                    <?= escaparMovimentacao($nomeCadastro) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <h3 class="h6 mb-0">Produtos</h3>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        id="adicionarItemMovimentacao" <?= !$produtos ? 'disabled' : '' ?>>
                                        <i class="bi bi-plus-lg"></i> Adicionar produto
                                    </button>
                                </div>
                                <?php if (!$produtos) { ?>
                                    <div class="alert alert-warning">
                                        Cadastre ao menos um produto antes de registrar uma movimentação.
                                    </div>
                                <?php } ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 210px;">Produto</th>
                                                <th style="min-width: 120px;">Quantidade</th>
                                                <th style="min-width: 140px;">Custo unitário</th>
                                                <th style="min-width: 140px;">Venda unitária</th>
                                                <th style="min-width: 110px;">Total custo</th>
                                                <th>Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itensMovimentacao"></tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end gap-4">
                                    <div>Itens: <strong id="quantidadeItensMovimentacao">0</strong></div>
                                    <div>Total de custo: <strong id="totalCustoMovimentacao">R$ 0,00</strong></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary" <?= !$produtos ? 'disabled' : '' ?>>
                                    Salvar movimentação
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>