<?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/contas.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/recebimentos.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
require_once __DIR__ . '/../db/entities/pagar.php';
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
$lateral_target = 'dashboard';
 
$data = new DateTime();
$data_atual = $data->format('Y-m-d');

$data_ontem = $data->modify('-1 days');
$data_ontem = $data_ontem->format('Y-m-d');

$data_amanha = $data->modify('+2 days');
$data_amanha = $data_amanha->format('Y-m-d');



$pagamentos_venceu = Pag02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, 
$data_atual, null, null, null, null, null, true, 'venceu');
$total_pag_venceu = 0;
foreach($pagamentos_venceu as $pag02) {
    $total_pag_venceu += $pag02->valor_par;
}
$total_pag_venceu = number_format($total_pag_venceu, 2, ',', '.');

$pagamentos_hoje = Pag02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'hoje');
$total_pag_hoje = 0;
foreach($pagamentos_hoje as $pag02) {
    $total_pag_hoje += $pag02->valor_par;
}
$total_pag_hoje = number_format($total_pag_hoje, 2, ',', '.');

$pagamentos_a_vencer = Pag02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'a_vencer');
$total_pag_a_vencer = 0;
foreach($pagamentos_a_vencer as $pag02) {
    $total_pag_a_vencer += $pag02->valor_par;
}
$total_pag_a_vencer = number_format($total_pag_a_vencer, 2, ',', '.');

$recebimentos_venceu = Rec02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'venceu');
$total_rec_venceu = 0;
foreach($recebimentos_venceu as $rec02) {
    $total_rec_venceu += $rec02->valor_par;
}
$total_rec_venceu = number_format($total_rec_venceu, 2, ',', '.');

$recebimentos_hoje = Rec02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'hoje');
$total_rec_hoje = 0;
foreach($recebimentos_hoje as $rec02) {
    $total_rec_hoje += $rec02->valor_par;
}
$total_rec_hoje = number_format($total_rec_hoje, 2, ',', '.');

$recebimentos_a_vencer = Rec02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'a_vencer');
$total_rec_a_vencer = 0;
foreach($recebimentos_a_vencer as $rec02) {
    $total_rec_a_vencer += $rec02->valor_par;
}
$total_rec_a_vencer = number_format($total_rec_a_vencer, 2, ',', '.');

?>


<!DOCTYPE html>




<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js" integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

            <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="style/dash.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>

    <title>Gestor Office Control</title>
</head>

<body id="body" >


    <?php require_once __DIR__ . '/../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../componentes/header/header.php' ?>



    <div class="main" id="container" style="padding-block:0;">
        <div class="view-dash">
            <div class="dashboard-group">
                <table class="table-bordered">
                    <thead>
                        <tr class="tr-clientes-dash">
                            <h1>Contas a receber</h1>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <tr>
                            <td>
                                <table class="table-bordered" onclick="window.location.href='receber.php?&filtro_data_final=<?= $data_ontem ?>&filtro_por=vencimento&opcao_filtro=abertos'">
                                    <thead>
                                        <tr class="tr-clientes-dash parcela_cor_vermelha" >
                                            <th>Vencidos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="tr-clientes-dash">
                                            <td>R$ <?=$total_rec_venceu?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>

                            <td>
                                <table class="table-bordered" onclick="window.location.href='receber.php?filtro_data_inicial=<?= $data_atual ?>&filtro_data_final=<?= $data_atual ?>&filtro_por=vencimento&opcao_filtro=abertos'">
                                    <thead>
                                        <tr class="tr-clientes-dash parcela_cor_amarela">
                                            <th>Vence hoje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="tr-clientes-dash">
                                            <td>R$ <?=$total_rec_hoje?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>

                            <td>
                                <table class="table-bordered" onclick="window.location.href='receber.php?&filtro_data_inicial=<?= $data_amanha ?>&filtro_por=vencimento&opcao_filtro=abertos'">
                                    <thead>
                                        <tr class="tr-clientes-dash parcela_cor_azul">
                                            <th>A vencer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="tr-clientes-dash">
                                            <td>R$ <?=$total_rec_a_vencer?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="dashboard-group">
                <table class="table-bordered">
                    <thead>
                        <tr>
                            <h1>Contas a pagar</h1>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <tr>
                            <td>
                                <table class="table-bordered" onclick="window.location.href='pagar.php?&filtro_data_final=<?= $data_ontem ?>&filtro_por=vencimento&opcao_filtro=abertos'">
                                    <thead>
                                        <tr class="tr-clientes-dash parcela_cor_vermelha">
                                            <th>Vencidos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="tr-clientes-dash">
                                            <td>R$ <?=$total_pag_venceu?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>

                            <td>
                                <table class="table-bordered" onclick="window.location.href='pagar.php?filtro_data_inicial=<?= $data_atual ?>&filtro_data_final=<?= $data_atual ?>&filtro_por=vencimento&opcao_filtro=abertos'">
                                    <thead>
                                        <tr class="tr-clientes-dash parcela_cor_amarela">
                                            <th>Vence hoje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="tr-clientes-dash">
                                            <td>R$ <?=$total_pag_hoje?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>

                            <td>
                                <table class="table-bordered" onclick="window.location.href='pagar.php?&filtro_data_inicial=<?= $data_amanha ?>&filtro_por=vencimento&opcao_filtro=abertos'">
                                    <thead>
                                        <tr class="tr-clientes-dash parcela_cor_azul">
                                            <th>A vencer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="tr-clientes-dash">
                                            <td>R$ <?=$total_pag_a_vencer?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
</div>
</div>
</div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</body>

<script>

document.addEventListener('DOMContentLoaded', function() {
    var userBtn = document.getElementById('userBtn');
    var userMenu = document.getElementById('userMenu');
    if (userBtn && userMenu) {
        userBtn.onclick = function(e) {
            e.stopPropagation();
            if (userMenu.style.display === 'block') {
                userMenu.style.display = 'none';
            } else {
                userMenu.style.display = 'block';
            }
        };
        document.addEventListener('click', function(e) {
            if (userMenu.style.display === 'block') {
                userMenu.style.display = 'none';
            }
        });
        userMenu.onclick = function(e) {
            e.stopPropagation();
        };
    }
});
document.getElementById('titulo').addEventListener('change', function() {
    var tituloId = this.value;
    var subtituloSelect = document.getElementById('subtitulo');
    var options = subtituloSelect.querySelectorAll('option');

    options.forEach(function(option) {
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
    document.querySelectorAll('.valor-parcela').forEach(function(input) {
        let val = parseFloat(input.value.replace(',', '.'));
        if (!isNaN(val)) total += val;
    });
    document.getElementById('totalParcelas').textContent = total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    // Pega o valor total esperado do campo oculto ou célula da tabela
    let valorTotal = document.querySelector('input[name="valor"]');
    let valorEsperado = 0;
    if (valorTotal) {
        valorEsperado = parseFloat(valorTotal.value.replace(',', '.'));
    } else {
        // Alternativa: pega da célula da tabela
        let celula = document.querySelector('td:last-child');
        if (celula) {
            valorEsperado = parseFloat(celula.textContent.replace(/[^0-9,\.]/g, '').replace(',', '.'));
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

document.querySelectorAll('.valor-parcela').forEach(function(input) {
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
    }}
    

</script>





</html>


