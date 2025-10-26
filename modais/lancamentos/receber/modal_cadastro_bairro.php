<div class="modal fade" id="modal_cadastro_bairro" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Novo bairro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="cadastro">
                        <input type="hidden" name="target" value="bairro">
                        <input type="hidden" name="insta" value="receber">
                        <input type="hidden" name="id" value="">
                        <label>Informe o nome do bairro </label>

                        <div class="input-nome input-form-adm">
                            <!-- Nome: -->
                            <input type="text"  name="nome" class="form-control" placeholder="Nome"
                                value=""  required>
                        </div>

                        <div style="margin-bottom: 3em;" class="footer">

                        <button name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" href="consulta_cliente.php">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                            

                    </form>


                </div>

            </div>
        </div>
    </div>