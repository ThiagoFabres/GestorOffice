<?php


require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/bairro.php';
require_once __DIR__ . '/../db/entities/cidade.php';
require_once __DIR__ . '/../db/entities/contas.php';



session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
$get_registro = filter_input(INPUT_GET, 'registro');

$get_nome = filter_input(INPUT_GET, 'nome');
$get_data_inicial = filter_input(INPUT_GET, 'dataInicial');
$get_data_final = filter_input(INPUT_GET, 'dataFinal');
$get_estado = filter_input(INPUT_GET, 'estado');
$get_cidade = filter_input(INPUT_GET, 'cidade');
$get_bairro = filter_input(INPUT_GET, 'bairro');
$get_titulo = filter_input(INPUT_GET, 'titulo');
$get_acao = filter_input(INPUT_GET, 'acao');

if(!empty($_GET) && isset($get_registro) && $get_registro == 'cadastros') {
    $cadastros_reg = Cadastro::read(null,null,$_SESSION['usuario']->id_empresa,$get_nome ??null, $get_data_inicial ?? null,$get_data_final ?? null,$get_estado ?? null,$get_cidade ?? null,$get_bairro ?? null);
}

if(!empty($_GET) && isset($get_registro) && $get_registro == 'contas') {
    $subtitulos_reg = Con02::read(null, $_SESSION['usuario']->id_empresa,$get_titulo ?? null, $get_data_inicial ?? null, $get_data_final ?? null);
}


$estadosLista = [
    "AC" => "Acre",
    "AL" => "Alagoas",
    "AP" => "Amapá",
    "AM" => "Amazonas",
    "BA" => "Bahia",
    "CE" => "Ceará",
    "ES" => "Espírito Santo",
    "GO" => "Goiás",
    "MA" => "Maranhão",
    "MT" => "Mato Grosso",
    "MS" => "Mato Grosso do Sul",
    "MG" => "Minas Gerais",
    "PA" => "Pará",
    "PB" => "Paraíba",
    "PR" => "Paraná",
    "PE" => "Pernambuco",
    "PI" => "Piauí",
    "RJ" => "Rio de Janeiro",
    "RN" => "Rio Grande do Norte",
    "RS" => "Rio Grande do Sul",
    "RO" => "Rondônia",
    "RR" => "Roraima",
    "SC" => "Santa Catarina",
    "SP" => "São Paulo",
    "SE" => "Sergipe",
    "TO" => "Tocantins"
];
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
            <a href="/usuario/"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-layers"></i></div> Dashboard </a>
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
            <a href="index.php?view=contas"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-journal-bookmark"></i></div> Plano de Contas </a>
        </div>

        <div class="menu-item ">
            <a href="index.php?view=receber"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-wallet"></i></div> Contas a Receber </a>
        </div>

        <div class="menu-item">
            <a href="index.php?view=pagar"> <div style="padding: 0.5em; align-items:center;"><i class="bi bi-cash-stack"></i></div> Contas a Pagar </a>
        </div>

        <div class="menu-item accordion" >

        <a class="nav-link text-white" data-bs-toggle="collapse" href="#registrosMenu" role="button" aria-expanded="false" aria-controls="cadastrosMenu">
        <i class="bi bi-journal-bookmark"></i> Registros
      </a>

        <div class="collapse"  id="registrosMenu">
            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
            <li><a href="registros.php?registro=cadastros" class="link-light nav-link text-decoration-none"><i class="bi bi-list-task"></i>Cadastros</a></li>
            <li><a href="registros.php?registro=contas" class="link-light nav-link text-decoration-none"><i class="bi bi-journal-bookmark"></i>Plano de Contas</a></li>
            <li><a href="registros.php?registro=receber" class="link-light nav-link text-decoration-none"><i class="bi bi-wallet"></i>Contas a Receber</a></li>
            <li><a href="registros.php?registro=pagar" class="link-light nav-link text-decoration-none"><i class="bi bi-cash-stack"></i>Contas a Pagar</a></li>
            <li><a href="registros.php?registro=dre" class="link-light nav-link text-decoration-none"><i class="bi bi-file-earmark-text"></i>DRE</a></li>
            
            </ul>
        </div>
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
        <a class="superior-item" href="index.php">Dashboard</a>
    </div>
    <div class="conta-header" style="position:relative; float:right; margin-right:2em;">
        <button id="userBtn" type="button" style="background:none;border:none;font-size:1.2em;color:#181f2b;outline:none;cursor:pointer;">
            <span style="color:#181f2b;"><?= $_SESSION['usuario']->nome ?> </span>
        </button>
        <div id="userMenu" style="right:0; z-index: 1000000;">
            <a href="/" class="dropdown-item">
                <i class="bi bi-box-arrow-left"></i> Logout
        </a>
        </div>
    </div>
    </div>



    

            
<div class="main" id="container">
    

        <?php if(isset($get_registro) && $get_registro == 'cadastros') {
            
            
            $bairros = Bairro::read(null, $_SESSION['usuario']->id_empresa);
            $cidades = Cidade::read(null, $_SESSION['usuario']->id_empresa);

            ?>

            
                <div class="card mb-4">
        <div class="card-header">
            <h3>Clientes / Fornecedores</h3>
        </div>

        <div class="card-header-div">
        <div class="card-header-borda">
            <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
                <h5 class="card-title">Filtros</h5>
                
                <form class="row g-3 align-items-end mb-3" method="get" action="registros.php">
                    <input type="hidden" name="registro" value="cadastros">
                    <div class="col-md-2" style="width: 15%;">
                        <label for="nome" class="form-label">Nome:</label>
                        <div class="input-group">
                            <input name="nome" value="<?= htmlspecialchars($get_nome, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="text" class="form-control" id="dataInicio" placeholder="Nome">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 12.5%;">
                        <label for="dataInicio" class="form-label">Data inicial:</label>
                        <div class="input-group">
                            <input name="dataInicial" value="<?= htmlspecialchars($get_data_inicial, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="date" class="form-control" id="dataInicio" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>
                    <div class="col-md-2" style="width: 12.5%;">
                        <label for="dataFinal" class="form-label">Data final:</label>
                        <div class="input-group">
                            <input name="dataFinal"  value="<?= htmlspecialchars($get_data_final, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="date" class="form-control" id="dataFinal" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>

                    <div class="col-md-1" style="width: 12.5%;">
                        <label for="estado" class="form-label">Estado:</label>
                        <select name="estado" class="form-select" id="estado">
                        
                            <option value="" <?php if(!isset($get_estado)) {?> selected <?php } ?> >Selecione uma Estado</option>
                            <?php foreach ($estadosLista as $sigla => $estado) { ?>
                                <option value="<?= $sigla ; ?>"  <?php if(isset($get_estado) && $sigla == $get_estado) {?> selected <?php } ?>  ><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php }; ?>
                        </select>
                    </div>
                
                    <div class="col-md-1" style="width: 12.5%;">
                        <label for="cidade" class="form-label">Cidade:</label>
                        <select name="cidade" class="form-select" id="cidade">
                        
                            <option value="" <?php if(!isset($get_cidade)) {?> selected <?php } ?> >Selecione uma cidade</option>
                        
                            <?php foreach ($cidades as $cidade) { ?>
                                <option value="<?php echo $cidade->id; ?>"  <?php if(isset($get_cidade) && $cidade->id == $get_cidade) {?> selected <?php } ?>  ><?php echo htmlspecialchars($cidade->nome, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php }; ?>
                        </select>
                    </div>

                    <div class="col-md-1" style="width: 12.5%;">
                        <label for="bairro" class="form-label">Bairro:</label>
                        <select name="bairro" class="form-select" id="bairro">
                        
                            <option value="" <?php if(!isset($get_bairro)) {?> selected <?php } ?> >Selecione um bairro</option>
                        
                            <?php foreach ($bairros as $bairro) { ?>
                                <option value="<?php echo $bairro->id; ?>"  <?php if(isset($get_bairro) && $bairro->id == $get_bairro) {?> selected <?php } ?>  ><?php echo htmlspecialchars($bairro->nome, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php }; ?>
                        </select>
                    </div>
                
                    
                    <div style="width: 10%;">
                        <button type="submit" style="background-color: #5856d6; border: 0;" class="btn btn-primary w-100">Buscar</button>
                    </div>
                    <div style="width: 10%;">
                        <a type="button" style="background-color: #5856d6; border: 0;" href="registros.php?registro=cadastros" class="btn btn-secondary">Limpar Filtros</a>
                    </div>
                </form>
                </div>
                </div>
            </div>

            <div class="card-body tab-content" id="relatorioTabsContent" style="padding: 0;">

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Estado</th>
                        <th>Bairro</th>
                        <th>Data de registro</th>
                    </tr>
                </thead>
                <tbody>
            <?php if (!empty($cadastros_reg)) { ?>
                        <?php foreach ($cadastros_reg as $cadastro) {
                            $cidade = Cidade::read($cadastro->id_cidade)[0];
                            $bairro = Bairro::read($cadastro->id_bairro)[0];
                             ?>

                                                            <tr data-id="<?= $cadastro->id ?>">
                                <td>
                                                <?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8')?>
                                                <p>Razao social: <?= htmlspecialchars($cadastro->razao_soc, ENT_QUOTES, 'UTF-8')?></p>
                                            </td>

                                            <td>
                                                <?=$cadastro->celular?>
                                                <p> Fixo: <?= htmlspecialchars($cadastro->fixo, ENT_QUOTES, 'UTF-8')?></p>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($cadastro->estado, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($cidade->nome, ENT_QUOTES, 'UTF-8') ?>
                                                <p>CEP: <?= htmlspecialchars($cadastro->cep, ENT_QUOTES, 'UTF-8') ?></p>
                                            </td>
                                            <td>
                                                <?= $bairro->nome ?>
                                                <p>Rua: <?= htmlspecialchars($cadastro->rua, ENT_QUOTES, 'UTF-8') ?></p>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime(htmlspecialchars($cadastro->data_r, ENT_QUOTES, 'UTF-8'))) ?>
                                            </td>
                                
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td>Nenhum cliente encontrado</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>


                        </tr>
                    <?php } ?>
            
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } 

else if(isset($get_registro) && $get_registro == 'contas') {
        $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa);
                        $subtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
            ?>

            
                <div class="card">
        <div class="card-header">
            <h3>Titulos</h3>
        </div>


        <div class="card-header-div">
        <div class="card-header-borda">

            <div class="tab-pane fade show active" id="vendas" role="tabpanel" aria-labelledby="vendas-tab">
                <h5 class="card-title">Filtros</h5>
                
                <form class="row g-3 align-items-end mb-3" method="get" action="registros.php">
                    <input type="hidden" name="registro" value="contas">
                    <div class="col-md-3">
                        <label for="dataInicio" class="form-label">Data inicial:</label>
                        <div class="input-group">
                            <input name="dataInicial" value="<?= htmlspecialchars($get_data_inicial, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="date" class="form-control" id="dataInicio" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dataFinal" class="form-label">Data final:</label>
                        <div class="input-group">
                            <input name="dataFinal"  value="<?= htmlspecialchars($get_data_final, ENT_QUOTES, 'UTF-8') ?? "" ?>" type="date" class="form-control" id="dataFinal" placeholder="dd/mm/aaaa">
                            
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="titulo" class="form-label">Titulos:</label>
                        <select name="titulo" class="form-select" id="titulo">
                        
                            <option value="" <?php if(!isset($get_titulo)) {?> selected <?php } ?> >Selecione um Titulo</option>
                            <?php foreach ($titulos as $titulo) { ?>
                                <option value="<?= $titulo->id ; ?>"  <?php if(isset($get_titulo) && $titulo->id == $get_titulo) {?> selected <?php } ?>  ><?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php }; ?>
                        </select>
                    </div>                
                    
                    <div style="width: 6em; height: 2.5em; ">
                        <button type="submit" style="background-color: #5856d6; border: 0;" class="btn btn-primary w-100">Buscar</button>
                    </div>
                    <div style="width: 6em; height: 2.5em;">
                        <a type="button" href="registros.php?registro=contas" style="background-color:#5856d6; border:0;" class="btn btn-primary">Limpar Filtros</a>
                    </div>
                </form>
                </div>
                </div>
            </div>
                    <div class="card-body tab-content" id="relatorioTabsContent" style="padding: 0;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Subtitulo</th>
                        <th>Titulo</th>
                        <th>Data de Registro</th>
                    </tr>
                </thead>
                <tbody>
            <?php if (!empty($subtitulos_reg)) { ?>
                        <?php foreach ($subtitulos_reg as $subtitulo) 
                            
                        {
                            $titulo = Con01::read($subtitulo->id_con01)[0];
                            
                             ?>


                            <tr data-id="<?= htmlspecialchars($subtitulo->id, ENT_QUOTES, 'UTF-8') ?>">
                                <td><?= htmlspecialchars($subtitulo->nome, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($subtitulo->data_r, ENT_QUOTES, 'UTF-8') ?></td>
                                
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td>Nenhum cliente encontrado</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>


                        </tr>
                    <?php } ?>
            
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } ?>

    

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

<?php if ( isset($acao) && $acao == 'adicionar') { ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_empresa');
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'index.php';
            });
        });


    </script>
<?php }
; ?>



</html>


