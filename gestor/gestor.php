<?php
$post_acao = filter_input(INPUT_POST, 'acao');

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $get_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $get_acao = filter_input(INPUT_GET, 'acao');
    $get_nome = filter_input(INPUT_GET, 'nome');
    $get_data_inicial = filter_input(INPUT_GET, 'dataInicial');
    $get_data_final = filter_input(INPUT_GET, 'dataFinal');

    $status_req = filter_input(INPUT_POST, 'status');
    $consultar_req = filter_input(INPUT_POST, 'consultar');
    $processar_req = filter_input(INPUT_POST, 'processar');

    $nome = filter_input(INPUT_POST, 'nome');
    $email = filter_input(INPUT_POST, 'email');
    $status = isset($status_req) ? 1 : 0;
    $consultar = isset($consultar_req) ? 1 : 0;
    $processar = isset($processar_req) ? 1 : 0;

if(isset($post_acao) && $post_acao == 'editar') {

$senha_antiga = Usuario::read($id, null, $_SESSION['usuario']->id_empresa)[0]->senha;
$usuario = new Usuario(
    $id,
    $_SESSION['usuario']->id_empresa,
    $nome,
    $email,
    $senha_antiga,
    $processar,
    $consultar,
    null,
    $status
);

    Usuario::update($usuario);
}

if (isset($post_acao) && $post_acao == 'inserir_cliente') {


    function permissao() {
        // Exemplo: só permite se o usuário logado for admin
        return isset($_SESSION['usuario']) && $_SESSION['usuario']->cargo == Cargo::GESTOR;
    }

    function atribuirCargo($cargo) {
        // Verifica se o cargo é válido
        if (!in_array($cargo, [Cargo::ADMIN, Cargo::GESTOR, Cargo::USUARIO])) {
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
    $processar, // processar
    $consultar, // consultar
    Cargo::USUARIO, // cargo
    $status
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