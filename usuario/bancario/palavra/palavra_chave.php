

<!DOCTYPE html>
<?php 
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/palavra_chave.php';




session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$lateral_target = 'palavraChave';
$lateral_bancario = true;
$acao = filter_input(INPUT_GET, 'acao', FILTER_SANITIZE_STRING);
?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>

<script type="module" src="node_modules/smart-webcomponents/source/modules/smart.combobox.js"></script>
<link rel="stylesheet" type="text/css" href="node_modules/smart-webcomponents/source/styles/smart.default.css" />



<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/componentes/modais/lancamentos/modais.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="../../choices/choices.css"></link>
<style>
    .choices div {
        background-color: white;
    }
</style>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>
    <div class="main" id="container">
        
        <card class="card">
            <div class="card-header">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_cadastro_palavra">Adicionar Palavra</button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="background-color: #f3f4f7;">Palavra-Chave</th>
                            <th style="background-color: #f3f4f7;">Titulo</th>
                            <th style="background-color: #f3f4f7;">Subtitulo</th>
                            <th style="background-color: #f3f4f7;">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $palavras = Pal01::read(null, $_SESSION['usuario']->id_empresa);
                        foreach ($palavras as $palavra) {
                        ?>
                        <tr style="background-color: #f3f4f7;" onclick="window.location.href='palavra_chave.php?acao=editar&id=<?=$palavra->id?>'">
                            <td><?php echo htmlspecialchars($palavra->palavra); ?></td>
                            <td>
                                <?php 
                                    $titulo = Con01::read($palavra->id_con01)[0];
                                    echo htmlspecialchars($titulo->nome); 
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $subtitulo = Con02::read($palavra->id_con02)[0];
                                    echo htmlspecialchars($subtitulo->nome); 
                                ?>
                            </td>
                            <td>
                                <?php echo $palavra->tipo == 'D' ? 'Débito' : 'Crédito' ?>
                            </td>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </card>
    </div>




<?php require_once __DIR__ . '/../../../componentes/modais/bancario/modal_cadastro_palavra.php'; ?>

<?php require_once __DIR__ . '/../../../componentes/footer/footer.php' ?>     
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

    


    
    


</script>

<script src="gerar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../choices/choices.js"></script>
<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>



<?php
if($acao == 'editar') {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_cadastro_palavra');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'palavra_chave.php';
            });
        });
    </script>
<?php } ?>




</html>