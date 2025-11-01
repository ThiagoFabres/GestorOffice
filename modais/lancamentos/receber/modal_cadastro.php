 
<div class="modal fade" id="modal_cadastro" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Cadastrar</h5>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="cadastro">
                        <input type="hidden" name="insta" value="receber">
                        <input type="hidden" name="target" value="cliente">
                        <input type="hidden" name="id" value="">
                        <label>Informe os dados do Cliente ou Fornecedor</label>

                        <div class="input-nome input-form-adm" style="width:100%;">
                            <!-- Razão social / Nome: -->
                            <input type="text" onchange="checar()" name="nome" class="form-control"
                                placeholder="Razão social / Nome" value="" required>
                        </div>



                        <div class="input-fantasia input-form-adm">
                            <!--Nome fantasia-->
                            <input type="text" onchange="checar()" name="fantasia" class="form-control"
                                placeholder="Nome fantasia" value="" required>
                        </div>



                        <div class="input-cpf input-form-adm">
                            <!--CPF-->
                            <input type="text" onchange="checar()" name="cpf" class="form-control" placeholder="CPF"
                                value="" >
                        </div>



                        <div class="input-cnpj input-form-adm">
                            <!--cnpj-->
                            <input type="text" onchange="checar()" name="cnpj" class="form-control" placeholder="CNPJ"
                                value="" >
                        </div>



                        <div class="input-cep input-form-adm">
                            <input type="text" onchange="checar()" name="cep" class="form-control" placeholder="CEP"
                                value="" >
                        </div>



                        <div class="input-endereco input-form-adm">
                            <input type="text" onchange="checar()" name="endereco" class="form-control"
                                placeholder="Endereço" value="" >
                        </div>

                        <div class="input-form-adm-group input-form-adm">
                            <div class="input-bairro input-select-geral ">
                                <div class="input-modal-div-select">
                                    <select id="bairro" name="bairro" class="form-control" >


                                    <option value="">Bairro</option>

                                    <?php foreach (Bairro::read(null, $_SESSION['usuario']->id_empresa) as $bairro) { ?>
                                        <option value="<?= $bairro->id ?>">
                                            <?= htmlspecialchars($bairro->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                </div>
                                <div class="input-modal-div-btn">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_bairro" type="button"
                                        class="form-control" id="btnModalBairro"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>



                            <div class="input-cidade input-select-geral ">
                                <div class="input-modal-div-select">
                                    <select id="cidade" name="cidade" class="form-control"
                                    >

                                    <option value="">Cidade</option>
                                    <?php foreach (Cidade::read(null, $_SESSION['usuario']->id_empresa) as $cidade) { ?>


                                        <option value="<?= $cidade->id ?>">
                                            <?= htmlspecialchars($cidade->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>

                                </select>
                                </div>
                                <div class="input-modal-div-btn">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_cidade" type="button"
                                        class="form-control" id="btnModalCidade"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>


                                
                            <div class="input-estado input-select-geral-estado">
                                
                                <div class="input-modal-div-select-estado">
                                <select id="estado" name="estado" class="form-control" >

                                    <option value="">Estado</option>
                                    <?php foreach ($estadosLista as $sigla => $estado) { ?>
                                        <option value="<?= $sigla ?>">
                                            <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                        </div>
                        </div>

                        <div class="input-form-contato-adm-group input-form-adm">



                            <div class="input-celular">
                                <input type="text" onchange="checar()" name="celular" class="form-control"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                    placeholder="Celular" value="" >
                            </div>



                            <div class="input-telefone">
                                <input type="text" onchange="checar()" name="fixo" class="form-control"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                    placeholder="Telefone Fixo" value="" >
                            </div>

                        </div>

                        <div class="input-email input-form-adm">
                            <input type="text" onchange="checar()" name="email" class="form-control"
                                placeholder="E-mail" value="" >
                        </div>

                         <div class="input-categoria input-select-geral">
                                <div class="input-modal-div-select-categoria">
                                    <select id="categoria" name="categoria" class="form-control"
                                    >

                                    <option value="">Categoria</option>
                                    <?php foreach (Categoria::read(null, $_SESSION['usuario']->id_empresa) as $categoria) { ?>


                                        <option value="<?= $categoria->id ?>">
                                            <?= htmlspecialchars($categoria->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>

                                </select>
                                </div>
                                <div class="input-modal-div-btn-categoria">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_categoria" type="button"
                                        class="form-control" id="btnModalCategoria"><i
                                            class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>






                        <div style="margin-bottom: 3em;" class="footer">

                            <button name="acao" value="adicionar" class="btn btn-success"
                                style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                href="consulta_cliente.php">Salvar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>


                    </form>


                </div>

            </div>
        </div>
    </div>
