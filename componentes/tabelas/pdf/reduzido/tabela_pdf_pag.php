<table class="" id="tabela-pdf" >
                        <thead>
                            <?php
                            if ($direcao == 'ASC') {
                                $seta = '▲';
                            } else if ($direcao == 'DESC') {
                                $seta = '▼';
                            } else {
                                $seta = '';
                            }
                            ?>
                            <tr class="tr-clientes-header">
                                <?php if($get_filtro_por == 'lancamento' || !isset($get_filtro_por)) { ?>
                                    <th>DATA.LANC</th>
                                <?php } else if($get_filtro_por == 'vencimento') { ?>
                                    <th>DATA.VENC</th>
                                <?php } else if($get_filtro_por == 'pagamento') { ?>
                                    <th>DATA.PAG</th>
                                <?php } ?>
                                <th>DESCRIÇÃO</th>
                                <th>VALOR</th>
                            </tr>
                        </thead>
<tbody class="avoid-page-break">
                            <?php
                            $parcelas = Pag02::read(
                                id_empresa: $_SESSION['usuario']->id_empresa,
                                filtro_data_inicial: $get_filtro_data_inicial,
                                filtro_data_final: $get_filtro_data_final,
                                filtro_documento: $get_filtro_nome ?? null,
                                filtro_opcao: $get_filtro_opcao,
                                filtro_por: $get_filtro_por,
                                filtro_pagamento: $get_filtro_pagamento,
                                filtro_cadastro: $get_filtro_cadastro,
                                filtro_con01: $get_filtro_titulo,
                                filtro_con02: $get_filtro_subtitulo,
                                ordenar_por: $ordenar_por,
                                direcao: $direcao,
                                filtro_custos: $get_filtro_custo,
                                
                            );
                            if (!empty($parcelas)) {
                        
                                $total_valor_pago = 0;
                                $total_valor_par = 0;
                                if (empty($recebimentos_pagos) || $recebimentos_pagos === null)
                                    $recebimentos_pagos = []; 
                                    
                                ?>
                                <?php foreach ($parcelas as $pag02) {
                                    $data_pag = null;
                                    $data_venc = null;
                                    $data_lanc = null;


                                    $data_atual = new DateTime();
                                    $data_atual = $data_atual->format('Y-m-d');

                                    if (($pag02->vencimento == $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_amarela';
                                    } else if (($pag02->vencimento < $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_vermelha';
                                    } else if (($pag02->vencimento > $data_atual) && $pag02->valor_pag == 0) {
                                        $cor_parcela = 'parcela_cor_azul';
                                    } else if ($pag02->valor_pag > 0) {
                                        $cor_parcela = 'parcela_cor_verde';
                                    }

                                    $pag01 = Pag01::read($pag02->id_pag01, $_SESSION['usuario']->id_empresa)[0];

                                    if ($pag02->id_pgto != null) {
                                        $pagamento = TipoPagamento::read($pag02->id_pgto)[0];
                                    } else {
                                        $pagamento = null;
                                    }
                                    ;

                                    $data_pag = null;
                                    if (!empty($pag02->data_pag) && $pag02->valor_pag > 0) {
                                        $data_pag = (new DateTime($pag02->data_pag))->format('d/m/Y');
                                    }

                                    $data_venc = new DateTime($pag02->vencimento);
                                    $data_venc = $data_venc->format('d/m/Y');

                                    $data_lanc = new DateTime($pag01->data_lanc);
                                    $data_lanc = $data_lanc->format('d/m/Y');

                                    $cadastro = Cadastro::read($pag01->id_cadastro)[0];
                                    $valor_total = number_format($pag01->valor, 2, ',', '.');
                                    $valor_parcela = number_format($pag02->valor_par, 2, ',', '.');
                                    $valor_pago = number_format($pag02->valor_pag, 2, ',', '.');

                                    $centro_custos = '';
                                    if($pag01->centro_custos != null) {
                                    $centro_custos = CentroCustos::read($pag01->centro_custos, $_SESSION['usuario']->id_empresa)[0]->nome ?? '';
                                    }

                                    $link = 'pagar.php?view=pagar&acao=visualizar&id=' . $pag02->id;

                                    $ultima_parcela = null;
                                    if ($pag02->parcela == $pag01->parcelas)
                                        $ultima_parcela = true;

                                    ?>
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 3px solid #5856d6;<?php } ?> border-inline: 1px solid #5856d6;" -->
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 2px solid #5856d6;<?php } else if ($pag02->parcela == 1) { ?> border-top: 3px solid #5856d6; <?php } ?> border-inline: 2px solid #5856d6;" -->
                                <div class="avoid-page-break">
                                    <tr class="avoid-page-break">
                                        <?php if($get_filtro_por == 'lancamento' || !isset($get_filtro_por)) { ?>
                                            <td><?= $data_lanc ?></td>
                                        <?php } else if($get_filtro_por == 'vencimento') { ?>
                                            <td><?= $data_venc ?></td>
                                        <?php } else if($get_filtro_por == 'pagamento') { ?>
                                            <td><?= $data_pag ?? 'Não foi pago' ?></td>
                                        <?php } ?>
                                        <td colspan="9" class="descricao-full" style="text-align:start;" id="td-descricao"><?= nl2br(htmlspecialchars($cadastro->razao_soc . ' - ' . $pag01->descricao, ENT_QUOTES, 'UTF-8')) ?></td>

                                        <?php if($get_filtro_por == 'lancamento' || $get_filtro_por == 'vencimento' || !isset($get_filtro_por)) { ?>
                                            <td>R$ <?= $valor_parcela ?></td>
                                        <?php } else if($get_filtro_por == 'pagamento') { ?>
                                            <td><?php if ($pag02->valor_pag == 0) {
                                                echo '';
                                            } else {
                                                echo 'R$ ' . $valor_pago;
                                            } ?></td>
                                        <?php } ?>
                                    </tr>
                                </div> 


                                                    
                                
                                <?php 
                                $total_valor_pago += $pag02->valor_pag;
                                $total_valor_par += $pag02->valor_par;
                                
                                }  
                                ?> 
                                <tr id="tr-totais">
                                    <td style="text-align: end; ">Total:</td>
                                    <td style="text-align: end;">R$</td>
                                    <?php if($get_filtro_por == 'lancamento' || $get_filtro_por == 'vencimento' || !isset($get_filtro_por)) { ?>
                                        <td style="text-align: center;"><?= number_format($total_valor_par, '2', ',', '.')?></td>
                                    <?php } else if($get_filtro_por == 'pagamento') { ?>
                                        <td style="text-align: center;"><?= number_format($total_valor_pago, '2', ',', '.')?></td>
                                    <?php } ?>
                                </tr>
                                <?php }  else { ?>
                                <tr >
                                    <td>Nenhum Lançamento encontrado</td>
                                    <td></td>
                                    <td></td>

                                </tr>
                            <?php } ?>
                        
                        </tbody>

                
                </table>