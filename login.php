<?php

require_once __DIR__ . '/db/base.php';
require_once __DIR__ . '/db/entities/usuarios.php';
require_once __DIR__ . '/db/entities/empresas.php';
require_once  __DIR__ . '/db/entities/cargo.php';

session_start();


if(!isset($_POST['acao']) && !isset($_GET['acao'])) {

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];



    if ($email && $senha) {
        $usuario = Usuario::read(null, $email);
        if(!$usuario) {
            header('Location: index.php?erro=credenciais');
            exit;
        }
        

        if($usuario[0]->status == 0 && $usuario[0]->cargo == 3) {
            header('Location: /index.php?erro=usuario_inativo');
            exit;
        }

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
    exit; 
    } else {
        header('Location: /index.php?erro=empresa_inativa');
    }
            break;
        case 3:
            $empresa = Empresa::read($usuario[0]->id_empresa);
           if ($empresa[0]->status == 1) {
            header('Location: usuario/index.php');
           } else {
        header('Location: /index.php?erro=empresa_inativa');
    }
            break;
        case 4:
            $empresa = Empresa::read($usuario[0]->id_empresa);
           if ($empresa[0]->status == 1) {
            header('Location: /index.php?erro=usuario_seguranca');
           } else {
            header('Location: /index.php?erro=empresa_inativa');
        }
                break;

    }
    exit;

} else {
            header('Location: index.php?erro=credenciais');
            exit;
        }
    } else {
        $error = "Dados inválidos.";
        header('Location: index.php?erro=dados');
        exit;
    }
} else {
    $error = "Por favor, preencha todos os campos.";
    header('Location: index.php?erro=campos');
    exit; 
}


} else if (isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    if(isset($_POST['nova_senha']) && isset($_POST['senha_confirmar'])) {

            if($_POST['nova_senha'] !== $_POST['senha_confirmar']) {
                echo "As senhas não coincidem.";
                exit;
            } 
            } else {
                echo "As senhas não.";
            }

    if (isset($_POST['email']) && isset($_POST['senha']) && isset($_POST['nova_senha'])) {
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $nova_senha = $_POST['nova_senha'];

        if ($email && $senha && $nova_senha) {
            $usuario = Usuario::read(null, $email);

            if ($usuario && password_verify($senha, $usuario[0]->senha)) {
                $usuario[0]->senha = $nova_senha;

                Usuario::updateSenha($usuario[0]);
                $_SESSION['usuario'] = $usuario[0];
                $success = "Senha atualizada com sucesso.";
                header('Location: index.php?sucesso=1');
                exit;

                //     switch ($usuario[0]->cargo) {
                //     case 1:
                //         header('Location: admin/index.php');
                //         break;  
                //     case 2:
                //         $empresa = Empresa::read($usuario[0]->id_empresa);

                //     if ($empresa[0]->status == 1) {
                //         header('Location: gestor/index.php');
                //     } else {
                //         $error = "Empresa inativa.";
                //     }
                //         break;
                    
                //     case 3:
                //         $empresa = Empresa::read($usuario[0]->id_empresa);

                //     if ($empresa[0]->status == 1 && $usuario[0]->status == 1) {
                //         header('Location: usuario/index.php');  
                //     } else {
                //         $error = "Empresa ou usuario inativo.";
                //     }
                //         break;

                // }
                // exit;
                

            } else {
                $error = "Email ou senha inválidos.";
            }
        } else {
            $error = "Por favor, preencha todos os campos.";
        }
    } else {
        $error = "Dados inválidos.";
    }

    echo isset($success) ? $success : $error;
} else if(isset($_GET['acao']) && $_GET['acao'] == 'autenticacao_insta') {
        
        $get_target = filter_input(INPUT_GET, 'target');
        $get_url = filter_input(INPUT_GET, 'url_atual');

        $empresa_target = Empresa::read(id:$get_target)[0] ?? null;

        $empresa_session = Empresa::read(id:$_SESSION['usuario']->id_empresa)[0] ?? null;
        if($empresa_target === null || $empresa_session === null) {
            header('Location: index.php?erro=empresa_nao_encontrada');
            exit;
        }

        if($empresa_target->cnpj_principal != $empresa_session->cnpj_principal) {
            header('Location: index.php?erro=sem_permissao_cnpj');
            exit;
        }

        $usuario_principal_target = Usuario::read(idempresa:$empresa_target->id, principal:true, cargo:3)[0] ?? Usuario::read(idempresa:$empresa_target->id, cargo:3)[0] ?? null;
        

        if($usuario_principal_target === null) {
            header('Location: index.php?erro=sem_usuario');
            exit;
        }
        
        $_SESSION['usuario'] = $usuario_principal_target;
        
        

        if (!$get_url || strpos($get_url, '/') !== 0) {
            $get_url = '/usuario/index.php';
        }
        

        header("Location: $get_url");
        exit;

    }

    
header('Location: index.php');
exit;
?>