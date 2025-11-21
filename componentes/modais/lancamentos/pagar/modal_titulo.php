<div class="modal fade" id="modal_titulo" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Novo título</h5>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="conta">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="target" value="titulo">
                        <input type="hidden" name="acao" value="adicionar">
                        <input type="hidden" name="insta" value="pagar">

                        <div class="input-nome input-form-adm">
                            <!--Nome: -->
                            <label for="nome">Nome:</label>
                            <input type="text" onchange="checar()" name="nome" class="form-control" placeholder="Nome" value="" required>
                        </div>

                        <div class="input-nome input-form-adm">
                            <label for="nome">Tipo:</label>
                            <select name="tipo" class="form-select" id="tipo">
                                <option value="C">Crédito</option>
                                <option value="D">Débito</option>
                            </select>
                        </div>


                        <div style="margin-bottom: 3em;" class="footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;">Fechar</button>
                        <button class="btn btn-success" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Salvar</button>
                            

                    </form>


                </div>

            </div>
        </div>
    </div>