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
$lateral_recorrente = true;
$lateral_target = 'recorrente_receber';

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

                <div  style="padding: 0; min-width:0; ">
                    <div class="card" style="padding: 0; min-width:0;">



                        <div class="card-header">
                            <h3>Dados do Lançamento Recorrente</h3>
                        </div>
                        <div class="card-body" style="overflow:visible;">

                                <div class="tab-pane fade show active" id="vendas" role="tabpanel" style="min-width:0"
                                    aria-labelledby="vendas-tab">
                                    <form method="post" action="recorrente_manager.php"
                                        onkeydown="return event.key != 'Enter';">
                                    <input type="hidden" name="view" value="receber">
                                    <input type="hidden" name="documento_inicial" value="<?=$novo_documento?>">
                                    

                                            <div 
                                                style=" width:100%; position:relative; display:flex; flex-direction:row; justify-content:center; min-width:0;">
                                                <div class="input-cadastro" style="width:calc(100%/4)">
                                                    <!--Nome: -->
                                                    <label for="cadastro">Cliente / Fornecedor:</label>
                                                    <select name="cadastro" class="form-select" id="cadastro">
                                                        <option value="">Selecione</option>
                                                        <?php
                                                        $cadastros = Cadastro::read($id_passado, null, $_SESSION['usuario']->id_empresa);
                                                        foreach ($cadastros as $cadastro) { ?>
                                                            <option value="<?= $cadastro->id_cadastro ?>">
                                                                <?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?>
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

                                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, 'C');
                                                        foreach ($titulos as $titulo) { ?>
                                                            <option value="<?= $titulo->id ?>">
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
                                                                data-titulo-id="<?= $sub->id_con01 ?>">
                                                                <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
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
                                                            <option value="<?= $custo->id ?>">
                                                                <?= htmlspecialchars($custo->nome, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                
                                            
                                                
                                            

                                                
                                            </div>

                                            <div class="input-group-valores" style="display:flex; flex-direction: row; width:100%; justify-content:center">
                                                <div class="input-valor" style="width:calc(100%/3)">
                                                    <!--Nome: -->
                                                    <label for="valor">Valor:</label>
                                                    <input type="text" onchange="checar()" name="valor" style="border-radius:0;"
                                                        class="form-control" placeholder="Valor" value="" required>
                                                </div>

                                                <div class="input-data-lanc" style="width:calc(100%/3)">
                                                    <label for="data_venc">Vencimento inicial:</label>
                                                    <input type="date" onchange="checar()" name="data_venc" style="border-radius:0;"
                                                        class="form-control" placeholder="Data de lançamento"
                                                        value="" required>
                                                </div>

                                                <div class="input-parcelas" style="width:calc(100%/3)">
                                                    <!--Nome: -->
                                                    <label for="parcelas">N. Lançamentos:</label>
                                                    <input type="number" onchange="checar()" name="n_lanc"
                                                        class="form-control" placeholder="N. Lançamentos"
                                                        value=""
                                                        style="border-radius:0;"
                                                        required>
                                                </div>
                                            </div>
                                            <div>
                                            <div class="d-flex flex-column">
                                                    <label>Descrição:</label>
                                                    <input value="" name="descricao" placeholder="Descricao" class="form-control">
                                                </div>
                                            </div>






                                            <div style="width: 100%;">
                                                <button type="submit" class="btn btn-primary mt-4"
                                                    style="float:right; background-color: #5856d6; border: 0;">Gerar
                                                    parcelas</button>
                                            </div>
                                    </form>



                                </div>

                        </div>
                    </div>
    </div>

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