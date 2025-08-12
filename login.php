<?php

require_once __DIR__ . '/db/base.php';
require_once __DIR__ . '/db/entities/usuarios.php';
require_once __DIR__ . '/db/entities/empresas.php';
require_once  __DIR__ . '/db/entities/cargo.php';

session_start();

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];



    if ($email && $senha) {
        $usuario = Usuario::read(null, $email); // busca apenas 1 usuário
        

        if ($usuario && password_verify($senha, $usuario[0]->senha)) {

    session_regenerate_id(true);

    $_SESSION['usuario'] = $usuario[0];


    switch ($usuario[0]->cargo) {
        case 1:
            header('Location: admin/index.php');
            break;  
        case 2:
            $empresa = Empresa::read($usuario[0]->id_empresa);

           if ($empresa[0]->status == 1) {
    header('Location: gestor/index.php');
} else {
    $error = "Empresa inativa.";
}
            break;
        case 3:
            header('Location: usuario/index.php');  
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

echo $error;




?>