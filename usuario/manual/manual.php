<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$lateral_target = 'manual';
$con01 = filter_input(INPUT_GET, 'con01id');
$con02 = filter_input(INPUT_GET, 'con02id');
$erro = filter_input(INPUT_GET, 'erro');
$acao = $_GET['acao'] ?? null;
$target = $_GET['target'] ?? null;

if($target != 'titulo' && $target != 'subtitulo' && $target != null) {
    header('Location: contas.php');
    exit;
}

$titulos = [
    'Introdução',
    'Dashboard',
    'Cadastros',
    'Controle Financeiro',
    'Controle Bancário',
    'Controle de Cartão',
    'Recorrentes',
    'Importar',
    'DRE',
    'Importação de arquivo OFX',
    
    
];
$videos = [
    'Dashboard' => '<iframe width="100%" height="700px" src="https://www.youtube.com/embed/OXPsQ3-QVck" title="DASHBOARD" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
    'Cadastros' => '<iframe width="100%" height="700px" src="https://www.youtube.com/embed/-WLAPH795zU" title="CADASTROS" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
    'Controle Financeiro' => '<iframe width="100%" height="700px" src="https://www.youtube.com/embed/1w0CcZs0ZTE" title="CONTROLE FINANCEIRO" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',   
    'Controle Bancário' => '<iframe width="100%" height="700px" src="https://www.youtube.com/embed/zVdaOzBbs-Q" title="CONTROLE BANCÁRIO" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
    'Controle de Cartão' => '<iframe width="100%" height="700px" src="https://www.youtube.com/embed/jnVvwGpgxPw" title="Controle de Cartão - GestorOffice Control" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
    'Recorrentes' => '<iframe width="100%" height="700px" src="https://www.youtube.com/embed/nyoZ798Q-Tw?feature=youtu.be&start=245&end=325&autoplay=1&mute=1&controls=1&loop=1&playlist=nyoZ798Q-Tw&modestbranding=1&rel=0&iv_load_policy=3&showinfo=1&disablekb=1&wmode=transparent&vq=default&theme=default&t=245s#t=245s " title="" frameborder="" allow="" referrerpolicy="" allowfullscreen=""></iframe>',
    'Importar' => '<iframe width="1397" height="787" src="https://www.youtube.com/embed/x6rmNmigUEc" title="Módulo de Importação - Sistema GestorOffice Control" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
    'DRE' => '<iframe width="100%" height="700px" src="https://www.youtube.com/embed/ek9I0S5aW5I?feature=youtu.be&amp;t=245&amp;suggested=true&amp;" title="" frameborder="" allow="" referrerpolicy="" allowfullscreen=""></iframe>',
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
    <link rel="shortcut icon" href="/gestor-office.png" type="image/x-icon">
    <title>Gestor Office Control</title>
</head>

<body id="body" >


    <?php require_once __DIR__ . '/../../componentes/lateral/lateral.php'?>
    <?php require_once __DIR__ . '/../../componentes/header/header.php' ?>


    
<div class="main" id="container">   


        

<?php if(!empty($titulos)){ ?>
                        <div class="accordion custom-accordion" id="accordionExample">
<?php foreach($titulos as $i => $titulo) { 
    

    ?>
    
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?=$i?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?=$i?>" aria-expanded="false" aria-controls="collapse<?=$i?>">
                <span style="color: #303640; font-size:1.1em; font-weight:500;"> <?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?> </span>
            </button>
        </h2>
        <div id="collapse<?=$i?>" class="accordion-collapse <?php if(!isset($con01) || $con01 != $i ) {?>collapse<?php } else { ?>show<?php } ?>" aria-labelledby="heading<?=$i?>">
            <div class="accordion-body">
                <div class="inner-accordion">
                    <?= $videos[$titulo] ?? '' ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</div>
<?php } ?>
    <!-- Modal -->
    <div class="modal fade" id="modal_titulo" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <?php if(isset($target )&& $target == 'titulo') {
                        $titulo_modal = 'Novo Título';
                        $target = 'titulo';

                    } else if(isset($target) && $target == 'subtitulo') {
                        $titulo_modal = 'Novo Subtítulo';
                        $target = 'subtitulo';
                    }

                     if($acao == 'editar') {
                        if($target == 'titulo') {
                            $conta_modal = Con01::read($con01, $_SESSION['usuario']->id_empresa)[0];
                        $titulo_modal = 'Editar Título: ' .$conta_modal->nome;
                        $target = 'titulo'; 
                        } else if($target == 'subtitulo') {
                            $conta_modal = Con02::read($con02, $_SESSION['usuario']->id_empresa)[0];
                            $titulo_modal = 'Editar Subtítulo: ' .$conta_modal->nome;
                            $target = 'subtitulo';
                        }  
                    }
                     ?>
                    <h5 class="modal-title" id="exampleModalLongTitle"><?= htmlspecialchars($titulo_modal, ENT_QUOTES, 'UTF-8') ?></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post" id="content" action="cadastros_manager.php">
                        <input type="hidden" name="view" value="conta">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="target" value="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?? ''; ?>">
                        <input type="hidden" name="con01id" value="<?php echo htmlspecialchars($con01, ENT_QUOTES, 'UTF-8') ?? ''; ?>">
                        <input type="hidden" name="con02id" value="<?php echo htmlspecialchars($con02, ENT_QUOTES, 'UTF-8') ?? ''; ?>">
                        <input type="hidden" name="acao" value="<?=$acao?>">

                        <div class="input-nome input-form-adm">
                            <!--Nome: -->
                            <label for="nome">Nome:</label>
                            <input type="text" onchange="checar()" name="nome" class="form-control" placeholder="Nome" value="<?php if($acao == 'editar'){ echo $conta_modal->nome ;}?>" required>
                        </div>
                    <?php if(isset($target) && $target == 'titulo'){ ?>
                        <div class="input-nome input-form-adm">
                            <label for="nome">Tipo:</label>
                            <select name="tipo" class="form-select" id="tipo" style="margin-bottom: 1em;">
                                <option <?php if (($acao == 'editar') && $conta_modal->tipo == 'C') {?> selected <?php } ?> value="C">Crédito</option>
                                <option <?php if (($acao == 'editar') && $conta_modal->tipo == 'D') {?> selected <?php } ?> value="D">Débito</option>
                            </select>
                            <div class="d-flex flex-column justify-content-start align-items-start">
                                <label>Operacional:</label>
                                <input name="operacional" type="checkbox" <?php if($acao != 'editar' || $conta_modal->operacional == 1) {?> checked <?php } ?>></input>
                            </div>
                            
                        </div>
                    <?php } ?>


                        <div style="margin-bottom: 3em;" class="footer">
                        <?php if($acao == 'editar'){ ?><button name="acao" value="excluir" style="float: left;" class="btn btn-danger" >Excluir</button> <?php } ?>
                        <button class="btn btn-success" style="border-top-left-radius: 0; border-bottom-left-radius: 0; float:right;">Salvar</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #5856d6; border: #5856d6; border-top-right-radius: 0; border-bottom-right-radius: 0; float:right;">Fechar</button>
                        
                        
                        
                            

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
<?php require_once __DIR__ . '/../../componentes/footer/footer.php' ?> 
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


</script>

<?php if ( isset($acao) && ($acao == 'adicionar' || $acao == 'editar')) 
    
    { ?>
    
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modal_titulo')
            var Modal = new bootstrap.Modal(modalEl);
            Modal.show();
            modalEl.addEventListener('hidden.bs.modal', function () {
                window.location.href = 'contas.php?con01id=<?= $con01 ?>' ;
            });
        });


    </script>
<?php }
; ?>

<?php if (isset($erro) && $erro == 'usado') { ?>
    <script>
        alert('Não é possível editar ou excluir este subtitulo", pois ela está vinculada a um recebimento ou pagamento.');
        window.location.href = 'contas.php?con01id=<?= $con01 ?>';
    </script>
<?php } else if(isset($erro) && $erro == 'usado_sub') {?>
    <script>
        alert('Não é possível editar ou excluir este titulo, pois ela está vinculada a subtitulo.');
        window.location.href = 'contas.php?con01id=<?= $con01 ?>';
    </script>
<?php } ?>

</html>


