<?php


require_once __DIR__ . '/../db/entities/bairro.php';
require_once __DIR__ . '/../db/entities/cidade.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/categoria.php';
require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
require_once __DIR__ . '/../db/entities/contas.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
echo '<pre>';
print_r($_POST);
echo '</pre>';

if(isset($_POST['view']) && $_POST['view'] == 'cadastro'){

if (isset($_POST['target'])) {
    $target = $_POST['target'];
} else if (isset($_GET['target'])) {
    $target = $_GET['target'];
}


if(isset($_POST['id'])) {
    $id = $_POST['id'];
} else if (isset($_GET['id'])) {
    $id = $_GET['id'];
}



$id_empresa = $_SESSION['usuario']->id_empresa;
$nome = $_POST['nome'];
$fantasia = $_POST['fantasia'];
$endereco = $_POST['endereco'];
$bairro = $_POST['bairro'];
$cidade = $_POST['cidade'];
$estado = $_POST['estado'];
$cpf = $_POST['cpf'];
$cnpj = $_POST['cnpj'];
$email = $_POST['email'];
$celular = $_POST['celular'];
$fixo = $_POST['fixo'];
$cep = $_POST['cep'];
$categoria = $_POST['categoria'];


if($_POST['acao'] == 'adicionar') {
    switch($_POST['target']) {
        case 'cliente':
            $cadastro = new Cadastro(
                $_SESSION['usuario']->id_empresa,
                null,
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
                $fixo,
                $cep,
                $categoria
            );

            if(!Cadastro::read(null,$email)) {
                Cadastro::create($cadastro);
            }
            
        break;

        case 'bairro':

            $bairro = new Bairro(
                null,
                $id_empresa,
                $nome
            );

            Bairro::create($bairro);
        break;

            case 'cidade':

            $cidade = new Cidade(
                null,
                $id_empresa,
                $nome
            );
            if(!Cidade::read(null, $id_empresa, $nome)) {
            Cidade::create($cidade);
            }
        break;

            case 'categoria':

            $categoria = new Categoria(
                null,
                $id_empresa,
                $nome
            );
            if(!Categoria::read(null, $id_empresa, $nome)) {
            Categoria::create($categoria);
            }
        break;

            case 'pagamento':

            $categoria = new TipoPagamento(
                null,
                $id_empresa,
                $nome
            );
            if(!TipoPagamento::read(null, $id_empresa, $nome)) {
            TipoPagamento::create($categoria);
            }
        break;
    }
} else if($_POST['acao'] == 'editar') {
    switch($_POST['target']) {
    case 'cliente':
        $cadastro = new Cadastro(
            null,
            $id,
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
            $fixo,
            $cep,
            $categoria
        );
        

        Cadastro::update($cadastro);

        

    break;

    case 'bairro':

        $bairro = new Bairro(
            $id,
            null,
            $nome
        );

        Bairro::Update($bairro);
    break;

        case 'cidade':

        $cidade = new Cidade(
            $id,
            null,
            $nome
        );

        Cidade::Update($cidade);
    break;

        case 'categoria':

        $categoria = new Categoria(
            $id,
            null,
            $nome
        );

        Categoria::Update($categoria);
    break;

        case 'pagamento':

        $pagamento = new TipoPagamento(
            $id,
            null,
            $nome
        );
        TipoPagamento::update($pagamento);
        break;
}
}
else if($_GET['acao'] == 'excluir') {
    switch($_GET['target']) {
        case 'cliente':
            Cadastro::delete($id);
        break;

        case 'bairro':
            Bairro::delete($id);
        break;

        case 'cidade':
            Cidade::delete($id);
        break;

        case 'categoria':
            Categoria::delete($id);
        break;

        case 'pagamento':
            TipoPagamento::delete($id);
        break;
    }
}


header('Location: cadastrar.php?cadastro='. $target);

} else if (isset($_POST['view']) && $_POST['view'] == 'conta') {

    if (isset($_POST['acao']) && $_POST['acao'] == 'adicionar' && $_POST['target'] == 'titulo') {
        $conta = new Con01(
            null,
            $_SESSION['usuario']->id_empresa,
            $_POST['tipo'],
            $_POST['nome']
        );

        Con01::create($conta);
    } else if (isset($_POST['acao']) && $_POST['acao'] == 'editar' && $_POST['target'] == 'titulo') {
        $conta = new Con01(
            $_POST['id'],
            null,
            $_POST['tipo'],
            $_POST['nome']
        );

        Con01::update($conta);
    } else if (isset($_GET['acao']) && $_GET['acao'] == 'excluir') {
        Con01::delete($_GET['id']);
    } else if(isset($_POST['target']) && $_POST['target'] == 'subtitulo' && isset($_POST['acao']) && $_POST['acao'] == 'adicionar') {
        $conta = new Con02(
            null,
            $_SESSION['usuario']->id_empresa,
            $_POST['con01id'],
            $_POST['nome']
        );

        Con02::create($conta);
    } else if(isset($_POST['target']) && $_POST['target'] == 'subtitulo' && isset($_POST['acao']) && $_POST['acao'] == 'editar') {
        $conta = new Con02(
            $_POST['id'],
            null,
            $_POST['id_conta'],
            $_POST['nome']
        );

    


}
header('Location: index.php?view=contas');
}


?>