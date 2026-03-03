<?php
require_once __DIR__ . '/../../../db/base.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/contas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/categoria.php';
require_once __DIR__ . '/../../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/pagar.php';
require_once __DIR__ . '/../../../db/entities/centrocustos.php';
require_once __DIR__ . '/../../../db/entities/banco02.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
session_start();
// Função para alinhar valores monetários

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
$lateral_bancario = true;
$lateral_target = 'dreBancario';
function format_valor_alinhado($valor) {
    $formatado = number_format($valor, 2, ',', '.');
    // 12 caracteres para alinhar valores grandes e pequenos
    $formatado = str_pad($formatado, 12, ' ', STR_PAD_LEFT);
    return $formatado;
}

$get_data_final = filter_input(INPUT_GET, 'data_final', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_data_inicial = filter_input(INPUT_GET, 'data_inicial', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$get_titulo = filter_input(INPUT_GET, 'titulo') ?: null;
$get_subtitulo = null;
$get_custos = filter_input(INPUT_GET, 'filtro_custos') ?? null;
$get_operacional = filter_input(INPUT_GET, 'filtro_operacional') ?: null;
$todas_empresas = filter_input(INPUT_GET, 'todas_empresas') == 'on' ? 1 : 0;


if ($get_titulo != null)
    $get_subtitulo = filter_input(INPUT_GET, 'subtitulo') ?: null;
if($todas_empresas) {
    $empresa = Empresa::read(id: $_SESSION['usuario']->id_empresa)[0];
    $empresa_lista = Empresa::read(cnpj_principal: $empresa->cnpj_principal);
} else {
    $empresa_lista = Empresa::read(id: $_SESSION['usuario']->id_empresa);
}


?>
<!DOCTYPE html>






<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js"
    integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.0.2/jspdf.plugin.autotable.min.js" integrity="sha512-JizZOUNesiGhMcp9fsA/9W31FOat6QysBM8hSj6ir8iIANIUJ2mhko7Lo1+j0ErftmJ8SebMZLm9iielKjeIEQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="../../style/dre.css">

<link rel="stylesheet" href="../../../choices/choices.css">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
<title>Gestor Office Control</title>
</head>

<body id="body">

    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php' ?>
    
    <div class="main" id="container">

            <div class="col-md-12" style="padding: 0;">


                <div class="card">
                    <div class="card-header">
                        <button class="btn btn-primary dre-menu-btn" class="btn-dre-menu" onclick="window.location.href='sintetico.php'">
                            <h3>DRE - Sintético</h3>
                        </button>

                        <button class="btn btn-primary btn-dre-selecionado dre-menu-btn" style="border-bottom: 2px solid #5856d6;" class="btn-dre-menu">
                            <h3>DRE - Analitico</h3>
                        </button>

                        <button class="btn btn-primary dre-menu-btn"  onclick="window.location.href='grafico.php'" class="btn-dre-menu">
                            <h3>Gráfico</h3>
                        </button>

                        <button class="btn btn-primary dre-menu-btn" onclick="window.location.href='grafico_periodo.php'" class="btn-dre-menu">
                            <h3>Gráfico por período</h3>
                        </button>
                    </div>

                    <div class="card-header-div">
                        <div class="card-header-borda">
                            <div class="tab-pane fade show active" id="vendas" role="tabpanel"
                                aria-labelledby="vendas-tab">
                                <h5 class="card-title">Filtros</h5>
                                <form method="get" action="analitico.php">
                                        <div class="inputs-dre">
                                        <div class="inputs-dre-text" >
                                        <div class="data-dre">
                                            <div>
                                                <label for="data_inicial">Data Inicial:</label>
                                                <input type="date" id="data_inicial" name="data_inicial"
                                                    value="<?= $get_data_inicial ?>" class="form-control">
                                            </div>

                                            <div>
                                                <label for="data_final">Data Final:</label>
                                                <input type="date" id="data_final" name="data_final"
                                                    value="<?= $get_data_final ?>" class="form-control">
                                            </div>

                                            
                                        </div>

                                        <div>
                                                <label for="data_final">Tipo:</label>
                                                <select id="filtro_operacional" class="form-control" name="filtro_operacional" style="height: 53%; border-radius: 0;">
                                                    <option value=""  <?php if($get_operacional == null)  echo 'selected' ?>>Todos</option>
                                                    <option value="1" <?php if($get_operacional == 1)  echo 'selected' ?> >Operacional</option>
                                                    <option value="2" <?php if($get_operacional == 2)  echo 'selected' ?>>Não Operacional</option>
                                                </select>
                                                </div>
                                        
                                        <div>
                                                <label for="titulo">Titulo:</label>
                                                <div class="input-select-titulo ">
                                                    <select id="input-titulo" class="input-select-geral" name="titulo" onchange="this.form.submit()">
                                                        <option value="">Selecione</option>
                                                        <?php
                                                        foreach(Con01::read(null, $_SESSION['usuario']->id_empresa) as $titulo) {?>
                                                            <option <?php if($get_titulo == $titulo->id) { ?> selected <?php } ?> value="<?=$titulo->id?>"><?=$titulo->nome?></option>
                                                        <?php } ?>
                                                        </select>

                                                </div>
                                        </div>

                                        <div id="subtitulo-dre-div">
                                        <label for="subtitulo">Sub-Titulo</label>
                                            <div id="subtitulo-dre">
                                                <select id="input-subtitulo" class="input-select-geral" name="subtitulo" class="form-control" onchange="this.form.submit()">
                                                    <option value="">  Selecione</option>
                                                    <?php
                                                    if(isset($get_titulo)) {
                                                        foreach(Con02::read(null, $_SESSION['usuario']->id_empresa, con01_id: $get_titulo) as $subtitulo) {?>
                                                            <option <?php if($get_subtitulo == $subtitulo->id) { ?> selected <?php } ?> value="<?=$subtitulo->id?>"><?=$subtitulo->nome?></option>
                                                        <?php } } ?>
                                                                                                   
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-center">
                                                <label for="data_final" id="input-label-todas-empresas" >Todas as Empresas:</label>
                                                <input <?php if($todas_empresas) echo 'checked' ?> type="checkbox" name="todas_empresas">
                                                </div>               

                                        </div>
                                        <div class="inputs-dre-btn" id="inputs-btn-analitico">
                                            <div class="botoes-acao">
                                                <button type="submit" class="btn-sm btn" style="background-color: #5856d6; color: white; ">Filtrar</button>
                                                <a href="analitico.php" class="btn btn-secondary btn-sm">Limpar</a>
                                            </div>

                                            
                                                <?php if ((isset($get_data_inicial) && $get_data_inicial != '') || (isset($get_data_final) && $get_data_final != '') || (isset($get_titulo) && $get_titulo != '') || (isset($get_subtitulo) && $get_subtitulo != '') || $get_custos != '') { ?>
                                                    <div class="botoes-gerar">
                                                        <button type="button" class="btn-sm btn" id="botao-gerar-pdf"
                                                            onclick="prepararGeracao('pdf')">Gerar PDF</button>
                                                        <button type="button" class="btn-sm btn" id="botao-gerar-excel"
                                                            onclick="prepararGeracao('excel')">Gerar Excel</button>
                                                    </div>
                                                <?php } ?>
                                            
                                        </div>
                                    </div>

                                </form>



                            </div>
                        </div>

                        </tbody>

                    </div>

                    </tbody>
                    <?php
                    // Buscar lançamentos de todas as empresas
                    $lancamentos = [];
                    foreach($empresa_lista as $empresa) {
                        $lancamentos_empresa = Ban02::read(
                            id_empresa: $empresa->id,
                            filtro_data_inicial: $get_data_inicial,
                            filtro_data_final: $get_data_final,
                            filtro_titulo: $get_titulo,
                            filtro_subtitulo: $get_subtitulo,
                            dre_read: true
                        );
                        $lancamentos[] = $lancamentos_empresa;
                    }
                    
                    // Obter subtítulos únicos (con02) usados nos lançamentos
                    $titulos = [];
                    $subtitulos = [];
                    $subtitulos_ids = [];
                    
                    if (!empty($lancamentos)) {
                        foreach ($lancamentos as $lancamentos_lista) {
                            foreach($lancamentos_lista as $lan) {
                                if (!empty($lan->id_con02) && !in_array($lan->id_con02, $subtitulos_ids)) {
                                    $subtitulos_ids[] = $lan->id_con02;
                                }
                            }
                        }

                        // carregar objetos Con02
                        foreach ($subtitulos_ids as $id_con02) {
                            $c2 = Con02::read($id_con02);
                            if ($c2 && isset($c2[0])) {
                                $subtitulos[] = $c2[0];
                            }
                        }
                    }
                    
                    // Agrupar subtítulos por título
                    $titulos_array = [];
                    $subtitulos_agrupados = [];
                    foreach ($subtitulos as $subtitulo) {
                        $titulo = Con01::read(
                            id: $subtitulo->id_con01,
                            ordenar_por: 'tipo',
                            filtro_operacional: $get_operacional);
                        if ($titulo && isset($titulo[0])) {
                            $nome_titulo = $titulo[0]->nome;
                            if(!isset($titulos_array[$nome_titulo])) {
                                $titulos_array[$nome_titulo] = $titulo[0];
                                $subtitulos_agrupados[$nome_titulo] = [];
                            }
                            if (!in_array($subtitulo, $subtitulos_agrupados[$nome_titulo])) {
                                $subtitulos_agrupados[$nome_titulo][] = $subtitulo;
                            }
                        }
                    }
                    $titulos = array_values($titulos_array);
                    
                    // Exibir cada título como um accordion
                        if (isset($titulos)) { ?>
                            
                           <div class="accordion custom-accordion avoid-page-break"style="border:0;" id="accordionTitulos">
                            <?php 
                            $total_geral = [];
                            $total_receitas = [];
                            $total_despesas = [];
                            foreach ($titulos as $i => $titulo) {
                                
                               
                                $collapseId = 'tituloCollapse' . $i;
                                ?>
                                <div class="accordion-item avoid-page-break">
                                    <h2 class="accordion-header avoid-page-break" id="headingTitulo<?= $i ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#<?= $collapseId ?>" aria-expanded="false"
                                            aria-controls="<?= $collapseId ?>">
                                            <span
                                                style="color: #303640; font-size:25px; font-weight:500;"><?= ucfirst($titulo->nome); ?></span>
                                        </button>
                                    </h2>
                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse "
                                        aria-labelledby="headingTitulo<?= $i ?>">
                                        <div class="accordion-body">
                                            <?php
                                            // Buscar todos os subtítulos (Con02) desse título
                                
                                            $totais_gerais = [];

                                            $sub_idx = 1;
                                            $subtitulos_do_titulo = $subtitulos_agrupados[$titulo->nome] ?? [];
                                            
                                            // Agrupar subtítulos por nome e consolidar IDs
                                            $subtitulos_consolidados_nomes = [];
                                            $parcelas_por_nome = [];
                                            
                                            foreach ($subtitulos_do_titulo as $subtitulo) {
                                                if (!isset($subtitulos_consolidados_nomes[$subtitulo->nome])) {
                                                    $subtitulos_consolidados_nomes[$subtitulo->nome] = [
                                                        'primeiro_obj' => $subtitulo,
                                                        'ids' => []
                                                    ];
                                                    $parcelas_por_nome[$subtitulo->nome] = [];
                                                }
                                                $subtitulos_consolidados_nomes[$subtitulo->nome]['ids'][] = $subtitulo->id;
                                            }
                                            
                                            // Buscar parcelas para todos os subtítulos consolidados
                                            foreach ($subtitulos_consolidados_nomes as $nome_subtitulo => $dados_subtitulo) {
                                                $ids_subtitulo = $dados_subtitulo['ids'];
                                                
                                                // Filtrar parcelas dos lançamentos pelos IDs dos subtítulos consolidados
                                                if (!empty($lancamentos)) {
                                                    foreach ($lancamentos as $lancamentos_lista) {
                                                        foreach($lancamentos_lista as $lancamento) {
                                                            if (in_array($lancamento->id_con02, $ids_subtitulo)) {
                                                                $parcelas_por_nome[$nome_subtitulo][] = $lancamento;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            // Exibir subtítulos agrupados com suas parcelas consolidadas
                                            foreach ($subtitulos_consolidados_nomes as $nome_subtitulo => $dados_subtitulo) {
                                                $subtitulo = $dados_subtitulo['primeiro_obj'];
                                                $parcelas = $parcelas_por_nome[$nome_subtitulo] ?? [];
                                                $total_subtitulo = 0;
                                                if (count($parcelas) > 0) {
                                                ?>
                                                <div class="">
                                                <h5 class=""> <?=htmlspecialchars($subtitulo->nome)?> </h5>
                                                    
                                                    
                                                    <table class="table table-striped table-bordered " style="margin: none;">
                                                        <thead>
                                                            <tr class="tr-dre-analitico avoid-page-break">
                                                                <th>Descrição</th>
                                                                <th>Valor</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            
                                                            foreach ($parcelas as $rec) {
                                                                
                                                                if ($titulo->tipo == 'D') {
                                                                    $obj = Ban02::read($rec->id)[0];
                                                                } else {
                                                                    $obj = Ban02::read($rec->id)[0];
                                                                }
                                                            
                                                                $data = $rec->data ? (new DateTime($rec->data))->format('d/m/Y') : '';
                                                                if($rec->descricao_comp != null && $rec->descricao_comp != '') {
                                                                    $descricao = $rec->descricao . ' / ' . $rec->descricao_comp .  ' - ' .$data;
                                                                } else {
                                                                    $descricao = $rec->descricao . ' - ' .$data;
                                                                }
                                                                
                                                                $valor = $rec->valor;
                                                                $total_subtitulo += $valor;
                                                                echo '<tr class="tr-dre-analitico avoid-page-break">';
                                                                echo '<td style="width:84rem;">' . htmlspecialchars($descricao) .  '</td>';
                                                                echo '<td style="width:9rem;" ><div class="valor-monetario"><div>R$</div> <div>' . format_valor_alinhado($valor) . '</div></div></td>';
                                                                echo '</tr>';
                                                            }
                                                            ?>
                                                            <tbody>
                                                                <tr class="tr-dre-total avoid-page-break">
                                                                <td>Saldo do subtitulo:</td>
                                                                <td id="total-dre-analitico"> <div>R$</div><div><?= number_format($total_subtitulo, 2, ',') ?></div></td>
                                                                </tr>
                                                            </tbody>
                                                            
                                                            
                                                            </tbody>
                                                            </table>
                                                            </div>
                                                        <?php    
                                                            $totais_gerais[] = $total_subtitulo;
                                                }
                                            }
                                            // Exibir total geral do título
                                            if (count($totais_gerais) > 0) {
                                                $total_titulo = array_sum($totais_gerais);
                                                // mostra total imediatamente após subtítulos
                                                echo '<div style="font-size:1.2em; margin-top:2em;" id="total-titulo-'.$i.'">Total do Titulo: R$ ' . number_format($total_titulo, 2, ',', '.') . '</div>';
                                            } else {
                                                $total_titulo = 0;
                                            }
                                            echo '</div></div></div>';
                                            if ($titulo->tipo == 'D') {
                                                $total_despesas[] = $total_titulo;
                                            } else if ($titulo->tipo == 'C') {
                                                $total_receitas[] = $total_titulo;
                                            }
                                            $total_geral[] = $total_titulo;

                            }
                            echo '</div>';
                        }
                        // ...existing code...
                    
                        ?>
                                            <?php if (empty($titulos)) { ?>
                                                <div id="div-registro-vazio-dre">
                                                    <h3>Nenhum registro encontrado</h3>
                                                </div>
                                            <?php } ?>

                                </div>

                            </div>
                         <?php if (!empty($titulos)) { ?>
                            <div class="card-footer avoid-page-break" id="totais-dre">

                                <?php
                                $total_geral = array_sum($total_geral);
                                $total_receitas = array_sum($total_receitas);
                                $total_despesas = array_sum($total_despesas);

                                ?>
                                <div style="margin-top:2em;" class="avoid-page-break" id="total-receitas">Total receitas: <br> R$
                                    <?= number_format($total_receitas, 2, ',', '.') ?> </div>
                                <div style="margin-top:2em;" class="avoid-page-break" id="total-despesas">Total despesas: <br>  R$
                                    <?= number_format($total_despesas, 2, ',', '.') ?> </div>
                                <div style="margin-top:2em;" class="avoid-page-break" id="total-dre">Saldo do DRE: <br>  R$
                                    <?= number_format($total_geral, 2, ',', '.') ?> </div>

                            </div>
                        <?php } ?>                       
                        </div>
                        

                    </div>
                </div>
            </div>
            
<?php require_once __DIR__ . '/../../../componentes/footer/footer.php' ?> 
</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../../choices/choices.js"></script>

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

    function prepararGeracao(target) {
    let titulo = document.getElementById('input-titulo').options[document.getElementById('input-titulo').selectedIndex].text;
    let subtitulo = document.getElementById('input-subtitulo').options[document.getElementById('input-subtitulo').selectedIndex].text;
    let nomeEmpresa = document.querySelector('#nome-empresa h1').innerHTML

    if(subtitulo == 'Selecione' || subtitulo == null || titulo == null) {
        subtitulo = '';
    }
    if(titulo == 'Selecione' || titulo == null) {
        titulo = ''
    }
    if (subtitulo !== '' && subtitulo !== 'Selecione') {
        subtitulo = ' - ' + subtitulo;
    }
    let data_inicial = document.getElementById('data_inicial').value;
    let data_final = document.getElementById('data_final').value;
    let dataTexto = '';
    if (data_inicial !== '' && data_final !== '') {
        dataTexto = 'Período: ' + data_inicial + ' até ' + data_final;
    } else if (data_inicial !== '') {
        dataTexto = 'Data Inicial: ' + data_inicial;
    } else if (data_final !== '') {
        dataTexto = 'Data Final: ' + data_final;
    }
    if(target == 'pdf'){
         gerarpdf('analitico', dataTexto, titulo + subtitulo, nomeEmpresa);
    } else if(target == 'excel'){
        gerarexcel('analitico', dataTexto, titulo + subtitulo, nomeEmpresa);
    }
}
    
    function checarTitulo(resetSubtitulo = false) {
        var tituloSelect = document.getElementById('input-titulo');
        var tituloId = tituloSelect.value;
        var subtituloSelect = document.getElementById('input-subtitulo');
        let subtituloDiv = document.getElementById('subtitulo-dre-div');
        var options = subtituloSelect.querySelectorAll('option');
        let divText = document.querySelector('.inputs-dre-text');
        divChildren = divText.children
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


        

        if (resetSubtitulo) {
            subtituloSelect.value = ""; // Só reseta se for troca de título
        }
        
    }

    document.getElementById('input-titulo').addEventListener('change', function () {
        checarTitulo();
    });
    checarTitulo()




    function checar() {
        let nome = document.querySelector('.input-nome input').value;
        let fantasia = document.querySelector('.input-fantasia input').value;
        let cpf = document.querySelector('.input-cpf input').value;
        let cnpj = document.querySelector('.input-cnpj input').value;
        let cep = document.querySelector('.input-cep input').value;
        let endereco = document.querySelector('.input-endereco input').value;
        let bairro = document.querySelector('.input-bairro input').value;
        let cidade = document.querySelector('.input-cidade input').value;
        let estado = document.querySelector('.input-estado input').value;
        let celular = document.querySelector('.input-celular input').value;
        let telefone = document.querySelector('.input-telefone input').value;
        let email = document.querySelector('.input-email input').value;




        if (nome !== '' && fantasia !== '' && cpf !== '' && cnpj !== '' && cep !== '' && endereco !== '' && bairro !== '' && cidade !== '' && estado !== '' && celular !== '' && telefone !== '' && email !== '') {
            document.querySelector('button[name="acao"]').disabled = false;
        } else {
            document.querySelector('button[name="acao"]').disabled = true;
        }
    }

</script>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="gerar.js"></script>




</html>