<?php
if ($acao == 'editar') {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $conta = Ban01::read($id, $_SESSION['usuario']->id_empresa)[0];
}
?>
<div class="modal fade" id="modal_cadastro_conta" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel"><?php if($acao == 'editar') {echo 'Editar Conta Bancária';} else {echo 'Adicionar Nova Conta Bancária';}?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <?php if($_SESSION['usuario']->processar === 1) { ?>
                    <form method="post" action="conta_manager.php">
                    <?php } ?>
                    <?php if($acao == 'editar') { ?><input type="hidden" name="id" value="<?=$conta->id ?? ''?>"> <?php } ?>

                    <div class="mb-3 d-flex fd-row gap-2">
                        <div style="width: 65%;">
                            <label for="nomeBanco" class="form-label">Nome do banco</label>
                            <input type="text" id="nomeBanco" name="nome" class="form-control" maxlength="100" placeholder="Nome" value="<?php if($acao == 'editar'){echo $conta->nome ?? '';}?>" required>
                        </div>
                        <div style="width: 35%;">
                            <label for="codigoBanco" class="form-label">Código do banco</label>
                            <input type="text" id="codigoBanco" name="codigo" class="form-control" maxlength="3" placeholder="Código" value="<?php if($acao == 'editar'){echo $conta->banco ?? '';}?>" required>
                        </div>
                    </div>
                    <div class="mb-3 d-flex fd-row gap-2">
                        <div style="width:35%;">
                            <label for="agencia" class="form-label">Agência</label>
                            <input type="text" id="agencia" name="agencia" class="form-control" maxlength="15" placeholder="Agência" required value="<?php if($acao == 'editar') {echo $conta->agencia ?? '';}?>">
                        </div>
                        <div style="width:65%;">
                            <label for="numeroConta" class="form-label">Número da Conta</label>
                            <input type="text" id="numeroConta" name="numero_conta" class="form-control" maxlength="15" placeholder="Conta" required value="<?php if($acao == 'editar') {echo $conta->conta ?? '';}?>">
                        </div>
                    </div>
                    <div class="mb-3 d-flex fd-row gap-2">
                        <div style="width:65%;">
                            <label for="agencia" class="form-label">Saldo</label>
                            <input type="text" id="valor" name="valor" class="form-control" placeholder="Saldo" required value="<?php if($acao == 'editar') {echo number_format($conta->valor, 2, ',', '.') ?? '';}?>">
                        </div>
                        <div style="width:35%;">
                            <label for="numeroConta" class="form-label">Data</label>
                            <input type="date" id="data" name="data" class="form-control" placeholder="Data" required value="<?php if($acao == 'editar') {echo $conta->data ?? (new DateTime())->format('Y-m-d');}?>">
                        </div>
                    </div>
                    
                    <!-- Botões -->
                    <?php if($_SESSION['usuario']->processar === 1) { ?>
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <button type="submit" name="acao" value="excluir" class="btn btn-danger">Excluir</button>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" name="acao" value="<?php if(isset($acao) && $acao == 'editar') { ?>editar<?php } else {?>adicionar<?php } ?>" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if($_SESSION['usuario']->processar === 1) { ?>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>