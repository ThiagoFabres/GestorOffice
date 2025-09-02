<?php

require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/contas.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/recebimentos.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$view = filter_input(INPUT_GET, 'view');
$target = filter_input(INPUT_GET, 'target');
$con01 = filter_input(INPUT_GET, 'con01id');
$acao = filter_input(INPUT_GET, 'acao');
$get_opcao = filter_input(INPUT_GET, 'opcao');
$get_id = filter_input(INPUT_GET, 'id');



if(isset($view) && $view == 'contas'){
    $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa);
 }

 if(isset($get_id)) {
    $rec02_target = Rec02::read($get_id)[0];
    $recebimento = Rec01::read($rec02_target->id_rec01)[0];
    $parcelas_pagas = false;
    for($i = 1; $i < $recebimento->parcelas; $i++) {
        $rec02 = Rec02::read(null, $_SESSION['usuario']->id_empresa, $recebimento->id, null, $i)[0];
        if($parcelas_pagas == false && $rec02->valor_pag == $rec02->valor_par) {
            $parcelas_pagas = true;
            break;
        } else {
            continue;
            
        }
    }
 }


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



    

            <?php if(isset($view) && $view == 'contas') { require_once 'contas.php'; } 
            
            else if ($view == 'receber' && (!isset($acao) || $acao == 'adicionar' || $acao == 'visualizar')) { require_once 'receber.php'; }?>


    <!-- Modal -->
    <div class="modal fade" id="modal_titulo" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <?php if(isset($target )&& $target == 'titulo') {
                        $titulo_modal = 'Título';
                        $target = 'titulo';

                    } else if(isset($target) && $target == 'subtitulo') {
                        $titulo_modal = 'Subtítulo';
                        $target = 'subtitulo';
                    } 
                     ?>
                    <h5 class="modal-title" id="exampleModalLongTitle">Novo <?= htmlspecialchars($titulo_modal, ENT_QUOTES, 'UTF-8') ?></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="conta">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="target" value="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?? ''; ?>">
                        <input type="hidden" name="con01id" value="<?php echo htmlspecialchars($con01, ENT_QUOTES, 'UTF-8') ?? ''; ?>">

                        <div class="input-nome input-form-adm">
                            <!--Nome: -->
                            <label for="nome">Nome:</label>
                            <input type="text" onchange="checar()" name="nome" class="form-control" placeholder="Nome" value="" required>
                        </div>
                    <?php if(isset($target) && $target == 'titulo'){ ?>
                        <div class="input-nome input-form-adm">
                            <label for="nome">Tipo:</label>
                            <select name="tipo" class="form-select" id="tipo">
                                <option value="C">Crédito</option>
                                <option value="D">Débito</option>
                            </select>
                        </div>
                    <?php } ?>


                        <div style="margin-bottom: 3em;" class="footer">

                        <button name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" href="consulta_cliente.php">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                            

                    </form>


                </div>

            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>

    <div class="modal fade" id="modal_visualizar" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">

                    <h5 class="modal-title" id="exampleModalLongTitle">Opções da conta</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                <?php if(!isset ($get_opcao)){ 
                    $rec02 = Rec02::read($get_id)[0];
                    ?>
                    <form method="get" id="content" action="index.php">
                        
                        <input type="hidden" name="view" value="receber">
                        <input type="hidden" name="acao" value="visualizar">
                        <input type="hidden"  name="id" value="<?=$get_id?>">
                        

                        <div class="acoes">

                        <button type="submit" class="btn-lg btn-primary btn" <?php if ($rec02->valor_pag == $rec02->valor_par) { ?> disabled <?php } ?>name="opcao" value="quitar">Quitar</button>
                        <button type="button" class="btn-lg btn-primary btn" <?php if ($rec02->valor_pag == 0) { ?> disabled <?php } ?> name="opcao" onclick="window.location.href='cadastros_manager.php?view=receber&target=parcela&acao=estornar&id=<?=$get_id?>'" value="estornar">Estornar</button>
                        <button type="button" class="btn-lg btn-primary btn" <?php if ($parcelas_pagas == true) { ?> disabled <?php } ?>onclick="window.location.href= 'index.php?view=receber&acao=editar&id=<?=$get_id?>'" name="opcao">Editar parcelas</button>
                        
                        </div>
                        <div style="margin-bottom: 3em;" class="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                            

                    </form>
            <?php } else if(isset($get_opcao) && $get_opcao == 'quitar') {
                $data_atual = new DateTime();
                $parcela = Rec02::read($get_id)[0];
                $valor_restante = $parcela->valor_par - $parcela->valor_pag;
                $pagamentos = TipoPagamento::read(null, $_SESSION['usuario']->id_empresa);


                ?> 
                    
                    <form method="post" id="content" action="cadastros_manager.php">
                        
                        <input type="hidden" name="view" value="receber">
                        <input type="hidden" name="acao" value="quitar">
                        <input type="hidden" name="target" value="parcela">
                        <input type="hidden" name="id" value="<?=$get_id?>">

                        
                        <div class="valor-alvo"><p style="color: #00000096;">Valor restante da parcela: R$ <?=$valor_restante?></p></div>
                        <label for="data">Data do pagamento</label>
                        <input class="form-control" type="date" placeholder="dd/mm/aa" name="data" value="<?=$data_atual->format('Y-m-d')?>">
                        <label for="valor">Valor pago</label>
                        <input class="form-control" type="text" name="valor" placeholder="Valor pago">
                        <label for="forma_pagamento">Forma de pagamento</label>
                        <select class="form-control" name="forma_pagamento">
                            <option value="">Selecione uma forma de pagamento</option>
                            <?php foreach($pagamentos as $pagamento) {?>
                                <option value="<?=$pagamento->id?>">
                                    <?= $pagamento->nome?>
                                </option>
                            <?php } ?>
                        </select>
                        <div style="margin-bottom: 3em;" class="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                        <button type="submit" class="btn btn-primary">Pagar</button>

                    </form>
            <?php } ?>

                </div>

            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>

    

        <div class="modal fade" id="modal_receber" tabindex="-1" role="dialog"
        aria-labelledby="modal_receber_title" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">

                    <h5 class="modal-title" id="modal_receber_long_title">Novo Lançamento</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="index.php?view=receber&acao=editar">
                        <input type="hidden" name="view" value="receber">

                        

                        <div class="input-documento input-form-adm">
                            <!--Nome: -->
                            <label for="documento">Documento:</label>
                            <input type="text" onchange="checar()" name="documento" class="form-control" placeholder="Documento" value="" required>
                        </div>

                        <div class="input-cadastro input-form-adm">
                            <!--Nome: -->
                            <label for="cadastro">Cliente / Fornecedor:</label>
                            <select name="cadastro" class="form-select" id="cadastro">
                                <option value="">Selecione</option>
                                <?php foreach (Cadastro::read(null, null, $_SESSION['usuario']->id_empresa) as $cadastro) { ?>
                                    <option value="<?= $cadastro->id_cadastro?>"><?= htmlspecialchars($cadastro->nom_fant, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="input-valor input-form-adm">
                            <!--Nome: -->
                            <label for="valor">Valor:</label>
                            <input type="text" onchange="checar()" name="valor" class="form-control" placeholder="Valor" value="" required>
                        </div>

                        <div class="input-parcelas input-form-adm">
                            <!--Nome: -->
                            <label for="parcelas">Parcelas:</label>
                            <input type="number" onchange="checar()" name="parcelas" class="form-control" placeholder="Parcelas" value="" required>
                        </div>

                        <div class="input-descricao input-form-adm">
                            <!--Nome: -->
                            <label for="descricao">Descrição:</label>
                            <input type="text" onchange="checar()" name="descricao" class="form-control" placeholder="Descrição" value="" required>
                        </div>

                        
                        <div class="titulos-receber" style="display:flex; flex-direction: row;">

                        <div class="input-titulo input-form-adm" style="width: 50%;" >
                            <!--Nome: -->
                            <label for="titulo">Titulo</label>
                            <select name="titulo" class="form-select" id="titulo" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <option value="">Selecione</option>
                                
                                <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa); 
                                foreach ($titulos as $titulo) { ?>
                                    <option value="<?= $titulo->id ?>"><?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="input-subtitulo input-form-adm" style="width: 50%;">
                            <!--Nome: -->
                            <label for="subtitulo">Sub-Titulo</label>
                            <select id="subtitulo" name="subtitulo" class="form-control">
                                <option value="">Selecione</option>
                                <?php
                                // Buscar todos os subtítulos da empresa
                                $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                foreach ($todosSubtitulos as $sub) { ?>
                                    <option value="<?= $sub->id ?>" data-titulo-id="<?= $sub->id_con01 ?>">
                                        <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                            <div class="input-nome input-form-adm">
                            <label for="obs">Observação:</label>
                            <input type="text" onchange="checar()" name="obs" class="form-control" placeholder="Observação" value="" required>
                        </div>
                    
                        <div style="margin-bottom: 3em;" class="footer">

                        <button name="acao" value="adicionar" class="btn btn-success" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0;" href="consulta_cliente.php">Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">Fechar</button>
                            

                    </form>


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

<?php if ( isset($acao) && $acao == 'adicionar' || $acao == 'visualizar') 
    
    { ?>
    
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById(<?php if($view == 'contas') {?> 'modal_titulo' <? } 
                                                     else if ($view == 'receber') { if($acao == 'adicionar'){?> 'modal_receber' <? } 
                                                     else if($acao == 'visualizar'){ ?> 'modal_visualizar' <?php }}?>);
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'index.php?view=<?= $view ?>';
            });
        });


    </script>
<?php }
; ?>



</html>0


