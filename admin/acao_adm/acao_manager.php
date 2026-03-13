<?php 
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/banco02.php';
require_once __DIR__ . '/../../db/entities/recebimentos.php';
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 1) {
    header('Location: /');
    exit;
}

$target = filter_input(INPUT_POST, 'target', FILTER_SANITIZE_STRING);

if($target == 'vendas') {
    $filtro_empresa  = filter_input(INPUT_POST, 'empresa');
    $filtro_cadastro  = filter_input(INPUT_POST, 'cadastro') ?? null;
    $filtro_data_inicial  = filter_input(INPUT_POST, 'data_inicial') ?? null;
    $filtro_data_final  = filter_input(INPUT_POST, 'data_final') ?? null;
    $filtro_cadastro  = filter_input(INPUT_POST, 'cadastro') ?? null;
    $filtro_titulo  = filter_input(INPUT_POST, 'titulo') ?? null;
    $filtro_subtitulo  = filter_input(INPUT_POST, 'subtitulo') ?? null;
    $filtro_custos  = filter_input(INPUT_POST, 'custos') ?? null;
    $filtro_vendas  = filter_input(INPUT_POST, 'vendas') ? true : null;
    if($filtro_data_final === '') $filtro_data_final = null;
    if($filtro_data_inicial === '') $filtro_data_inicial = null;

    if($filtro_empresa == null || !isset($filtro_empresa)) {
        header('Location: vendas.php?erro=empresa');
        exit;
    }
    $rec01_lista = Rec01::read(
        id_empresa: $filtro_empresa,
        id_cadastro: $filtro_cadastro,
        filtro_data_inicial: $filtro_data_inicial,
        filtro_data_final: $filtro_data_final,
        con01:$filtro_titulo,
        con02:$filtro_subtitulo,
        filtro_custos:$filtro_custos,
        read_vendas:true,
    );

        $rec03_lista = Rec03::read(
            id_empresa:$filtro_empresa,
            data_inicial:$filtro_data_inicial,
            data_final:$filtro_data_final,
        );
    

    foreach($rec01_lista as $rec) {
        Rec02::deletebyrec01($rec->id);
        Rec01::delete($rec->id);
    }

    foreach($rec03_lista as $rec) {
        Rec03::delete($rec->id);
    }


    header('Location: vendas.php?sucesso=1&empresa=' . $filtro_empresa . '&cadastro=' . $filtro_cadastro . '&data_inicial=' . $filtro_data_inicial . '&data_final=' . $filtro_data_final . '&custos=' . $custos . 'filtro_titulo=' . $filtro_titulo . 'filtro_subtitulo=' . $filtro_subtitulo);
    exit;
} 

else if($target == 'bancario') {
    $filtro_empresa = filter_input(INPUT_POST, 'empresa');
    $filtro_conta = filter_input(INPUT_POST, 'conta');
    $filtro_data_inicial = filter_input(INPUT_POST, 'data_inicial');
    $filtro_data_final = filter_input(INPUT_POST, 'data_final');
    if($filtro_data_final === '') $filtro_data_final = null;
    if($filtro_data_inicial === '') $filtro_data_inicial = null;

    if($filtro_empresa == null || !isset($filtro_empresa)) {
        header('Location: bancario.php?erro=empresa');
        exit;
    }
    
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