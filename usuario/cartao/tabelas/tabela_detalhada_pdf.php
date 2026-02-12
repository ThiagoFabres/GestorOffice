<table class="table table-striped avoid-page-break" id="tabela-pdf">
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
                                <th><a
                                        href="<?= $caminho ?>?ordenar=documento&direcao=<?php echo ($ordenar_por === 'documento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Documento</a><?php if ($ordenar_por == 'documento') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=data_lancamento&direcao=<?php echo ($ordenar_por === 'data_lancamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Data
                                        de Lançamento</a><?php if ($ordenar_por == 'data_lancamento') {
                                            echo $seta;
                                        } ?>
                                </th>
                                <th><a
                                        href="<?= $caminho ?>?ordenar=nome&direcao=<?php echo ($ordenar_por === 'nome' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Nome</a><?php if ($ordenar_por == 'nome') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th><a>Descrição</a></th>
                                <th><a>C. Custos</a></th>
                                
                                <th><a>Parcela Geral</a></th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=valor&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Valor Bruto
                                    </a><?php if ($ordenar_por == 'valor') {echo $seta;} ?>
                                </th>
                                <th>
                                    Taxa
                                </th>
                                <th>
                                    Taxa Aplicada
                                </th>
                                <th>
                                    Valor Liquido
                                </th>
                                <th>
                                    Valor Liquido GO
                                </th>
                                <th>
                                    Diferença
                                </th>
                            </tr>
                        </thead>
                        <tbody class="avoid-page-break">
                            <?php
                            $recebimentos = Rec01::read(
                                id_empresa: $_SESSION['usuario']->id_empresa,
                                read_vendas: $exibir_detalhes,
                                read_diferencas: $exibir_diferencas,
                                filtro_data_inicial: $get_filtro_data_inicial,
                                filtro_data_final: $get_filtro_data_final,
                                id_cadastro: $get_filtro_cadastro,
                                filtro_custos: $get_filtro_custo,
                            );
                            if (!empty($recebimentos)) {
                        
                                $total_valor_b = 0;
                                $total_valor_l = 0;
                                $total_valor_l_go = 0;
                                $total_valor_d = 0;
                                ?>
                                <?php foreach ($recebimentos as $rec01) {
                                    $data_atual = new DateTime();
                                    $data_atual = $data_atual->format('Y-m-d');
                                    $data_lanc = new DateTime($rec01->data_lanc);
                                    $data_lanc = $data_lanc->format('d-m-Y');
                                    $cadastro = Cadastro::read($rec01->id_cadastro)[0];
                                    $centro_custos = CentroCustos::read($rec01->centro_custos, $_SESSION['usuario']->id_empresa)[0];
                                    $taxa = (($rec01->valor_b - $rec01->valor_liq_go) / $rec01->valor_b) * 100;
                                    $taxa_aplicada = (($rec01->valor_b - $rec01->valor) / $rec01->valor_b) * 100;

                                    ?>
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 3px solid #5856d6;<?php } ?> border-inline: 1px solid #5856d6;" -->
                                    <!-- style="<?php if ($ultima_parcela) { ?>border-bottom: 2px solid #5856d6;<?php } else if ($rec02->parcela == 1) { ?> border-top: 3px solid #5856d6; <?php } ?> border-inline: 2px solid #5856d6;" -->
                                    <div class="avoid-page-break">
                                        <tr class="avoid-page-break">
                                            <td><?= $rec01->documento; ?> </td>
                                            <td><?= $data_lanc; ?> </td>
                                            <td><?= $cadastro->razao_soc; ?> </td>
                                            <td colspan="9" class="descricao-full" style="text-align:start;" id="td-descricao"><?= nl2br($rec01->descricao); ?></td>
                                        </tr>

                                        <tr class="avoid-page-break">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><?=$centro_custos->nome?></td>
                                            <td><?= $rec01->parcelas ?></td>
                                            <td>R$ <?= number_format($rec01->valor_b, 2, ',', '.') ?></td>
                                            <td><?= number_format($taxa, 2, ',', '.')?></td>
                                            <td><?= number_format($taxa_aplicada, 2, ',', '.') ?></td>
                                            <td><?= $rec01->valor ?></td>
                                            <td><?= $rec01->valor_liq_go?></td>
                                            <td><?= number_format($rec01->valor - $rec01->valor_liq_go, 2, ',', '.')?></td>
                                        </tr>
                                    </div>



                                                    
                                
                                <?php 
                                $total_valor_b += $rec01->valor_b;
                                $total_valor_l += $rec01->valor;
                                $total_valor_l_go += $rec01->valor_liq_go;
                                $total_valor_d += $rec01->valor - $rec01->valor_liq_go;
                                }  
                                ?> 
                                <tr id="tr-totais">
                                    <td style="text-align: start; font-size: 100%;">Totais:</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: center; font-size: 100%;">R$</td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_b, '2', ',', '.')?></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_l, '2', ',', '.')?></td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_l_go, '2', ',', '.')?></td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_d, '2', ',', '.')?></td>
                                    
                                </tr>
                                <?php }  else { ?>
                                <tr >
                                    <td>Nenhum Lançamento encontrado</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                </tr>
                            <?php } ?>
                        
                        </tbody>

                
                </table>