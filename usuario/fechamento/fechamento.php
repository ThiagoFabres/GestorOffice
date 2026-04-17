<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/fecha01.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$lateral_target = 'fechamento';
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


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>

    <div class="main" id="container">
        <div class="card card-fechamento-responsivo" style="overflow:visible !important;">
            <div class="card-header">
                <h3>Fechamento de Caixa</h3>
            </div>
            <div class="card-body" style="overflow: visible; padding-bottom: 0;">
                <?php 
                if(!Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa)) {
                    echo '<div class="alert alert-danger" style="text-align:center;">Não Existe um Parametro de fechamento cadastrado.</div>';
                } else {
                ?>
                <form action="fechamento_manager.php" method="post">
                    <div class="d-flex flex-row justify-content-evenly gap-3 mb-3">
                        <div class="d-flex flex-column w-50">
                            <label>Data</label>
                            <input class="form-control rounded-0" type="date" id="data_fechamento" name="data" value="<?= (new DateTime())->format('Y-m-d') ?>">
                        </div>
                        <div class="d-flex flex-column w-50">
                            <label>Turno</label>
                            <input class="form-control rounded-0" type="number" name="turno" id="input_turno" list="turno-list" placeholder="Turno" onkeypress="return /[0-9,]/.test(event.key)">
                            <datalist id="turno-list"></datalist>
                        </div>
                        <div class="d-flex flex-column w-50">
                            <label>Nome</label>
                            <input class="form-control rounded-0" type="text" id="input_nome_caixa" name="nome_caixa" placeholder="Nome">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex flex-column">
                        <div class="d-flex flex-column gap-3">
                            <?php foreach($tipo_pagamento_lista as $i => $tipo_pagamento) {?>
                            <div class="d-flex flex-row">
                                <div class="d-flex flex-column w-50">
                                    <select class="form-select tipo-pagamento form-control rounded-0" name="tipo_pagamento[<?= $i ?>]" style="height:2.75em; appearance: none; background-image: none; pointer-events:none;">
                                            <option value="<?=$tipo_pagamento->id?>"><?=$tipo_pagamento->nome?></option>
                                    </select>
                                </div>

                                <div class="d-flex flex-column w-50">
                                    <input class="form-control valor" type="text" inputmode="decimal" pattern="[0-9.,]*" onkeypress="return /[0-9,]/.test(event.key)" name="valor[<?= $i ?>]" placeholder="Valor">
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex flex-row justify-content-between align-items-center mt-3">
                        <div>
                            <div>
                                Total: R$ <span id="total-valor">0.00</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Processar</button>
                    </div>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
    

    

</body>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>


<script>

// Função para converter valores brasileiros para número
function parseBrazilianDecimal(valorStr) {
    let value = String(valorStr).trim().replace(/\s+/g, '');
    if (value === '') {
        return NaN;
    }

    const commaCount = (value.match(/,/g) || []).length;
    const dotCount = (value.match(/\./g) || []).length;

    if (commaCount > 0 && dotCount > 0) {
        // Formato brasileiro com separador de milhar e decimal: 1.234,56
        value = value.replace(/\./g, '').replace(/,/g, '.');
    } else if (commaCount > 0) {
        // Formato brasileiro simples: 1234,56
        value = value.replace(/,/g, '.');
    }

    return parseFloat(value);
}

function formatBrazilianDecimal(valor) {
    if (valor === null || valor === undefined || valor === '') {
        return '';
    }
    const numero = typeof valor === 'number' ? valor : parseBrazilianDecimal(valor);
    if (Number.isNaN(numero)) {
        return '';
    }
    return numero.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Função para atualizar o total
function atualizarTotal() {
    let total = 0;
    document.querySelectorAll('.valor').forEach(input => {
        const valor = parseBrazilianDecimal(input.value);
        if (!isNaN(valor)) {
            total += valor;
        }
    });
    document.getElementById('total-valor').textContent = total.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function definirTurnoPadrao(turnos) {
    if (!Array.isArray(turnos) || turnos.length === 0) {
        if (!document.getElementById('input_turno').value) {
            document.getElementById('input_turno').value = '1';
        }
        return;
    }

    const numeros = turnos
        .map(turno => parseInt(turno, 10))
        .filter(n => !Number.isNaN(n));

    const proximo = numeros.length ? Math.max(...numeros) + 1 : 1;
    if (!document.getElementById('input_turno').value) {
        document.getElementById('input_turno').value = String(proximo);
    }
}

// Atualiza o total quando qualquer input de valor é alterado
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.valor').forEach(input => {
        input.addEventListener('change', atualizarTotal);
        input.addEventListener('input', atualizarTotal);
    });
    atualizarTotal();

        const dataInput = document.getElementById('data_fechamento');
        const turnoInput = document.getElementById('input_turno');

        function clearDadosTurno() {
            document.getElementById('input_nome_caixa').value = '';
            document.querySelectorAll('.valor').forEach(input => input.value = '');
            atualizarTotal();
        }

        function preencherValoresPorTurno(dados) {
            if (!dados || !dados.valores) {
                clearDadosTurno();
                return;
            }

            document.getElementById('input_nome_caixa').value = dados.nome_caixa || '';
            document.querySelectorAll('.tipo-pagamento').forEach((select, index) => {
                const tipoId = select.value;
                const valorInput = document.querySelectorAll('.valor')[index];
                if (dados.valores[tipoId] !== undefined) {
                    valorInput.value = formatBrazilianDecimal(dados.valores[tipoId]);
                } else {
                    valorInput.value = '';
                }
            });
            atualizarTotal();
        }

        const fechamentoApiUrl = './fechamento_ajax.php';

        async function carregarTurnos(data) {
            if (!data) return;
            const url = `${fechamentoApiUrl}?action=getTurnos&data=${encodeURIComponent(data)}`;
            const response = await fetch(url, { credentials: 'same-origin' });
            const text = await response.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (error) {
                console.error('Erro ao parsear JSON de carregarTurnos:', error, 'responseText:', text);
                return;
            }
            const list = document.getElementById('turno-list');
            list.innerHTML = '';

            if (json.success && Array.isArray(json.turnos)) {
                json.turnos.forEach(turno => {
                    const option = document.createElement('option');
                    option.value = String(turno);
                    list.appendChild(option);
                });
                definirTurnoPadrao(json.turnos);
            } else {
                definirTurnoPadrao([]);
            }
        }

        async function carregarDadosTurno(data, turno) {
            if (!data || !turno) {
                clearDadosTurno();
                return;
            }

            const url = `${fechamentoApiUrl}?action=getDadosTurno&data=${encodeURIComponent(data)}&turno=${encodeURIComponent(turno)}`;
            const response = await fetch(url, { credentials: 'same-origin' });
            const text = await response.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (error) {
                console.error('Erro ao parsear JSON de carregarDadosTurno:', error, 'responseText:', text);
                clearDadosTurno();
                return;
            }
            if (json.success) {
                preencherValoresPorTurno(json);
            } else {
                clearDadosTurno();
            }
        }

        dataInput.addEventListener('change', function() {
            carregarTurnos(this.value);
            clearDadosTurno();
        });

        turnoInput.addEventListener('change', function() {
            carregarDadosTurno(dataInput.value, this.value);
        });

        turnoInput.addEventListener('blur', function() {
            carregarDadosTurno(dataInput.value, this.value);
        });

        carregarTurnos(dataInput.value);
});


</script>




</html>