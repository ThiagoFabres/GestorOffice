<?php

require_once __DIR__ . '/db/base.php';
require_once __DIR__ . '/db/entities/usuarios.php';

$usuarios = Usuario::read();

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $senhaprotegida = password_hash($senha, PASSWORD_DEFAULT);

    foreach ($usuarios as $usuario) {
        if ($usuario->email === $email && password_verify($senha, $usuario->senha)) {
            session_start();
            $_SESSION['usuario'] = $usuario;

            if ($usuario->cargo === 1) {
                header('Location: admin/index.php');
            } else if ($usuario->cargo === 2) {
                header('Location: gerente/index.php');
            } else if ($usuario->cargo === 3) {
                header('Location: dashboard.php');
            }
            exit;
        }
    }

    $error = "Email ou senha inválidos.";
} else {
    $error = "Por favor, preencha todos os campos.";
}

echo $error;

echo 'usuarios:';

    echo "<pre>";
    print_r($usuarios);
    echo "</pre>";



echo 'post:';
if (!empty($_POST)) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}

echo $senhaprotegida;



?>