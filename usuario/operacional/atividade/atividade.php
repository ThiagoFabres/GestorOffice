<?php

require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/fecha01.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/ativ01.php';
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$lateral_target = 'operacional_atividade';
$lateral_operacional = true;
$tipo_pagamento_lista = TipoPagamento::read(idempresa: $_SESSION['usuario']->id_empresa);
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

    <div class="main" id="container">
        <div class="card" style="max-width: 50%;">
            <!-- enviar relatorio de abertura da loja -->
            <div class="card-body">
                <form action="atividade_manager.php" method="post">
                    <input type="hidden" name="localizacao" id="input-localizacao" value="">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar Inicio De Atividade</button>
                </form>

                <div class="d-flex">
                    <table class="table table-striped mt-4">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Nome</th>
                                <th>Localização</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $atividades = Ativ01::read(id_empresa: $_SESSION['usuario']->id_empresa);
                            $empresa = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0] ?? null;
                            $hora_inicio = $empresa && $empresa->ativ_inicio ? date('H:i:s', strtotime($empresa->ativ_inicio)) : null;
                            $tolerancia = $empresa && $empresa->tolerancia ? date('H:i:s', strtotime($empresa->tolerancia)) : '00:00:00';
                            
                            // Calcular hora limite (início + tolerância)
                            $hora_limite = null;
                            if ($hora_inicio && $tolerancia) {
                                $timestamp_inicio = strtotime($hora_inicio);
                                $timestamp_tolerancia = strtotime($tolerancia) - strtotime('00:00:00');
                                $hora_limite = date('H:i:s', $timestamp_inicio + $timestamp_tolerancia);
                            }

                            foreach($atividades as $atividade):
                                if ($hora_inicio && $hora_limite) {
                                    $hora_atividade = strtotime($atividade->hora);
                                    $hora_inicio_ts = strtotime($hora_inicio);
                                    $hora_limite_ts = strtotime($hora_limite);

                                    if ($hora_atividade <= $hora_inicio_ts) {
                                        $cor_atividade = 'parcela_cor_verde';
                                    } elseif ($hora_atividade <= $hora_limite_ts) {
                                        $cor_atividade = 'parcela_cor_amarela';
                                    } else {
                                        $cor_atividade = 'parcela_cor_vermelha';
                                    }
                                } else {
                                    $cor_atividade = 'parcela_cor_azul';
                                }
                            ?>
                            
                                <tr class="<?= $cor_atividade ?>">
                                    <td><?php echo date('d/m/Y', strtotime($atividade->data)); ?></td>
                                    <td><?php echo date('H:i:s', strtotime($atividade->hora)); ?></td>
                                    <td><?php echo htmlspecialchars($atividade->nome); ?></td>
                                    <td><?php echo htmlspecialchars($atividade->localizacao); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                </div>
            </div>
        </div>
    </div>
    

    

</body>
<script>

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                document.getElementById('input-localizacao').value = lat + ', ' + lng;
            },
            function(error) {
                alert('Erro ao obter localização: ' + error.message);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        alert('Geolocalização não é suportada pelo seu navegador.');
    }

    <?php if(isset($_GET['erro'])): ?>
        <?php if($_GET['erro'] === 'localizacao') {?>
            alert('Por favor, permita o acesso a sua localização para registrar o inicio da atividade.')
        <?php } else if($_GET['erro'] === 'nome') { ?>
            alert('Nome é obrigatório para iniciar a atividade.')
        <?php } else if($_GET['erro'] === 'data') { ?>
            alert('Já existe um registro de inicio de atividade para hoje.')
        <?php } ?>
    <?php endif; ?>

</script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>





</html>