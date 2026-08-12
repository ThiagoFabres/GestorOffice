<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagar.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';

session_start();
$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];
$nome_empresa = $empresa_usuario_obj->nom_fant ?? '';
if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']->cargo != 3 ||
    $_SESSION['usuario']->permissao_bancario != 1 ||
    $empresa_usuario_obj->permissao_bancario != 1
) {
    header('Location: /');
    exit;
}

$lateral_financeiro = true;
$lateral_target  = 'dre';
$dre_target      = 'anual';

// ─── Filtros GET ─────────────────────────────────────────────────────────────
$get_ano         = filter_input(INPUT_GET, 'ano')               ?: date('Y');
$get_operacional = filter_input(INPUT_GET, 'filtro_operacional') ?: null;
$get_todas_emp   = filter_input(INPUT_GET, 'todas_empresas') == 'on' ? 1 : 0;

// ─── Empresas ────────────────────────────────────────────────────────────────
$empresa_sessao = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0];

if ($get_todas_emp == '1') {
    $empresa_lista = Empresa::read(cnpj_principal: $empresa_sessao->cnpj_principal);
} else {
    $empresa_lista = [$empresa_sessao];
}

$meses_labels = [
    1  => 'Jan', 2  => 'Fev', 3  => 'Mar',
    4  => 'Abr', 5  => 'Mai', 6  => 'Jun',
    7  => 'Jul', 8  => 'Ago', 9  => 'Set',
    10 => 'Out', 11 => 'Nov', 12 => 'Dez',
];

// ─── Pré-carregar Con01, Con02, Rec01 e Pag01 de todas as empresas ────────────
// $map_titulos[id]    = objeto Con01  (precisamos do ->tipo e ->nome)
// $map_subtitulos[id] = nome do Con02
// $map_rec01[id]      = ['titulo' => id_con01, 'subtitulo' => id_con02]
// $map_pag01[id]      = ['titulo' => id_con01, 'subtitulo' => id_con02]
$map_titulos    = [];   // id => Con01 objeto
$map_subtitulos = [];   // id => nome
$map_rec01      = [];
$map_pag01      = [];

foreach ($empresa_lista as $empresa) {
    $con01_lista = Con01::read(idempresa: $empresa->id);
    foreach ($con01_lista as $c) {
        $map_titulos[(int)$c->id] = $c;          // objeto completo (usamos ->tipo e ->nome)
    }

    $con02_lista = Con02::read(idempresa: $empresa->id);
    foreach ($con02_lista as $c) {
        $map_subtitulos[(int)$c->id] = $c->nome;
    }

    $rec01_lista = Rec01::read(id_empresa: $empresa->id);
    foreach ($rec01_lista as $c) {
        $map_rec01[(int)$c->id] = ['titulo' => (int)$c->id_con01, 'subtitulo' => (int)$c->id_con02];
    }

    $pag01_lista = Pag01::read(id_empresa: $empresa->id);
    foreach ($pag01_lista as $c) {
        $map_pag01[(int)$c->id] = ['titulo' => (int)$c->id_con01, 'subtitulo' => (int)$c->id_con02];
    }
}

// ─── Estrutura de dados ───────────────────────────────────────────────────────
// Agrupamento por NOME (normalizado) para unir títulos/subtítulos homônimos
// de empresas diferentes.
//
// $dados[tkey]['_meta']          = ['nome' => ..., 'tipo' => 'C'|'D', 'subtitulos' => [skey => nome]]
// $dados[tkey]['_total'][1..12]  = soma mensal do título (débitos já negativos)
// $dados[tkey][skey][1..12]      = soma mensal do subtítulo
$dados       = [];
$total_geral = array_fill(1, 12, 0.0);

foreach ($empresa_lista as $empresa) {
    for ($mes = 1; $mes <= 12; $mes++) {
        $data_inicial = sprintf('%04d-%02d-01', $get_ano, $mes);
        $data_final   = date('Y-m-t', strtotime($data_inicial));

        // ── Receitas (Rec02) ──────────────────────────────────────────────────
        $lancamentos_rec = Rec02::read(
            id_empresa:          $empresa->id,
            filtro_data_inicial: $data_inicial,
            filtro_data_final:   $data_final,
            filtro_por:          'pagamento',
            filtro_operacional:  $get_operacional,
            filtro_opcao:        'quitados'
        );

        foreach ($lancamentos_rec as $lanc) {
            $meta = $map_rec01[(int)$lanc->id_rec01] ?? null;
            if (!$meta) continue;

            $tid_raw = $meta['titulo'];
            $sid_raw = $meta['subtitulo'];
            $con01   = $map_titulos[$tid_raw] ?? null;

            // Filtra por operacional se necessário
            if ($get_operacional !== null && $con01) {
                if ($get_operacional == 1 && $con01->operacional != 1) continue;
                if ($get_operacional == 2 && $con01->operacional != 0) continue;
            }

            $tnom  = $con01->nome         ?? 'Sem Título';
            $tipo  = $con01->tipo         ?? 'C';
            $snom  = $map_subtitulos[$sid_raw] ?? 'Sem Subtítulo';
            $valor = (float)($lanc->valor_pag ?? 0);

            // Débitos viram negativos (consistência com sintetico.php)
            if ($tipo === 'D') $valor *= -1;

            _acumular_anual($dados, $total_geral, $tnom, $tipo, $snom, $mes, $valor);
        }

        // ── Despesas (Pag02) ──────────────────────────────────────────────────
        $lancamentos_pag = Pag02::read(
            id_empresa:          $empresa->id,
            filtro_data_inicial: $data_inicial,
            filtro_data_final:   $data_final,
            filtro_por:          'pagamento',
            filtro_operacional:  $get_operacional,
            filtro_opcao:        'quitados'
        );

        foreach ($lancamentos_pag as $lanc) {
            $meta = $map_pag01[(int)$lanc->id_pag01] ?? null;
            if (!$meta) continue;

            $tid_raw = $meta['titulo'];
            $sid_raw = $meta['subtitulo'];
            $con01   = $map_titulos[$tid_raw] ?? null;

            // Filtra por operacional se necessário
            if ($get_operacional !== null && $con01) {
                if ($get_operacional == 1 && $con01->operacional != 1) continue;
                if ($get_operacional == 2 && $con01->operacional != 0) continue;
            }

            $tnom  = $con01->nome              ?? 'Sem Título';
            $tipo  = $con01->tipo              ?? 'D';
            $snom  = $map_subtitulos[$sid_raw] ?? 'Sem Subtítulo';
            $valor = (float)($lanc->valor_pag  ?? 0);

            // Pag02 são sempre despesas → negativo
            $valor *= -1;

            _acumular_anual($dados, $total_geral, $tnom, $tipo, $snom, $mes, $valor);
        }
    }
}

// ─── Função auxiliar de acumulação ───────────────────────────────────────────
function _acumular_anual(array &$dados, array &$total_geral, string $tnom, string $tipo, string $snom, int $mes, float $valor): void
{
    $tkey = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $tnom)));
    $skey = $tkey . '||' . strtolower(trim($snom));

    if (!isset($dados[$tkey])) {
        $dados[$tkey] = [
            '_meta'  => ['nome' => $tnom, 'tipo' => $tipo, 'subtitulos' => []],
            '_total' => array_fill(1, 12, 0.0),
        ];
    }

    if (!isset($dados[$tkey][$skey])) {
        $dados[$tkey]['_meta']['subtitulos'][$skey] = $snom;
        $dados[$tkey][$skey] = array_fill(1, 12, 0.0);
    }

    $dados[$tkey][$skey][$mes]    += $valor;
    $dados[$tkey]['_total'][$mes] += $valor;
    $total_geral[$mes]            += $valor;
}

// Ordena títulos por total absoluto (desc)
uasort($dados, function ($a, $b) {
    return strcasecmp($a['_meta']['nome'], $b['_meta']['nome']);
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
        integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.0.2/jspdf.plugin.autotable.min.js"
        integrity="sha512-JizZOUNesiGhMcp9fsA/9W31FOat6QysBM8hSj6ir8iIANIUJ2mhko7Lo1+j0ErftmJ8SebMZLm9iielKjeIEQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="../../style/dre.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragscroll/0.0.8/dragscroll.min.js"></script>
    <link rel="stylesheet" href="../../choices/choices.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
    <title>Gestor Office Control</title>
    <style>
        /* ── Tabela anual ─────────────────────────────────────────── */
        .tabela-anual-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .tabela-anual {
            min-width: 1100px;
            font-size: 0.78rem;
            border-collapse: collapse;
        }
        .tabela-anual thead th {
            background-color: #343434;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            padding: 6px 8px;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .tabela-anual thead th:first-child {
            position: sticky;
            left: 0;
            z-index: 3;
            background-color: #343434;
        }
        /* Coluna de descrição fixa */
        .tabela-anual td:first-child,
        .tabela-anual th:first-child {
            position: sticky;
            left: 0;
            background-color: #fff;
            z-index: 1;
            min-width: 200px;
            max-width: 260px;
        }
        /* Título (linha pai) */
        .row-titulo td {
            background-color: #e8e7fb !important;
            font-weight: 700;
            cursor: pointer;
        }
        .row-titulo td:first-child {
            background-color: #e8e7fb !important;
        }
        /* Subtítulo */
        .row-subtitulo td {
            background-color: #f8f8ff;
            padding-left: 1.8rem !important;
            color: #444;
        }
        .row-subtitulo td:first-child {
            background-color: #f8f8ff !important;
        }
        /* Total Geral */
        .row-total-geral td {
            background-color: #343434 !important;
            color: #fff !important;
            font-weight: 700;
        }
        .row-total-geral td:first-child {
            background-color: #343434 !important;
            color: #fff !important;
        }
        /* Coluna de totais */
        .col-total:not(.valor) {
            background-color: #343434 !important;
            font-weight: 700;
        }
        .row-total-geral .col-total {
            background-color: #343434 !important;
        }
        /* Valores alinhados */
        .tabela-anual td.valor {
            text-align: right;
            white-space: nowrap;
            padding: 4px 10px;
        }
        .valor .col-total {
            background-color: #fff;
        }
        /* Zero em cinza */
        .valor-zero { color: #bbb; }

        /* Toggle ícone */
        .toggle-icon { transition: transform 0.2s; display: inline-block; }
        .collapsed .toggle-icon { transform: rotate(-90deg); }

        /* Zebra nas linhas de subtítulo visíveis */
        .row-subtitulo:nth-child(even) td { background-color: #f3f3ff !important; }
        .row-subtitulo:nth-child(even) td:first-child { background-color: #f3f3ff !important; }

        /* Totalizador do título (após subtítulos) */
        .row-total-titulo td {
            background-color: #d4d3f5 !important;
            font-weight: 700;
            font-style: italic;
            border-top: 2px solid #5856d6 !important;
        }
        .row-total-titulo td:first-child {
            background-color: #d4d3f5 !important;
            padding-left: 1.8rem !important;
        }
        .row-total-titulo .col-total {
            background-color: #c5c3ef !important;
        }
    </style>
</head>
<body id="body">

    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php' ?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>

    <div class="main" id="container">
        <div class="col-md-12" style="padding: 0;">
            <div class="card">
                <?php require_once __DIR__ . '/../../componentes/financeiro/dre-header.php'; ?>

                <!-- ── Filtros ──────────────────────────────────────────── -->
                <div class="card-header-div">
                    <div class="card-header-borda">
                        <h5 class="card-title">Filtros</h5>
                        <form method="get" action="anual.php">
                            <div class="d-flex flex-row">
                                <div class="inputs-dre d-flex flex-row flex-wrap gap-2 align-items-end">
                                    <div class="d-flex flex-row ">
                                        <div>
                                            <label for="ano" style="font-size:90%;">Ano:</label>
                                            <input type="number" id="ano" name="ano"
                                                value="<?= htmlspecialchars($get_ano) ?>"
                                                min="2000" max="2099" step="1"
                                                class="form-control rounded-0">
                                        </div>

                                        <div>
                                            <label for="filtro_operacional">Operacional:</label>
                                            <select id="filtro_operacional" name="filtro_operacional" class="form-control rounded-0">
                                                <option value=""  <?= $get_operacional === null  ? 'selected' : '' ?>>Todos</option>
                                                <option value="1" <?= $get_operacional == 1   ? 'selected' : '' ?>>Operacional</option>
                                                <option value="2" <?= $get_operacional == 2   ? 'selected' : '' ?>>Não Operacional</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column align-items-center">
                                        <label for="data_final" id="input-label-todas-empresas" >Todas as Empresas:</label>
                                        <input <?php if($get_todas_emp == '1') echo 'checked' ?> type="checkbox" name="todas_empresas">
                                    </div>               
                                </div>
                                <div class="inputs-dre-btn">
                                    <div class="botoes-acao">
                                        <button type="submit" class="btn-sm btn" style="background-color: #5856d6; color: white;">Filtrar</button>
                                        <a href="anual.php" class="btn btn-secondary btn-sm">Limpar</a>
                                    </div>   
                                    <div id="inputs-btn-analitico">     
                                    <div class="botoes-gerar">
                                        <button type="button" class="btn-sm btn" id="botao-gerar-pdf"
                                            onclick="prepararGeracao('pdf')">Gerar PDF</button>
                                        <button type="button" class="btn-sm btn" id="botao-gerar-excel"
                                            onclick="prepararGeracao('excel')">Gerar Excel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- ── /Filtros ─────────────────────────────────────────── -->

                <div class="card-body dragscroll">
                    <?php if (empty($dados)): ?>
                        <div class="alert alert-info">Nenhum lançamento encontrado para o período selecionado.</div>
                    <?php else: ?>

                    <div class="tabela-anual-wrap dragscroll" id="tabela-anual-wrap">
                        <table class="tabela-anual table table-bordered dragscroll" id="tabela-anual">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Título / Subtítulo</th>
                                    <?php foreach ($meses_labels as $m => $label): ?>
                                        <th><?= $label ?></th>
                                    <?php endforeach; ?>
                                    <th class="col-total">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($dados as $tid => $info):
                                    $total_titulo_linha = array_sum($info['_total']);
                                    $grupo_id = 'grupo_' . $tid;
                                ?>

                                <!-- ── Linha Título ───────────────────────── -->
                                <tr class="row-titulo" data-bs-toggle="collapse"
                                    data-bs-target=".<?= $grupo_id ?>"
                                    style="cursor:pointer;" id="tr-titulo-<?= $tid ?>">
                                    <td>
                                        <span class="toggle-icon me-1">&#9660;</span>
                                        <?= htmlspecialchars($info['_meta']['nome']) ?>
                                    </td>
                                    <?php foreach ($meses_labels as $m => $label): ?>
                                        <td class="valor <?= $info['_total'][$m] == 0 ? 'valor-zero' : '' ?>">
                                            <?= number_format($info['_total'][$m], 2, ',', '.') ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="valor col-total">
                                        <?= number_format($total_titulo_linha, 2, ',', '.') ?>
                                    </td>
                                </tr>

                                <!-- ── Linhas Subtítulos ──────────────────── -->
                                <?php foreach ($info['_meta']['subtitulos'] as $sid => $snom): ?>
                                    <?php $total_sub_linha = array_sum($info[$sid]); ?>
                                    <tr class="row-subtitulo collapse show <?= $grupo_id ?>">
                                        <td><?= htmlspecialchars($snom) ?></td>
                                        <?php foreach ($meses_labels as $m => $label): ?>
                                            <td class="valor <?= $info[$sid][$m] == 0 ? 'valor-zero' : '' ?>">
                                                <?= number_format($info[$sid][$m], 2, ',', '.') ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="valor col-total">
                                            <?= number_format($total_sub_linha, 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php endforeach; ?>

                                <!-- ── Total Geral ────────────────────────── -->
                                <tr class="row-total-geral">
                                    <td><strong>Total Geral</strong></td>
                                    <?php
                                    $grand_total = 0;
                                    foreach ($meses_labels as $m => $label):
                                        $grand_total += $total_geral[$m];
                                    ?>
                                        <td class="valor"><?= number_format($total_geral[$m], 2, ',', '.') ?></td>
                                    <?php endforeach; ?>
                                    <td class="valor col-total">
                                        <?= number_format($grand_total, 2, ',', '.') ?>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <?php endif; ?>
                </div><!-- /.card-body -->
            </div><!-- /.card -->
        </div>
    </div>

<?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../choices/choices.js"></script>
<script src="gerar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── Toggle ícone ao colapsar/expandir ────────────────────── */
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (tr) {
        tr.addEventListener('click', function () {
            const icon = this.querySelector('.toggle-icon');
            if (!icon) return;
            // Verifica se algum filho ainda está visível
            const target = this.dataset.bsTarget;
            const firstChild = document.querySelector(target.replace('.', '.'));
            // Bootstrap adiciona 'show' com delay; usamos toggle manual no ícone
            this.classList.toggle('collapsed');
        });
    });

    /* ── userMenu (padrão do sistema) ────────────────────────── */
    var userBtn  = document.getElementById('userBtn');
    var userMenu = document.getElementById('userMenu');
    if (userBtn && userMenu) {
        userBtn.onclick = function (e) {
            e.stopPropagation();
            userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
        };
        document.addEventListener('click', function () {
            if (userMenu.style.display === 'block') userMenu.style.display = 'none';
        });
        userMenu.onclick = function (e) { e.stopPropagation(); };
    }
});

/* ── Exportar Excel ──────────────────────────────────────────── */
function exportarExcel() {
    const wb   = XLSX.utils.book_new();
    const rows = [];

    // Cabeçalho
    const header = ['Título / Subtítulo', 'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez','Total'];
    rows.push(header);

    document.querySelectorAll('#tabela-anual tbody tr').forEach(function (tr) {
        const row = [];
        tr.querySelectorAll('td').forEach(function (td) {
            // Remove espaços extras e converte valor numérico
            const raw = td.innerText.trim().replace(/\./g,'').replace(',','.');
            const num = parseFloat(raw);
            row.push(isNaN(num) ? td.innerText.trim() : num);
        });
        rows.push(row);
    });

    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, 'Anual');
    XLSX.writeFile(wb, 'relatorio_anual_<?= $get_ano ?>.xlsx');
}

    function prepararGeracao(target) {
    let titulo = null
    let subtitulo = null
    let nomeEmpresa = <?= json_encode($nome_empresa) ?>;
                                        
    if(subtitulo == 'Selecione' || subtitulo == null || titulo == null) {
        subtitulo = '';
    }
    if(titulo == 'Selecione' || titulo == null) {
        titulo = ''
    }
    if (subtitulo !== '' && subtitulo !== 'Selecione') {
        subtitulo = ' - ' + subtitulo;
    }
    let data_inicial = document.getElementById('ano').value;
    let data_final = null
    let dataTexto = '';
    if (data_inicial !== '' && data_final !== '') {
        dataTexto = 'Período: ' + data_inicial + ' até ' + data_final;
    } else if (data_inicial !== '') {
        dataTexto = 'Data Inicial: ' + data_inicial;
    } else if (data_final !== '') {
        dataTexto = 'Data Final: ' + data_final;
    }
    if(target == 'pdf'){
         gerarpdf_anual(data_inicial, nomeEmpresa);
    } else if(target == 'excel'){
        gerarexcel_anual(data_inicial, nomeEmpresa);
    }
}
</script>

</body>
</html>