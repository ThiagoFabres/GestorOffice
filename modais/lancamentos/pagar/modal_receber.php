<div class="modal fade" id="modal_receber" tabindex="-1" role="dialog" aria-labelledby="modal_receber_title"
            aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width:1100px;">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 class="modal-title" id="modal_receber_long_title">Novo Lançamento</h5>
                    </div>
                    <?php
    
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
    if($documento == '' && isset($recebimento)) {
        $documento = $recebimento->documento;
    }

    $post_custo = filter_input(INPUT_POST, 'custo') ?? '';
    if($post_custo == '' && isset($recebimento)) {
        $post_custo = $recebimento->documento;
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
            $data_lanc = new DateTime(filter_input(INPUT_POST, 'data_lanc'));
        }
    } else {
        $data_lanc = new DateTime(filter_input(INPUT_POST, 'data_lanc')) ??$recebimento->data_lanc ?? new DateTime();
    }
    $data_lanc = $data_lanc->format('Y-m-d');


    if (!isset($get_id)) {

        $recebimento = new Pag01(
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
            $_SESSION['usuario']->id_usuario,
        );

        $parcelas = [];
        if ($parcelas_d && $valor) {
            $data_vencimento = new DateTime();
            $data_vencimento->modify('+1 month');

            for ($parcela_n = 1; $parcela_n < $recebimento->parcelas + 1; $parcela_n++, $data_vencimento = $data_vencimento->modify('+1 month')) {
                $parcelas[$parcela_n] = new Pag02(
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
            $parcelas = Pag02::read(null, $_SESSION['usuario']->id_empresa, $recebimento->id, ordenar_por: 'parcela');
        } else {

            $parcelas = [];
            if ($parcelas_d && $valor) {
                $data_vencimento = new DateTime($data_lanc) ?? new DateTime();
                $data_vencimento->modify('+1 month');

                for ($parcela_n = 1; $parcela_n < $parcelas_d + 1; $parcela_n++, $data_vencimento = $data_vencimento->modify('+1 month')) {

                    $parcelas[$parcela_n] = new Pag02(
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
    ?>

        <div class="tabela">

            <div class="row">
                <div class="col-md-12" style="padding: 0;">
                    <?php if(!empty($parcelas)) echo'<div class="card" style="padding: 0;">'?>

                        <div class="card-header-div">
                            <?php if(!empty($parcelas)) echo '<div class="card-header-borda">'?> 
                                <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                    aria-labelledby="vendas-tab">
                                    <form method="post" action="pagar.php?view=pagar&acao=editar"
                                        onkeydown="return event.key != 'Enter';">
                                        <?php if (isset($get_id)) { ?>
                                            <input type="hidden" name="id" value="<?= $get_id ?>">
                                        <?php } ?>
                                        <div class="row">

                                            <div class="input-group-edit-parcela"style="width: 100%; position:relative; display:flex; flex-direction:row; justify-content: space-between;">
                                            <div class="modal-input-group">
                                                <label for="documento">Documento:</label>
                                                <div class="input-documento-group" style="display: flex; flex-direction: row;">
                                                    <div class="input-documento" style="width: 75%;">
                                                        <!--Nome: -->
                                                    
                                                        <input type="text" onchange="checar()" name="documento"
                                                            class="form-control" placeholder="Documento" id="documento"
                                                            value="<?= htmlspecialchars($documento, ENT_QUOTES, 'UTF-8') ?>"
                                                            required>
                                                    </div>

                                                    <div class="input-documento-generator" style="width: 25%;">

                                                        <button type="button" class="form-control" id="btnBuscarDoc"><i class="bi bi-text-center"></i></button>

                                                    </div>
                                                </div>
                                            </div>
                                                

                                                
                                            <div class="modal-input-group">
                                                <label for="centro">Centro de custos:</label>
                                                <div class="input-documento-group">
                                                        <div class="input-custo" style="width: 75%"> 
                                                            <select name="custo" class="form-select" id="custo">
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
                                                        <div class="input-documento-generator" style="width:25%">
                                                            <button data-bs-toggle="modal" data-bs-target="#modal_cadastro_custos" type="button" class="form-control" id="btnModalCustosPagar" data-bs-dismiss="modal">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                            </div>

                                            <div class="modal-input-group">
                                                <label for="cadastro">Cliente / Fornecedor:</label>
                                                    <div class="input-documento-group">
                                                        <div class="input-cadastro" style="width: 75%"> 
                                                            <select name="cadastro" class="form-select" id="cadastro">
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
                                                        <div class="input-documento-generator" style="width:25%">
                                                            <button data-bs-toggle="modal" data-bs-target="#modal_cadastro" type="button" class="form-control" id="btnModalCadastroPagar" data-bs-dismiss="modal">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                            </div>
                                            
                                            <div class="modal-input-group">
                                                <label for="data_lanc">Data de lançamento:</label>
                                                <div class="input-data-lanc">
                                                    <input type="date" onchange="checar()" name="data_lanc"
                                                        class="form-control" placeholder="Data de lançamento"
                                                        value="<?php echo $data_lanc ?>" required>
                                                </div>
                                            </div>

                                            </div>
                                            

                                            <div class="titulos-receber" style="display:flex; flex-direction: row; justify-content: space-between; width: 100%;">

                                            <div class="modal-input-group">
                                                <label for="titulo">Titulo</label>
                                            <div class="titulo-group">
                                                <div class="input-titulo" style="width:75%">
                                                    <!--Nome: -->
                                                    
                                                    <select name="titulo" class="form-select" id="titulo" 
                                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0; ">
                                                        <option value="">Selecione</option>

                                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, 'D');
                                                        foreach ($titulos as $titulo) { ?>
                                                            <option value="<?= $titulo->id ?>" <?php if ($titulo->id == $post_titulo) { ?> selected <?php } ?>>
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
                                            
                                            <div class="modal-input-group">
                                                <label for="subtitulo">Sub-Titulo</label>
                                                <div class="subtitulo-group">
                                                    <div class="input-subtitulo-div" style="width:75%;">

                                                        <select id="subtitulo" name="subtitulo" class="form-control" >
                                                            <option value="">Selecione</option>
                                                            <?php
                                                            // Buscar todos os subtítulos da empresa
                                                            $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                                            foreach ($todosSubtitulos as $sub) { ?>
                                                                <option value="<?= $sub->id ?>"
                                                                    data-titulo-id="<?= $sub->id_con01 ?>" <?php if ($sub->id == $post_subtitulo) { ?> selected <?php } ?>>
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
                                                <div class="modal-input-group">
                                                        <label for="valor">Valor:</label>         
                                                    <div class="input-valor">
                                                        <input type="text" onchange="checar()" name="valor"
                                                            class="form-control" placeholder="Valor"
                                                            value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"
                                                            required>
                                                    </div>
                                                </div>  
                                                <div class="modal-input-group">
                                                    <label for="parcelas">Parcelas:</label>                   
                                                    <div class="input-parcelas">   
                                                        <input type="number" onchange="checar()" name="parcelas"
                                                            class="form-control" placeholder="Parcelas"
                                                            value="<?= htmlspecialchars($parcelas_d, ENT_QUOTES, 'UTF-8') ?>"
                                                            required>
                                                    </div>
                                                </div> 
                                            </div>

                                            <div class="input-descricao" style="width: 100%;">
                                                <!--Nome: -->
                                                <label for="descricao">Descrição:</label>
                                                <input type="text" onchange="checar()" name="descricao"
                                                    class="form-control" placeholder="Descrição"
                                                    value="<?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?>"
                                                    required>
                                            </div>






                                            <div style="width: 100%;">
                                                <button type="submit" class="btn btn-primary mt-4"
                                                    style="float:right; background-color: #5856d6; border: 0;">Gerar
                                                    parcelas</button>
                                            </div>

                                        </div>
                                    </form>
<?php if(!empty($parcelas)){ ?>


                                </div>
                            </div>
                        </div>

                        <form action="cadastros_manager.php" method="post" onkeydown="return event.key != 'Enter';">
                            <input type="hidden" name="cadastro" value="<?= $post_cadastro ?>">
                            <input type="hidden" name="titulo" value="<?= $post_titulo ?>">
                            <input type="hidden" name="subtitulo" value="<?= $post_subtitulo ?>">
                            <input type="hidden" name="documento" value="<?= $documento ?>">
                            <input type="hidden" name="descricao" value="<?= $descricao ?>">
                            <input type="hidden" name="valor" value="<?= $valor ?>">
                            <input type="hidden" name="parcelas_d" value="<?= $parcelas_d ?>">
                            <input type="hidden" name="view" value="receber">
                            <input type="hidden" name="pagar" value="1">
                            <input type="hidden" name="id_rec01" value="<?= $get_id ?>">
                            <input type="hidden" name="data_lanc" value="<?= $data_lanc ?>">
                            <input type="hidden" name="custo" value="<?= $post_custo ?>">

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
                                                <td><input type="date" class="form-control"
                                                        name="vencimento[<?= $parcela->parcela ?>]"
                                                        value="<?php if (!isset($get_id) || empty($_GET['id'])) {
                                                            echo $parcela->vencimento->format('Y-m-d');
                                                        } else
                                                            echo $parcela->vencimento ?>"
                                                            placeholder="vencimento"></td>
                                                    <td><input class="form-control valor-parcela vencimento"
                                                            name="valor_par[<?= $parcela->parcela ?>]"
                                                        value="<?= $valor_parcela; ?>"></input></td>
                                                <td><input class="form-control" name="obs02[<?= $parcela->parcela ?>]"
                                                        value="<?= $parcela->obs ?>"></input></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                    <table class="table table-striped table-bordered">

                                        <thead>
                                            <tr>
                                                <th id="total_parcelas">Valor das parcelas</th>
                                                <th>Valor Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>R$ <span id="totalParcelas"></span></td>
                                                <td id="valorEsperadoCell">R$ <?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        </tbody>

                                    </table>
                                </table>
                            </div>
                            <div class="card-footer" style="align-items: end;">
                            <?php if(!empty($parcelas)){ ?>
                                <button name="acao"
                                    value="<?php if (!isset($get_acao)) { ?>adicionar<?php } else if (isset($get_acao)) { ?>editar<? } ?>"
                                    class="btn btn-primary" type="submit" id="botao-editar-parcela"
                                    style="background-color:#5856d6; padding-inline:1.5em; float:right; margin-left: 90%; ">Salvar</button>
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
                totalValido = diff <= 0.01;
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
                    totalValido = Math.abs(total - valorEsperado) <= 0.01;
                }
            }

            // round to cents to avoid floating point noise
            var totalRounded = Math.round(totalSum * 100) / 100;
            var esperadoRounded = isNaN(valorEsperadoLog) ? NaN : Math.round(valorEsperadoLog * 100) / 100;
            var totalValidoCalc = false;
            if (!isNaN(esperadoRounded)) {
                totalValidoCalc = Math.abs(totalRounded - esperadoRounded) <= 0.01;
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
    document.addEventListener('DOMContentLoaded', function () {
        var userBtn = document.getElementById('userBtn');
        var userMenu = document.getElementById('userMenu');
        if (userBtn && userMenu) {
            userBtn.onclick = function (e) {
                e.stopPropagation();
                if (userMenu.style.display === 'block') {
                    userMenu.style.display = 'none';
                } else {
                    userMenu.style.display = 'block';
                }
            };
            document.addEventListener('click', function (e) {
                if (userMenu.style.display === 'block') {
                    userMenu.style.display = 'none';
                }
            });
            userMenu.onclick = function (e) {
                e.stopPropagation();
            };
        }
    });
    document.getElementById('titulo').addEventListener('change', function () {
        var tituloId = this.value;
        var subtituloSelect = document.getElementById('subtitulo');
        var options = subtituloSelect.querySelectorAll('option');

        options.forEach(function (option) {
            if (option.value === "") {
                option.style.display = '';
                return;
            }
            if (option.getAttribute('data-titulo-id') === tituloId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });

        subtituloSelect.value = ""; // Reseta seleção
    });

    // (Duplicate implementation removed - single canonical atualizarTotalParcelas is defined above inside the IIFE)




    const subtituloSelecionado = <?= isset($post_subtitulo) ? json_encode($post_subtitulo) : 'null' ?>;
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function() {
        // ========== INICIALIZAÇÃO DO CHOICES.JS ==========
        const tituloFiltroElement = document.querySelector('#titulo');
        const subtituloFiltroElement = document.querySelector('#subtitulo');

        // IMPORTANTE: Guarda os subtítulos ANTES de inicializar Choices.js
        const todosSubtitulos = subtituloFiltroElement ? 
            Array.from(subtituloFiltroElement.querySelectorAll('option')).map(opt => ({
                value: opt.value,
                label: opt.textContent.trim(),
                tituloId: opt.getAttribute('data-titulo-id')
            })) : [];

        // Inicializa Choices.js nos elementos de filtro
        let tituloFiltroChoice = null;
        let subtituloFiltroChoice = null;
        
        if (tituloFiltroElement && typeof Choices !== 'undefined') {
            tituloFiltroChoice = new Choices(tituloFiltroElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
        }

        if (subtituloFiltroElement && typeof Choices !== 'undefined') {
            subtituloFiltroChoice = new Choices(subtituloFiltroElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
            
            // LIMPA IMEDIATAMENTE após inicializar
            subtituloFiltroChoice.clearStore();
            subtituloFiltroChoice.clearChoices();
            subtituloFiltroChoice.setChoices(
                [{ value: '', placeholder: 'Selecione', disabled: false }],
                'value',
                'placeholder',
                false
            );
        }
        // ========== FILTRO DE SUBTÍTULOS (FILTROS) ==========
        if (tituloFiltroElement && subtituloFiltroElement) {
            function carregarSubtitulosFiltro(tituloId, manterSelecao = false) {
                const valorAtual = subtituloFiltroElement.value;

                if (subtituloFiltroChoice) {
                    subtituloFiltroChoice.clearStore();
                    subtituloFiltroChoice.clearChoices();
                    subtituloFiltroChoice.setChoices(
                        [{ value: '', placeholder: 'Selecione', disabled: false }],
                        'value',
                        'placeholder',
                        true
                    );

                    const subtitulosFiltrados = todosSubtitulos.filter(sub => sub.tituloId === tituloId);
                    if (subtitulosFiltrados.length > 0) {
                        subtituloFiltroChoice.setChoices(subtitulosFiltrados, 'value', 'label', false);
                    }

                    if (manterSelecao && valorAtual && subtitulosFiltrados.some(sub => sub.value === valorAtual)) {
                        setTimeout(() => {
                            subtituloFiltroChoice.setChoiceByValue(valorAtual);
                        }, 50);
                    } 
                } else {
                    subtituloFiltroElement.innerHTML = '<option value="">Selecione</option>';
                    
                    if (!tituloId) return;

                    todosSubtitulos
                        .filter(sub => sub.tituloId === tituloId)
                        .forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.value;
                            option.textContent = sub.label;
                            option.setAttribute('data-titulo-id', sub.tituloId);
                            subtituloFiltroElement.appendChild(option);
                        });

                    if (manterSelecao && valorAtual) {
                        subtituloFiltroElement.value = valorAtual;
                    } else {
                        subtituloFiltroElement.value = '';
                    }
                }

            }

            tituloFiltroElement.addEventListener('change', function(e) {
                const valor = e.detail ? e.detail.value : e.target.value;
                carregarSubtitulosFiltro(valor, false);
            });

            const tituloInicial = tituloFiltroElement.value;
            if (tituloInicial) {
                carregarSubtitulosFiltro(tituloInicial, true);
            }
        }
            if (subtituloSelecionado) {
            // aguarda o Choices atualizar o DOM antes de selecionar
            setTimeout(() => {
                subtituloFiltroChoice.setChoiceByValue(String(subtituloSelecionado));
            }, 100);
            }

      
    ;}, 100);
});
    </script>

