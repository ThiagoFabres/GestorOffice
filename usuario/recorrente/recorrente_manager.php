<?php

require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/contas.php';
require_once __DIR__ . '/../../db/entities/cadastro.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../db/entities/pagar.php';
require_once __DIR__ . '/../../db/entities/pagamento.php';
require_once __DIR__ . '/../../db/entities/centrocustos.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;

}
require_once __DIR__ . '/../../db/buscar_documento_pag.php';
require_once __DIR__ . '/../../db/buscar_documento_rec.php';

$view = filter_input(INPUT_POST, 'view');
$documento_inicial = filter_input(INPUT_POST, 'documento_inicial');
$cadastro = filter_input(INPUT_POST, 'cadastro');
$titulo = filter_input(INPUT_POST, 'titulo');
$subtitulo = filter_input(INPUT_POST, 'subtitulo');
$custo = filter_input(INPUT_POST, 'custos');
$n_lanc = filter_input(INPUT_POST, 'n_lanc');
$data_venc = filter_input(INPUT_POST, 'data_venc');
$valor = filter_input(INPUT_POST, 'valor');
$valor = str_replace('.', '', $valor);
$valor = str_replace(',', '.', $valor);

$descricao = filter_input(INPUT_POST, 'descricao') ?? '';


if($view == 'receber') {
    $documento_inicial = buscarDocumentoRec();
    if(Rec02::read(id_empresa:$_SESSION['usuario']->id_empresa, filtro_documento:$documento_inicial)) {
        header('Location:receber.php');
        exit;
    }
}
if($view == 'pagar') {
    $documento_inicial = buscarDocumentoPag();
    if(Pag02::read(id_empresa:$_SESSION['usuario']->id_empresa, filtro_documento:$documento_inicial)) {
        header('Location:pagar.php');
        exit;
    }
}
$lancamentos = [];
$parcelas = [];
$data = date_create($data_venc);

for($i = 1, $documento = $documento_inicial ;  $i < $n_lanc + 1; $i++, $documento++, ($data->modify('+1 month')) ) {
    $lancamento = new Rec01(
            null,
            $_SESSION['usuario']->id_empresa,
            $cadastro,
            $titulo,
            $subtitulo,
            $documento,
            $descricao,
            $valor,
            1,
            $data->format('Y-m-d'),
            $_SESSION['usuario']->id_usuario,
            $custo,
            null
        );
        $lancamentos[] = $lancamento;
        if($view == 'receber') {
            if(Rec01::create($lancamento)) {    
                $lancamento01 = Rec01::read(id_empresa:$_SESSION['usuario']->id_empresa, documento:$documento)[0];
            } else {
                foreach($lancamentos as $lancamento) {
                    Rec01::delete($lancamento->id);
                }
                header('Location:receber.php');
                exit;
            }
        } else if($view == 'pagar') {
             if(Pag01::create($lancamento)) {    
                $lancamento01 = Pag01::read(id_empresa:$_SESSION['usuario']->id_empresa, documento:$documento)[0];
            } else {
                foreach($lancamentos as $lancamento) {
                    Pag01::delete($lancamento->id);
                }
                header('Location:pagar.php');
                exit;
            }
        } else {
            
            header('Location: /usuario/index.php');
            exit;
        }
    
    if($view == 'receber'){
        $parcela = new Rec02(
        null,
        $_SESSION['usuario']->id_empresa,
        $lancamento01->id,
        $valor,
        1,
        $data->format('Y-m-d'),
        0,
        null,
        null,
        null
    );
        Rec02::create($parcela);
    } 
    else if($view == 'pagar') {
        $parcela = new Pag02(
        null,
        $_SESSION['usuario']->id_empresa,
        $lancamento01->id,
        $valor,
        1,
        $data->format('Y-m-d'),
        0,
        null,
        null,
        null
    );
        Pag02::create($parcela);
    }
   
}
if($view == 'receber') {
    header('Location: /usuario/receber.php');
    exit;
} else if($view == 'pagar') {
    header('Location: /usuario/pagar.php');
    exit;
} else {
    header('Location: /usuario/index.php');
    exit;
}
?>