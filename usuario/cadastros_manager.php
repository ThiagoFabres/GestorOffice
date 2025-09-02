<?php


require_once __DIR__ . '/../db/entities/bairro.php';
require_once __DIR__ . '/../db/entities/cidade.php';
require_once __DIR__ . '/../db/entities/cadastro.php';
require_once __DIR__ . '/../db/entities/categoria.php';
require_once __DIR__ . '/../db/entities/usuarios.php';
require_once __DIR__ . '/../db/entities/pagamento.php';
require_once __DIR__ . '/../db/entities/contas.php';
require_once __DIR__ . '/..//db/entities/recebimentos.php';
require_once __DIR__ . '/..//db/entities/pagar.php';

session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;

}

$view = filter_input(INPUT_POST, 'view');
if($view == null){$view = filter_input(INPUT_GET, 'view');}
$acao = filter_input(INPUT_POST, 'acao');
if($acao == null){$acao = filter_input(INPUT_GET, 'acao');}

    $target = filter_input(INPUT_POST, 'target');
    if($target == null){$target = filter_input(INPUT_GET, 'target');}



    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if($id == null){$id = filter_input(INPUT_GET, 'id');}
    $con01 = filter_input(INPUT_POST, 'con01id');


if(isset($view) && $view == 'cadastro'){





$id_empresa = $_SESSION['usuario']->id_empresa;
$nome = filter_input(INPUT_POST, 'nome');
$fantasia = filter_input(INPUT_POST, 'fantasia');
$endereco = filter_input(INPUT_POST, 'endereco');
$bairro = filter_input(INPUT_POST, 'bairro');
$cidade =filter_input(INPUT_POST, 'cidade');
$estado = filter_input(INPUT_POST, 'estado');
$cpf = filter_input(INPUT_POST, 'cpf');
$cnpj = filter_input(INPUT_POST, 'cnpj');
$email = filter_input(INPUT_POST, 'email');
$celular = filter_input(INPUT_POST, 'celular');
$fixo = filter_input(INPUT_POST, 'fixo');
$cep = filter_input(INPUT_POST, 'cep');
$categoria = filter_input(INPUT_POST, 'categoria');
$nome = filter_input(INPUT_POST, 'nome');
$tipo = filter_input(INPUT_POST, 'tipo');



if($acao == 'adicionar') {
    switch($target) {
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
} else if($acao == 'editar') {
    switch($target) {
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
else if(filter_input(INPUT_GET, 'acao') == 'excluir') {
    switch(filter_input(INPUT_GET, 'target')) {
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
exit;







} else if (isset($view) && $view == 'conta') {
    $nome = filter_input(INPUT_POST, 'nome');
    $tipo = filter_input(INPUT_POST, 'tipo');
    $id_conta = filter_input(INPUT_POST, 'id_conta');


    if (isset($acao) && $acao == 'adicionar' && $target == 'titulo') {
        $conta = new Con01(
            null,
            $_SESSION['usuario']->id_empresa,
            $tipo,
            $nome
        );

        Con01::create($conta);
    } else if (isset($acao) && $acao == 'editar' && $target == 'titulo') {
        $conta = new Con01(
            $id,
            null,
            $tipo,
            $nome
        );

        Con01::update($conta);
    } else if (isset($acao) && $acao == 'excluir') {
        Con01::delete($id);
    } else if(isset($target) && $target == 'subtitulo' && isset($acao) && $acao == 'adicionar') {
        $conta = new Con02(
            null,
            $_SESSION['usuario']->id_empresa,
            $con01,
            $nome
        );

       

        Con02::create($conta);
    } else if(isset($target) && $target == 'subtitulo' && isset($acao) && $acao == 'editar') {
        $conta = new Con02(
            $id,
            null,
            $id_conta,
            $nome
        );

    


}
header('Location: contas.php');
exit;
} else if (isset($view) && $view == 'receber') {
    $cadastro = filter_input(INPUT_POST, 'cadastro');
    $titulo = filter_input(INPUT_POST, 'titulo');
    $subtitulo = filter_input(INPUT_POST, 'subtitulo');
    $documento = filter_input(INPUT_POST, 'documento');
    $descricao = filter_input(INPUT_POST, 'descricao');
    $valor = filter_input(INPUT_POST, 'valor');
    $parcelas_d = filter_input(INPUT_POST, 'parcelas_d');
    $parcela   = $_POST['parcela'] ?? [];
    $vencimento = $_POST['vencimento'] ?? [];
    $valor_par  = $_POST['valor_par'] ?? [];
    $obs02      = $_POST['obs02'] ?? [];
    $id = $_POST['id'] ?? [];
    $id_rec = filter_input(INPUT_POST, 'id_rec01');
    $data_pag = filter_input(INPUT_POST, 'data');
    // $data_pag = new DateTime($data_pag);
    // $data_pag = $data_pag->format('d-m-Y');
    $forma_pagamento = filter_input(INPUT_POST, 'forma_pagamento');
    if($_POST['pagar'] == 1 || $_GET['pagar'] == 1){
        $pagar = true;
    } else {
        $pagar = false;
    }


    if(isset($acao) && $acao == 'adicionar') {

        $recebimento = new Rec01(
            null,
            $_SESSION['usuario']->id_empresa,
            $cadastro,
            $titulo,
            $subtitulo,
            $documento,
            $descricao,
            $valor,
            $parcelas_d,
            null,
            $_SESSION['usuario']->id_usuario,
        );
        if($pagar) {
            Pag01::create($recebimento);
            $rec01 = Pag01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0];
        }else {
            Rec01::create($recebimento);
            $rec01 = Rec01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0];
        }
        
        $parcelas = [];
        for($i = 1; $i < $parcelas_d + 1; $i++) {
            $valor_parcela = $valor_par[$i];
            $vencimento_data = $vencimento[$i];
            $obs_parcela = $obs02[$i];

            if ($pagar) {
    $parcelas[$i] = new Pag02(
        null,                           // id (PK da parcela)
        $_SESSION['usuario']->id_empresa,
        $rec01->id,                     // id_pag01 (FK)
        $valor_parcela,
        $i,
        $vencimento_data,
        0,
        null,
        $obs_parcela,
        null
    );
    Pag02::create($parcelas[$i]);
} else {
    $parcelas[$i] = new Rec02(
        null,                           // id (PK da parcela)
        $_SESSION['usuario']->id_empresa,
        $rec01->id,                     // id_rec01 (FK)
        $valor_parcela,
        $i,
        $vencimento_data,
        0,
        null,
        $obs_parcela,
        null
    );
    Rec02::create($parcelas[$i]);
};
        if($pagar) {
            Pag02::create($parcelas[$i]);
        } else {
            Rec02::create($parcelas[$i]);
        }
        }
        if($pagar) {
            header('Location: pagar.php');
            exit;
        } else {
            header('Location: receber.php');
            exit;
        }
} else if(isset($acao) && $acao == 'editar') {
    if($pagar) {
            $rec01 = Pag01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0];
        }else {
            $rec01 = Rec01::read(null, $_SESSION['usuario']->id_empresa, null, $documento)[0];
        }
    $recebimento = new Rec01(
            $id_rec,
            $_SESSION['usuario']->id_empresa,
            $cadastro,
            $titulo,
            $subtitulo,
            $documento,
            $descricao,
            $valor,
            $parcelas_d,
            $rec01->data_lanc,
            $_SESSION['usuario']->id_usuario,
        );
        if($pagar) {
            Pag01::update($recebimento);
        } else {
            Rec01::update($recebimento);
        }
         

    
        $parcelas = [];
        for($i = 1; $i < $parcelas_d + 1; $i++) {
            $valor_parcela = $valor_par[$i];
            $vencimento_data = $vencimento[$i];
            $obs_parcela = $obs02[$i];
            $id_parcela = $id[$i];

            $parcelas[$i] = new Rec02(
            $id_parcela,
            $_SESSION['usuario']->id_empresa,
            $rec01->id,
            $valor_parcela,
            $i,
            $vencimento_data,
            0,
            null,
            $obs_parcela,
            null

        );
        if($pagar) {
            Pag02::update($parcelas[$i]);
        } else {
            Rec02::update($parcelas[$i]);
        }
             
        }
        if($pagar) {
            header('Location: pagar.php');
            exit;
        } else {
            header('Location: receber.php');
            exit;
        }

} else if(isset($acao) && $acao == 'quitar') {
    if($target == 'parcela') {
        if($pagar) {
            $parcela_antiga = Pag02::read($id)[0];
        } else {
             $parcela_antiga = Rec02::read($id)[0];
        }
       
        $parcela = new Rec02(
            $id,
            null,
            null,
            $parcela_antiga->valor_par,
            $parcela_antiga->parcela,
            $parcela_antiga->vencimento,
            $valor + $parcela_antiga->valor_pag,
            $data_pag,
            $parcela_antiga->obs,
            $forma_pagamento
        );
        if($pagar) {
            Pag02::update($parcela);
            header('Location: pagar.php');
            exit;
        } else {
            Rec02::update($parcela);
            header('Location: receber.php');
            exit;
        }
        
    
    }
} else if(isset($acao) && $acao == 'estornar') {
     $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if($id == null){$id = filter_input(INPUT_GET, 'id');}
    if($target == 'parcela') {
        if($pagar) {
            $parcela_antiga = Pag02::read($id)[0];
        } else {
             $parcela_antiga = Rec02::read($id)[0];
        }
        $parcela = new Rec02(
            $id,
            null,
            null,
            $parcela_antiga->valor_par,
            $parcela_antiga->parcela,
            $parcela_antiga->vencimento,
            0,
            $parcela_antiga->data_pag,
            $parcela_antiga->obs,
            null
        );
        if($pagar) {
            Pag02::update($parcela);
            header('Location: pagar.php');
            exit;
        } else {
            Rec02::update($parcela);
            header('Location: receber.php');
            exit;
        }
        
        
    }
}
    
}


?>
