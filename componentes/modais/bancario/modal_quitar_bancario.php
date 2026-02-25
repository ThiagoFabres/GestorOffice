<?php
if($acao == 'quitar_bancario') {
$quitar_id = filter_input(INPUT_GET, 'id');
$ban02 = Ban02::read($quitar_id, $_SESSION['usuario']->id_empresa)[0];
$data_quitar_inicial = filter_input(INPUT_GET, 'data_quitar_inicial') ?? ($ban02->data) ?? null;
$data_quitar_final = filter_input(INPUT_GET, 'data_quitar_final') ?? ($ban02->data) ?? null;
$quitados = filter_input(INPUT_GET, 'quitados') == 'on' ? null  : 'abertos';
$tipo = null;
$tipo = $ban02->valor < 0 ? 'D' : 'C';

if($data_quitar_inicial != null || $data_quitar_final != null) {
    if($tipo == 'C') {
        $lancamentos = Rec02::read(
            id_empresa: $_SESSION['usuario']->id_empresa,
            filtro_data_inicial:$data_quitar_inicial,
            filtro_data_final:$data_quitar_final,
            filtro_por:'vencimento',
            filtro_opcao:$quitados
            
        );
        $link_adicionar = 'movimentacao.php?acao=quitar_adicionar&tipo=C&id_ban='.$ban02->id;
        
    } else if($tipo == 'D') {
        $lancamentos = Pag02::read(
            id_empresa: $_SESSION['usuario']->id_empresa,
            filtro_data_inicial:$data_quitar_inicial,
            filtro_data_final:$data_quitar_final,
            filtro_por:'vencimento',
            filtro_opcao:$quitados
        );
        $link_adicionar = 'movimentacao.php?acao=quitar_adicionar&tipo=D&id_ban='.$ban02->id;
    }
}
}
?>
<div class="modal fade" id="modal_quitar_bancario" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Adicionar Nova Conta Bancária</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">

                    <form method="get" enctype="multipart/form-data" action="movimentacao.php">
                        <?php foreach($filtros_get as $i => $filtro) { ?>
                            <input type="hidden" name="<?=$i?>" value="<?=$filtro?>">
                        <?php } ?>
                        <input type="hidden" name="acao" value="quitar_bancario"></input>
                        <input type="hidden" name="id" value="<?=$ban02->id?>"></input>
                        <div class="mb-3 gap-2 d-flex flex-row" style=justify-content:space-evenly;>
                            
                                    <div style="width:calc(100%/4);">
                                        <label>Data de Vencimento Inicial:</label>
                                        <input type="date" value="<?=$ban02->data?>" name="data_quitar_inicial" class="form-control">
                                    </div>
                                    <div style="width:calc(100%/4);">
                                        <label>Data de Vencimento Final:</label>
                                        <input type="date" value="<?=$ban02->data?>" name="data_quitar_final" class="form-control">
                                    </div>
                                
                                <div class="d-flex flex-column" style=" gap:1em; width:calc(100%/4);">
                                    <label style="text-align:center;">Exibir Quitados:</label>
                                    <input type="checkbox" name="quitados" <?php if($quitados == null) echo 'checked'?>>
                                </div>
                                <div class="d-flex flex-row justify-content-center" style="margin-top: 2.4%; width:calc(100%/7); align-itens:center;">
                                    <button type="submit" class="btn-sm btn btn-primary" style=" width:50%; height:55%">Buscar</button>
                                </div>
                                

                            </div>
                            
                            
                        </div>
                        
                        

                            
                        
                    </form>

<?php if($data_quitar_inicial != null || $data_quitar_final != null) { ?>

                    <div class="mb-3 mt-3" style="max-height:30rem; overflow: auto;">
                        <div class="table-responsive">
                            <table class="table">
                                <thead style="position:sticky;">
                                    <tr style="position:sticky;">
                                        <th>Centro de custos</th>
                                        <th>Documento</th>
                                        <th>Data de Lançamento</th>
                                        <th>Descrição</th>
                                        <th>Parcela Geral</th>
                                        <th>Parcela_atual</th>
                                        <th>Valor da Parcela</th>
                                        <th>Vencimento</th>
                                        <th>Valor Pago</th>
                                        <th>Tipo de Pagamento</th>
                                        <th>Quitar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    
                                    $id_linha = 0;
                                    $data_atual = new DateTime();
                                    $data_atual = $data_atual->format('Y-m-d');
                                    foreach ($lancamentos as $lancamento){ 
                                        
                                        if (($lancamento->vencimento == $data_atual) && $lancamento->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_amarela';
                                    } else if (($lancamento->vencimento < $data_atual) && $lancamento->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_vermelha';
                                    } else if (($lancamento->vencimento > $data_atual) && $lancamento->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_azul';
                                    } else if ($lancamento->valor_pag > 0) {
                                        $cor_parcela = 'parcela_cor_verde';
                                    }
                                        if($tipo == 'D') {
                                            $lancamento01 = Pag01::read($lancamento->id_pag01)[0];
                                        } else if($tipo == 'C') {
                                            $lancamento01 = Rec01::read($lancamento->id_rec01)[0];
                                        }
                                        $tipo_pagamento = TipoPagamento::read($lancamento->id_pgto)[0]->nome;
                                        $centro_custos = CentroCustos::read($lancamento01->centro_custos)[0]->nome;
                                        $data_lancamento = (new DateTime($lancamento01->data_lanc))->format('d/m/Y');
                                        $data_vencimento = (new DateTime($lancamento->vencimento))->format('d/m/Y');
                                        ?>
                                        <tr class="<?=$cor_parcela?>">
                                            <td><?=$centro_custos?></td>
                                            <td><?=$lancamento->documento?></td>
                                            <td><?=$data_lancamento?></td>
                                            <td><?=$lancamento01->descricao?></td>
                                            <td><?=$lancamento01->parcelas?></td>
                                            <td><?=$lancamento->parcela?></td>
                                            <td><?=$lancamento->valor_par?></td>
                                            <td><?=$data_vencimento?></td>
                                            <td><?php if($lancamento->valor_pag > 0) echo $lancamento->valor_pag; ?></td>
                                            <td><?php if($lancamento->id_pgto != null) echo $tipo_pagamento?></td>
                                            <td class="td-acoes">
                                            <?php $valor_restante = number_format($lancamento->valor_par - $lancamento->valor_pag, 2, ',', '.') ?>
                                            <button class="btn btn-primary" <?php if($lancamento->valor_pag > 0) echo 'disabled'?> type="button" data-bs-toggle="modal" data-bs-target="#modal_quitar" onclick="window.location.href='<?php if(empty($filtros)) {echo $caminho . '?';} else {echo $caminho . '&';}?>acao=quitar&tipo=<?=$tipo?>&id=<?=$lancamento->id?>'"><i class="bi bi-cash-stack"></i></button>
                                        </td>
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
                    <div class="d-flex justify-content-between gap-2" style="padding:1em">
                        <div>
                            <button 
                            onclick="
                            window.location.href='<?=$link_adicionar?>'" type="button" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Novo Lançamento</button>
                        </div>
                        <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>