<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';

session_start();
$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];
$nome_empresa = $empresa_usuario_obj->nom_fant ?? '';
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->permissao_bancario != 1 || $empresa_usuario_obj->permissao_bancario != 1) {
    header('Location: /');
    exit;
}

$lateral_financeiro = true;
$lateral_target = 'dre';
$dre_target = 'curva_abc';
function format_valor_alinhado($valor) {
    $formatado = number_format($valor, 2, ',', '.');
    $formatado = str_pad($formatado, 12, ' ', STR_PAD_LEFT);
    return $formatado;
}

$get_data_final = filter_input(INPUT_GET, 'data_final') ?? null;
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial') ?? null;
$get_custos = filter_input(INPUT_GET, 'filtro_custos') ?: null;
$get_operacional = filter_input(INPUT_GET, 'filtro_operacional') ?: null;

$empresa = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0];
$empresa_lista = Empresa::read(cnpj_principal: $empresa->cnpj_principal);

$lancamentos = [];
$subtitulos = [];
$subtitulos_ids = [];
$total_receita = 0;

// Calcular totais primeiro
foreach($empresa_lista as $i => $empresa) {
    $lancamentos_empresa[$i] = Rec02::read(
        id_empresa:          $empresa->id,
        filtro_data_inicial: $get_data_inicial,
        filtro_data_final:   $get_data_final,
        filtro_por:'pagamento',
        filtro_operacional:  $get_operacional,
        filtro_opcao: 'quitados',
        read_total: true
    );
    $totais_empresa[$i] = $lancamentos_empresa[$i];
    $total_receita += $totais_empresa[$i];
}

// Ordenar empresa_lista por porcentagem (maior para menor)
$receitas = [];
foreach($empresa_lista as $i => $empresa) {
    $receitas[$i] = $totais_empresa[$i] ?? 0;
}
arsort($receitas);

$empresa_lista_ordenada = [];
$totais_empresa_ordenada = [];
foreach($receitas as $i => $value) {
    $empresa_lista_ordenada[] = $empresa_lista[$i];
    $totais_empresa_ordenada[] = $totais_empresa[$i];
}
$empresa_lista = $empresa_lista_ordenada;
$totais_empresa = $totais_empresa_ordenada;

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.0.2/jspdf.plugin.autotable.min.js" integrity="sha512-JizZOUNesiGhMcp9fsA/9W31FOat6QysBM8hSj6ir8iIANIUJ2mhko7Lo1+j0ErftmJ8SebMZLm9iielKjeIEQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="/../../style/dre.css">

<link rel="stylesheet" href="../../choices/choices.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>


    <div class="main" id="container">
            <div class="col-md-12" style="padding: 0;">
                <div class="card">
                    <?php require_once __DIR__ . '/../../componentes/financeiro/dre-header.php'; ?>
                    <div class="card-header-div">
                        <div class="card-header-borda">
                            <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                aria-labelledby="vendas-tab">
                                <h5 class="card-title">Filtros</h5>
                                <form method="get" action="curva_abc.php">
                                    <div class="inputs-dre d-flex flex-row">
                                        <div class="inputs-dre-text d-flex flex-row">
                                            <div class="data-dre d-flex flex-row">
                                                <div>
                                                    <label for="data_inicial" style="font-size:90%;">Data Inicial:</label>
                                                    <input type="date" id="data_inicial" name="data_inicial"
                                                        value="<?= htmlspecialchars($get_data_inicial) ?>" class="form-control"
                                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-top-left-radius: 0.25em; border-bottom-left-radius: 0.25em;">
                                                </div>

                                                <div>
                                                    <label for="data_final" style="font-size:90%;">Data Final:</label>
                                                    <input type="date" id="data_final" name="data_final"
                                                        value="<?= htmlspecialchars($get_data_final)?>" class="form-control"
                                                        style="border-radius: 0;">
                                                </div>

                                                <div>
                                                <label for="data_final">Tipo:</label>
                                                <select id="filtro_operacional" class="form-control" name="filtro_operacional" style="height: 50%; border-radius: 0;">
                                                    <option value=""  <?php if($get_operacional == null)  echo 'selected' ?>>Todos</option>
                                                    <option value="1" <?php if($get_operacional == 1)  echo 'selected' ?> >Operacional</option>
                                                    <option value="2" <?php if($get_operacional == 2)  echo 'selected' ?>>Não Operacional</option>
                                                </select>
                                                </div>
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
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="table-curva-abc">
                            <thead>
                                <tr>
                                    <th style="width:45%;">Empresa</th>
                                    <th style="width:45%;">Receita</th>
                                    <th style="width:10%;">Porcentagem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($empresa_lista as $i => $empresa) { 
                                    ?>
                                    <tr>
                                        <td><?php echo $empresa->nom_fant; ?></td>
                                        <td><div class="d-flex flex-row justify-content-between"><div>R$</div><div><?php echo number_format($totais_empresa[$i] ?? 0, 2, ',', '.'); ?></div></div></td>
                                        <td><?php echo number_format(($totais_empresa[$i] ?? 0) / $total_receita * 100, 2, ',', '.') . '%' ?></td>
                                    </tr>
                                <?php } ?>
                                <tr class="tr-dre-total">
                                    <td style="background-color: transparent;"><strong>Total Geral</strong></td>
                                     <td id="total-dre-sintetico" style="background-color:transparent; justify-content:space-between" class="d-flex flex-row total-dre-sintetico"><div>R$ </div><div><?=number_format($total_receita, 2, ',', '.') ?></div></td>
                                    <td style="background-color: transparent;"></td>
                                   
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- card -->
        </div>
    </div>
<?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?> 
</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../choices/choices.js"></script>

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

    function checarTitulo(resetSubtitulo = false) {
        var tituloSelect = document.getElementById('input-titulo');
        if (!tituloSelect) return;
        var tituloId = tituloSelect.value;
        var subtituloSelect = document.getElementById('subtitulo');
        let subtituloDiv = document.getElementById('subtitulo-dre');
        var options = subtituloSelect ? subtituloSelect.querySelectorAll('option') : [];
        let divText = document.querySelector('.inputs-dre-text');
        let divBtn = document.querySelector('.inputs-dre-btn');

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

        if (tituloId == '') {
            if (subtituloDiv) subtituloDiv.style.display = 'none';
        } else {
            if (subtituloDiv) subtituloDiv.style.display = 'block';
            if (divText) divText.style.width = 'calc(55% + 1em)';
            if (divBtn) divBtn.style.width = 'calc(40% + 1em)';
        }

        if (resetSubtitulo && subtituloSelect) {
            subtituloSelect.value = ""; // Só reseta se for troca de título
        }
    }

    var inputTitulo = document.getElementById('input-titulo');
    if (inputTitulo) {
        inputTitulo.addEventListener('change', function () {
            checarTitulo();
        });
        checarTitulo();
    }


function prepararGeracao(target) {

    let data_inicial = document.getElementById('data_inicial').value;
    let data_final = document.getElementById('data_final').value;

    let nomeEmpresa = <?= json_encode($nome_empresa) ?>;
    let dataTexto = '';

    if (data_inicial && data_final) {
        dataTexto = `Período: ${data_inicial} até ${data_final}`;
    }

    if (target === 'pdf') {
        gerarpdf_curvaabc(dataTexto, nomeEmpresa);
    }

    if (target === 'excel') {
        gerarexcel_curvaabc(dataTexto, nomeEmpresa);
    }
}
  

</script>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="gerar.js"></script>

</html>
