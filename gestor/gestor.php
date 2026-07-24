<?php

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $get_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $get_acao = filter_input(INPUT_GET, 'acao');
    $get_nome = filter_input(INPUT_GET, 'nome');
    $get_data_inicial = filter_input(INPUT_GET, 'dataInicial');
    $get_data_final = filter_input(INPUT_GET, 'dataFinal');

    $status_req = filter_input(INPUT_POST, 'status');
    $consultar_req = filter_input(INPUT_POST, 'consultar');
    $processar_req = filter_input(INPUT_POST, 'processar');
    $seguranca_req = filter_input(INPUT_POST, 'seguranca');
    $principal_req = filter_input(INPUT_POST, 'principal');

    $permissao_cartao_req = isset($_POST['permissao_cartao']) ? 1 : 0;
    $permissao_seguranca_req = isset($_POST['permissao_seguranca']) ? 1 : 0;
    $permissao_financeiro_req = isset($_POST['permissao_financeiro']) ? 1 : 0;
    $permissao_bancario_req = isset($_POST['permissao_bancario']) ? 1 : 0;
    $permissao_operacional_req = isset($_POST['permissao_operacional']) ? 1 : 0;
    $permissao_inicio_req = isset($_POST['permissao_inicio']) ? 1 : 0;
    $post_acao = filter_input(INPUT_POST, 'acao');

    $nome = filter_input(INPUT_POST, 'nome');
    $email = filter_input(INPUT_POST, 'email');
    $status = isset($status_req) ? 1 : 0;
    $consultar = isset($consultar_req) ? 1 : 0;
    $processar = isset($processar_req) ? 1 : 0;
    $seguranca = isset($seguranca_req) ? 1 : 0;
    $principal = isset($principal_req) ? 1 : 0;

    
    

$usuario_principal = Usuario::read(idempresa:$_SESSION['usuario']->id_empresa, principal:true)[0] ?? false;

if(isset($post_acao) && $post_acao == 'editar') {
$usuario_antigo = Usuario::read($id, null, $_SESSION['usuario']->id_empresa)[0] ?? null;
$senha_antiga = $usuario_antigo->senha;

    if($principal == 1 && $usuario_antigo->principal === 0 && $usuario_principal) {
        header('Location: index.php?erro=principal_extra');
        exit;
    }

$usuario = new Usuario(
    $id,
    $_SESSION['usuario']->id_empresa,
    $nome,
    $email,
    $senha_antiga,
    $seguranca ? 0 : $processar,
    $seguranca ? 0 : $consultar,
    $seguranca ? Cargo::SEGURANCA : Cargo::USUARIO,
    $status,
    $permissao_cartao_req,
    $permissao_seguranca_req,
    $permissao_financeiro_req,
    $permissao_bancario_req,
    $permissao_operacional_req,
    $permissao_inicio_req,
    $principal
);

    Usuario::update($usuario);
}

if (isset($post_acao) && $post_acao == 'adicionar') {

    if($principal == 1 && $usuario_principal) {
        header('Location: index.php?erro=principal_extra');
        exit;
    }

    function permissao() {
        // Exemplo: só permite se o usuário logado for admin
        return isset($_SESSION['usuario']) && $_SESSION['usuario']->cargo == Cargo::GESTOR;
    }

    function atribuirCargo($cargo) {
        // Verifica se o cargo é válido
        if (!in_array($cargo, [Cargo::ADMIN, Cargo::GESTOR, Cargo::USUARIO, Cargo::SEGURANCA])) {
            throw new Exception('Cargo inválido.');
        }
        // Se o cargo a ser atribuído for ADMIN, a pessoa precisa ter permissão para isso
        if ($cargo == Cargo::GESTOR && !permissao()) {
            throw new Exception('Você não tem permissão para atribuir o cargo de GESTOR.');
        }
        return true;
    }



// Exemplo de criação de usuário com validação de cargo
$usuario = new Usuario(
    null, // id_usuario
    $_SESSION['usuario']->id_empresa, // id_empresa
    $nome,
    $email,
    null,
    $seguranca ? 0 : $processar, // processar
    $seguranca ? 0 : $consultar, // consultar
    $seguranca ? Cargo::SEGURANCA : Cargo::USUARIO, // cargo
    $status,
    $permissao_cartao_req,
    $permissao_seguranca_req,
    $permissao_financeiro_req,
    $permissao_bancario_req,
    $permissao_operacional_req,
    $permissao_inicio_req,
    $principal,
);


// Atribuindo cargo
try {
    atribuirCargo($usuario->cargo);
    // O cargo foi atribuído com sucesso
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}


    if(!Usuario::read(null,$email)) {
        if(Usuario::create($usuario)) {
            header('Location: index.php');
    exit;
        };
        
    } else {
        header('Location: index.php?erro=usado');
    }
    

    
} else {
    $error = "Dados inválidos.";

}
?>