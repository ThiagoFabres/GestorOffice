<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../db/base.php'; 

session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../../db/buscar_documento_rec.php';
$lateral_target = 'importar';
$filtro_tipo_lancamento = filter_input(INPUT_GET, 'tipo_lancamento', FILTER_SANITIZE_STRING) ?? 'pagar';
$filtro_cliente = filter_input(INPUT_GET, 'cliente', FILTER_SANITIZE_STRING) ?? '';
$filtro_custos = filter_input(INPUT_GET, 'custos', FILTER_SANITIZE_STRING) ?? '';
$filtro_titulo = filter_input(INPUT_GET, 'titulo', FILTER_SANITIZE_STRING) ?? '';
$filtro_subtitulo = filter_input(INPUT_POST, 'subtitulo', FILTER_SANITIZE_STRING) ?? '';

$erro = filter_input(INPUT_GET, 'erro') ?? '';

$novo_documento = buscarDocumentoRec();
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
<link rel="stylesheet" href="recorrentes.css">
<link rel="stylesheet" href="../../choices/choices.css">



<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>



    <div id="container" class="main" style="min-height:80vh; justify-content: center;">
                    <div class="card" style="padding: 0; width:100%; max-width:800px;">



                        <div class="card-header">
                            <h3>Importação de Lançamento</h3>
                        </div>
                        <div class="card-body" style="overflow:visible; width:100%;">

                                <div class="tab-pane fade show active" id="vendas" role="tabpanel" style="min-width:0"
                                    aria-labelledby="vendas-tab">
                                    <form method="post" action="financeiro_manager.php"
                                        enctype="multipart/form-data"
                                        onkeydown="return event.key != 'Enter';">
                                    <input type="hidden" name="documento_inicial" value="<?=$novo_documento?>">
                                    <input type="hidden" name="acao" value="processar">

                                            <div style=" width:100%; position:relative; display:flex; flex-direction:row; justify-content:center; min-width:0;">
                                                <div class="input-cadastro" style="width:calc(100%/4)">
                                                    <!--Nome: -->
                                                    <label for="cadastro">Cliente / Fornecedor:</label>
                                                    <select name="cadastro" class="form-select" id="cadastro">
                                                        <option value="">Selecione</option>
                                                        <?php
                                                        $cadastros = Cadastro::read($id_passado, null, $_SESSION['usuario']->id_empresa);
                                                        foreach ($cadastros as $cadastro) { ?>
                                                            <option value="<?= $cadastro->id_cadastro ?>" <?= $filtro_cliente == $cadastro->id_cadastro ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="input-subtitulo-div" style="width:calc(100%/4)">
                                                    <!--Nome: -->
                                                    <label for="custos">C. Custos</label>
                                                    <select id="custos" name="custos" class="form-control" >
                                                        <option value="">Selecione</option>
                                                        <?php
                                                        // Buscar todos os subtítulos da empresa
                                                        $centro_custos = CentroCustos::read(null, $_SESSION['usuario']->id_empresa);
                                                        foreach ($centro_custos as $custo) { ?>
                                                            <option value="<?= $custo->id ?>" <?= $filtro_custos == $custo->id ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($custo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="input-titulo" style="width:calc(100%/4)">
                                                    <!--Nome: -->
                                                    <label for="titulo">Titulo</label>
                                                    <select name="titulo" class="form-select" id="titulo" 
                                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                        <option value="">Selecione</option>

                                                        <?php
                                                        // Carrega todos os títulos e adiciona o atributo data-tipo (D ou C)
                                                        $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa);
                                                        foreach ($titulos as $titulo) { ?>
                                                            <option value="<?= $titulo->id ?>" data-tipo="<?= htmlspecialchars($titulo->tipo, ENT_QUOTES, 'UTF-8') ?>" <?= $filtro_titulo == $titulo->id ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="input-subtitulo-div" style="width:calc(100%/4)">
                                                    <!--Nome: -->
                                                    <label for="subtitulo">Sub-Titulo</label>
                                                    <select id="subtitulo" name="subtitulo" class="form-control" >
                                                        <option value="">Selecione</option>
                                                        <?php
                                                        // Buscar todos os subtítulos da empresa
                                                        $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                                        foreach ($todosSubtitulos as $sub) { ?>
                                                            <option value="<?= $sub->id ?>"
                                                                data-titulo-id="<?= $sub->id_con01 ?>" <?= $filtro_subtitulo == $sub->id ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                

                                                
                                            
                                                
                                            

                                                
                                            </div>
                                            <hr>

                                            <div class="input-group-valores" style="display:flex; flex-direction: row; width:100%; justify-content:space-between; gap:5em;">
                                            <div class="d-flex flex-row justify-content-between w-25">
                                                
                                                    <div class="d-flex flex-column">
                                                        <label>A Pagar</label>
                                                                <input type="radio" class="form-control" name="tipo_lancamento" value="pagar" style="width:15px; height:15px; border-radius: 100%;" <?php if ($filtro_tipo_lancamento == 'pagar') echo 'checked'; ?> >
                                                            </div>
                                                    <div class="d-flex flex-column">
                                                        <label>A Receber</label>
                                                        <input type="radio" class="form-control" name="tipo_lancamento" value="receber" style="width:15px; height:15px; border-radius: 100%;" <?php if ($filtro_tipo_lancamento == 'receber') echo 'checked'; ?> >
                                                    </div>
                                            </div>
                                                <div style="width: 100%;">
                                                <input type="file" class="form-control" name="arquivo"
                                                    style="width:100%; margin-top:20px;" onchange="this.form.submit()">
                                            </div>
                                            </div>
                                            
                                    </form>



                                </div>

                        </div>
                    </div>
<?php require_once __DIR__ . '/../../componentes/modais/lancamentos/importar/modal_adicionar_lancamentos.php' ?> 
<?php if(isset($_SESSION['excel_transactions'])){
    unset($_SESSION['excel_transactions']); 
    }?>

<?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?> 
</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../choices/choices.js"></script>


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
            })).filter(s => s.value !== '') : [];

        // Guarda todos os títulos (cada option tem data-tipo="D" ou "C")
        const todosTitulos = tituloFiltroElement ?
            Array.from(tituloFiltroElement.querySelectorAll('option')).map(opt => ({
                value: opt.value,
                label: opt.textContent.trim(),
                tipo: opt.getAttribute('data-tipo')
            })).filter(t => t.value !== '') : [];

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
            // Função para filtrar títulos pelo tipo (D = pagar, C = receber)
            function filtrarTitulosPorTipo(tipoChar, manterSelecao = false) {
                const valorAtual = tituloFiltroElement.value;
                const filtrados = todosTitulos.filter(t => !t.tipo || t.tipo === tipoChar);

                tituloFiltroChoice.clearStore();
                tituloFiltroChoice.clearChoices();

                // placeholder item (disabled) para aparecer como placeholder não selecionável
                const placeholderItem = { value: '', label: 'Selecione', disabled: true };
                const choicesList = [placeholderItem].concat(filtrados.length ? filtrados : [{ value: '', label: 'Nenhum título', disabled: true }]);

                tituloFiltroChoice.setChoices(choicesList, 'value', 'label', true);

                if (manterSelecao && valorAtual && filtrados.some(t => t.value === valorAtual)) {
                    setTimeout(() => tituloFiltroChoice.setChoiceByValue(valorAtual), 50);
                } else {
                    try { tituloFiltroChoice.removeActiveItems(); } catch (e) {}
                }
            }
            filtrarTitulosPorTipo('D', false);

            // Começa apenas com o placeholder; títulos filtrados aparecem ao mudar o radio
            // tituloFiltroChoice.setChoices([{ value: '', label: 'Selecione', disabled: true }], 'value', 'label', true);

            // Escuta mudanças nos radios para atualizar os títulos
            document.querySelectorAll('input[name="tipo_lancamento"]').forEach(radio => {
                radio.addEventListener('change', function (e) {
                    const tipoChar = e.target.value === 'pagar' ? 'D' : 'C';
                    filtrarTitulosPorTipo(tipoChar, false);
                });
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
            // Remove opções nativas do select para evitar que apareçam além do placeholder
            try {
                subtituloFiltroElement.innerHTML = '';
            } catch (e) {}

            // LIMPA IMEDIATAMENTE após inicializar e insere apenas o placeholder
            subtituloFiltroChoice.clearStore();
            subtituloFiltroChoice.clearChoices();
            subtituloFiltroChoice.setChoices(
                [{ value: '', label: 'Selecione', disabled: true }],
                'value',
                'label',
                true
            );
        }
        // ========== FILTRO DE SUBTÍTULOS (FILTROS) ==========
        if (tituloFiltroElement && subtituloFiltroElement) {
            function carregarSubtitulosFiltro(tituloId, manterSelecao = false) {
                const valorAtual = subtituloFiltroElement.value;

                // constrói lista final: sempre começa com placeholder
                const placeholder = { value: '', label: 'Selecione', disabled: true };

                if (subtituloFiltroChoice) {
                    subtituloFiltroChoice.clearStore();
                    subtituloFiltroChoice.clearChoices();

                    if (!tituloId) {
                        // sem título selecionado -> apenas placeholder
                        subtituloFiltroChoice.setChoices([placeholder], 'value', 'label', true);
                        return;
                    }

                    const subtitulosFiltrados = todosSubtitulos.filter(sub => sub.tituloId === tituloId);
                    const choicesList = subtitulosFiltrados.length ? [placeholder].concat(subtitulosFiltrados) : [placeholder];

                    subtituloFiltroChoice.setChoices(choicesList, 'value', 'label', true);

                    if (manterSelecao && valorAtual && subtitulosFiltrados.some(sub => sub.value === valorAtual)) {
                        setTimeout(() => subtituloFiltroChoice.setChoiceByValue(valorAtual), 50);
                    }
                } else {
                    // sem Choices.js, atualiza o select nativo
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
subtituloFiltroChoice.clearStore();
            subtituloFiltroChoice.clearChoices();
      
    ;}, 100);
});
<?php if( isset($_GET['excel']) && $_GET['excel'] == '1') { ?>
    document.addEventListener('DOMContentLoaded', function() {
        const modalAdicionar = new bootstrap.Modal(document.getElementById('modal_adicionar_lancamento'));
        modalAdicionar.show();
    });

<?php } ?>
<?php if($erro == 'selecao') {?>
    alert('Selecione Todos os campos')
    window.location.href='importar.php'
<?php } if($erro == 'cadastrado') { ?>
    alert('Arquivo vazio ou já cadastrado')
    window.location.href='importar.php'
<?php } ?>
</script>





</html>