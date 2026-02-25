<?php 
if($acao == 'editar' && $target == 'operadora') {
    $id = filter_input(INPUT_GET, 'id_ope01');
    $ope01 = Ope01::read($id, $_SESSION['usuario']->id_empresa)[0];
}
?>
<div class="modal fade" id="modal_adicionar_operadora" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel"><?= $acao == 'editar' ? 'Editar' : 'Nova' ?> Operadora</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="cartao_manager.php" onkeydown="return event.key != 'Enter';">
                <?php if($acao == 'editar' && $target == 'operadora') { ?>
                    <input type="hidden" id="id_operadora" name="id" value="<?php if($acao == 'editar') {echo $ope01->id;} ?>">
                <?php } ?>
                    <input type="hidden" name="target" value="operadora">
                    <div class="d-flex flex-column"> 
                        <div class="mb-3">
                            <label for="nomeOperadora" class="form-label">Informe o nome da Operadora</label>
                            <input type="text" id="nomeOperadora" name="nome" class="form-control" placeholder="Nome" value="<?=$ope01->descricao ?? ''?>" required>
                        </div>
                        <div class="d-flex flex-row mb-3">
                            <div class="d-flex flex-column w-50">
                                <label for="cadastro_operadora">Cadastro:</label>
                                <select name="cadastro">
                                    <option value="">Selecione</option>
                                    <?php foreach(Cadastro::read(id_empresa: $_SESSION['usuario']->id_empresa) as $cad) { ?>
                                        <option value="<?=$cad->id_cadastro?>" <?php if( isset($ope01) && $cad->id_cadastro == $ope01->id_cliente) {echo 'selected';}?>>
                                            <?=$cad->nom_fant?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="d-flex flex-column w-50">
                                <label for="cadastro_operadora">C. Custos:</label>
                                <select name="custos">
                                    <option value="">Selecione</option>
                                    <?php foreach(CentroCustos::read(id_empresa: $_SESSION['usuario']->id_empresa) as $cad) { ?>
                                        <option value="<?=$cad->id?>"  <?php if(isset($ope01) && $cad->id == $ope01->id_custos) {echo 'selected';}?>>
                                            <?=$cad->nome?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-row mb-3">
                            <div class="d-flex flex-column w-50">
                                <label for="cadastro_operadora">Titulo:</label>
                                <select name="titulo" id="titulo">
                                    <option value="" selected>Selecione</option>
                                    <?php foreach(Con01::read(idempresa: $_SESSION['usuario']->id_empresa, tipo: 'C') as $cad) { ?>
                                        <option value="<?=$cad->id?>" <?php if(isset($ope01) && $cad->id == $ope01->id_con01) {echo 'selected';}?>>
                                            <?=$cad->nome?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="d-flex flex-column w-50">
                                <label for="cadastro_operadora">Subtitulo:</label>
                                <select name="subtitulo" id="subtitulo">
                                    <option value="" selected>Selecione</option>
                                    <?php foreach(Con02::read(idempresa: $_SESSION['usuario']->id_empresa) as $cad) { ?>
                                        <option value="<?=$cad->id?>" data-titulo-id="<?=$cad->id_con01?>" <?php if( isset($ope01) && $cad->id == $ope01->id_con02) {echo 'selected';}?>>
                                            <?=$cad->nome?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Botões -->
                    <div class="d-flex <?php if($acao == 'editar') echo 'justify-content-between'; else echo 'justify-content-end'; ?> gap-2">
                        <?php if($acao == 'editar') { ?>
                        <div>
                            <button type="submit" name="acao" value="excluir" class="btn btn-danger">Excluir</button>
                        </div>
                        <?php } ?>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" name="acao" value="<?php if($acao == 'editar') echo $acao; else echo 'adicionar'; ?>" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>