<?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/contas.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/recebimentos.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$view = filter_input(INPUT_GET, 'view');
$target = filter_input(INPUT_GET, 'target');
$con01 = filter_input(INPUT_GET, 'con01id');
$acao = filter_input(INPUT_GET, 'acao');
$get_opcao = filter_input(INPUT_GET, 'opcao');

$get_id = filter_input(INPUT_GET, 'id') ?? filter_input(INPUT_POST, 'id');
$get_acao = filter_input(INPUT_GET, 'acao');
echo $get_id;



if (isset($view) && $view == 'contas') {
    $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa);

}

if (isset($get_id)) {
    $recebimento = Rec01::read($get_id, $_SESSION['usuario']->id_empresa)[0];
    if (!$recebimento)
        header('Location: receber.php');
    $parcelas_pagas = false;
    for ($i = 1; $i < $recebimento->parcelas; $i++) {
        $rec02 = Rec02::read(null, $_SESSION['usuario']->id_empresa, $recebimento->id, null, $i)[0];
        if ($parcelas_pagas == false && $rec02->valor_pag == $rec02->valor_par) {
            $parcelas_pagas = true;
            break;
        } else {
            continue;

        }
    }
}



?>

<!DOCTYPE html>




<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">

<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="../choices/choices.css">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
        <div id="itens-menu">
            <div class="menu-item">
                <a href="index.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-layers"></i></div> Dashboard
                </a>
            </div>
            <?php if ($_SESSION['usuario']->processar == 1) { ?>
                <div class="menu-item accordion">

                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button"
                        aria-expanded="false" aria-controls="cadastrosMenu">
                        <i class="bi bi-person"></i> Cadastros
                    </a>
                    <div class="collapse" id="cadastrosMenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                            <li><a href="cadastrar.php?cadastro=cliente" class="link-light text-decoration-none"><i
                                        class="bi bi-person"></i>Cliente/Fornecedor</a></li>
                            <li><a href="cadastrar.php?cadastro=bairro" class="link-light text-decoration-none"><i
                                        class="bi bi-houses"></i>Bairro</a></li>
                            <li><a href="cadastrar.php?cadastro=cidade" class="link-light text-decoration-none"><i
                                        class="bi bi-buildings"></i>Cidade</a></li>
                            <li><a href="cadastrar.php?cadastro=pagamento" class="link-light text-decoration-none"><i
                                        class="bi bi-cash-coin"></i>Tipo Pagamento</a></li>
                            <li><a href="cadastrar.php?cadastro=categoria" class="link-light text-decoration-none"><i
                                        class="bi bi-tag"></i>Categoria</a></li>
                            <li><a href="cadastrar.php?cadastro=custo" class="link-light text-decoration-none"><i class="bi bi-bank"></i>Centro de custos</a></li>

                        </ul>
                    </div>
                </div>
            <?php } ?>

            <div class="menu-item">
                <a href="contas.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano
                    de Contas
                </a>
            </div>

            <div class="menu-item menu-item-atual">
                <a href="receber.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber
                </a>
            </div>

            <div class="menu-item">
                <a href="pagar.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a
                    Pagar
                </a>
            </div>

            <div class="menu-item">
                <a href="dre/sintetico.php">
                    <div style="padding: 0.5em; align-items:center;"><i class="bi bi-file-earmark-text"></i></div>DRE
                </a>
            </div>


        </div>
        </div>

    </nav>


    <div id="header">

        <button onclick="encolher()"
            style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer; z-index:1000;">
            <span class="btn bi bi-list"></span>
        </button>

        <div id="titulo-header">

            <a>Dashboard</a>
        </div>
        <div id="menu-superior">
            <a class="superior-item" href="/admin/">Dashboard</a>
        </div>
        <div class="conta-header" style="position:relative; float:right; margin-right:2em;">
            <button id="userBtn" type="button"
                style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer;">
                <span style="color:#181f2b;"><?= htmlspecialchars($_SESSION['usuario']->nome, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </button>
            <div id="userMenu" style="right:0; z-index: 1000000;">
                <a href="/" class="dropdown-item">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </a>
            </div>
        </div>
    </div>



    <?php
    $post_cadastro = filter_input(INPUT_POST, 'cadastro') ?? $post_cadastro = Cadastro::read($recebimento->id_cadastro)[0]->id_cadastro;
    $post_titulo = filter_input(INPUT_POST, 'titulo') ?? $post_titulo = Con01::read($recebimento->id_con01)[0]->id;
    $post_subtitulo = filter_input(INPUT_POST, 'subtitulo') ?? $post_subtitulo = Con02::read($recebimento->id_con02)[0]->id;
    $documento = filter_input(INPUT_POST, 'documento') ?? $recebimento->documento;

    $valor = filter_input(INPUT_POST, 'valor');
    if($valor != null) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    }
    if(!isset($valor) || $valor == null) {
        $valor = $recebimento->valor;
    }
    $valor = floatval($valor);
    
    $parcelas_d = filter_input(INPUT_POST, 'parcelas') ?? $parcelas_d = $recebimento->parcelas;
    $parcelas_d = intval($parcelas_d);
    $descricao = filter_input(INPUT_POST, 'descricao') ?? $descricao = $recebimento->descricao;
    if ($get_id != null && isset($recebimento)) {
        if(empty($_POST['data_lanc'])) {
            $data_lanc = new DateTime($recebimento->data_lanc);
        } else {
            $data_lanc = new DateTime(filter_input(INPUT_POST, 'data_lanc'));
        }
    } else {
        $data_lanc = new DateTime(filter_input(INPUT_POST, 'data_lanc')) ?? new DateTime($recebimento->data_lanc) ?? new DateTime();
    }
    $data_lanc = $data_lanc->format('Y-m-d');
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
            $_SESSION['usuario']->id_usuario,
        );

        $parcelas = [];
        if ($parcelas_d && $valor) {
            $data_vencimento = new DateTime($data_lanc);
            $data_vencimento->modify('+1 month');

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






        if (!empty($_GET['id']) && $_GET['id'] == $get_id) {
            $parcelas = Rec02::read(null, $_SESSION['usuario']->id_empresa, $recebimento->id, ordenar_por: 'parcela');
        } else {

            $parcelas = [];
            if ($parcelas_d && $valor) {
                $data_vencimento = new DateTime($data_lanc) ?? new DateTime();
                $data_vencimento->modify('+1 month');

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
    $valor = number_format($valor, 2, ',', '.');
    if ($data_lanc == null) {
        $data_lanc = new Datetime();
        $data_lanc = $data_lanc->format('Y-m-d');
    }
    ?>

    <div class="main">
        <div class="tabela">

            <div class="row">
                <div class="col-md-12" style="padding: 0;">
                    <div class="card" style="padding: 0;">



                        <div class="card-header">
                            <h3>Dados das parcelas</h3>
                        </div>

                        <div class="card-header-div">
                            <div class="card-header-borda">
                                <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                    aria-labelledby="vendas-tab">
                                    <h5 class="card-title">Recebimento</h5>
                                    <form method="post" action="editar-receber.php?view=receber&acao=editar"
                                        onkeydown="return event.key != 'Enter';">
                                        <?php if (isset($get_id)) { ?>
                                            <input type="hidden" name="id" value="<?= $get_id ?>">
                                        <?php } ?>
                                        <div class="row">

                                            <div class="input-group-edit-parcela"
                                                style="width: 100%; position:relative; display:flex; flex-direction:row;">
                                                <div class="input-documento" style="width: 75%;">
                                                    <!--Nome: -->
                                                    <label for="documento">Documento:</label>

                                                    <input type="text" onchange="checar()" name="documento" 
                                                        class="form-control" placeholder="Documento"
                                                        value="<?= htmlspecialchars($documento, ENT_QUOTES, 'UTF-8') ?>"
                                                        required>
                                                </div>
                                                <?php ?>
                                                <div class="input-data-lanc" style="width: 25%;">
                                                    <label for="data_lanc">Data de lançamento:</label>
                                                    <input type="date" onchange="checar()" name="data_lanc" 
                                                        class="form-control" placeholder="Data de lançamento"
                                                        value="<?php echo $data_lanc ?>" required>
                                                </div>
                                            </div>
                                            <div class="input-cadastro" style="width: 50%;">
                                                <!--Nome: -->
                                                <label for="cadastro">Cliente / Fornecedor:</label>
                                                <select name="cadastro" class="form-select" id="cadastro" >
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

                                            <div class="titulos-receber" style="display:flex; flex-direction: row;">

                                                <div class="input-titulo" style="width: 50%;">
                                                    <!--Nome: -->
                                                    <label for="titulo">Titulo</label>
                                                    <select name="titulo" class="form-select" id="titulo" 
                                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                        <option value="">Selecione</option>

                                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, 'C');
                                                        foreach ($titulos as $titulo) { ?>
                                                            <option value="<?= $titulo->id ?>" <?php if ($titulo->id == $post_titulo) { ?> selected <?php } ?>>
                                                                <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="input-subtitulo-div" style="width: 50%;">
                                                    <!--Nome: -->
                                                    <label for="subtitulo">Sub-Titulo</label>
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
                                            </div>

                                            <div class="input-group-valores" style="display:flex; flex-direction: row;">
                                                <div class="input-valor-div" style="width: 50%;">
                                                    <!--Nome: -->
                                                    <label for="valor">Valor:</label>
                                                    <input type="text" onchange="checar()" name="valor" 
                                                        class="form-control" placeholder="Valor"
                                                        value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"
                                                        required>
                                                </div>

                                                <div class="input-parcelas" style="width: 50%;">
                                                    <!--Nome: -->
                                                    <label for="parcelas">Parcelas:</label>
                                                    <input type="number" onchange="checar()" name="parcelas" 
                                                        class="form-control" placeholder="Parcelas"
                                                        value="<?= htmlspecialchars($parcelas_d, ENT_QUOTES, 'UTF-8') ?>"
                                                        required>
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
                            <input type="hidden" name="obs" value="<?= $obs ?>">
                            <input type="hidden" name="view" value="receber">
                            <input type="hidden" name="id_rec01" value="<?= $get_id ?>">
                            <input type="hidden" name="data_lanc" value="<?= $data_lanc ?>">



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
                                                <td>R$ <?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        </tbody>

                                    </table>
                                </table>
                            </div>
                            <div class="card-footer">
                                <button name="acao"
                                    value="<?php if (!isset($get_acao)) { ?>adicionar<?php } else if (isset($get_acao) && $get_acao == 'editar') { ?>editar<? } ?>"
                                    class="btn btn-primary" type="submit" id="botao-editar-parcela"
                                    style="background-color:#5856d6; padding-inline:1.5em; float:right; margin-bottom: 0.5em; border: 0;">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../choices/choices.js"></script>

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
                let val = input.value.replace('.', '')
                val = parseFloat(val.replace(',', '.'));
                if (!isNaN(val)) total += val;
                console.log(total)
            });
            document.getElementById('totalParcelas').textContent = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Pega o valor total esperado do campo oculto ou célula da tabela
            let valorTotal = document.querySelector('input[name="valor"]');
            let valorEsperado = 0;
            if (valorTotal) {
                valorEsperado = parseFloat(
                    valorTotal.value.replace(/\./g, '').replace(',', '.')
                );
            }
            else {
                // Alternativa: pega da célula da tabela
                let celula = document.querySelector('td:last-child');
                if (celula) {
                    valorEsperado = parseFloat(celula.textContent.replace(/[^0-9,\.]/g, '').replace('.', ''));
                    valorEsperado = valorEsperado.replace(',', '.');
                    console.log(valorEsperado)
                }
            }

            var totalValido = true;
            if (!isNaN(valorEsperado)) {
                totalValido = Math.abs(total - valorEsperado) <= 0.01;
            }

            atualizarEstadoBotaoSalvar(totalValido);
        }

        function atualizarEstadoBotaoSalvar(totalValido) {
            var botao = document.getElementById('botao-editar-parcela');
            if (!botao) return;

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
                let valorEsperado = 0;
                if (valorTotal) valorEsperado = parseFloat(valorTotal.value.replace(/\./g, '').replace(',', '.'));
                totalValido = isNaN(valorEsperado) ? true : Math.abs(total - valorEsperado) <= 0.01;
            }

            if (!totalValido || !datasValidas) {
                botao.disabled = true;
                botao.title = (!totalValido ? 'A soma das parcelas deve ser igual ao valor total' : '') + (!datasValidas ? ' ' + 'Verifique as datas de vencimento' : '');
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

    function atualizarTotalParcelas() {
        let total = 0;
        document.querySelectorAll('.valor-parcela').forEach(function (input) {
            let val = input.value.replace('.', '')
            val = parseFloat(val.replace(',', '.'));
            if (!isNaN(val)) total += val;
            console.log(total)
        });
        document.getElementById('totalParcelas').textContent = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Pega o valor total esperado do campo oculto ou célula da tabela
        let valorTotal = document.querySelector('input[name="valor"]');
        let valorEsperado = 0;
        if (valorTotal) {
            valorEsperado = parseFloat(
                valorTotal.value.replace(/\./g, '').replace(',', '.')
            );
        }
        else {
            // Alternativa: pega da célula da tabela
            let celula = document.querySelector('td:last-child');
            if (celula) {
                valorEsperado = parseFloat(celula.textContent.replace(/[^0-9,\.]/g, '').replace('.', ''));
                valorEsperado = valorEsperado.replace(',', '.');
                console.log(valorEsperado)
            }
        }

        let botao = document.getElementById('botao-editar-parcela');
        if (botao) {
            if (Math.abs(total - valorEsperado) > 0.01) {
                botao.disabled = true;
                botao.title = 'A soma das parcelas deve ser igual ao valor total';
            } else {
                botao.disabled = false;
                botao.title = '';
            }
        }
    }

    document.querySelectorAll('.valor-parcela').forEach(function (input) {
        input.addEventListener('input', atualizarTotalParcelas);
    });

    atualizarTotalParcelas();





    // function checar() {
    //     let nome = document.querySelector('.input-nome input').value;
    //     let fantasia = document.querySelector('.input-fantasia input').value;
    //     let cpf = document.querySelector('.input-cpf input').value;
    //     let cnpj = document.querySelector('.input-cnpj input').value;
    //     let cep = document.querySelector('.input-cep input').value;
    //     let endereco = document.querySelector('.input-endereco input').value;
    //     let bairro = document.querySelector('.input-bairro input').value;
    //     let cidade = document.querySelector('.input-cidade input').value;
    //     let estado = document.querySelector('.input-estado input').value;
    //     let celular = document.querySelector('.input-celular input').value;
    //     let telefone = document.querySelector('.input-telefone input').value;
    //     let email = document.querySelector('.input-email input').value;




    //     if (nome !== '' && fantasia !== '' && cpf !== '' && cnpj !== '' && cep !== '' && endereco !== '' && bairro !== '' && cidade !== '' && estado !== '' && celular !== '' && telefone !== '' && email !== '') {
    //         document.querySelector('button[name="acao"]').disabled = false;
    //     } else {
    //         document.querySelector('button[name="acao"]').disabled = true;
    //     }
    // }

    function encolher() {
        let barra = document.getElementById('barra-lateral');
        let container = document.getElementById('container');
        let superior = document.getElementById('header');
        let body = document.getElementById('body');






        if (barra.style.animationName === 'encolher') {

            superior.style.animationName = 'expandir-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'backwards';

            barra.style.animationName = 'expandir';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'backwards';

            container.style.animationName = 'expandir-container'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'backwards';

            body.style.animationName = 'expandir-container'
            body.style.animationDuration = '0.5s';
            body.style.animationFillMode = 'backwards';
            return;
        } else {

            superior.style.animationName = 'encolher-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'forwards';

            barra.style.animationName = 'encolher';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'forwards';

            container.style.animationName = 'encolher'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'forwards';

            body.style.animationName = 'encolher'
            body.style.animationDuration = '0.5s';
            body.style.animationFillMode = 'forwards';
        }
    }
</script>

<script>
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




</html>