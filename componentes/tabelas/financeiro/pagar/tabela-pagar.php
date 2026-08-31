<div class="tabela-lancamento dragscroll avoid-page-break">
                    <table class="table table-striped avoid-page-break">
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
                                <th>Centro de custos</th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=documento&direcao=<?php echo ($ordenar_por === 'documento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        ID
                                    </a><?php if ($ordenar_por == 'documento') { echo $seta;  } ?>
                                </th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=documento&direcao=<?php echo ($ordenar_por === 'documento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Documento
                                    </a><?php if ($ordenar_por == 'documento') { echo $seta; } ?>
                                </th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=data_lancamento&direcao=<?php echo ($ordenar_por === 'data_lancamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Data de Lançamento
                                    </a><?php if ($ordenar_por == 'data_lancamento') { echo $seta; } ?>
                                </th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=data_vencimento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'data_vencimento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Vencimento
                                    </a><?php if ($ordenar_por == 'data_vencimento') { echo $seta; } ?>
                                </th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=nome&direcao=<?php echo ($ordenar_por === 'nome' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Nome
                                    </a><?php if ($ordenar_por == 'nome') { echo $seta;} ?>
                                </th>
                                <th><a href="<?= $caminho ?>?ordenar=descricao&direcao=<?php echo ($ordenar_por === 'descricao' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">Descrição</a><?php if ($ordenar_por == 'descricao') {
                                                         echo $seta;
                                                     } ?>
                                </th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=valor&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Valor
                                    </a><?php if ($ordenar_por == 'valor') { echo $seta; } ?>
                                </th>
                                <th>
                                    <a>
                                        Parcela Geral
                                    </a>
                                </th>
                                <th>
                                    <a>Parcela Atual</a>
                                </th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=valor_parcela&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor_parcela' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Valor da Parcela
                                    </a><?php if ($ordenar_por == 'valor_parcela') { echo $seta; } ?></th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=data_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'data_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Data de Pagamento
                                    </a><?php if ($ordenar_por == 'data_pagamento') { echo $seta; } ?>
                                </th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=valor_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'valor_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Valor Pago
                                    </a><?php if ($ordenar_por == 'valor_pagamento') { echo $seta; } ?></th>
                                <th>
                                    <a href="<?= $caminho ?>?ordenar=tipo_pagamento&pagina=<?=$numero_pagina?>&numero_exibido=<?=$numero_exibir?>&direcao=<?php echo ($ordenar_por === 'tipo_pagamento' && $direcao === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                        Tipo de Pagamento
                                    </a><?php if ($ordenar_por == 'tipo_pagamento') { echo $seta; } ?>
                                </th>
                                <th>OBS</th>
                                <?php if($_SESSION['usuario']->processar === 1) {?>
                                <th>Quitar</th>
                                <th>Estornar</th>
                                <th>Editar</th>
                                <?php } ?>
                                <th>Visualizar</th>
                            </tr>
                        </thead>
                        <tbody class="avoid-page-break">
                            <?php
                            $parcelas = Pag02::read(
                                id_empresa: $_SESSION['usuario']->id_empresa,
                                filtro_data_inicial: $get_filtro_data_inicial,
                                filtro_data_final: $get_filtro_data_final,
                                filtro_descricao: $get_filtro_descricao,
                                filtro_opcao: $get_filtro_opcao,
                                filtro_por: $get_filtro_por,
                                filtro_pagamento: $get_filtro_pagamento,
                                filtro_cadastro: $get_filtro_cadastro,
                                filtro_con01: $get_filtro_titulo,
                                filtro_con02: $get_filtro_subtitulo,
                                numero_exibir: $numero_exibir,
                                numero_pagina: $numero_pagina,
                                ordenar_por: $ordenar_por,
                                direcao: $direcao,
                                filtro_custos: $get_filtro_custo,
                                filtro_nf: $get_filtro_nf
                            );
                            if (!empty($parcelas)) {
                        
                                $total_valor_pago = 0;
                                $total_valor_par = 0;
                                if (empty($recebimentos_pagos) || $recebimentos_pagos === null)
                                    $recebimentos_pagos = []; 
                                    
                                ?>
                                <?php foreach ($parcelas as $pag02) {


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
                                    <tr class="tr-clientes <?= $cor_parcela ?> avoid-page-break context-menu-row"
                                        data-id="<?= $pag02->id ?>"
                                        data-id-pag01="<?= $pag01->id ?>"
                                        data-valor-pag="<?= $pag02->valor_pag ?>"
                                        data-valor-restante="<?= number_format($pag02->valor_par - $pag02->valor_pag, 2, ',', '.') ?>"
                                        data-parcela-atual="<?= $pag02->parcela ?>"
                                        data-parcela-geral="<?= $pag01->parcelas ?>"
                                        data-vencimento="<?= $data_venc ?>"
                                        data-documento="<?= htmlspecialchars($pag01->documento, ENT_QUOTES, 'UTF-8') ?>"
                                        data-id-pag01-recebido="<?= in_array($pag02->id_pag01, $recebimentos_pagos) ? '1' : '0' ?>"
                                        onclick=""
                                         >
                                        <td><?= substr($centro_custos, 0, 15)?></td>
                                        <td><?= substr($pag01->documento,0 ,15); ?> </td>
                                        <td><?= $pag01->nf == 0 ? '' : $pag01->nf ?? '' ?></td>
                                        <td><?= $data_lanc; ?> </td>
                                        <td><?= $data_venc ?></td>
                                        <td><?= substr($cadastro->razao_soc, 0, 15); ?> </td>
                                        <td><?= substr($pag01->descricao, 0, 100); ?></td>
                                        <td>R$ <?= $valor_total ?></td>
                                        <td><?= $pag01->parcelas ?></td>
                                        <td><?= $pag02->parcela ?></td>
                                        <td>R$ <?= $valor_parcela ?></td>
                                        <td><?php if ($pag02->valor_pag == 0) {
                                            echo 'Não foi pago';
                                        } else {
                                            echo $data_pag ?? 'Não foi pago';
                                        } ?>
                                        </td>
                                        <td><?php if ($pag02->valor_pag == 0) {
                                            echo '';
                                        } else {
                                            echo 'R$ ' . $valor_pago;
                                        } ?></td>
                                        <td><?= $pagamento != null ? substr($pagamento->nome, 0, 15) : '' ?></td>
                                        <td><?= substr($pag02->obs, 0, 50) ?></td>
                                        <?php if($_SESSION['usuario']->processar === 1) {?>
                                        <td class="td-acoes">
                                            <?php $valor_restante = number_format($pag02->valor_par - $pag02->valor_pag, 2, ',', '.') ?>
                                            <button class="btn btn-primary" data-bs-toggle="modal" <?php if ($pag02->valor_pag > 0) { ?> disabled <?php } ?> data-bs-target="#modal_quitar"
                                                data-id="<?= $pag02->id ?>"  data-valor-restante="<?= $valor_restante ?>"
                                                data-parcela-atual="<?= $pag02->parcela ?>"
                                                data-parcela-geral="<?= $pag01->parcelas ?>"
                                                data-vencimento="<?= $data_venc ?>"
                                                data-documento="<?= htmlspecialchars($pag01->documento, ENT_QUOTES, 'UTF-8') ?>"
                                            ><i class="bi bi-cash-stack"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if ($pag02->valor_pag == 0) { ?> disabled <?php } ?>
                                                onclick="window.location.href='cadastros_manager.php?view=pagar&pagar=1&target=parcela&acao=estornar&id=<?= $pag02->id ?>&caminho=<?= $caminho_get ?>&pagina=<?php if (empty($filtros)) {?>
                                                     <?='?pagina=' . $numero_pagina;?>
                                                <?php } else { ?>
                                                     <?='?pagina=' . $numero_pagina;?>
                                                <?php } ?>&numero_exibido=<?= 'knumero_exibido=' . $numero_exibir ?>'"><i
                                                    class="bi bi-wallet2"></i></button>
                                        </td>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary" <?php if (in_array($pag02->id_pag01, $recebimentos_pagos) ) { ?> disabled <?php } ?>
                                                onclick="window.location.href='pagar.php?id=<?= $pag01->id ?>&acao=editar&pagina=<?= $numero_pagina ?>&numero_exibido=<?= $numero_exibir ?>'"><i
                                                    class="bi bi-pen-fill"></i></button>
                                        </td>
                                        <?php } ?>
                                        <td class="td-acoes">
                                            <button class="btn btn-primary"
                                                onclick="window.location.href='pagar.php?id=<?= $pag01->id ?>&acao=visualizar&pagina=<?= $numero_pagina ?>&numero_exibido=<?= $numero_exibir ?>'"><i class="bi bi-eye"></i></button>
                                        </td>
                                        
                                    </tr>



                                                    
                                
                                <?php 
                                $total_valor_pago += $pag02->valor_pag;
                                $total_valor_par += $pag02->valor_par;
                                }  
                                ?> 
                                <tr id="tr-totais">
                                    <td style="text-align: end; font-size: 100%;">Totais:</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: end; font-size: 100%;">R$</td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_par, '2', ',', '.')?></td>
                                    <td></td>
                                    <td style="text-align: end; font-size: 100%;">R$</td>
                                    <td style="text-align: center; font-size: 100%;"><?= number_format($total_valor_pago, '2', ',', '.')?></td>
                                    <td></td>
                                    <?php if($_SESSION['usuario']->processar === 1) {?>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <?php } ?>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    
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
                                    <td></td>

                                </tr>
                            <?php } ?>
                        
                        </tbody>

                
                </table>
                </div>