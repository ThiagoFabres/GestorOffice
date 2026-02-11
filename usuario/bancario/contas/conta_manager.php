<?php

require_once __DIR__ . '/../../../db/entities/usuarios.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
require_once __DIR__ . '/../../../db/entities/banco01.php';


echo '<pre>';
print_r($_POST); 
echo '</pre>';

$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_NUMBER_INT);
$agencia = filter_input(INPUT_POST, 'agencia', FILTER_SANITIZE_NUMBER_INT);
$numero_conta = filter_input(INPUT_POST, 'numero_conta', FILTER_SANITIZE_STRING);
$acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_STRING);
$data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
$valor = filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_STRING);
$valor = str_replace('.', '', $valor);
$valor = str_replace(',', '.', $valor);
if($acao == 'adicionar') {
    $nova_conta = new Ban01(
        null,
        $_SESSION['usuario']->id_empresa,
        $codigo,
        $agencia,
        $numero_conta,
        $nome,
        $valor,
        $data
    );
    $resultado = Ban01::create($nova_conta);

    if ($resultado) {
        header('Location: /usuario/bancario/contas/conta.php?sucesso=sucesso.');
    } else {
        header('Location: /usuario/bancario/contas/conta.php?erro=erro');
    }
}
else if($acao == 'editar') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $conta_existente = new Ban01(
        $id,
        $_SESSION['usuario']->id_empresa,
        $codigo,
        $agencia,
        $numero_conta,
        $nome,
        $valor,
        $data
    );

    $resultado = Ban01::update($conta_existente);
    if ($resultado) {
        header('Location: /usuario/bancario/contas/conta.php?sucesso=sucesso.');
    } else {
        header('Location: /usuario/bancario/contas/conta.php?erro=erro');
    }
} else {
    header('Location: /usuario/bancario/contas.php?erro=acao_invalida');
}


?>