<?php
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/../db/entities/logo.php';

function permissao() {
        // Exemplo: só permite se o usuário logado for admin
        return isset($_SESSION['usuario']) && $_SESSION['usuario']->cargo == Cargo::ADMIN;
    }

    function atribuirCargo($cargo) {
        // Verifica se o cargo é válido
        if (!in_array($cargo, [Cargo::ADMIN, Cargo::GESTOR, Cargo::USUARIO])) {
            throw new Exception('Cargo inválido.');
        }
        // Se o cargo a ser atribuído for ADMIN, a pessoa precisa ter permissão para isso
        if ($cargo == Cargo::ADMIN && !permissao()) {
            throw new Exception('Você não tem permissão para atribuir o cargo de ADMIN.');
        }
        return true;
    }


if (isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $fantasia = $_POST['fantasia'];
    $cnpj = $_POST['cnpj'];
    $cnpj_principal = $_POST['cnpj_principal'] ?? $cnpj;
    $cpf = $_POST['cpf'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $celular = $_POST['celular'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $data_r = $_POST['data_r'];
    $status = isset($_POST['status']) ? 1 : 0;
    $permissao_cartao = isset($_POST['permissao_cartao']) ? 1 : 0;
    $permissao_seguranca = isset($_POST['permissao_seguranca']) ? 1 : 0;
    $permissao_financeiro = isset($_POST['permissao_financeiro']) ? 1 : 0;
    $permissao_bancario = isset($_POST['permissao_bancario']) ? 1 : 0;
    $permissao_operacional = isset($_POST['permissao_operacional']) ? 1 : 0;
    $permissao_inicio = isset($_POST['permissao_inicio']) ? 1 : 0;
    $inicio_atividade = $_POST['ativ_inicio'] == '' ? null : $_POST['ativ_inicio'] ?? null;
    $tolerancia_atividade = $_POST['tolerancia'] == '' ? null : $_POST['tolerancia'] ?? null;
    $celular1_atividade = $_POST['cel1'] ?? null;
    $celular2_atividade = $_POST['cel2'] ?? null;
    $parceiro = $_POST['parceiro'] ?? null;


    if(strlen($estado) == 2){$estado = mb_strtoupper($estado);};

    $empresa = new Empresa(
        $id, // id
        $nome,
        $fantasia,
        $endereco,
        $bairro,
        $cidade,
        $estado,
        $cpf,
        $cnpj,
        $email,
        $celular,
        $telefone,
        $status,
        $data_r,
        $cep,
        $cnpj_principal,
        $permissao_cartao,
        $permissao_seguranca,
        $permissao_financeiro,
        $permissao_bancario,
        $permissao_operacional,
        $permissao_inicio,
        ativ_inicio: $inicio_atividade,
        tolerancia: $tolerancia_atividade,
        celular1_atividade: $celular1_atividade,
        celular2_atividade: $celular2_atividade,
        parceiro: $parceiro,
);
$gestor_atual = Usuario::read(idempresa:$id, cargo:Cargo::GESTOR)[0];

$gestor = new Usuario(
    $gestor_atual->id, // id_usuario
    $id, // id_empresa
    $nome,
    $email,
    null,
    0, // processar
    0, // consultar
    Cargo::GESTOR
);


// Atribuindo cargo
try {
    atribuirCargo($gestor->cargo);
    // O cargo foi atribuído com sucesso
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}

Empresa::update($empresa);
Usuario::update($gestor);

// Processar logo se arquivo foi enviado
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $_FILES['logo']['size'] > 0) {
    $foto_blob = file_get_contents($_FILES['logo']['tmp_name']);
    
    if ($foto_blob && strlen($foto_blob) > 0) {
        $logos_existentes = Logo::read(null, $id);
        if ($logos_existentes) {
            $logo = $logos_existentes[0];
            $logo->foto = $foto_blob;
            Logo::update($logo);
        } else {
            $logo = new Logo(null, $id, $foto_blob);
            Logo::create($logo);
        }
    }
}

header('Location: index.php');
exit;




}

if (isset($_POST['acao']) && $_POST['acao'] == 'adicionar') {

    $nome = $_POST['nome'];
    $fantasia = $_POST['fantasia'];
    $cnpj = $_POST['cnpj'];
    $cnpj_principal = $_POST['cnpj_principal'] ?? $cnpj;
    $cpf = $_POST['cpf'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $celular = $_POST['celular'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $status = isset($_POST['status']) ? 1 : 0;
    $permissao_cartao = isset($_POST['permissao_cartao']) ? 1 : 0;
    $permissao_seguranca = isset($_POST['permissao_seguranca']) ? 1 : 0;
    $permissao_financeiro = isset($_POST['permissao_financeiro']) ? 1 : 0;
    $permissao_bancario = isset($_POST['permissao_bancario']) ? 1 : 0;
    $permissao_operacional = isset($_POST['permissao_operacional']) ? 1 : 0;
    $permissao_inicio = isset($_POST['permissao_inicio']) ? 1 : 0;
    $inicio_atividade = $_POST['ativ_inicio'] ?? null;
    $tolerancia_atividade = $_POST['tolerancia'] ?? null;
    $celular1_atividade = $_POST['cel1'] ?? null;
    $celular2_atividade = $_POST['cel2'] ?? null;
    $parceiro = $_POST['parceiro'] ?? null;

    $empresa = new Empresa(
        null, // id
        $nome,
        $fantasia,
        $endereco,
        $bairro,
        $cidade,
        $estado,
        $cpf,
        $cnpj,
        $email,
        $celular,
        $telefone,
        $status,
        date('Y-m-d H:i:s'), // data_r
        $cep,
        $cnpj_principal,
        $permissao_cartao,
        $permissao_seguranca,
        $permissao_financeiro,
        $permissao_bancario,
        $permissao_operacional,
        $permissao_inicio,
        ativ_inicio: $inicio_atividade,
        tolerancia: $tolerancia_atividade,
        celular1_atividade: $celular1_atividade,
        celular2_atividade: $celular2_atividade,
        parceiro: $parceiro,
    );
$gestor = new Usuario(
    null, // id_usuario
    null, // id_empresa
    $nome,
    $email,
    '123456',
    0, // processar
    0, // consultar
    Cargo::GESTOR,
    $status
);

// Atribuindo cargo
try {
    atribuirCargo($gestor->cargo);
    // O cargo foi atribuído com sucesso
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}

if(!Empresa::read(null, $email) && !Usuario::read(null, $email)) { 

    Empresa::create($empresa);
    $empresacriada = Empresa::read(null, $email);
    $empresaid = $empresacriada[0];
    $gestor->id_empresa = $empresaid->id;
    Usuario::create($gestor);
    
    // Processar logo se arquivo foi enviado
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $_FILES['logo']['size'] > 0) {
        $foto_blob = file_get_contents($_FILES['logo']['tmp_name']);
        if ($foto_blob && strlen($foto_blob) > 0) {
            $logo = new Logo(null, $empresaid->id, $foto_blob);
            Logo::create($logo);
        }
    }
    
    header('Location: index.php');
    exit;
} else {
    header('Location: index.php?erro=usado');
    exit;
}

} else {
    $error = "Dados inválidos.";

}
?>