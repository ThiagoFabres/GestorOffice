

<?php 
if($acao == 'conciliar') {
  $id = filter_input(INPUT_GET, 'id');
  $ban02 = Ban02::read($id)[0];
  $ban02_tipo = $ban02->valor < 0 ? 'D' : 'C';
}
?>
<div class="modal fade" id="modal_conciliar" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Conciliar Lançamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <form method="post" action="movimentacao_manager.php">

                    <input type="hidden" name="id" value="<?=$id?>">

                    <div class="d-flex fd-row gap-3" >
                    <div class="modal-input-group w-50">
                        <label for="titulo">Titulo</label>
                            <div class="titulo-group">
                                <div class="input-titulo" style="width:75%;">
                                    <!--Nome: -->
                                    
                                    <select name="titulo" class="form-control form-select-titulo" id="titulo"
                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0; ">
                                        <option value="">Selecione</option>

                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, $ban02_tipo);
                                        foreach ($titulos as $titulo) { ?>
                                            <option value="<?= $titulo->id ?>">
                                                <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="input-documento-generator" style="width:25%">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_titulo" type="button"
                                        class="form-control" id="btnModalCadastro"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>
                                                
                                            </div>

                    <div class="modal-input-group w-50 mb-3">
                        <label for="subtitulo">Sub-Titulo</label>
                        <div class="subtitulo-group">
                            <div class="input-subtitulo-div" style="width:75%;">

                                <select id="subtitulo" name="subtitulo" class="form-control form-select-titulo">
                                    <option value="">Selecione</option>
                                    <?php
                                    // Buscar todos os subtítulos da empresa
                                    $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                    foreach ($todosSubtitulos as $sub) { ?>
                                        <option value="<?= $sub->id ?>"

                                            data-titulo-id="<?= $sub->id_con01 ?>">
                                            <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="input-documento-generator" style="width:25%">
                            <button data-bs-toggle="modal" data-bs-target="#modal_subtitulo" type="button"
                                class="form-control" id="btnModalCadastro"><i
                                    class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>
                    </div>   
                </div>                             
                    
                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="acao" value="conciliar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

