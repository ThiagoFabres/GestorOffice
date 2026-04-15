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
                            <input class="form-control rounded-0" type="date" name="data" value="<?= (new DateTime())->format('Y-m-d') ?>">
                        </div>
                        <div class="d-flex flex-column w-50">
                            <label>Turno</label>
                            <input class="form-control rounded-0" type="number" name="turno" onkeypress="return /[0-9,]/.test(event.key)"  placeholder="Turno" id="input_turno" value="">
                            
                        </div>
                        <div class="d-flex flex-column w-50">
                            <label>Nome</label>
                            <input class="form-control rounded-0" type="text" name="nome_caixa" placeholder="Nome">
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
                                    <input class="form-control valor" type="number" onkeypress="return /[0-9,]/.test(event.key)"  name="valor[<?= $i ?>]" placeholder="Valor">
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>

                    <button style="float: right;" type="submit" class="btn btn-primary ">Processar</button>
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

// let index = 1;

// $(document).on('change', '.tipo-pagamento, .valor', function () {

//     let linha = $(this).closest('.linha');
//     let tipo = linha.find('.tipo-pagamento').val();
//     let valor = linha.find('.valor').val();

//     // Se os dois campos estiverem preenchidos
//     if (tipo && valor) {

//         // Evita duplicar linha se já tiver próxima
//         if (linha.next('.linha').length === 0) {

//             let novaLinha = `
//                 <div class="linha d-flex flex-row justify-content-evenly gap-3 mb-2">
                    
//                     <div class="d-flex flex-column w-50">
//                         <label>Tipo De Pagamento</label>
//                         <select class="form-select tipo-pagamento" name="tipo_pagamento[${index}]">
//                             <option value="">Selecione</option>
//                             <?php foreach($tipo_pagamento_lista as $tipo_pagamento) { ?>
//                                 <option value="<?=$tipo_pagamento->id?>"><?=$tipo_pagamento->nome?></option>
//                             <?php } ?>
//                         </select>
//                     </div>

//                     <div class="d-flex flex-column w-50">
//                         <label>Valor</label>
//                         <input class="form-control valor" type="number" name="valor[${index}]" placeholder="Valor">
//                     </div>

//                 </div>
//             `;

//             $('#linhas-container').append(novaLinha);
//             index++;
//         }
//     }
// });

// const input = document.getElementById('input_turno');
// input.addEventListener('input', function() {
//   this.value = this.value.replace(/[^0-9]/g, '');
//   if (this.value.length > 1) {
//     this.value = this.value.slice(0, 1);
//   }
// });


</script>




</html>