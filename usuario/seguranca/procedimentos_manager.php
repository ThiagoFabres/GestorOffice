<?php
require_once __DIR__ . '/../../db/entities/seguranca/procedimento.php';
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();

$texto = filter_input(INPUT_POST, 'texto_procedimento');
$empresa_id = $_SESSION['usuario']->id_empresa;
$texto_empresa = Procedimento::read($empresa_id)[0] ?? null;

if(($texto == '' || $texto == null) && $texto_empresa !== null) {
    header('Location: procedimentos.php?erro=campos_vazios');
    exit;
}

if(($texto == '' || $texto == null) && $texto_empresa === null) {
    Procedimento::delete($texto_empresa->id);
    header('Location: procedimentos.php');
    exit;
}
if($texto != '' && $texto != null){
    if($texto_empresa === null) {
        $procedimento = new Procedimento(
            id: null,
            id_empresa: $empresa_id,
            texto: $texto
        );
        Procedimento::create($procedimento);
        header('Location: procedimentos.php');
        exit;
    } else {
        $texto_empresa->texto = $texto;
        Procedimento::update($texto_empresa);
        header('Location: procedimentos.php');
        exit;
    }
}