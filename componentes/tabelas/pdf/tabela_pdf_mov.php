<table id="tabela-pdf">
                    <thead>
                        <tr class="tr-header">
                            <th>Documento</th>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Conta</th>
                            <th>Título</th>
                            <th>Subtítulo</th>
                            <th>Código</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        
                        

                         if(!empty($movimentacoes_pdf)) {
                            
                            foreach($movimentacoes_pdf as $movimentacao) {
                                
                                // echo '<pre>';
                                // print_r($movimentacao);
                                // echo '</pre>';
                                if(empty($filtros)) {
                                $link = $caminho . '?';
                            } else {$link = $caminho . '&';}
                                $link .= 'acao=conciliar&id=' . $movimentacao->id;
                            
                            if($movimentacao->id_con01 != null) {
                                $con01 = Con01::read($movimentacao->id_con01, $_SESSION['usuario']->id_empresa)[0];
                            } else {
                                $con01 = null;
                            }
                            if($movimentacao->id_con02 != null) {
                                $con02 = Con02::read($movimentacao->id_con02, $_SESSION['usuario']->id_empresa)[0];
                                $codigo = $con02->codigo ?? '';
                            } else {
                                $con02 = null;
                            }
                            if($movimentacao->id_con01 != null && $movimentacao->id_con02 != null )   {
                                $cor_parcela = 'parcela_cor_verde';
                            } else {
                                $cor_parcela = 'parcela_cor_vermelha';
                            }
                                $tipo = $movimentacao->valor < 0 ? 'Débito' : 'Crédito';
                                $caminho_quitar = $movimentacao->valor < 0 ? '/usuario/pagar.php?filtro_data_inicial='.$movimentacao->data.'&filtro_data_final='.$movimentacao->data.'&opcao_filtro=abertos&filtro_por=lancamento' : '/usuario/receber.php?filtro_data_inicial='.$movimentacao->data.'&filtro_data_final='.$movimentacao->data.'&opcao_filtro=abertos&filtro_por=lancamento';
                                $data_lancamento = DateTime::createFromFormat('Y-m-d', $movimentacao->data)->format('d/m/Y');
                                $conta_nome = Ban01::read($movimentacao->id_ban01, $_SESSION['usuario']->id_empresa)[0]->nome;
                         {?>
                         <tr>
                            <td onclick="window.location.href='<?=$link?>'"><?=$movimentacao->documento?></td>
                            <td onclick="window.location.href='<?=$link?>'"><?=$data_lancamento?></td>
                            <td onclick="window.location.href='<?=$link?>'"><?=$tipo?></td>
                            <td colspan="9" class="descricao-full" style="text-align:start;" id="td-descricao" onclick="window.location.href='<?=$link?>'"><?php echo $movimentacao->descricao_comp != '' ? substr(substr($movimentacao->descricao, 0, 60) . ' - ' . substr($movimentacao->descricao_comp, 0,60), 0, 100) : $movimentacao->descricao ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td onclick="window.location.href='<?=$link?>'">R$ <?=number_format($movimentacao->valor, 2, ',', '.', )?></td>
                            <td onclick="window.location.href='<?=$link?>'"><?=$conta_nome?></td>
                            <td onclick="window.location.href='<?=$link?>'"><?= isset($con01) ? substr($con01->nome, 0, 15) : ''?></td>
                            <td onclick="window.location.href='<?=$link?>'"><?= isset($con02) ? substr($con02->nome, 0, 15) : ''?></td>
                            <td onclick="window.location.href='<?=$link?>'"><?= isset($con02) ? $con02->codigo : ''?></td>
                            
                        </tr>
                        <?php } } }?>
                    </tbody>
                </table>