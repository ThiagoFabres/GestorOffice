<?php

require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/fecha01.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
session_start();


$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->permissao_operacional != 1 || $empresa_usuario_obj->permissao_operacional != 1) {
    header('Location: /');
    exit;
}

$lateral_target = 'fechamento_caixa';
$lateral_operacional = true;
$tipo_pagamento_lista = TipoPagamento::read(idempresa: $_SESSION['usuario']->id_empresa);
$fecha01_rec = Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo: 'C')[0] ?? null;
$fecha01_pag = Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo: 'D')[0] ?? null;

$descricao_str = $fecha01_pag->descricao ?? '';

$descricao_array = array_map('trim', explode('/', $descricao_str));

$todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
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
    <link rel="stylesheet" href="/choices/choices.css">
    </link>




    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
    <title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php' ?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>

    <div class="main" id="container">
        <div class="card card-fechamento-responsivo" style="overflow:visible !important;">
            <div class="card-header">
                <h3>Fechamento de Caixa</h3>
            </div>
            <div class="card-body" style="overflow: visible; padding-bottom: 0;">
                <?php
                if (!Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa)) {
                    echo '<div class="alert alert-danger" style="text-align:center;">Não Existe um Parametro de fechamento cadastrado.</div>';
                } else {
                    ?>
                    <form id="fechamento-form" action="fechamento_manager.php" method="post">
                        <input type="hidden" name="target" value="both">
                        <div class="d-flex flex-row justify-content-evenly gap-3 mb-3">
                            <div class="d-flex flex-column w-50">
                                <label>Data</label>
                                <input class="form-control rounded-0" type="date" id="data_fechamento" name="data"
                                    value="<?= (new DateTime())->format('Y-m-d') ?>">
                            </div>
                            <div class="d-flex flex-column w-50">
                                <label>Turno</label>
                                <input class="form-control rounded-0" type="number" name="turno" id="input_turno"
                                    list="turno-list" placeholder="Turno" onkeypress="return /[0-9,]/.test(event.key)">
                                <datalist id="turno-list"></datalist>
                            </div>
                            <div class="d-flex flex-column w-50">
                                <label>Nome</label>
                                <input class="form-control rounded-0" type="text" id="input_nome_caixa" name="nome_caixa"
                                    placeholder="Nome">
                            </div>
                        </div>

                        <hr>
                        <?php if (Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo: 'C')) { ?>
                            <div class="card-header d-flex flex-row justify-content-center gap-3 mb-3"
                                style="border-bottom: 0; background-color: #989898">
                                <h3 style="color: black">Receitas</h3>
                            </div>
                            <div style="margin-bottom: 4%;">
                                <div class="d-flex flex-row justify-content-evenly">
                                    <div class="d-flex flex-column" style="width: 40%;">
                                        <label>Cliente:</label>
                                        <select type="text" class="form-control" name="cadastro_receitas">
                                            <option>Selecione</option>
                                            <?php foreach (Cadastro::read(id_empresa: $_SESSION['usuario']->id_empresa) as $cliente) { ?>
                                                <option value="<?= $cliente->id_cadastro ?>" <?= $fecha01_rec && $fecha01_rec->id_cadastro == $cliente->id_cadastro ? 'selected' : '' ?>>
                                                    <?= $cliente->nom_fant ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="d-flex flex-column" style="width: 40%;">
                                        <label>C. Custos:</label>
                                        <select type="text" class="form-control" name="centro_custos_receitas">
                                            <option>Selecione</option>
                                            <?php foreach (CentroCustos::read(id_empresa: $_SESSION['usuario']->id_empresa) as $custo) { ?>
                                                <option value="<?= $custo->id ?>" <?= $fecha01_rec && $fecha01_rec->id_custos == $custo->id ? 'selected' : '' ?>><?= $custo->nome ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex flex-row justify-content-evenly">
                                    <div class="d-flex flex-column" style="width: 40%;">
                                        <label>Titulo:</label>
                                        <select type="text" class="form-control" id="titulo-receber" name="titulo_receitas">
                                            <option>Selecione</option>
                                            <?php foreach (Con01::read(idempresa: $_SESSION['usuario']->id_empresa, tipo: 'C') as $conta) { ?>
                                                <option value="<?= $conta->id ?>" <?= $fecha01_rec && $fecha01_rec->id_titulo == $conta->id ? 'selected' : '' ?>><?= $conta->nome ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="d-flex flex-column" style="width: 40%;">
                                        <label>Subtitulo:</label>
                                        <select type="text" class="form-control" id="subtitulo-receber" name="subtitulo_receitas">
                                            <?php
                                                $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                                foreach ($todosSubtitulos as $sub) { ?>
                                                    <option value="<?= $sub->id ?>"
                                                    data-titulo-id="<?= $sub->id_con01 ?>" <?php if ($fecha01_rec->id_subtitulo == $sub->id) { ?> selected <?php } ?>>
                                                        <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <div class="d-flex flex-column gap-3">

                                    <?php
                                    if (empty($tipo_pagamento_lista)) {
                                        echo '<div class="alert alert-danger" style="text-align:center;">Não existe nenhum tipo de pagamento cadastrado.</div>';
                                    } else {
                                        foreach ($tipo_pagamento_lista as $i => $tipo_pagamento) { ?>
                                            <div class="d-flex flex-row">
                                                <div class="d-flex flex-column w-50">
                                                    <select class="form-select tipo-pagamento-receita form-control rounded-0"
                                                        name="tipo_pagamento_receita[<?= $i ?>]"
                                                        style="height:2.75em; appearance: none; background-image: none; pointer-events:none;">
                                                        <option value="<?= $tipo_pagamento->id ?>"><?= $tipo_pagamento->nome ?></option>
                                                    </select>
                                                </div>

                                                <div class="d-flex flex-column w-50">
                                                    <input class="form-control valor valor-receita" type="text" inputmode="decimal"
                                                        pattern="[0-9.,]*" onkeypress="return /[0-9,]/.test(event.key)"
                                                        name="valor_receita[<?= $i ?>]" placeholder="Valor">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="d-flex flex-row justify-content-between align-items-center mt-3">
                                            <div>
                                                <div>
                                                    Total: R$ <span id="total-valor-rec">0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <hr>
                        <?php }
                        if (Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo: 'D')) { ?>
                            <div class="card-header d-flex flex-row justify-content-center gap-3 mb-3"
                                style="border-bottom: 0; background-color: #989898">
                                <h3 style="color: black">Despesas</h3>
                            </div>
                            <div style="margin-bottom: 4%;">
                            <div class="d-flex flex-row justify-content-evenly">
                                <div class="d-flex flex-column" style="width: 40%;">
                                    <label>Cliente:</label>
                                    <select type="text" class="form-control" name="cadastro_despesas">
                                        <option>Selecione</option>
                                        <?php foreach (Cadastro::read(id_empresa: $_SESSION['usuario']->id_empresa) as $cliente) { ?>
                                            <option value="<?= $cliente->id_cadastro ?>" <?= $fecha01_pag && $fecha01_pag->id_cadastro == $cliente->id_cadastro ? 'selected' : '' ?>>
                                                <?= $cliente->nom_fant ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="d-flex flex-column" style="width: 40%;">
                                    <label>C. Custos:</label>
                                    <select type="text" class="form-control" name="centro_custos_despesas">
                                        <option>Selecione</option>
                                        <?php foreach (CentroCustos::read(id_empresa: $_SESSION['usuario']->id_empresa) as $custo) { ?>
                                            <option value="<?= $custo->id ?>" <?= $fecha01_pag && $fecha01_pag->id_custos == $custo->id ? 'selected' : '' ?>><?= $custo->nome ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex flex-row justify-content-evenly">
                                <div class="d-flex flex-column" style="width: 40%;">
                                    <label>Titulo:</label>
                                    <select type="text" class="form-control" id="titulo-pagar" name="titulo_despesas">
                                        <option>Selecione</option>
                                        <?php foreach (Con01::read(idempresa: $_SESSION['usuario']->id_empresa, tipo: 'D') as $conta) { ?>
                                            <option value="<?= $conta->id ?>" <?= $fecha01_pag && $fecha01_pag->id_titulo == $conta->id ? 'selected' : '' ?>><?= $conta->nome ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="d-flex flex-column" style="width: 40%;">
                                    <label>Subtitulo:</label>
<select type="text" class="form-control" id="subtitulo-pagar" name="subtitulo_despesas">
                                        <?php
                                            $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                            foreach ($todosSubtitulos as $sub) { ?>
                                                <option value="<?= $sub->id ?>"
                                                data-titulo-id="<?= $sub->id_con01 ?>" <?php if ($fecha01_rec->id_subtitulo == $sub->id) { ?> selected <?php } ?>>
                                                    <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                
                            </div>
                           <div class="d-flex justify-content-center mt-3">
                                <div style="width:40%;">
                                    <label>Tipo de Pagamento:</label>

                                    <select class="form-control" id="tipo_pagamento" name="tipo_pagamento_despesas">
                                        <option>Selecione</option>

                                        <?php foreach (TipoPagamento::read(idempresa: $_SESSION['usuario']->id_empresa) as $tipo_pagamento) { ?>
                                            <option value="<?= $tipo_pagamento->id ?>"
                                                <?= $fecha01_pag && $fecha01_pag->tipo_pagamento == $tipo_pagamento->id ? 'selected' : '' ?>>
                                                <?= $tipo_pagamento->nome ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            </div>
                            <?php if (empty($descricao_array)) {
                                echo '<div class="alert alert-danger" style="text-align:center;">Não existe nenhuma descrição cadastrada.</div>';
                            } else { ?>
                                <div class="d-flex flex-column">
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($descricao_array as $i => $descricao) { ?>
                                            <div class="d-flex flex-row">
                                                <div style="margin-right: 1em;"><input type="checkbox" name="custom_despesa[<?= $i ?>]"></div>
                                                <div class="d-flex flex-column w-50">
                                                    <input type="text" class="form-control rounded-0 descricao_padrao"
                                                        placeholder="Descrição" name="descricao_despesa[<?= $i ?>]"
                                                        value="<?= $descricao ?>">
                                                </div>

                                                <div class="d-flex flex-column w-50">
                                                    <input class="form-control valor valor-despesa" type="text" inputmode="decimal"
                                                        pattern="[0-9.,]*" onkeypress="return /[0-9,]/.test(event.key)"
                                                        name="valor_despesa[<?= $i ?>]" placeholder="Valor">
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="d-flex flex-row justify-content-between align-items-center mt-3">
                                            <div>
                                                <div>
                                                    Total: R$ <span id="total-valor-pag">0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            <?php }
                        } ?>
                        <div class="d-flex flex-row justify-content-between align-items-center mt-3 w-100">
                            <button type="submit" id="btn-processar" class="btn btn-primary w-100">
                                Processar
                            </button>
                        </div>
                        <small id="msg-btn-processar"
                            class="text-danger d-block text-center mt-2">
                        </small>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>




</body>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>


<script>
    let btnProcessar;
let msgProcessar;

let inputData;
let inputTurno;
let inputNome;
function validarFormulario() {

    const faltando = [];

    if (!inputData.value.trim()) {
        faltando.push('Data');
    }

    if (!inputTurno.value.trim()) {
        faltando.push('Turno');
    }

    if (!inputNome.value.trim()) {
        faltando.push('Nome do Caixa');
    }

    const habilitado = faltando.length === 0;

    btnProcessar.disabled = !habilitado;

    btnProcessar.classList.toggle('btn-primary', habilitado);
    btnProcessar.classList.toggle('btn-secondary', !habilitado);

    if (habilitado) {
        msgProcessar.textContent = '';
    } else {
        msgProcessar.textContent =
            'Preencha os seguintes campos para continuar: ' +
            faltando.join(', ');
    }
}
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

    function atualizarTotais() {
        let totalRec = 0;
        let totalPag = 0;

        document.querySelectorAll('.valor-receita').forEach(input => {
            const valor = parseBrazilianDecimal(input.value);
            if (!isNaN(valor)) {
                totalRec += valor;
            }
        });

        document.querySelectorAll('.valor-despesa').forEach(input => {
            const valor = parseBrazilianDecimal(input.value);
            if (!isNaN(valor)) {
                totalPag += valor;
            }
        });

        const totalRecElement = document.getElementById('total-valor-rec');
        const totalPagElement = document.getElementById('total-valor-pag');
        if (totalRecElement) {
            totalRecElement.textContent = totalRec.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        if (totalPagElement) {
            totalPagElement.textContent = totalPag.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    function definirTurnoPadrao(turnos) {
    if (!Array.isArray(turnos) || turnos.length === 0) {
        if (!document.getElementById('input_turno').value) {
            document.getElementById('input_turno').value = '1';
        }
    } else {
        const numeros = turnos
            .map(turno => parseInt(turno, 10))
            .filter(n => !Number.isNaN(n));

        const proximo = numeros.length ? Math.max(...numeros) + 1 : 1;

        if (!document.getElementById('input_turno').value) {
            document.getElementById('input_turno').value = String(proximo);
        }
    }

    validarFormulario();
}

    // Atualiza o total quando qualquer input de valor é alterado
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.valor-receita, .valor-despesa').forEach(input => {
            input.addEventListener('change', atualizarTotais);
            input.addEventListener('input', atualizarTotais);
        });
        atualizarTotais();

        const dataInput = document.getElementById('data_fechamento');
        const turnoInput = document.getElementById('input_turno');

        function clearDadosTurno() {
            document.getElementById('input_nome_caixa').value = '';
            document.querySelectorAll('.valor-receita, .valor-despesa')
                .forEach(input => input.value = '');

            atualizarTotais();
            validarFormulario();
        }

        function preencherValoresPorTurno(dados) {
            if (!dados) {
                return;
            }

            if (dados.nome_caixa !== undefined && dados.nome_caixa !== '') {
                document.getElementById('input_nome_caixa').value = dados.nome_caixa;
            }

            validarFormulario();

            const valoresReceitas = dados.valores_receitas || {};
            const valoresDespesas = dados.valores_despesas || {};

            document.querySelectorAll('.tipo-pagamento-receita').forEach((select, index) => {
                const tipoId = select.value;
                const valorInput = document.querySelectorAll('.valor-receita')[index];
                if (!valorInput) {
                    return;
                }
                if (valoresReceitas[tipoId] !== undefined) {
                    valorInput.value = formatBrazilianDecimal(valoresReceitas[tipoId]);
                }
            });

            document.querySelectorAll('.descricao_padrao').forEach((descricaoInput, index) => {
                const descricao = descricaoInput.value || '';
                const valorInput = document.querySelectorAll('.valor-despesa')[index];
                if (!valorInput) {
                    return;
                }
                if (descricao && valoresDespesas[descricao] !== undefined) {
                    valorInput.value = formatBrazilianDecimal(valoresDespesas[descricao]);
                }
            });

            atualizarTotais();
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

        async function carregarDadosTurno(data, turno, nome) {
            if (!data || !turno) {
                clearDadosTurno();
                return;
            }

            const url = `${fechamentoApiUrl}?action=getDadosTurno&data=${encodeURIComponent(data)}&turno=${encodeURIComponent(turno)}&nome=${encodeURIComponent(nome || '')}&target=both`;
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

        dataInput.addEventListener('change', function () {
            carregarTurnos(this.value);
            clearDadosTurno();
        });

        turnoInput.addEventListener('change', function () {
            carregarDadosTurno(dataInput.value, this.value, document.getElementById('input_nome_caixa').value);
        });

        document.getElementById('input_nome_caixa').addEventListener('change', function () {
            carregarDadosTurno(dataInput.value, turnoInput.value, this.value);
        });

        carregarTurnos(dataInput.value);
    });

document.addEventListener('DOMContentLoaded', () => {

    const todosSubtitulos = [
        <?php foreach ($todosSubtitulos as $sub): ?>
        {
            value: "<?= $sub->id ?>",
            label: <?= json_encode($sub->nome) ?>,
            tituloId: "<?= $sub->id_con01 ?>"
        },
        <?php endforeach; ?>
    ];

    const tituloReceber    = document.getElementById('titulo-receber');
    const subtituloReceber = document.getElementById('subtitulo-receber');

    const tituloPagar    = document.getElementById('titulo-pagar');
    const subtituloPagar = document.getElementById('subtitulo-pagar');

    // Remove as opções originais
    subtituloReceber.innerHTML = '';
    subtituloPagar.innerHTML = '';

    // Cria manualmente os Choices
    const choicesReceber = new Choices(subtituloReceber, {
        searchEnabled: true,
        shouldSort: false,
        itemSelectText: '',
        allowHTML: false,
        placeholder: true,
        placeholderValue: 'Selecione'
    });

    const choicesPagar = new Choices(subtituloPagar, {
        searchEnabled: true,
        shouldSort: false,
        itemSelectText: '',
        allowHTML: false,
        placeholder: true,
        placeholderValue: 'Selecione'
    });

    const choicesTituloReceber = new Choices(tituloReceber, {
    searchEnabled: true,
    shouldSort: false,
    itemSelectText: '',
    allowHTML: false
});

const choicesTituloPagar = new Choices(tituloPagar, {
    searchEnabled: true,
    shouldSort: false,
    itemSelectText: '',
    allowHTML: false
});

    function atualizarSubtitulos(tituloId, choices, selecionado = '') {

        const lista = todosSubtitulos.filter(sub =>
            String(sub.tituloId) === String(tituloId)
        );

        choices.clearStore();

        choices.setChoices(
            [
                {
                    value: '',
                    label: 'Selecione',
                    selected: selecionado === ''
                },
                ...lista.map(sub => ({
                    value: sub.value,
                    label: sub.label,
                    selected: sub.value == selecionado
                }))
            ],
            'value',
            'label',
            true
        );

        if (selecionado !== '') {
            choices.setChoiceByValue(String(selecionado));
        }
    }

    // Inicialização
    atualizarSubtitulos(
        tituloReceber.value,
        choicesReceber,
        "<?= $fecha01_rec->id_subtitulo ?? '' ?>"
    );

    atualizarSubtitulos(
        tituloPagar.value,
        choicesPagar,
        "<?= $fecha01_pag->id_subtitulo ?? '' ?>"
    );

    // Eventos
    tituloReceber.addEventListener('change', () => {
        atualizarSubtitulos(
            tituloReceber.value,
            choicesReceber
        );
    });

    tituloPagar.addEventListener('change', () => {
        atualizarSubtitulos(
            tituloPagar.value,
            choicesPagar
        );
    });

});

document.addEventListener('DOMContentLoaded', () => {

    btnProcessar = document.getElementById('btn-processar');
    msgProcessar = document.getElementById('msg-btn-processar');

    inputData = document.getElementById('data_fechamento');
    inputTurno = document.getElementById('input_turno');
    inputNome = document.getElementById('input_nome_caixa');

    inputData.addEventListener('input', validarFormulario);
    inputData.addEventListener('change', validarFormulario);

    inputTurno.addEventListener('input', validarFormulario);
    inputTurno.addEventListener('change', validarFormulario);

    inputNome.addEventListener('input', validarFormulario);
    inputNome.addEventListener('change', validarFormulario);

    validarFormulario();
});
</script>




</html>