<?php 
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/banco02.php';
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 1) {
    header('Location: /');
    exit;
}

$target = filter_input(INPUT_POST, 'target', FILTER_SANITIZE_STRING);

if($target == 'vendas') {
    
} 

else if($target == 'bancario') {
    $filtro_empresa = filter_input(INPUT_POST, 'empresa');
    $filtro_conta = filter_input(INPUT_POST, 'conta');
    $filtro_data_inicial = filter_input(INPUT_POST, 'data_inicial');
    $filtro_data_final = filter_input(INPUT_POST, 'data_final');
    if($filtro_data_final === '') $filtro_data_final = null;
    if($filtro_data_inicial === '') $filtro_data_inicial = null;
    $ban02_lista = Ban02::read(
        id_empresa: $filtro_empresa,
        filtro_conta: $filtro_conta,
        filtro_data_inicial: $filtro_data_inicial ?? null,
        filtro_data_final: $filtro_data_final ?? null
    );
    $ban02_imp_lista = Ban02Imp::read(
        id_empresa: $filtro_empresa,
        id_ban01: $filtro_conta,
        data_inicial: $filtro_data_inicial ?? null,
        data_final: $filtro_data_final ?? null
    );
    if(empty($ban02_lista) || empty($ban02_imp_lista)) {
        header('Location: bancario.php?erro=1');
        exit;
    }
    
    foreach($ban02_lista as $ban02) {
        Ban02::delete($ban02->id);
    }
    foreach($ban02_imp_lista as $ban02_imp) {
        Ban02Imp::delete($ban02_imp->id);
    }
    header('Location: bancario.php?sucesso=1&empresa=' . $filtro_empresa . '&conta=' . $filtro_conta . '&data_inicial=' . $filtro_data_inicial . '&data_final=' . $filtro_data_final);
    exit;
} 

else {
    header('Location: /');
    exit;
}
?>