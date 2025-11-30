

<!DOCTYPE html>
<?php 
require_once __DIR__ . '/../../../db/entities/usuarios.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
require_once __DIR__ . '/../../../db/entities/banco01.php';
require_once __DIR__ . '/../../../db/entities/banco02.php';
require_once __DIR__ . '/../../../db/entities/palavra_chave.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/buscar_documento.php';

$numero_exibir = filter_input(INPUT_POST, 'numero_exibido') ?? filter_input(INPUT_GET, 'numero_exibido') ?? 10;
$numero_pagina = filter_input(INPUT_POST, 'pagina') ?? filter_input(INPUT_GET, 'pagina') ?? 1;
$numero_pagina = intval($numero_pagina);
$numero_exibir = intval($numero_exibir);

$bancario_paginas = Ban02::read(
    id_empresa:$_SESSION['usuario']->id_empresa,
    read_paginas:true
); 
echo '<pre>';
var_dump($bancario_paginas);
echo '</pre>';

$total_paginas = ceil($bancario_paginas / $numero_exibir);

$novo_documento = buscarDocumento();

$lateral_target = 'movimentacao';
$lateral_bancario = true;
$acao = filter_input(INPUT_GET, 'acao', FILTER_SANITIZE_STRING);
$ofx = filter_input(INPUT_GET, 'ofx', FILTER_SANITIZE_NUMBER_INT);
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
<link rel="stylesheet" href="movimentacao.css">
<link rel="stylesheet" href="/componentes/modais/lancamentos/modais.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="/../../../choices/choices.css"></link>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>
    <div class="main" id="container">
        <button class="btn btn-danger btn-sm" onclick="window.location.href='movimentacao_manager.php?acao=limpar_titulo'">Limpar Titulo</button>
        <button  data-bs-toggle="modal" data-bs-target="#modal_cadastro_bancario" class="btn btn-primary" >Upload Arquivo OFX</button>
        <div class="card">
            <div class="card-header d-flex fd-row justify-content-center align-items-center">
                
                    <div class="card-header-borda d-flex flex-row justify-content-space-between align-items-center">
                        <div class="inputs-pagamento-text ai-start justify-content-start">

                            <!-- Data inicial -->
                            <div style="width: 30%;">
                                <label for="filtro_data_inicial">Data Inicial:</label>
                                <input type="date" id="filtro_data_inicial"
                                    name="filtro_data_inicial"
                                    value=""
                                    class="form-control" style="border-top-right-radius: 0;">
                            </div>

                            <!-- Data final -->
                            <div style="width: 30%;">
                                <label for="filtro_data_final">Data Final:</label>
                                <input type="date" id="filtro_data_final"
                                    name="filtro_data_final"
                                    value="" class="form-control"
                                    style="border-radius: 0;">
                            </div>
                        </div>
                        <div class="inputs-pagamento-btn d-flex fd-column ai-start justify-content-space-between">
                            <div style="width: 25%; display:flex; flex-direction:row; gap: 3%;">
                                <button class="btn btn-primary btn-sm">Buscar</button>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal_conciliar_palavra">Conciliar</button>
                            </div>
                        </div>
                    </div>
                
            </div>
            <div class="card-body">
                <table class="table table-hover" >
                    <thead>
                        <tr class="tr-header">
                            <th>Documento</th>
                            <th>Data de Lançamento</th>
                            <th>Tipo de Lançamento</th>
                            <th>Valor</th>
                            <th>Conta</th>
                            <th>Título</th>
                            <th>Subtítulo</th>
                            <th>Descrição</th>
                            <th>Descrição Complementar</th>
                        </tr>
                    </thead>
                    <tbody >
                        <?php

                        $movimentacoes = Ban02::read(
                            id_empresa: $_SESSION['usuario']->id_empresa,
                            numero_exibir: $numero_exibir,
                            numero_pagina: $numero_pagina
                        );

                         if(!empty($movimentacoes)) {
                            foreach($movimentacoes as $movimentacao) {
                                // echo '<pre>';
                                // print_r($movimentacao);
                                // echo '</pre>';
                            if($movimentacao->id_con01 != null) {
                                $con01 = Con01::read($movimentacao->id_con01, $_SESSION['usuario']->id_empresa)[0];
                            } else {
                                $con01 = null;
                            }
                            if($movimentacao->id_con02 != null) {
                                $con02 = Con02::read($movimentacao->id_con02, $_SESSION['usuario']->id_empresa)[0];
                            } else {
                                $con02 = null;
                            }
                            if($movimentacao->id_con01 != null && $movimentacao->id_con02 != null )   {
                                $cor_parcela = 'parcela_cor_verde';
                            } else {
                                $cor_parcela = 'parcela_cor_vermelha';
                            }
                                $tipo = $movimentacao->valor < 0 ? 'Débito' : 'Crédito';
                                $data_lancamento = DateTime::createFromFormat('Y-m-d', $movimentacao->data)->format('d/m/Y');
                                $conta_nome = Ban01::read($movimentacao->id_ban01, $_SESSION['usuario']->id_empresa)[0]->nome;
                         {?>
                         <tr class="<?=$cor_parcela?> tr-bancario" onclick="window.location.href='movimentacao.php?acao=conciliar&id=<?= $movimentacao->id ?>'">
                            <td><?=$movimentacao->documento?></td>
                            <td><?=$data_lancamento?></td>
                            <td><?=$tipo?></td>
                            <td>R$ <?=$movimentacao->valor?></td>
                            <td><?=$conta_nome?></td>
                            <td><?= isset($con01) ? $con01->nome : ''?></td>
                            <td><?= isset($con02) ? $con02->nome : ''?></td>
                            <td><?=$movimentacao->descricao?></td>
                            <td><?=$movimentacao->descricao_comp?></td>
                         </tr>
                        <?php } } }?>
                        <?php
                        // echo $novo_documento;
                        // echo '<pre>';
                        // print_r($_SESSION);
                        // echo '</pre>';
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                    <div class="card-select-pagina">
                        <?php
                        ?>
                        <form method="post" action="movimentacao.php">
                            <?php if ($numero_exibir != 10) { ?> <input type="hidden" value="<?= $numero_exibir ?>"
                                    name="numero_exibido" /> <?php } ?>



                            <?php for ($i = 1; $i <= $total_paginas; $i++) {
                                if ((($i == ($numero_pagina + 4) || $i == ($numero_pagina - 4)) && $i != 1) && $i != $total_paginas) { ?>
                                    ...

                                    <?php continue;
                                }
                                if ((($i > ($numero_pagina + 4) || $i < ($numero_pagina - 4)) && $i < $total_paginas) && $i != 1) {
                                    continue;
                                }
                                ?>

                                <button type="submit" name="pagina" class="form-control" <?php if ($numero_pagina == $i) { ?>
                                        disabled <?php } ?> value="<?= $i ?>"><?= $i ?></button>
                            <?php } ?>
                        </form>
                    </div>

                    <div class="card-select-numero">
                        <div>
                            <form method="post" action="movimentacao.php">

                                <select class="form-control" onchange="this.form.submit()" name="numero_exibido">
                                    <option <?php if ($numero_exibir == 5) { ?> selected <?php } ?> value="5">5</option>
                                    <option <?php if ($numero_exibir == 10) { ?> selected <?php } ?> value="10">10</option>
                                    <option <?php if ($numero_exibir == 20) { ?> selected <?php } ?> value="20">20</option>
                                    <option <?php if ($numero_exibir == 30) { ?> selected <?php } ?> value="30">30</option>
                                    <option <?php if ($numero_exibir == 40) { ?> selected <?php } ?> value="40">40</option>
                                    <option <?php if ($numero_exibir == 50) { ?> selected <?php } ?> value="50">50</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
        </div>
        
    </div>
    

    <?php 
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_cadastro_bancario.php';
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_conciliar.php';
    require_once __DIR__ . '/../../../componentes/modais/bancario/modal_conciliar_palavra.php';
    // Limpa a sessão após exibir a tabela para não mostrar em acessos futuros
    if (isset($_SESSION['ofx_transactions'])) {
        unset($_SESSION['ofx_transactions']);
    }
    if (isset($_SESSION['ofx_conta'])) {
        unset($_SESSION['ofx_conta']);
    }
    if (isset($_SESSION['file_name'])) {
        unset($_SESSION['file_name']);
    }
    if (isset($_SESSION['dias_usados'])) {
        unset($_SESSION['dias_usados']);
    }
    ?>

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
<script src="/../../../choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>




<?php if($ofx == 1) {?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_cadastro_bancario');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'movimentacao.php';
            });
        });
    </script>
<?php } ?>

<?php if($acao == 'conciliar') {?>
    <script>
        document.getElementById('modal_conciliar').addEventListener('shown.bs.modal', function () {
    // Inicialize Choices.js aqui para os selects do modal
    initTituloSubtitulo('titulo', 'subtitulo', null);
});
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_conciliar');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'movimentacao.php';
            });
        });
    </script>
<?php } ?>




</html>