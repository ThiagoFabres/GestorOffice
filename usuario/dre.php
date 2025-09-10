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
 
$data = new DateTime();
$data_atual = $data->format('Y-m-d');

$data_semana = $data->modify('+7 days');
$data_semana = $data_semana->format('Y-m-d');


$pagamentos_venceu = Pag02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'venceu');
$pagamentos_hoje = Pag02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'hoje');
$pagamentos_semana = Pag02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, $data_semana, null, null, null, null, true, 'semana');

$recebimentos_venceu = Rec02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'venceu');
$recebimentos_hoje = Rec02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, null, null, null, null, null, true, 'hoje');
$recebimentos_semana = Rec02::read(null, $_SESSION['usuario']->id_empresa, null, null, null, $data_atual, $data_semana, null, null, null, null, true, 'semana');

?>


<!DOCTYPE html>




<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js" integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

            <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x-icon">
    <title>Gestor Office Control</title>
</head>

<body id="body" >


    <nav id="barra-lateral">
        <div id="logo-container">
            <img width="220px" height="220px" src="/gestor-office.png" alt="Logo" class="logo">
        </div>
    <div id="itens-menu">
                        <div class="menu-item">
            <a href="index.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-layers"></i></div> Dashboard </a>
        </div>
    <?php if($_SESSION['usuario']->processar == 1) { ?>
        <div class="menu-item accordion" >

<a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button" aria-expanded="false" aria-controls="cadastrosMenu">
        <i class="bi bi-person"></i> Cadastros
      </a>
      <div class="collapse" id="cadastrosMenu">
        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
          <li><a href="cadastrar.php?cadastro=cliente" class="link-light text-decoration-none"><i class="bi bi-person"></i>Cliente/Fornecedor</a></li>
          <li><a href="cadastrar.php?cadastro=bairro" class="link-light text-decoration-none"><i class="bi bi-houses"></i>Bairro</a></li>
          <li><a href="cadastrar.php?cadastro=cidade" class="link-light text-decoration-none"><i class="bi bi-buildings"></i>Cidade</a></li>
          <li><a href="cadastrar.php?cadastro=pagamento" class="link-light text-decoration-none"><i class="bi bi-cash-coin"></i>Tipo Pagamento</a></li>
          <li><a href="cadastrar.php?cadastro=categoria" class="link-light text-decoration-none"><i class="bi bi-tag"></i>Categoria</a></li>
          
        </ul>
      </div>
        </div>
        <?php } ?>

        <div class="menu-item">
            <a href="contas.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano de Contas </a>
        </div>

        <div class="menu-item">
            <a href="receber.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber </a>
        </div>

        <div class="menu-item">
            <a href="pagar.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a Pagar </a>
        </div>

        <div class="menu-item">
            <a href="dre.php"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-file-earmark-text"></i></div>DRE</a>
        </div>


        </div>
        </div>

    </nav>


    <div id="header">
        
        <button onclick="encolher()" style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer; z-index:1000;">
            <span class="btn bi bi-list"></span>
        </button>
        
    <div id="titulo-header">
        
        <a>Dashboard</a>
    </div>
    <div id="menu-superior">
        <a class="superior-item" href="/admin/">Dashboard</a>
    </div>
    <div class="conta-header" style="position:relative; float:right; margin-right:2em;">
        <button id="userBtn" type="button" style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer;">
            <span style="color:#181f2b;"><?= htmlspecialchars($_SESSION['usuario']->nome, ENT_QUOTES, 'UTF-8') ?> </span>
        </button>
        <div id="userMenu" style="right:0; z-index: 1000000;">
            <a href="/" class="dropdown-item">
                <i class="bi bi-box-arrow-left"></i> Logout
        </a>
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


