<?php

require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
session_start();
$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->permissao_bancario != 1 || $empresa_usuario_obj->permissao_bancario != 1) {
    header('Location: /');
    exit;
}

require_once '../../../db/entities/palavra_chave.php';
require_once '../../../db/entities/contas.php';

$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_STRING);
$titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
$subtitulo = filter_input(INPUT_POST, 'subtitulo', FILTER_SANITIZE_STRING);
if($acao == 'adicionar') {
    $titulo_tipo = Con01::read($titulo)[0]->tipo;
    $palavra = new Pal01(
        null,
        $nome,
        $_SESSION['usuario']->id_empresa,
        $titulo,
        $subtitulo,
        $titulo_tipo
    );
    Pal01::create($palavra);
    header('Location: palavra_chave.php?status=sucesso1');
    exit;
} else if($acao == 'editar') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $titulo_tipo = Con01::read($titulo)[0]->tipo;
    $palavra = new Pal01(
        $id,
        $nome,
        $_SESSION['usuario']->id_empresa,
        $titulo,
        $subtitulo,
        $titulo_tipo
    );
     if(Pal01::update($palavra)) {
        header('Location: palavra_chave.php?status=sucesso1');
        exit;
     } else {
        header('Location: palavra_chave.php?status=erro');
        exit;
     }
} else if($acao == 'excluir') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    Pal01::delete($id);
    header('Location:palavra_chave.php');
    exit;
}
else {
    header('Location: palavra_chave.php?status=acao_invalida');
    exit;
}

?>