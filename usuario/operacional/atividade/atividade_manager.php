<?php
require_once __DIR__ . '/../../../db/entities/ativ01.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

date_default_timezone_set('America/Sao_Paulo');

$data_atual = date('Y-m-d');
$hora_atual = date('H:i:s');

if($_POST['localizacao'] == '') {
    header('Location: atividade.php?erro=localizacao');
    exit;
}

if($_POST['nome'] == '') {
    header('Location: atividade.php?erro=nome');
    exit;
}

if(Ativ01::read(id_empresa: $_SESSION['usuario']->id_empresa, data: $data_atual)) {
    header('Location: atividade.php?erro=data');
    exit;
} else {
$ativ01 = new Ativ01(
    id:null,
    id_empresa:$_SESSION['usuario']->id_empresa,
    data:$data_atual,
    hora:$hora_atual,
    nome:$_POST['nome'],
    localizacao:$_POST['localizacao']
    );

Ativ01::create($ativ01);
header('Location: atividade.php?status=sucesso');
}