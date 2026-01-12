

<!DOCTYPE html>
<?php 
require_once __DIR__ . '/../../db/entities/usuarios.php';

session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$lateral_target = 'relatorioBancario';
$lateral_bancario = true;
?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>

<script type="module" src="node_modules/smart-webcomponents/source/modules/smart.combobox.js"></script>
<link rel="stylesheet" type="text/css" href="node_modules/smart-webcomponents/source/styles/smart.default.css" />



<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/componentes/modais/lancamentos/modais.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="../choices/choices.css"></link>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>
    <div class="main" id="container">
        <div class="card">
            <div class="card-header d-flex fd-row justify-content-center align-items-center">
                
                    <div class="card-header-borda d-flex flex-row justify-content-space-between align-items-center">
                        <div class="inputs-pagamento-text ai-start justify-content-start">

                            <!-- Data inicial -->
                            <div style="width: 25%;">
                                <label for="filtro_data_inicial">Data Inicial:</label>
                                <input type="date" id="filtro_data_inicial"
                                    name="filtro_data_inicial"
                                    value=""
                                    class="form-control" style="border-top-right-radius: 0;">
                            </div>

                            <!-- Data final -->
                            <div style="width: 25%;">
                                <label for="filtro_data_final">Data Final:</label>
                                <input type="date" id="filtro_data_final"
                                    name="filtro_data_final"
                                    value="" class="form-control"
                                    style="border-radius: 0;">
                            </div>
                        </div>
                        <div class="inputs-pagamento-btn d-flex fd-column ai-start justify-content-space-between">
                            <div style="width: 25%;">
                                <button class="btn btn-primary btn-sm">Buscar</button>
                            </div>

                            <div style="width: 25%;">
                                
                            </div>
                        </div>
                    </div>
                
            </div>
        </div>
    </div>    

</body>

<script>
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

    function filtroSubtitulo(resetSubtitulo = true) {
        var tituloId = document.querySelector('select[name=filtro_titulo]').value;
        var subtituloSelect = document.querySelector('select[name=filtro_subtitulo]');
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

        if (resetSubtitulo) {
            subtituloSelect.value = ""; // Só reseta se for troca de título
        }
    }

    document.querySelector('select[name=filtro_titulo]').addEventListener('change', function () {
        filtroSubtitulo()
    });
    filtroSubtitulo()

    var posicao = localStorage.getItem('posicaoScroll');

    /* Se existir uma opção salva seta o scroll nela */
    if (posicao) {
        /* Timeout necessário para funcionar no Chrome */
        setTimeout(function () {
            window.scrollTo(0, posicao);
        }, 1);
    }

    /* Verifica mudanças no Scroll e salva no localStorage a posição */
    window.onscroll = function (e) {
        posicao = window.scrollY;
        localStorage.setItem('posicaoScroll', JSON.stringify(posicao));
    }




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

    document.addEventListener('DOMContentLoaded', function () {
        var modalQuitar = document.getElementById('modal_quitar');
        modalQuitar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var valorRestante = button.getAttribute('data-valor-restante');
            var parcelaAtual = button.getAttribute('data-parcela-atual');
            var parcelaGeral = button.getAttribute('data-parcela-geral');
            var vencimento = button.getAttribute('data-vencimento');
            var documento = button.getAttribute('data-documento');
            document.getElementById('modal_quitar_id').value = id;
            document.getElementById('modal_quitar_valor_restante').textContent = "Valor restante da parcela: R$ " + valorRestante;
            document.getElementById('modal_quitar_parcela_atual').textContent = parcelaAtual || '';
            document.getElementById('modal_quitar_parcela_geral').textContent = parcelaGeral || '';
            document.getElementById('modal_quitar_vencimento').textContent = vencimento || '';
            document.getElementById('modal_quitar_documento').textContent = documento || '';
            document.getElementById('modal_quitar_valor').placeholder = valorRestante || '';
        });
    });

    



    document.getElementById("btnBuscarDoc").addEventListener("click", function () {
        document.getElementById("documento").placeholder = 'Buscando...';
        fetch("/../db/buscar_documento_pag.php")
            .then(response => response.json())
            .then(data => {

                if (data.sucesso) {
                    document.getElementById("documento").value = data.numero;
                } else {
                    alert("Nenhum documento disponível encontrado.");
                }
            })
            .catch(err => console.error("Erro:", err));
    });

    
    


</script>

<script src="gerar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>



<script>
    
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function() {
        // ========== INICIALIZAÇÃO DO CHOICES.JS ==========
        const tituloFiltroElement = document.querySelector('#titulo-filtro');
        const subtituloFiltroElement = document.querySelector('#subtitulo-filtro');
        const tituloModalElement = document.querySelector('#titulo');
        const subtituloModalElement = document.querySelector('#subtitulo');

        // IMPORTANTE: Guarda os subtítulos ANTES de inicializar Choices.js
        const todosSubtitulos = subtituloFiltroElement ? 
            Array.from(subtituloFiltroElement.querySelectorAll('option')).map(opt => ({
                value: opt.value,
                label: opt.textContent.trim(),
                tituloId: opt.getAttribute('data-titulo-id')
            })) : [];

        const todosSubtitulosModal = subtituloModalElement ?
            Array.from(subtituloModalElement.querySelectorAll('option')).map(opt => ({
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
                true
            );
            subtituloFiltroChoice.setChoiceByValue('');
        }

        <?php if($acao != 'visualizar'){?>

        // Inicializa Choices.js nos elementos do modal
        let tituloModalChoice = null;
        let subtituloModalChoice = null;
        
        if (tituloModalElement && typeof Choices !== 'undefined') {
            tituloModalChoice = new Choices(tituloModalElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
        }

        if (subtituloModalElement && typeof Choices !== 'undefined') {
            subtituloModalChoice = new Choices(subtituloModalElement, {
                searchEnabled: true,
                searchPlaceholderValue: 'Digite para buscar...',
                noResultsText: 'Nenhum resultado encontrado',
                itemSelectText: '',
                removeItemButton: false,
            });
            
            // LIMPA IMEDIATAMENTE após inicializar
            subtituloModalChoice.clearChoices();
            subtituloModalChoice.clearStore();
            subtituloModalChoice.setChoiceByValue('');

        }

        console.log('Choices.js inicializado em receber.php:', {
            tituloFiltro: !!tituloFiltroChoice,
            subtituloFiltro: !!subtituloFiltroChoice,
            tituloModal: !!tituloModalChoice,
            subtituloModal: !!subtituloModalChoice
        });
        <?php } ?>
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

                    if (!tituloId) {
                        subtituloFiltroChoice.setChoiceByValue('');
                        return;
                    }

                    const subtitulosFiltrados = todosSubtitulos.filter(sub => sub.tituloId === tituloId);
                    if (subtitulosFiltrados.length > 0) {
                        subtituloFiltroChoice.setChoices(subtitulosFiltrados, 'value', 'label', false);
                    }

                    if (manterSelecao && valorAtual && subtitulosFiltrados.some(sub => sub.value === valorAtual)) {
                        setTimeout(() => {
                            subtituloFiltroChoice.setChoiceByValue(valorAtual);
                        }, 50);
                    } else {
                        subtituloFiltroChoice.setChoiceByValue('<?= $get_filtro_subtitulo; ?>');
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

        // ========== FILTRO DE SUBTÍTULOS (MODAL) ==========
        if (tituloModalElement && subtituloModalElement) {
            function carregarSubtitulosModal(tituloId) {
                if (subtituloModalChoice) {
                    subtituloModalChoice.clearStore();
                    subtituloModalChoice.clearChoices();
                    subtituloModalChoice.setChoices(
                        [{ value: '', placeholder: 'Selecione', disabled: false }],
                        'placeholder',
                        'label',
                        true
                    );

                    if (!tituloId) {
                        subtituloModalChoice.setChoiceByValue('');
                        return;
                    }

                    const subtitulosFiltrados = todosSubtitulosModal.filter(sub => sub.tituloId === tituloId);
                    if (subtitulosFiltrados.length > 0) {
                        subtituloModalChoice.setChoices(subtitulosFiltrados, 'value', 'label', false);
                    }
                    
                    subtituloModalChoice.setChoiceByValue('');
                } else {
                    subtituloModalElement.innerHTML = '<option value="">Selecione</option>';
                    
                    if (!tituloId) return;

                    todosSubtitulosModal
                        .filter(sub => sub.tituloId === tituloId)
                        .forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.value;
                            option.textContent = sub.label;
                            option.setAttribute('data-titulo-id', sub.tituloId);
                            subtituloModalElement.appendChild(option);
                        });
                    
                    subtituloModalElement.value = '';
                }
            }

            tituloModalElement.addEventListener('change', function(e) {
                const valor = e.detail ? e.detail.value : e.target.value;
                carregarSubtitulosModal(valor);
            });

            const tituloInicialModal = tituloModalElement.value;
            if (tituloInicialModal) {
                carregarSubtitulosModal(tituloInicialModal);
            }
        }
    
    
    ;}, 100);
});
</script>





</html>