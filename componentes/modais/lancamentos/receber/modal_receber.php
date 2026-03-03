<div class="modal fade" id="modal_receber" tabindex="-1" role="dialog" aria-labelledby="modal_receber_title"
            aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width:1100px;">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 class="modal-title" id="modal_receber_long_title">Novo Lançamento</h5>
                    </div>
                    <?php
    $get_acao = filter_input(INPUT_GET, 'acao');
    $id_ban = filter_input(INPUT_GET, 'id_ban') ?? filter_input(INPUT_POST, 'id_ban') ?? null;
    if(isset($id_ban) && $id_ban != null) {
        $ban02 = Ban02::read($id_ban, $_SESSION['usuario']->id_empresa)[0];
        $ban02_valor = $ban02->valor < 0 ? $ban02->valor * (-1) : $ban02->valor;
        $ban02_valor = number_format($ban02_valor, 2, ',', '.');
        if($ban02->descricao_comp != '') {
            $ban02_desc = "$ban02->descricao - $ban02->descricao_comp";
        } else {
            $ban02_desc = "$ban02->descricao";
        }
        
        $ban02_desc = htmlspecialchars($ban02_desc, ENT_QUOTES, 'UTF-8');
    }
    if($get_acao == 'editar' && (!isset($get_id) || $get_id == null)) {
        $get_id= filter_input(INPUT_POST, 'id');
    }
                    
    if (isset($get_id)) {
    $recebimento = Rec01::read($get_id, $_SESSION['usuario']->id_empresa)[0] ?? null;
    $parcelas_pagas = false;
    if($recebimento != null) {
        for ($i = 1; $i < $recebimento->parcelas; $i++) {
        $rec02 = Rec02::read(null, $_SESSION['usuario']->id_empresa, $recebimento->id, null, $i, )[0];
        if ($parcelas_pagas == false && $rec02->valor_pag > 0) {
            $parcelas_pagas = true;
            break;
        } else {
            continue;

        }
    }
    
    }
    if($get_acao != 'visualizar'){
        if ($parcelas_pagas == true || empty(Rec01::read($get_id, $_SESSION['usuario']->id_empresa))) {
        header('Location: index.php?');
        exit;
    }
    }
    
    
}
    
    


    $post_cadastro = filter_input(INPUT_POST, 'cadastro') ?? '';
    if($post_cadastro == '' && isset($recebimento)) {
        $post_cadastro = $recebimento->id_cadastro;
    }

    $post_titulo = filter_input(INPUT_POST, 'titulo') ?? '';
    if($post_titulo == '' && isset($recebimento)) {
        $post_titulo = $recebimento->id_con01;
    }

    $post_subtitulo = filter_input(INPUT_POST, 'subtitulo') ?? '';
    if($post_subtitulo == '' && isset($recebimento)) {
        $post_subtitulo = $recebimento->id_con02;
    }

    $documento = filter_input(INPUT_POST, 'documento') ?? '';
    if($documento == '') {

    if(isset($recebimento)) {
        $documento = $recebimento->documento ?? '';
    } else {
        $documento = $novo_documento;
    }
}

    $post_custo = filter_input(INPUT_POST, 'custo') ?? '';
    if($post_custo == '' && isset($recebimento)) {
        $post_custo = $recebimento->centro_custos;
    }


    $valor = filter_input(INPUT_POST, 'valor');
    if($valor != null) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    }
    if(!isset($valor) || $valor == null) {
        $valor = $recebimento->valor  ?? '';
    }

    if($valor != '') $valor = floatval($valor);
    $parcelas_d = filter_input(INPUT_POST, 'parcelas') ?? $parcelas_d = $recebimento->parcelas ?? '';
    if ($parcelas_d != '' )$parcelas_d = intval($parcelas_d);
    $descricao = filter_input(INPUT_POST, 'descricao') ?? $descricao = $recebimento->descricao ?? '';

    if ($get_id != null && isset($recebimento)) {

        if(empty($_POST['data_lanc'])) {
            $data_lanc = $recebimento->data_lanc;
        } else {
            $data_lanc = (new DateTime(filter_input(INPUT_POST, 'data_lanc')))->format('Y-m-d');
        }
    } else {
        $data_lanc = (new DateTime(filter_input(INPUT_POST, 'data_lanc')))->format('Y-m-d') ?? $recebimento->data_lanc ?? (new DateTime())->format('Y-m-d');
    }


    if (!isset($get_id)) {

        $recebimento = new Rec01(
            null,
            $_SESSION['usuario']->id_empresa,
            $post_cadastro,
            $post_titulo,
            $post_subtitulo,
            $documento,
            $descricao,
            $valor,
            $parcelas_d,
            null,
            $_SESSION['usuario']->id,
        );

        $parcelas = [];
        if ($parcelas_d && $valor) {
            $data_vencimento = new DateTime($data_lanc);

            for ($parcela_n = 1; $parcela_n < $recebimento->parcelas + 1; $parcela_n++, $data_vencimento = $data_vencimento->modify('+1 month')) {
                $parcelas[$parcela_n] = new Rec02(
                    null,
                    $_SESSION['usuario']->id_empresa,
                    null,
                    round($valor / $parcelas_d, 2),
                    $parcela_n,
                    clone $data_vencimento,
                    0,
                    null
                );
            }
        }

    } else if (isset($get_id)) {






        if (!empty($_GET['id'])) {
            $parcelas = Rec02::read(null, $_SESSION['usuario']->id_empresa, $recebimento->id, ordenar_por: 'parcela');
        } else {

            $parcelas = [];
            if ($parcelas_d && $valor) {
                $data_vencimento = new DateTime($data_lanc);

                for ($parcela_n = 1; $parcela_n < $parcelas_d + 1; $parcela_n++, $data_vencimento = $data_vencimento->modify('+1 month')) {

                    $parcelas[$parcela_n] = new Rec02(
                        null,
                        $_SESSION['usuario']->id_empresa,
                        null,
                        round($valor / $parcelas_d, 2),
                        $parcela_n,
                        clone $data_vencimento,
                        0,
                        null
                    );
                }
            }
        }
        ;



    }
    if($valor != '')$valor = number_format($valor, 2, ',', '.');
    if ($data_lanc == null) {
        $data_lanc = new Datetime();
        $data_lanc = $data_lanc->format('Y-m-d');
    }
    if(isset($id_ban) && $id_ban != null) {
        $data_lanc = $ban02->data;
    }
    ?>

        <div class="tabela">

            <div class="row">
                <div class="col-md-12" style="padding: 0;">
                    <div class="card" style="padding: 0;">
                <?php ?>
                <form method="post" action>
                        <div class="card-header-div">
                            <div class="mb-3 gap-2">
                            <label for="agencia" class="form-label">Arquivo Excel</label>
                            <input type="file"
                            onchange="this.form.submit()"
                            accept=".xlsx, .xls, .csv" id="agencia" name="vendas_excel"
                            class="form-control" placeholder="Agência"
                            >
                        </div>
                </form>
                            <div class="card-header-borda">
                                <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                    aria-labelledby="vendas-tab">
                                    <form method="post" action="receber.php?acao=editar"
                                        onkeydown="return event.key != 'Enter';">
                                        <?php if (isset($get_id)) { ?>
                                            <input type="hidden" name="id" readonly value="<?= $get_id ?>">
                                        <?php } ?>
                                        <?php if (isset($id_ban) && $id_ban != null) { ?>
                                            <input type="hidden" name="id_ban" readonly value="<?= $id_ban ?>">
                                        <?php } ?>
                                        <div class="row">
                                            <div class="input-group-edit-parcela"style="width: 100%; position:relative; display:flex; flex-direction:row; justify-content: space-between;">
                                            <div class="modal-input-group">
                                                <label for="documento">Documento:</label>
                                                <div class="input-documento-group" style="display: flex; flex-direction: row;">
                                                    <div class="input-documento" style="width:100%;">
                                                        <!--Nome: -->
                                                    
                                                        <input type="text" onchange="checar()" name="documento"<?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?>
                                                            class="form-control" placeholder="Documento" id="documento"
                                                            value="<?= $documento ?>" readonly
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                                

                                                
                                            <div class="modal-input-group">
                                                <label for="centro">Centro de custos:</label>
                                                <div class="input-documento-group">
                                                        <div class="input-custo" style="<?php if($get_acao != 'visualizar') {?>width:75%;<?php } else {?> width: 100%; <?php } ?>"> 
                                                            <select name="custo" class="form-select" id="custo" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?> >
                                                                <option value="">Selecione</option>


                                                                <?php

                                                                $custos = CentroCustos::read( null, $_SESSION['usuario']->id_empresa);
                                                                foreach ($custos as $custo) { ?>
                                                                    <option value="<?= $custo->id ?>" <?php if (($post_custo == $custo->id)) { ?> selected <?php } ?>>
                                                                        <?= htmlspecialchars($custo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <?php if($get_acao != 'visualizar'){ ?>
                                                        <div class="input-documento-generator" style="width:25%">
                                                            <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_custos" type="button" class="form-control" id="btnModalCustosPagar" data-bs-dismiss="modal">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                        </div>
                                                        <?php } ?>
                                                    </div>
                                            </div>

                                            <div class="modal-input-group">
                                                <label for="cadastro">Cliente / Fornecedor:</label>
                                                    <div class="input-documento-group">
                                                        <div class="input-cadastro" style="<?php if($get_acao != 'visualizar') {?>width:75%;<?php } else {?> width: 100%; <?php } ?>"> 
                                                            <select name="cadastro" class="form-select" id="cadastro" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?>>
                                                                <option value="">Selecione</option>


                                                                <?php

                                                                $cadastros = Cadastro::read($id_passado, null, $_SESSION['usuario']->id_empresa);
                                                                foreach ($cadastros as $cadastro) { ?>
                                                                    <option value="<?= $cadastro->id_cadastro ?>" <?php if (($post_cadastro == $cadastro->id_cadastro)) { ?> selected <?php } ?>>
                                                                        <?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <?php if($get_acao != 'visualizar'){ ?>
                                                        <div class="input-documento-generator" style="width:25%">
                                                            <button data-bs-toggle="modal" data-bs-target="#modal_cadastro" type="button" class="form-control" id="btnModalCadastroPagar" data-bs-dismiss="modal">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                        </div>
                                                        <?php } ?>
                                                    </div>
                                            </div>
                                            
                                            <div class="modal-input-group">
                                                <label for="data_lanc">Data de lançamento:</label>
                                                <div class="input-data-lanc">
                                                    <input type="date"
                                                    <?php if($id_ban != null) echo 'readonly'?>
                                                    onchange="checar()" name="data_lanc" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?>
                                                        class="form-control" placeholder="Data de lançamento"
                                                        value="<?php echo $data_lanc ?>" required>
                                                </div>
                                            </div>

                                            </div>
                                            

                                            <div class="titulos-receber" style="display:flex; flex-direction: row; justify-content: space-between; width: 100%;">

                                            <div class="modal-input-group">
                                                <label for="titulo">Titulo</label>
                                            <div class="titulo-group">
                                                <div class="input-titulo" style="<?php if($get_acao != 'visualizar' && $id_ban == null) {?>width:75%;<?php } else {?>width: 100%; height:92%<?php } ?>">
                                                    <!--Nome: -->
                                                    
                                                    <select name="titulo" class="form-control form-select-titulo" style="height:100%; border-radius: 0;" id="titulo" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?> <?php if($id_ban != null) echo 'readonly' ?>> 
                                                        <option value="">Selecione</option>

                                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, 'C');
                                                        foreach ($titulos as $titulo) { ?>
                                                            <option value="<?= $titulo->id ?>" <?php if (($titulo->id == $post_titulo) || ($id_ban != null && $ban02->id_con01 == $titulo->id)) { ?> selected <?php } ?>>
                                                                <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <?php if($get_acao != 'visualizar'){ ?>
                                                <div class="input-documento-generator" style="width:25%">
                                                    <button data-bs-toggle="modal" data-bs-target="#modal_titulo" type="button"
                                                        class="form-control" id="btnModalCadastro"><i
                                                            class="bi bi-plus-lg"></i></button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                                
                                            </div>
                                            
                                            <div class="modal-input-group">
                                                <label for="subtitulo">Sub-Titulo</label>
                                                <div class="subtitulo-group">
                                                    <div class="input-subtitulo-div" style="<?php if($get_acao != 'visualizar' && $id_ban == null) {?>width:75%;<?php } else {?>width: 100%; height: 92%;<?php } ?>">

                                                        <select id="subtitulo" name="subtitulo" class="form-control form-select-titulo" style="height: 100%; border-radius: 0;" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?> <?php if($id_ban != null) echo 'readonly' ?>>
                                                            <option value="">Selecione</option>
                                                            <?php
                                                            // Buscar todos os subtítulos da empresa
                                                            $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                                            foreach ($todosSubtitulos as $sub) { ?>
                                                                <option value="<?= $sub->id ?>"
                                                                    data-titulo-id="<?= $sub->id_con01 ?>" <?php if (($sub->id == $post_subtitulo) || ($id_ban != null && $ban02->id_con02 == $sub->id)) { ?> selected <?php } ?>>
                                                                    <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <?php if($get_acao != 'visualizar'){ ?>
                                                    <div class="input-documento-generator" style="width:25%">
                                                    <button data-bs-toggle="modal" data-bs-target="#modal_subtitulo" type="button"
                                                        class="form-control" id="btnModalCadastro"><i
                                                            class="bi bi-plus-lg"></i></button>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                                <div class="modal-input-group">
                                                        <label for="valor">Valor:</label>         
                                                    <div class="input-valor">
                                                        <input type="text" id="valor" onchange="checar()" name="valor" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?>
                                                            class="form-control" placeholder="Valor"
                                                            <?php if($id_ban != null) echo 'readonly'?>
                                                            value="<?php if($id_ban != null) echo $ban02_valor; else echo $valor?>"
                                                            required>
                                                    </div>
                                                </div>  
                                                <div class="modal-input-group">
                                                    <label for="parcelas">Parcelas:</label>                   
                                                    <div class="input-parcelas">   
                                                        <input type="number" id="parcelas" onchange="checar()" name="parcelas" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?> <?php if($id_ban != null) echo 'readonly'?>
                                                            class="form-control" placeholder="Parcelas"
                                                            value="<?php if($id_ban != null) echo 1; else echo $parcelas_d?>"
                                                            required>
                                                    </div>
                                                </div> 
                                            </div>

                                            <div class="input-descricao" style="width: 100%;">
                                                <!--Nome: -->
                                                <label for="descricao">Descrição:</label>
                                                <input type="text" onchange="checar()" <?php if($id_ban != null) echo 'readonly'?> id="descricao" name="descricao" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?>
                                                    class="form-control" placeholder="Descrição"
                                                    value="<?php if($id_ban != null) echo $ban02_desc; else echo htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8')?>"
                                                    required>
                                            </div>





                                        <?php if($get_acao != 'visualizar' && !isset($recebimento->valor_b) || $recebimento->valor_b == 0){ ?>                            
                                            <div style="width: 100%;">
                                                <button type="submit" class="btn btn-primary mt-4"
                                                    style="float:right; background-color: #5856d6; border: 0;">Gerar
                                                    parcelas</button>
                                            </div>
                                        <?php } ?>
                                        </div>
                                    </form>
<?php if(!empty($parcelas)){ ?>


                                </div>
                            </div>
                        </div>

                        <form action="cadastros_manager.php" method="post" onkeydown="return event.key != 'Enter';">
                            <input type="hidden" name="view" value="receber">
                            <input type="hidden" name="id_lancamento" value="<?= $get_id ?>">
                            <?php if (isset($id_ban) && $id_ban != null) { ?>
                                <input type="hidden" name="id_ban" readonly value="<?= $id_ban ?>">
                            <?php } ?>
                            <input type="hidden" id="cadastro2" name="cadastro" value="<?= $post_cadastro ?>">
                            <input type="hidden" id="titulo2" name="titulo" value="<?= $post_titulo ?>">
                            <input type="hidden" id="subtitulo2" name="subtitulo" value="<?= $post_subtitulo ?>">
                            <input type="hidden" id="documento2" name="documento" value="<?= $documento ?>">
                            <input type="hidden" id="descricao2" name="descricao" value="<?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8')?>">
                            <input type="hidden" id="valor2" name="valor" value="<?= $valor ?>">
                            <input type="hidden" id="parcelas_d"name="parcelas_d" value="<?= $parcelas_d ?>">
                            <input type="hidden" id="data_lanc2" name="data_lanc" value="<?= $data_lanc ?>">
                            <input type="hidden" id="custo2" name="custo" value="<?= $post_custo ?>">




                            <div class="tabelas-edit">
                                <table class="table table-striped table-bordered" style="margin-bottom: 0;">
                                    <thead>
                                        <tr>
                                            <th>Parcela</th>
                                            <th>Vencimento</th>
                                            <th>Valor</th>
                                            <th>OBS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($parcelas as $parcela) {
                                            $valor_parcela = number_format($parcela->valor_par, 2, ',', '.');
                                            ?>
                                            <!-- <?php if (isset($get_id) && !empty($get_id)) { ?> <input type="hidden" name="id[<?= $parcela->parcela ?>]" value="<?= $parcela->id ?>"> <?php } ?> -->
                                            <input type="hidden" name="parcela[<?= $parcela->parcela ?>]"
                                                value="<?= $parcela->parcela ?>">
                                            <tr>
                                                <td><?= htmlspecialchars($parcela->parcela, ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><input type="date" class="form-control" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?><?php if($id_ban != null) echo 'readonly'?>
                                                        name="vencimento[<?= $parcela->parcela ?>]"
                                                        value="<?php if (!isset($get_id) || empty($_GET['id'])) {
                                                            echo $parcela->vencimento->format('Y-m-d');
                                                        } else
                                                            echo $parcela->vencimento ?>"
                                                            placeholder="vencimento"></td>
                                                    <td><input class="form-control valor-parcela vencimento" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?> <?php if($id_ban != null) echo 'readonly'?>
                                                            name="valor_par[<?= $parcela->parcela ?>]"
                                                        value="<?= $valor_parcela; ?>"></input></td>
                                                <td><input class="form-control" name="obs02[<?= $parcela->parcela ?>]" <?php if($get_acao == 'visualizar'){ ?> disabled <?php } ?>
                                                        value="<?= $parcela->obs ?>"></input></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                    <table class="table table-striped table-bordered">

                                        <thead>
                                            <tr>
                                                <?php if($get_acao != 'visualizar') {?><th style="width:50%;" id="total_parcelas">Valor das parcelas</th> <?php } ?>
                                                <th>Valor Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <?php if($get_acao != 'visualizar') {?><td>R$ <span id="totalParcelas"></span></td><?php } ?>
                                                <td id="valorEsperadoCell">R$ <?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        </tbody>

                                    </table>
                                </table>
                            </div>
                            <div class="card-footer" style="display:flex; flex-direction: row; width: 100%; justify-content: space-between;">
                            <?php if((!empty($parcelas) && $get_acao != 'visualizar') && !isset($recebimento->valor_b) || $recebimento->valor_b == 0){ ?>
                                <button name="acao"
                                    value="excluir"
                                    class="btn btn-danger" type="submit" id="botao-excluir-parcela"
                                    style="padding-inline:1.5em;">Excluir</button>
                                <button name="acao"
                                    value="<?php if (!isset($get_acao)) { ?>adicionar<?php } else if (isset($get_acao)) { ?>editar<?php } ?>"
                                    class="btn btn-primary" type="submit" id="botao-editar-parcela"
                                    style="padding-inline:1.5em; ">Salvar</button>
                            <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

    <script>
document.addEventListener("DOMContentLoaded", function () {
        const inputs = Array.from(document.querySelectorAll("input[name^='vencimento[']"));

        inputs.forEach((input, index) => {
            input.addEventListener("change", function () {
                const atual = this.value;

                // checa com anterior
                if (index > 0 && atual <= inputs[index - 1].value) {
                    alert(`A data da parcela ${index + 1} não pode ser menor ou igual que a da parcela ${index}`);
                    this.value = "";
                    return;
                }
                // checa com próxima
                if (index < inputs.length - 1 && atual >= inputs[index + 1].value) {
                    alert(`A data da parcela ${index + 1} não pode ser maior ou igual que a da parcela ${index + 2}`);
                    this.value = "";
                }
            });
        });
    });

    // Validação adicional: nenhuma data de vencimento pode ser menor que a data de lançamento
    // Também controla o estado do botão Salvar junto com a validação de soma das parcelas
    (function () {
        // adiciona estilo para inputs inválidos
        var style = document.createElement('style');
        style.innerHTML = '.invalid-date { outline: 2px solid #d9534f; } .error-msg { color: #d9534f; margin-top:0.5rem; }';
        document.head.appendChild(style);

        // cria elemento de mensagem de erro se não existir
        function ensureDateErrorEl() {
            var el = document.getElementById('dateErrorMsg');
            if (!el) {
                el = document.createElement('div');
                el.id = 'dateErrorMsg';
                el.className = 'error-msg';
                // tenta inserir abaixo da tabela de parcelas
                var tabela = document.querySelector('.tabelas-edit');
                if (tabela) tabela.parentNode.insertBefore(el, tabela.nextSibling);
                else document.body.appendChild(el);
            }
            return el;
        }

        function parseDateInput(val) {
            if (!val) return null;
            // cria Date no timezone local para comparação de datas
            var parts = val.split('-');
            if (parts.length !== 3) return null;
            return new Date(parts[0], parts[1] - 1, parts[2]);
        }

        function validarDatas() {
            var dataLancInput = document.querySelector('input[name="data_lanc"]');
            var dataLancVal = dataLancInput ? dataLancInput.value : null;
            var dateErrorEl = ensureDateErrorEl();
            dateErrorEl.textContent = '';

            var valid = true;
            var dataLanc = parseDateInput(dataLancVal);

            var vencimentos = Array.from(document.querySelectorAll("input[name^='vencimento[']"));
            vencimentos.forEach(function (inp) {
                inp.classList.remove('invalid-date');
                if (!inp.value) return;
                if (!dataLanc) return; // sem data de lançamento definida, ignora esta regra
                var v = parseDateInput(inp.value);
                if (!v) return;
                if (v < dataLanc) {
                    inp.classList.add('invalid-date');
                    valid = false;
                }
            });

            if (!valid) {
                dateErrorEl.textContent = 'Uma ou mais parcelas possuem data de vencimento anterior à data de lançamento.';
            }

            return valid;
        }

        function atualizarTotalParcelas() {
            let total = 0;
            document.querySelectorAll('.valor-parcela').forEach(function (input) {
                // remove pontos de milhares e normaliza vírgula para ponto
                let rawVal = (input.value || '').toString().trim();
                let valClean = rawVal.replace(/\./g, '').replace(',', '.');
                let val = parseFloat(valClean);
                if (!isNaN(val)) total += val;
                // opcional: log por input
                // console.log('input raw', rawVal, 'clean', valClean, 'num', val);
            });
            document.getElementById('totalParcelas').textContent = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Pega o valor total esperado do campo oculto ou célula da tabela
            let valorTotal = document.querySelector('input[name="valor"]');
            let valorEsperado = 0;
            if (valorTotal) {
                var raw = (valorTotal.value || '').toString().trim();
                if (raw !== '') valorEsperado = parseFloat(raw.replace(/\./g, '').replace(',', '.'));
            }
            else {
                // Alternativa: pega da célula da tabela com id específico (mais confiável)
                let celula = document.getElementById('valorEsperadoCell');
                if (celula) {
                    var txt = (celula.textContent || '').replace(/[^0-9,\.]/g, '').trim();
                    // remove pontos de milhares e usa ponto como separador decimal
                    txt = txt.replace(/\./g, '').replace(',', '.');
                    valorEsperado = parseFloat(txt);
                    console.log('valorEsperado(from cell):', valorEsperado);
                }
            }

            var totalValido = false;
            if (!isNaN(valorEsperado) && typeof valorEsperado === 'number') {
                var diff = Math.abs(total - valorEsperado);
                totalValido = diff <= 0;
            } else {
                totalValido = (total === 0 && (isNaN(valorEsperado) || valorEsperado === 0));
            }

            console.log('DEBUG atualizarTotalParcelas', { total: total, valorEsperado: valorEsperado, diff: (Math.abs(total - (valorEsperado||0))), totalValido: totalValido });

            atualizarEstadoBotaoSalvar(totalValido);
        }

        function atualizarEstadoBotaoSalvar(/* totalValido param ignored - function recomputes */) {
            var botao = document.getElementById('botao-editar-parcela');
            if (!botao) return;

            // Recompute totals here (single source of truth)
            var totalSum = 0;
            document.querySelectorAll('.valor-parcela').forEach(function (input) {
                var rawVal = (input.value || '').toString().trim();
                var clean = rawVal.replace(/\./g, '').replace(',', '.');
                var num = parseFloat(clean);
                if (!isNaN(num)) totalSum += num;
            });

            // parse expected value from hidden input or fallback cell
            var valorEsperadoLog = NaN;
            var valorTotalInput = document.querySelector('input[name="valor"]');
            if (valorTotalInput) {
                var raw = (valorTotalInput.value || '').toString().trim();
                if (raw !== '') valorEsperadoLog = parseFloat(raw.replace(/\./g, '').replace(',', '.'));
            }
            if (isNaN(valorEsperadoLog)) {
                var cel = document.getElementById('valorEsperadoCell');
                if (cel) {
                    var txt = (cel.textContent || '').replace(/[^0-9,\.]/g, '').trim();
                    txt = txt.replace(/\./g, '').replace(',', '.');
                    valorEsperadoLog = parseFloat(txt);
                }
            }

            var datasValidas = validarDatas();

            if (typeof totalValido === 'undefined') {
                // recomputa totalValido caso não tenha sido fornecido
                let total = 0;
                document.querySelectorAll('.valor-parcela').forEach(function (input) {
                    let val = input.value.replace('.', '')
                    val = parseFloat(val.replace(',', '.'));
                    if (!isNaN(val)) total += val;
                });

                let valorTotal = document.querySelector('input[name="valor"]');
                let valorEsperado = NaN;
                if (valorTotal) {
                    var raw = (valorTotal.value || '').toString().trim();
                    if (raw !== '') {
                        valorEsperado = parseFloat(raw.replace(/\./g, '').replace(',', '.'));
                    }
                }
                if (isNaN(valorEsperado)) {
                    // fallback para a célula formatada
                    let celula = document.getElementById('valorEsperadoCell');
                    if (celula) {
                        var txt = (celula.textContent || '').replace(/[^0-9,\.]/g, '').trim();
                        txt = txt.replace(/\./g, '').replace(',', '.');
                        valorEsperado = parseFloat(txt);
                    }
                }

                if (isNaN(valorEsperado)) {
                    // sem valor esperado válido: se não houver parcelas, considera válido; caso contrário inválido
                    totalValido = (total === 0);
                } else {
                    totalValido = Math.abs(total - valorEsperado) <= 0;
                }
            }

            // round to cents to avoid floating point noise
            var totalRounded = Math.round(totalSum * 100) / 100;
            var esperadoRounded = isNaN(valorEsperadoLog) ? NaN : Math.round(valorEsperadoLog * 100) / 100;
            var totalValidoCalc = false;
            if (!isNaN(esperadoRounded)) {
                totalValidoCalc = Math.abs(totalRounded - esperadoRounded) <= 0;
            } else {
                totalValidoCalc = (totalRounded === 0);
            }

            console.log('DEBUG atualizarEstadoBotaoSalvar', { totalSum: totalSum, totalRounded: totalRounded, valorEsperado: valorEsperadoLog, esperadoRounded: esperadoRounded, totalValidoCalc: totalValidoCalc, datasValidas: datasValidas });

            if (!totalValidoCalc || !datasValidas) {
                botao.disabled = true;
                botao.title = (!totalValidoCalc ? 'A soma das parcelas deve ser igual ao valor total' : '') + (!datasValidas ? ' ' + 'Verifique as datas de vencimento' : '');
            } else {
                botao.disabled = false;
                botao.title = '';
            }
        }

        // ligar ouvintes
        document.querySelectorAll('.valor-parcela').forEach(function (input) {
            input.addEventListener('input', function () { atualizarTotalParcelas(); });
        });

        document.querySelectorAll("input[name^='vencimento[']").forEach(function (input) {
            input.addEventListener('change', function () {
                // já existe verificação de ordenação; além disso valida contra data de lançamento
                validarDatas();
                atualizarEstadoBotaoSalvar();
            });
        });

        var dataLanc = document.querySelector('input[name="data_lanc"]');
        if (dataLanc) {
            dataLanc.addEventListener('change', function () {
                validarDatas();
                atualizarEstadoBotaoSalvar();
            });
        }

        // inicializa estado
        atualizarTotalParcelas();
        validarDatas();
        atualizarEstadoBotaoSalvar();
    })();


function syncHiddenInputs() {
    const campos = [
        { origem: 'cadastro', destino: 'cadastro2' },
        { origem: 'titulo', destino: 'titulo2' },
        { origem: 'subtitulo', destino: 'subtitulo2' },
        { origem: 'documento', destino: 'documento2' },
        { origem: 'descricao', destino: 'descricao2' },
        { origem: 'valor', destino: 'valor2' },
        { origem: 'parcelas', destino: 'parcelas_d' },
        { origem: 'data_lanc', destino: 'data_lanc2' },
        { origem: 'custo', destino: 'custo2' }
    ];



    campos.forEach(({ origem, destino }) => {
        const origemInput = document.getElementById(origem);
        const destinoInput = document.getElementById(destino);
        const botao = document.getElementById('botao-editar-parcela')

        if (origemInput && destinoInput) {
            // Para selects, datas e textos
            origemInput.addEventListener('input', function () {
                destinoInput.value = origemInput.value;
            });
            origemInput.addEventListener('change', function () {
                if(origemInput.id != 'valor' && origemInput.id != 'parcelas') {
                    destinoInput.value = origemInput.value;
                    console.log(destinoInput.id + ' alterado')
                } else {
                    console.log(destinoInput.id + ' alterado')
                    botao.disabled = true
                }
                
            });
            // Atualiza valor inicial ao carregar
            destinoInput.value = origemInput.value;
        }
    });
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncHiddenInputs);
} else {
    syncHiddenInputs();
}




<?php if($get_acao != 'visualizar'  && $id_ban == null) {?>

document.addEventListener('DOMContentLoaded', () => {

    console.log("🔧 Inicializando títulos e subtítulos (modal + filtros)...");

    // --- Valores vindos do PHP ---
    const tituloSelecionado       = <?= json_encode($post_titulo ?? '') ?>;
    const subtituloSelecionado    = <?= json_encode($post_subtitulo ?? '') ?>;

    const filtroTituloSelecionado   = <?= json_encode($get_filtro_titulo ?? '') ?>;
    const filtroSubtituloSelecionado = <?= json_encode($get_filtro_subtitulo ?? '') ?>;

    // ========================================================================
    // FUNÇÃO PRINCIPAL
    // ========================================================================
    function initTituloSubtitulo(tituloId, subtituloId, subtituloSelecionado) {

        const tituloSelect = document.getElementById(tituloId);
        const subtituloSelect = document.getElementById(subtituloId);

        if (!tituloSelect || !subtituloSelect) return;

        // ======================================================
        // 1) Inicializa TÍTULO com Choices (se ainda não tem)
        // ======================================================
        if (!tituloSelect._choices) {
            tituloSelect._choices = new Choices(tituloSelect, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: '',
                removeItemButton: false,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado'
            });
        }

        // ======================================================
        // 2) Captura opções ORIGINAIS de Subtítulo
        // ======================================================
        const origOptions = Array.from(subtituloSelect.querySelectorAll('option'))
            .map(opt => ({
                value: String(opt.value),
                label: opt.textContent.trim(),
                tituloId: String(opt.getAttribute('data-titulo-id') || '')
            }))
            .filter(o => o.value !== '' && o.label.toLowerCase() !== 'seleccione'
        );

        // ======================================================
        // 3) Destroi instancia anterior do subtitulo (se houver)
        // ======================================================
        if (subtituloSelect._choices) {
            try { subtituloSelect._choices.destroy(); } catch {}
        }

        // ======================================================
        // 4) Inicializa subtítulo novamente
        // ======================================================
        const subtituloChoices = new Choices(subtituloSelect, {
            searchEnabled: true,
            shouldSort: false,
            itemSelectText: '',
            removeItemButton: false,
            searchPlaceholderValue: 'Digite para buscar...',
            noResultsText: 'Nenhum subtítulo encontrado...'
        });
        subtituloSelect._choices = subtituloChoices;

        // Começa vazio
        subtituloChoices.clearChoices();
        subtituloChoices.clearStore();
        subtituloChoices.setChoices([{ value: '', label: 'Selecione', disabled: true }]);

        // ========================================================================
        // 5) Função para filtrar subtítulos pelo título selecionado
        // ========================================================================
        function filtrarSubtitulos(tituloValue, manterSelecao) {

            subtituloChoices.clearChoices();
            subtituloChoices.clearStore();
            subtituloChoices.setChoices([{ value: '', label: 'Selecione', disabled: true }]);

            const filtrados = origOptions.filter(o => o.tituloId === String(tituloValue));

            if (filtrados.length) {
                subtituloChoices.setChoices(filtrados, 'value', 'label', false);
            }

            if (!manterSelecao) {
                subtituloChoices.removeActiveItems();
            }
        }

        // ========================================================================
        // 6) Listener para mudança no título
        // ========================================================================
        tituloSelect.addEventListener('change', (e) => {
            filtrarSubtitulos(e.target.value, false);
        });

        // ========================================================================
        // 7) PRÉ-SELEÇÃO DO PHP (título + subtítulo)
        // ========================================================================
        function aplicarPreSelecao() {
            if (!subtituloSelecionado) return;

            const encontrado = origOptions.find(o => o.value === String(subtituloSelecionado));
            if (!encontrado) return;

            const tituloId = encontrado.tituloId;

            // Seleciona o título antes
            tituloSelect.value = tituloId;
            tituloSelect._choices.setChoiceByValue(tituloId);

            // Recarrega subtítulos
            filtrarSubtitulos(tituloId, true);
            

            // Seleciona o subtítulo (com delay mínimo)
            setTimeout(() => {
                try {
                    subtituloChoices.setChoiceByValue(String(subtituloSelecionado));
                } catch {
                    console.warn("⚠ Não conseguiu selecionar via método. Tentando fallback...");
                }

                subtituloSelect.value = subtituloSelecionado;
                subtituloSelect.dispatchEvent(new Event('change', { bubbles: true }));

            }, 80);
        }

        // Tempo para Choices terminar de construir
        setTimeout(aplicarPreSelecao, 200);
    }

    // ========================================================================
    //  inicializa MODAL
    // ========================================================================
    initTituloSubtitulo('titulo', 'subtitulo', subtituloSelecionado);

    // ========================================================================
    // inicializa FILTROS LATERAIS
    // ========================================================================
    initTituloSubtitulo('titulo-filtro', 'subtitulo-filtro', filtroSubtituloSelecionado);

    // ========================================================================
    // Restaura título do filtro
    // ========================================================================
    setTimeout(() => {
        if (filtroTituloSelecionado) {
            const t = document.getElementById('titulo-filtro');
            if (t && t._choices) t._choices.setChoiceByValue(String(filtroTituloSelecionado));
        }
    }, 400);

});



</script>
<?php }?>