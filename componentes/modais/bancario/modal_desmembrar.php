
<?php
if($acao == 'desmembrar'){
$get_id = filter_input(INPUT_GET, 'id');
$ban02 = Ban02::read($get_id, $_SESSION['usuario']->id_empresa);
if(!isset($get_id) || !$ban02) {
    header('Location: movimentacao.php');
    exit;
}
$ban02 = $ban02[0];
$data_formatada = (new DateTime($ban02->data))->format('d / m / Y');
$quantidade = filter_input(INPUT_GET, 'quantidade_parcelas') ?? null; 
if($quantidade != null) {
    $desmembramentos = [];
    for($i = 0; $i < $quantidade; $i++) {
        $novo_ban02 = new Ban02(
            null,
            $_SESSION['usuario']->id_empresa,
            $ban02->id_ban01,
            $ban02->data,
            $ban02->documento,
            null,
            null,
            $ban02->descricao,
            $ban02->descricao_comp,
            $ban02->valor,
            $ban02->id,
            $ban02->ativo
        );
        $desmembramentos[] = $novo_ban02;
    }
    if(empty($filtros)) {
        $caminho_alterado = $caminho . '?';
    } else {
        $caminho_alterado = $caminho . '&';
    }
    
}
}
?>
<div class="modal fade" id="modal_desmembrar" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header" style="display: flex; justify-content: center;">
                    <div class="card-header-borda">
                        <div class="d-flex flex-row gap-2">

                            <div class="d-flex flex-column">
                                <label>Documento:</label>
                                <input class="form-control" readonly value="<?=$ban02->documento?>"></input>
                            </div>
                            <div class="d-flex flex-column">
                                <label>Data:</label>
                                <input class="form-control" readonly value="<?=$data_formatada?>"></input>
                            </div>
                            <div class="d-flex flex-column">
                                <label>Valor:</label>
                                <input class="form-control" readonly value="<?=$ban02->valor?>"></input>
                            </div>
                        </div>
                        <div class="d-flex flex-row gap-2">
                            <div class="d-flex flex-column w-50">
                                <label>Descrição:</label>
                                <p type="text" class="form-control"<?php if($ban02->descricao == '') echo 'style=background-color:#ccc'?>><?=$ban02->descricao == '' ? 'Sem Descrição' : $ban02->descricao?></p>
                            </div>
                            <div class="d-flex flex-column w-50">
                                <label>Descrição Complementar:</label>
                                <input id="desc_comp_original_origem" type="text" class="form-control" style="height:55%" value="<?=$ban02->descricao_comp?>"></input>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="get" action="movimentacao.php">

                    <?php foreach($filtros_get as $i => $filtro) { ?>
                        <input type="hidden" name="<?=$i?>" value="<?=$filtro?>">
                    <?php } ?>
                    <input type="hidden" name="id" value="<?=$ban02->id?>"> 
                    <input type="hidden" name="acao" value="desmembrar"> 
                    

                    <div class="mb-3 d-flex fd-row gap-2" style="justify-content: center;">
                        <div class="w-100">
                            <label for="nomeBanco" class="form-label">Quantidade de desmembramentos</label>
                            <input type="text" id="nomeBanco" value="<?=$quantidade?>" name="quantidade_parcelas" class="form-control" placeholder="Quantidade" required>
                        </div>
                    </div>
                    <button type="submit" name="acao" value="desmembrar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Gerar</button>
                </form>
                <?php if($quantidade != null) { ?>
                <form method="post" action="movimentacao_manager.php">
                    <input type="hidden" name="id" value="<?=$get_id?>">
                    <input type="hidden" name="valor" value="<?=$ban02->valor?>">
                    <input type="hidden" name="caminho" value="<?=$caminho_alterado?>">
                    <input id="desc_comp_original_destino" type="hidden" name="descricao_comp_original" value="<?=$ban02->descricao_comp?>">
                    
                    <div style="display:flex; flex-direction:column;">
                    <?php 
                    $i = 0;
                    
                    foreach($desmembramentos as $ban02) {?>
                        <h5>Demembramento <?=$i+1?>:</h5>
                    <div class="d-flex flex-row">
                        <div class="w-25">
                            <label>Valor:</label>
                            <input class="form-control" type="text" placeholder="Valor" name="valor_desmembrado[<?=$i?>]" value=""></input>
                        </div>
                        <div class="w-75" >
                            <label>Descrição Complementar</label>
                            <input class="form-control" type="text" placeholder="Descrição Complementar" name="descricao_comp_desmembrado[<?=$i?>]" value=""></input>
                        </div>
                    </div>
                    <div>
                        
                    </div>  
                        <hr>
                    <?php
                    $i++;
                    } } ?>
                    </div>
                    
                    <!-- Botões -->
                     <?php if($quantidade != null) {?>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="acao" value="desmembrar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                    </div>
                    </form>
                    <?php } ?>
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        let origemInput = document.getElementById('desc_comp_original_origem')
        let destinoInput = document.getElementById('desc_comp_original_destino')
        if (origemInput && destinoInput) {
            // Para selects, datas e textos
            origemInput.addEventListener('input', function () {
                destinoInput.value = origemInput.value;
            });
        }
    </script>
