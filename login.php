<?php

require_once __DIR__ . '/db/base.php';
require_once __DIR__ . '/db/entities/usuarios.php';

session_start();

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];



    if ($email && $senha) {
        $usuario = Usuario::read(null, $email); // busca apenas 1 usuário

        if ($usuario && password_verify($senha, $usuario->senha)) {

    session_regenerate_id(true);

    $_SESSION['usuario'] = $usuario;

    switch ($usuario->cargo) {
        case 1:
            header('Location: admin/index.php');
            break;
        case 2:
            header('Location: gestor/index.php');
            break;
        case 3:
            header('Location: dashboard.php');
            break;
        default:
            header('Location: index.php');
            break;
    }
    exit;
} else {
            $error = "Email ou senha inválidos.";
        }
    } else {
        $error = "Dados inválidos.";
    }
} else {
    $error = "Por favor, preencha todos os campos.";
}





?>