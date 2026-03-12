<?php
// if (
//     isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
//     $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
// ) {
//     $_SERVER['HTTPS'] = 'on';
// }

// session_set_cookie_params([
//     'lifetime' => 0,
//     'path'     => '/',
//     'domain'   => '.gestorofficecontrol.com.br',
//     'secure'   => true,
//     'httponly' => true,
//     'samesite' => 'Lax'
// ]);
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/cargo.php';




if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 1) {
    header('Location: /');
    exit;
}
require_once __DIR__ . '/../admin.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';

$erro = filter_input(INPUT_GET, 'erro');
$filtro_empresa = filter_input(INPUT_GET, 'empresa');
$filtro_cadastro = filter_input(INPUT_GET, 'cadastro');
$filtro_titulo = filter_input(INPUT_GET, 'titulo');
$filtro_subtitulo = filter_input(INPUT_GET, 'subtitulo');
$filtro_custo = filter_input(INPUT_GET, 'custos');
$filtro_data_inicial = filter_input(INPUT_GET, 'data_inicial');
$filtro_data_final = filter_input(INPUT_GET, 'data_final');
$filtro_vendas = filter_input(INPUT_GET, 'filtro_vendas') == 'on' ? 1 : 0

?>
<!DOCTYPE html>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.3/html2pdf.bundle.min.js" integrity="sha512-yu5WG6ewBNKx8svICzUA01vozhmiQCVfzjzW40eCHJdsDRaOifh9hPlWBDex5b32gWCzawTp1F3FJz60ps6TnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
            <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js "></script>
    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css " rel="stylesheet">

    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="../choices/choices.css">

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

        <div class="menu-item ">
                <a href="/admin/">
                    <div style=" align-items:center;"><i class="bi bi-building"></i></div> Empresas
                </a>
        </div>
        <div class="menu-item menu-item-atual accordion">
            <a class="nav-link text-white" data-bs-toggle="collapse" href="#cadastrosMenu" role="button" aria-expanded="true" aria-controls="cadastrosMenu">
                <div style=" align-items:center;"><i class="bi bi-clipboard"></i></div> Ação Administrativa
            </a>
                    <div  id="cadastrosMenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                            <li class=" menu-li "><a href="/admin/acao_adm/bancario.php" class="link-light text-decoration-none"><i
                                        class="bi bi-bank"></i>Bancária</a></li>
                            <li class=" menu-li menu-li-atual"><a href="/admin/acao_adm/vendas.php" class="link-light text-decoration-none"><i
                                        class="bi bi-coin"></i>Vendas</a></li>
                        </ul>
                    </div>
        </div>

    </div>

    </nav>


    <?php  require_once __DIR__ . '/../../componentes/header/header.php' ?>

            
    <div class="main" id="container">
        <div class="card">
            <div class="card-header">
                <div class="card-header-lancamento">
                    <h3>Ação Administrativa - Vendas</h3>
                </div>
            </div>
            <div class="card-header-div">
                <div class="card-header-borda">
                    <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
                        <h5 class="card-title">Filtros</h5>
                        <form method="get" action="vendas.php">
                            <div class="d-flex flex-row w-100 justify-content-between">
                                <div class="w-100 d-flex flex-column">
                                    <div class="d-flex flex-row">
                                        <div style="width: calc(100% / 2.5);">
                                            <label>Empresa:</label>
                                            <select name="empresa" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione</option>
                                                <?php
                                                $empresas = Empresa::read();
                                                foreach ($empresas as $empresa) {
                                                    if($empresa->id == 1) continue;
                                                    echo "<option value=\"{$empresa->id}\" " . ($filtro_empresa == $empresa->id ? "selected" : "") . ">{$empresa->nom_fant}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div style="width: calc(100% / 2.5);">
                                            <label>Cliente / Fornecedor:</label>
                                            <select name="cadastro" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione</option>
                                                <?php
                                                if($filtro_empresa != null) {
                                                $cadastros = Cadastro::read(id_empresa: $filtro_empresa);
                                                    foreach ($cadastros as $cadastro) {
                                                        echo "<option value=\"{$cadastro->id_cadastro}\" " . ($filtro_cadastro == $cadastro->id_cadastro ? "selected" : "") . ">{$cadastro->nom_fant}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div style="width: calc(100% / 2.5);">
                                            <label>Titulo:</label>
                                            <select name="titulo" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione</option>
                                                <?php
                                                if($filtro_empresa != null) {
                                                $titulos = Con01::read(idempresa: $filtro_empresa, tipo:'C');
                                                    foreach ($titulos as $titulo) {
                                                        echo "<option value=\"{$titulo->id}\" " . ($filtro_titulo == $titulo->id ? "selected" : "") . ">{$titulo->nome}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div style="width: calc(100% / 2.5);">
                                            <label>SubTitulo:</label>
                                            <select name="subtitulo" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione</option>
                                                <?php
                                                if($filtro_empresa != null && $filtro_titulo != null) {
                                                $subtitulos = Con02::read(idempresa: $filtro_empresa, con01_id: $filtro_titulo);
                                                    foreach ($subtitulos as $subtitulo) {
                                                        echo "<option value=\"{$subtitulo->id}\" " . ($filtro_subtitulo == $subtitulo->id ? "selected" : "") . ">{$subtitulo->nome}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex flex-row">
                                        <div style="width: calc(100% / 2.5);">
                                            <label>C. Custos:</label>
                                            <select name="custos" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione</option>
                                                <?php
                                                if($filtro_empresa != null) {
                                                $custos = CentroCustos::read(id_empresa: $filtro_empresa);
                                                    foreach ($custos as $custo) {
                                                        echo "<option value=\"{$custo->id}\" " . ($filtro_custo == $custo->id ? "selected" : "") . ">{$custo->nome}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div style="width: calc(100% / 2.5);">
                                            <label>Data Inicial:</label>
                                            <input type="date" class="form-control" name="data_inicial" value="<?= $filtro_data_inicial ?>">
                                        </div>
                                        <div style="width: calc(100% / 2.5);">
                                            <label>Data Final:</label>
                                            <input type="date" class="form-control" name="data_final" value="<?= $filtro_data_final ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card-body">
                <!-- criar um accordion com cada conta, e dentro mostrar os lançamentos bancários (ban01 e ban02) -->
                <div class="accordion">
                    <?php
                    if($filtro_empresa != null) {
                        if($filtro_cadastro == null) {
                            $cadastros = Cadastro::read(id_empresa: $filtro_empresa);
                        } else {
                            $cadastros = [Cadastro::read($filtro_cadastro, id_empresa:$filtro_empresa)[0]];
                        }
                                                $custos = CentroCustos::read(id_empresa: $filtro_empresa);
                        foreach ($cadastros as $cadastro) {
                            $lancamentos = Rec01::read(
                                filtro_custos: $filtro_custo, 
                                id_cadastro: $cadastro->id_cadastro, 
                                con01: $filtro_titulo,
                                con02: $filtro_subtitulo, 
                                filtro_data_inicial: $filtro_data_inicial, 
                                read_vendas: true,
                                filtro_data_final: $filtro_data_final);
                            if(empty($lancamentos) && $filtro_cadastro == null) {
                               continue;
                            }
                            if($filtro_cadastro != null && $filtro_cadastro != $cadastro->id_cadastro) continue;
                            ?>
                            
                            <div class="accordion-item" style="position: sticky;">
                                <h2 class="accordion-header" id="heading<?=$cadastro->id_cadastro?>" style="position: sticky; margin-bottom: 0.1em;">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?=$cadastro->id_cadastro?>" aria-expanded="false" aria-controls="collapse<?=$cadastro->id_cadastro?>" style="color: black;">
                                        <?=$cadastro->nom_fant ?>
                                    </button>
                                </h2>
                                <div id="collapse<?=$cadastro->id_cadastro?>" class="accordion-collapse collapse" aria-labelledby="heading<?=$cadastro->id_cadastro?>"  data-bs-parent="#accordionExample" style="position: sticky;">
                                    <div class="accordion-body " style="position: sticky; max-height: 50vh; overflow: scroll;">
                                    <?php
                                    
                                    if(count($lancamentos) == 0) {?>
                                        <p>Não há lançamentos para esta cadastro.</p>
                                    <?php echo '</div></div></div>'; continue;
                                    } else { ?>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Centro de Custo</th>
                                                <th>Título</th>
                                                <th>Subtítulo</th>
                                                <th>Valor</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <?php
                                        foreach($lancamentos as $lancamento) {
                                            $titulo = Con01::read(id: $lancamento->id_con01)[0] ?? null;
                                            $subtitulo = Con02::read(id: $lancamento->id_con02)[0] ?? null;
                                            $custo = CentroCustos::read(id: $lancamento->centro_custos)[0] ?? null;
                                            ?>
                                            <tr>
                                                <td><?= $custo ? $custo->nome : '-' ?></td>
                                                <td><?= $titulo ? $titulo->nome : '-' ?></td>
                                                <td><?= $subtitulo ? $subtitulo->nome : '-' ?></td>
                                                <td>R$ <?= number_format($lancamento->valor, 2, ',', '.') ?></td>
                                                <td><?= date('d/m/Y', strtotime($lancamento->data_lanc)) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                    <?php }?>
                                    </div>
                                    <!-- <hr> -->
                                     <?php if(!empty($cadastros)) { ?>
                                     <form method="post" action="acao_manager.php">
                                        <input name="target" type="hidden" value="vendas">
                                        <input name="data_inicial" type="hidden" value="<?= $filtro_data_inicial ?>">
                                        <input name="data_final" type="hidden" value="<?= $filtro_data_final ?>">
                                        <input name="empresa" type="hidden" value="<?= $filtro_empresa ?>">
                                        <input name="cadastro" type="hidden" value="<?= $cadastro->id_cadastro ?>">
                                        <input name="titulo" type="hidden" value="<?= $filtro_titulo ?>">
                                        <input name="subtitulo" type="hidden" value="<?= $filtro_subtitulo ?>">
                                        <input name="custos" type="hidden" value="<?= $filtro_custo ?>">
                                        <input name="vendas" type="hidden" value="<?= $filtro_vendas ? 1 : 0?>">
                                        <button class="btn btn-danger m-3 mt-0 w-100">Excluir</button>
                                     </form>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php }
                    } else {
                        echo "<p>Selecione uma empresa para visualizar as contas e lançamentos.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>





</body>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="/choices/choices.js"></script>
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

<?php if ( isset($_GET['acao']) && $_GET['acao'] == 'adicionar') { ?>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_empresa');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'index.php';
            });
        });
<?php } if(isset($erro) && $erro == 'usado') { ?>
                alert('Não é possível adicionar essa empresa, pois já existe um usuario ou gestor com esse e-mail');
                window.location.href = 'index.php';
<?php } ?>

</script>





</html>


