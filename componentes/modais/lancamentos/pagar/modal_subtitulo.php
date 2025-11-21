<div class="modal fade" id="modal_subtitulo" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Novo subtítulo</h5>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="conta">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="target" value="subtitulo">
                        <input type="hidden" name="insta" value="pagar">
                        <input type="hidden" name="acao" value="adicionar">

                        <div class="input-nome input-form-adm">
                            <!--Nome: -->
                            <label for="nome">Titulo:</label>
                            <select name="con01id">
                                <option>Selecione</option>
                                <?php foreach(Con01::read(idempresa:$_SESSION['usuario']->id_empresa, tipo: 'D') as $titulo){ ?>
                                    <option value="<?= $titulo->id ?>"><?=$titulo->nome?></option>
                                <?php } ?>
                            </select>

                        <div class="input-nome input-form-adm">
                            <!--Nome: -->
                            <label for="nome">Nome:</label>
                            <input type="text" onchange="checar()" name="nome" class="form-control" placeholder="Nome" value="" required>
                        </div>

                        <div style="margin-bottom: 3em;" class="footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;">Fechar</button>
                        <button class="btn btn-success" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Salvar</button>
                            

                    </form>


                </div>

            </div>
        </div>
    </div>