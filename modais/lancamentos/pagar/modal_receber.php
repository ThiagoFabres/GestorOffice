<div class="modal fade" id="modal_receber" tabindex="-1" role="dialog" aria-labelledby="modal_receber_title"
            aria-hidden="true">
            <div class=" modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 class="modal-title" id="modal_receber_long_title">Novo Lançamento</h5>
                    </div>
                    <div class="modal-body">

                        <form method="post" id="content" action="editar-pagar.php"
                            onkeydown="return event.key != 'Enter';">
                            <input type="hidden" name="view" value="pagar">
                            <input type="hidden" name="pagar" value="1">

                            <label for="documento">Documento:</label>
                            <div class="input-documento-group">
                                <div class="input-documento input-form-adm">
                                    <!--Nome: -->

                                    <input type="text"  onchange="checar()" name="documento" id="documento"
                                        class="form-control" placeholder="Documento" value="" required>
                                </div>

                                <div class="input-documento-generator">
                                    <button type="button" class="form-control" id="btnBuscarDoc"><i
                                            class="bi bi-text-center"></i></button>
                                </div>
                            </div>
                            <label for="cadastro">Cliente / Fornecedor:</label>
                            <div class="input-documento-group">
                                <div class="input-documento input-form-adm">
                                    <!--Nome: -->

                                    <select name="cadastro" class="form-select" id="cadastro"
                                        style="border-top-right-radius:0; border-bottom-right-radius:0;">
                                        <option value="">Selecione</option>

                                        <?php
                                        $cadastros = Cadastro::read(null, null, $_SESSION['usuario']->id_empresa);
                                        foreach ($cadastros as $cadastro) { ?>
                                            <option value="<?= $cadastro->id_cadastro ?>">
                                                <?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="input-documento-generator">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro" type="button"
                                        class="form-control" id="btnModalCadastro"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>


                            <div class="input-valor input-form-adm">
                                <!--Nome: -->
                                <label for="valor">Valor:</label>
                                <input type="text" onchange="checar()" name="valor" class="form-control"
                                    placeholder="Valor" value="" required>
                            </div>

                            <div class="input-parcelas input-form-adm">
                                <!--Nome: -->
                                <label for="parcelas">Parcelas:</label>
                                <input type="number" onchange="checar()" name="parcelas" class="form-control"
                                    placeholder="Parcelas" value="" required>
                            </div>

                            <div class="input-descricao input-form-adm">
                                <!--Nome: -->
                                <label for="descricao">Descrição:</label>
                                <input type="text" onchange="checar()" name="descricao" class="form-control"
                                    placeholder="Descrição" value="" required>
                            </div>


                            <div class="titulos-receber" style="display:flex; flex-direction: row;">

                                <div class="input-titulo input-form-adm" style="width: 50%;">
                                    <!--Nome: -->
                                    <label for="titulo">Titulo</label>
                                    <select name="titulo" class="form-select" id="titulo"
                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                        <option value="">Selecione</option>
                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, 'D');
                                        foreach ($titulos as $titulo) { ?>
                                            <option value="<?= $titulo->id ?>">
                                                <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="input-subtitulo input-form-adm" style="width: 50%;">
                                    <!--Nome: -->
                                    <label for="subtitulo">Sub-Titulo</label>
                                    <select id="subtitulo" name="subtitulo" class="form-control">
                                        <option value="">Selecione</option>
                                        <?php
                                        // Buscar todos os subtítulos da empresa
                                        $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                        foreach ($todosSubtitulos as $sub) { ?>
                                            <option value="<?= $sub->id ?>" data-titulo-id="<?= $sub->id_con01 ?>">
                                                <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div style="margin-bottom: 3em;" class="footer">


                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button name="acao" value="adicionar" class="btn btn-success"
                                    style="float:right; background-color: #5856d6; border: #5856d6; "
                                    href="consulta_cliente.php">Salvar</button>

                        </form>


                    </div>

                </div>
            </div>
        </div>