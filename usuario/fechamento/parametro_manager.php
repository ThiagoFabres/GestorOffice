<?php

require_once __DIR__ . '/../../db/entities/fecha01.php';
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
if(
    (filter_input(INPUT_POST, 'id_cadastro') == null || filter_input(INPUT_POST, 'id_cadastro') == 'Selecione') ||
    (filter_input(INPUT_POST, 'id_custos') == null || filter_input(INPUT_POST, 'id_custos') == 'Selecione') ||
    (filter_input(INPUT_POST, 'id_titulo') == null || filter_input(INPUT_POST, 'id_titulo') == 'Selecione') ||
    (filter_input(INPUT_POST, 'id_subtitulo') == null || filter_input(INPUT_POST, 'id_subtitulo') == 'Selecione')
) {
    header('Location: parametro.php?erro=campos_obrigatorios');
    exit;
}
if(Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa)) {
    $acao = 'atualizar';
} else {
    $acao = 'adicionar';
}

if($acao == 'adicionar') {
    $fecha = new Fecha01(
        id_empresa: $_SESSION['usuario']->id_empresa,
        id_cadastro: filter_input(INPUT_POST, 'id_cadastro'),
        id_custos: filter_input(INPUT_POST, 'id_custos'),
        id_titulo: filter_input(INPUT_POST, 'id_titulo'),
        id_subtitulo: filter_input(INPUT_POST, 'id_subtitulo')
    );

    Fecha01::create($fecha);
} else if($acao == 'atualizar') {
    $fecha01_antigo = Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa)[0];
    $valor_fecha = filter_input(INPUT_POST, 'valor_fecha');
    $fecha = new Fecha01(
        id: $fecha01_antigo->id,
        id_empresa: $_SESSION['usuario']->id_empresa,
        id_cadastro: filter_input(INPUT_POST, 'id_cadastro'),
        id_custos: filter_input(INPUT_POST, 'id_custos'),
        id_titulo: filter_input(INPUT_POST, 'id_titulo'),
        id_subtitulo: filter_input(INPUT_POST, 'id_subtitulo')
    );
    Fecha01::update($fecha);
}

header('Location: parametro.php');
exit;