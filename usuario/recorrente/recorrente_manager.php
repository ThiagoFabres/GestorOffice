<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;

}

$view = filter_input(INPUT_POST, 'view');
$documento_inicial = filter_input(INPUT_POST, 'documento_inicial');
$cadastro = filter_input(INPUT_POST, 'cadastro');
$titulo = filter_input(INPUT_POST, 'titulo');
$subtitulo = filter_input(INPUT_POST, 'subtitulo');
$custo = filter_input(INPUT_POST, 'custos');
$n_lanc = filter_input(INPUT_POST, 'n_lanc');
$data_venc = filter_input(INPUT_POST, 'data_venc');
$valor = filter_input(INPUT_POST, 'valor');
if($view == 'receber') {
    if(Rec02::read(id_empresa:$_SESSION['usuario']->id_empresa, filtro_documento:$documento_inicial)) {
        header('Location:receber.php');
        exit;
    }
}
if($view == 'pagar') {
    if(Pag02::read(id_empresa:$_SESSION['usuario']->id_empresa, filtro_documento:$documento_inicial)) {
        header('Location:pagar.php');
        exit;
    }
}
$lancamentos = [];
$parcelas = [];
$data = date_create($data_venc);

// for($i = 1, $documento = $documento_inicial ;  $i < $n_lanc + 1; $i++, $documento++, ($data->modify('+1 month')) ) {
//     $lancamento = new Rec01(
//             null,
//             $_SESSION['usuario']->id_empresa,
//             $cadastro,
//             $titulo,
//             $subtitulo,
//             $documento,
//             '',
//             $valor,
//             1,
//             $data->format('Y-m-d'),
//             $_SESSION['usuario']->id_usuario,
//             $custo,
//             null
//         );
//         $lancamentos[] = $lancamento;
//         if($view == 'receber') {
//             Rec01::create($lancamento);
//         } else if($view == 'pagar') {
//             Pag01::create($lancamento);
//         } else {
//             header('Location: /usuario/index.php');
//         }
//     $parcela = new Rec02(
//         null
//     );
// }
echo '<pre>';
print_r($lancamentos);
echo '</pre>';

?>