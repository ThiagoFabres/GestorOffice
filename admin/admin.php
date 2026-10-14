<?php
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/../db/entities/logo.php';
require_once __DIR__ . '/../db/entities/empresas.php';
require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/centrocustos.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
require_once __DIR__ . '/../db/entities/contas.php';

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
    $permissao_notificacao = isset($_POST['permissao_notificacao']) ? 1 : 0;
    $royalty = $_POST['royalty'] == '' ? null : floatval(str_replace(',', '.', $_POST['royalty'])) ?? null;
    if($royalty == '0,00') {
        $royalty = null;
    }
    $taxa_marketing = $_POST['taxa_marketing'] == '' ? null : floatval(str_replace(',', '.', $_POST['taxa_marketing'])) ?? null;
    if($taxa_marketing == '0,00') {
        $taxa_marketing = null;
    }

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
        permissao_notificacao: $permissao_notificacao,
        royalty: $royalty,
        taxa_marketing: $taxa_marketing
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
    $tolerancia_atividade = $_POST['tolerancia'] == '' ? null : $_POST['tolerancia'] ?? null;
    $celular1_atividade = $_POST['cel1'] ?? null;
    $celular2_atividade = $_POST['cel2'] ?? null;
    $parceiro = $_POST['parceiro'] ?? null;
    $royalty = $_POST['royalty'] == '' ? null : floatval(str_replace(',', '.', $_POST['royalty'])) ?? null;
    if($royalty == '0,00') {
        $royalty = null;
    }
    $taxa_marketing = $_POST['taxa_marketing'] == '' ? null : floatval(str_replace(',', '.', $_POST['taxa_marketing'])) ?? null;
    if($taxa_marketing == '0,00') {
        $taxa_marketing = null;
    }

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
        royalty: $royalty,
        taxa_marketing: $taxa_marketing
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



try {
    atribuirCargo($gestor->cargo);
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}

if(!Empresa::read(null, $email) && !Usuario::read(null, $email)) { 

    Empresa::create($empresa);
    $empresa_criada = Empresa::read(null, $email);
    $empresa_id = $empresa_criada[0];
    $gestor->id_empresa = $empresa_id->id;
    Usuario::create($gestor);

    //cadastro padrão

$cliente_fornecedor = new Cadastro(
    id_cadastro: null,
    id_empresa: $empresa_id->id,
    razao_soc: 'DIVERSOS',
    nom_fant: 'DIVERSOS',
    data_r: date('Y-m-d H:i:s')
);
Cadastro::create($cliente_fornecedor);
$centro_custos_lista = ['EMPRESA', 'PARTICULAR'];
foreach ($centro_custos_lista as $centro_custo) {
    $centrocustos = new CentroCustos(id_empresa: $empresa_id->id, nome:$centro_custo);
    CentroCustos::create($centrocustos);
}
$tipo_pagamento_lista = ['DINHEIRO', 'PIX', 'CRÉDITO', 'DÉBITO', 'BOLETO'];
foreach ($tipo_pagamento_lista as $tipo_pagamento) {
    $tipopagamento = new TipoPagamento(id_empresa: $empresa_id->id, nome:$tipo_pagamento);
    TipoPagamento::create($tipopagamento);
}

$lista_plano_contas = [
        '01 - RECEITAS' => [
        'Operacional' => true,
        'Tipo' => 'C',
        'Subtitulos' => []
    ], 
        '02 - DESPESAS FIXAS' => [
        'Operacional' => true,
        'Tipo' => 'D',
        'Subtitulos' => ['ALUGUEL', 'INTERNET E TELEFONE', 'FORNECEDORES', 'CONTABILIDADE', 'MANUTENÇÃO SISTEMAS', 'AGUA', 'ENERGIA', 'IMPOSTOS', 'TAXA BANCÁRIA']
    ],
        '03 - DESPESAS VARIÁVEIS' => [
        'Operacional' => true,
        'Tipo' => 'D',
        'Subtitulos' => ['USO E CONSUMO', 'USO E CONSUMO', 'COMBUSTÍVEL', 'DIVERSOS', 'NÃO IDENTIFICADO']
    ],
        '04 - PRÓ-LABORE' => [
        'Operacional' => true,
        'Tipo' => 'D',
        'Subtitulos' => ['PRÓ-LABORE']
    ],
        '05 - CRÉDITO NÃO OPERACIONAL' => [
        'Operacional' => false,
        'Tipo' => 'C',
        'Subtitulos' => ['TRANSFERENCIA ENTRE CONTAS', 'RESGATE DE APLICAÇÃO']
    ],
        '06 - DÉBITO NÃO OPERACIONAL' => [
        'Operacional' => false,
        'Tipo' => 'D',
        'Subtitulos' => ['TRANSFERENCIA ENTRE CONTAS', 'ACORDO', 'APLICAÇÃO', 'DEVOLUÇÃO']
    ]
];

foreach ($lista_plano_contas as $titulo => $dados) {
    $conta_criada = null;
    $conta_id = null;

    $conta = new Con01(
        id_empresa: $empresa_id->id,
        tipo: $dados['Tipo'],
        nome: $titulo,
        operacional: $dados['Operacional'] ? 1 : 0
    );
    Con01::create($conta);
    $conta_criada = Con01::read(idempresa:$empresa_id->id, nome:$titulo);
    $conta_id = $conta_criada[0]->id;
    if(empty($lista_plano_contas[$titulo]['Subtitulos'])) {
        continue;
    }
    foreach ($lista_plano_contas[$titulo]['Subtitulos'] as $subtitulo) {
        $conta_sub = new Con02(
            id_empresa: $empresa_id->id,
            id_con01: $conta_id,
            nome: $subtitulo,
            codigo: 0
        );
        Con02::create($conta_sub);
    }
}

    // Processar logo se arquivo foi enviado
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $_FILES['logo']['size'] > 0) {
        $foto_blob = file_get_contents($_FILES['logo']['tmp_name']);
        if ($foto_blob && strlen($foto_blob) > 0) {
            $logo = new Logo(null, $empresa_id->id, $foto_blob);
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

if (isset($_GET['acao']) && $_GET['acao'] === 'excluir') {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 1) {
        header('Location: /');
        exit;
    }

    $entidadesRaiz = glob(__DIR__ . '/../db/entities/*.php');
    $entidadesSeguranca = glob(__DIR__ . '/../db/entities/seguranca/*.php');
    $todosArquivos = array_merge($entidadesRaiz, $entidadesSeguranca);

    foreach ($todosArquivos as $arquivo) {
        require_once $arquivo;
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id < 1) {
        header('Location: index.php?erro=empresa_nao_encontrada');
        exit;
    }

    if ($id === 1) {
        header('Location: index.php?erro=empresa_protegida');
        exit;
    }

    $pdo = (new Database())->connect();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('SELECT status FROM empresas WHERE id = :id FOR UPDATE');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            $pdo->rollBack();
            header('Location: index.php?erro=empresa_nao_encontrada');
            exit;
        }

        if ((int) $empresa['status'] !== 0) {
            $pdo->rollBack();
            header('Location: index.php?erro=empresa_ativa');
            exit;
        }

        $stmt = $pdo->prepare('SELECT id FROM usuario WHERE id_empresa = :id_empresa');
        $stmt->bindValue(':id_empresa', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->query('SHOW TABLES');
        $tabelasExistentes = array_flip($stmt->fetchAll(PDO::FETCH_COLUMN));

        // Excluir primeiro os registros dependentes, respeitando a ordem das chaves estrangeiras.
        $tabelasEmpresa = [
            'ban02_imp',
            'ban02',
            'pag02',
            'rec02',
            'rec03',
            'seg03',
            'seg04',
            'ocorrencias',
            'fecha01',
            'canceladas',
            'pag01',
            'rec01',
            'controle02',
            'pontos',
            'rondas',
            'seg02',
            'ope01',
            'pra01',
            'band01',
            'seg01',
            'ban01',
            'ativ01',
            'logo',
            'procedimentos',
            'tipo_pag',
            'pal01',
            'con02',
            'con01',
            'custos',
            'cadastro',
            'bairro',
            'cidade',
            'categoria',
            'controle',
        ];

        foreach ($tabelasEmpresa as $tabela) {
            if (!isset($tabelasExistentes[$tabela])) {
                continue;
            }

            if ($tabela === 'con02' && isset($tabelasExistentes['ban02'])) {
                $stmt = $pdo->prepare(
                    'DELETE b
                     FROM ban02 b
                     INNER JOIN con02 c ON c.id = b.id_con02
                     WHERE c.id_empresa = :id_empresa'
                );
                $stmt->bindValue(':id_empresa', $id, PDO::PARAM_INT);
                $stmt->execute();
            }

            $stmt = $pdo->prepare("DELETE FROM {$tabela} WHERE id_empresa = :id_empresa");
            $stmt->bindValue(':id_empresa', $id, PDO::PARAM_INT);
            $stmt->execute();
        }

        $tabelasUsuario = ['alarmes', 'panico', 'turnos'];
        foreach ($usuarios as $idUsuario) {
            foreach ($tabelasUsuario as $tabela) {
                if (!isset($tabelasExistentes[$tabela])) {
                    continue;
                }

                $stmt = $pdo->prepare("DELETE FROM {$tabela} WHERE id_usuario = :id_usuario");
                $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
                $stmt->execute();
            }

            $stmt = $pdo->prepare('DELETE FROM usuario WHERE id = :id AND id_empresa = :id_empresa');
            $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_empresa', $id, PDO::PARAM_INT);
            $stmt->execute();
        }

        $stmt = $pdo->prepare('DELETE FROM empresas WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    header('Location: index.php');
    exit;
}
?>