<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
require_once __DIR__ . '/../../db/entities/ope01.php';
require_once __DIR__ . '/../../db/entities/band01.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../db/entities/pra01.php';
require_once __DIR__ . '/../../db/entities/contas.php';
$lateral_cartao = true;
$lateral_target = 'cadastro_cartao';
$operadoras = Ope01::read(null, $_SESSION['usuario']->id_empresa);
$acao = filter_input(INPUT_GET, 'acao');
$target = filter_input(INPUT_GET, 'target');

?>
<!DOCTYPE html>
<head>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>



<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/componentes/modais/lancamentos/modais.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="../choices/choices.css"></link>




<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>
<body id="body">


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>

    <div class="main" id="container">

                <div class="botao">
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal_adicionar_operadora">Adicionar Operadora</button>
    </div>

        

<?php if(!empty($operadoras)){ ?>
<?php foreach($operadoras as $i => $operadora) {  
    $bandeiras = Band01::read(null, $_SESSION['usuario']->id_empresa, $operadora->id);
    ?>

<div class="accordion custom-accordion" id="accordionExample">
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?=$i?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?=$i?>" aria-expanded="false" aria-controls="collapse<?=$i?>">
                <span style="color: #303640; font-size:1em; font-weight:500;"> <?php echo htmlspecialchars($operadora->descricao, ENT_QUOTES, 'UTF-8'); ?> </span>
            </button>
        </h2>
        <div id="collapse<?=$i?>" class="accordion-collapse collapse" aria-labelledby="heading<?=$i?>">
            <div class="accordion-body">
                <div class="inner-accordion">
                    <div class="botoes-contas">
                        <button data-bs-toggle="modal" data-bs-target="#modal_adicionar_bandeira" data-operadora-id="<?=$operadora->id?>"  class="btn btn-primary btn-sm">Adicionar Bandeiras</button>
                        <a href="cadastro_cartao.php?target=operadora&acao=editar&id_ope01=<?=$operadora->id?>" class="btn btn-primary btn-sm">Editar Operadora</a>
                    </div>
                        <?php foreach($bandeiras as $j => $bandeira) { 
                            $prazos = Pra01::read(null, $operadora->id, $_SESSION['usuario']->id_empresa, id_bandeira: $bandeira->id);
                            ?>
                                <div class="accordion custom-accordion" id="accordionExample">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingb<?=$j?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseb<?=$j?>" aria-expanded="false" aria-controls="collapseb<?=$j?>">
                                                <span style="color: #303640; font-size:0.8em; font-weight:500;"><?php echo htmlspecialchars($bandeira->descricao, ENT_QUOTES, 'UTF-8') . '  (' . $bandeira->tipo . ')'; ?> </span>
                                            </button>
                                        </h2>
                                        <div id="collapseb<?=$j?>" class="accordion-collapse collapse" aria-labelledby="headingb<?=$j?>">
                                            <div class="accordion-body">
                                                <div class="inner-accordion">
                                                    <div class="botoes-contas">
                                                        <button data-bs-toggle="modal" data-bs-target="#modal_adicionar_prazo" data-operadora-id="<?=$operadora->id?>" data-bandeira-id="<?=$bandeira->id?>" class="btn btn-primary btn-sm">Adicionar Tarifa</button>
                                                        <a href="cadastro_cartao.php?target=bandeira&acao=editar&id_band01=<?=$bandeira->id?>" class="btn btn-primary btn-sm">Editar Bandeira</a>
                                                    </div>
                                                    <table class="table table-striped">
                                                        <tr>
                                                            <th>Parcela</th>
                                                            <th>Prazo</th>
                                                            <th>Taxa</th>
                                                        </tr>
                                                    
                                                        <?php foreach($prazos as $prazo) { 
                                                            $link = 'cadastro_cartao.php?target=prazo&acao=editar&id_pra01=' . $prazo->id;
                                                            ?>
                                                            <tr>
                                                                <td onclick="window.location.href='<?=$link?>'"><?= htmlspecialchars($prazo->parcela, ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td onclick="window.location.href='<?=$link?>'"><?= htmlspecialchars($prazo->prazo, ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td onclick="window.location.href='<?=$link?>'"><?= number_format(htmlspecialchars($prazo->taxa, ENT_QUOTES, 'UTF-8'), 2, ',', '.') ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php } ?>


    </div>   
    <?php require_once __DIR__ . '/../../componentes/modais/cartao/modal_adicionar_operadora.php' ?>
    <?php require_once __DIR__ . '/../../componentes/modais/cartao/modal_adicionar_bandeira.php' ?>
    <?php require_once __DIR__ . '/../../componentes/modais/cartao/modal_adicionar_prazo.php' ?>         
    
    
    <?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?> 
</body>
<script>

</script>
<script src="gerar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>


<script>

document.addEventListener('DOMContentLoaded', () => {

    console.log("🔧 Inicializando títulos e subtítulos (modal + filtros)...");

    <?php $subtitulo_selecionado = Ope01::read(filter_input(INPUT_GET, 'id_ope01'))[0]->id_con02 ?>

    const subtituloSelecionado    = <?= json_encode($subtitulo_selecionado ?? '') ?>;

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
        <?php if($acao == 'editar' && $target == 'operadora') { ?>
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
        <?php } ?>
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



<?php if($acao == 'editar') { ?>
    <?php if($target == 'bandeira') { ?>
    
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_adicionar_bandeira');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_cartao.php';
            });
        });
    
    <?php } ?>
    <?php if($target == 'operadora') { ?>
    
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_adicionar_operadora');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_cartao.php';
            });
        });
    
    <?php } ?>
    <?php if($target == 'prazo') { ?>
    
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_adicionar_prazo');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'cadastro_cartao.php';
            });
        });
    
    <?php } ?>
<?php } ?>
</script>


</html>