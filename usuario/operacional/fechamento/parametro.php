<?php

require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/fecha01.php';

session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$lateral_target = 'cadastro';
$get_cadastro = 'parametro';

$fecha01 = Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa)[0] ?? null;

?>
<!DOCTYPE html>

<head>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>



<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/componentes/modais/lancamentos/modais.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="/choices/choices.css"></link>




<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>
<body id="body">


    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>

    <div class="main" id="container" >
        <div class="card card-fechamento-responsivo" style=" overflow:visible !important;">
            <div class="card-header">
                <h3>Parametro de Fechamento de Caixa</h3>
            </div>
            <div class="card-body" style="overflow:visible !important;">
                <div class="d-flex flex-column justify-content-between">
                    <form action="parametro_manager.php" method="post">
                        <div class="d-flex flex-row justify-content-evenly">
                            <div class="d-flex flex-column" style="width: 40%;">
                                <label>Cliente:</label>
                                <select type="text" class="form-control" name="id_cadastro">
                                    <option>Selecione</option>
                                    <?php foreach(Cadastro::read(id_empresa: $_SESSION['usuario']->id_empresa) as $cliente) { ?>
                                        <option value="<?= $cliente->id_cadastro ?>" <?= $fecha01 && $fecha01->id_cadastro == $cliente->id_cadastro ? 'selected' : '' ?>><?= $cliente->nom_fant ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="d-flex flex-column" style="width: 40%;">
                                <label>C. Custos:</label>
                                <select type="text" class="form-control" name="id_custos">
                                    <option>Selecione</option>
                                    <?php foreach(CentroCustos::read(id_empresa: $_SESSION['usuario']->id_empresa) as $custo) { ?>
                                        <option value="<?= $custo->id ?>" <?= $fecha01 && $fecha01->id_custos == $custo->id ? 'selected' : '' ?>><?= $custo->nome ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-row justify-content-evenly">
                            <div class="d-flex flex-column" style="width: 40%;">
                                <label>Titulo:</label>
                                <select type="text" class="form-control" id="titulo" name="id_titulo">
                                    <option>Selecione</option>
                                    <?php foreach(Con01::read(idempresa: $_SESSION['usuario']->id_empresa) as $conta) { ?>
                                        <option value="<?= $conta->id ?>" <?= $fecha01 && $fecha01->id_titulo == $conta->id ? 'selected' : '' ?>><?= $conta->nome ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="d-flex flex-column" style="width: 40%;">
                                <label>Subtitulo:</label>
                                <select type="text" class="form-control" id="subtitulo" name="id_subtitulo">
                                    <option>Selecione</option>
                                    <?php foreach(Con02::read(idempresa: $_SESSION['usuario']->id_empresa) as $conta) { ?>
                                        <option data-titulo-id="<?= $conta->id_con01 ?>" value="<?= $conta->id ?>" <?= $fecha01 && $fecha01->id_subtitulo == $conta->id ? 'selected' : '' ?>><?= $conta->nome ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" style="margin-top: 15px; width: 100%;">Salvar</button>
                        </div>
                    </form>
                </div>
                
            </div>

        </div>
    </div>
    

    

</body>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const tituloElement = document.getElementById('titulo');
    const subtituloElement = document.getElementById('subtitulo');

    if (!tituloElement || !subtituloElement) return;

    const subtituloInicial = subtituloElement.value;

    // Salva todos os subtítulos
    const todosSubtitulos = Array.from(subtituloElement.querySelectorAll('option')).map(opt => ({
        value: opt.value,
        label: opt.textContent.trim(),
        tituloId: opt.getAttribute('data-titulo-id')
    }));

    // Inicializa Choices
    let tituloChoice = null;
    let subtituloChoice = null;

    if (typeof Choices !== 'undefined') {
        tituloChoice = new Choices(tituloElement, {
            searchEnabled: true,
            searchPlaceholderValue: 'Digite para buscar...',
            noResultsText: 'Nenhum resultado encontrado',
            itemSelectText: '',
        });

        subtituloChoice = new Choices(subtituloElement, {
            searchEnabled: true,
            searchPlaceholderValue: 'Digite para buscar...',
            noResultsText: 'Nenhum resultado encontrado',
            itemSelectText: '',
        });
    }

    function carregarSubtitulos(tituloId) {
        if (!subtituloChoice) return;

        subtituloChoice.clearStore();
        subtituloChoice.clearChoices();

        // Sempre adiciona opção padrão
        subtituloChoice.setChoices(
            [{ value: '', label: 'Selecione' }],
            'value',
            'label',
            true
        );

        if (!tituloId) {
            subtituloChoice.setChoiceByValue(subtituloInicial || '');
            return;
        }

        const filtrados = todosSubtitulos.filter(sub => sub.tituloId === tituloId);

        if (filtrados.length > 0) {
            subtituloChoice.setChoices(filtrados, 'value', 'label', false);
        }

        // 🔥 mantém o valor selecionado corretamente
        if (subtituloInicial && filtrados.some(s => s.value === subtituloInicial)) {
            subtituloChoice.setChoiceByValue(subtituloInicial);
        } else {
            subtituloChoice.setChoiceByValue('');
        }
    }

    // Evento de mudança
    tituloElement.addEventListener('change', function (e) {
        const valor = e.detail ? e.detail.value : e.target.value;
        carregarSubtitulos(valor);
    });

    // Carrega automaticamente ao abrir
    carregarSubtitulos(tituloElement.value);


    // ===== SCROLL =====
    let posicao = localStorage.getItem('posicaoScroll');

    if (posicao) {
        setTimeout(() => {
            window.scrollTo(0, JSON.parse(posicao));
        }, 1);
    }

    window.addEventListener('scroll', () => {
        localStorage.setItem('posicaoScroll', JSON.stringify(window.scrollY));
    });

});

<?php $erro = filter_input(INPUT_GET, 'erro'); if($erro == 'campos_obrigatorios') {
    echo "alert('Todos os campos são obrigatórios.');";
    echo "window.location.href = 'parametro.php';";
} ?>
</script>





</html>