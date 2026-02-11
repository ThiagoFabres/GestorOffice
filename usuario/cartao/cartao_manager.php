<?php
require_once __DIR__ . '/../../db/entities/usuarios.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;

}
require_once __DIR__ . '/../../db/entities/ope01.php';
require_once __DIR__ . '/../../db/entities/band01.php';
require_once __DIR__ . '/../../db/entities/pra01.php';
$acao = filter_input(INPUT_POST, 'acao');
$target = filter_input(INPUT_POST, 'target');
if($acao == 'adicionar') {

    if($target == 'operadora') {
        $descricao = filter_input(INPUT_POST,  'nome');
        $cadastro = filter_input(INPUT_POST,  'cadastro');
        $custos = filter_input(INPUT_POST,  'custos');
        $titulo = filter_input(INPUT_POST,  'titulo');
        $subtitulo = filter_input(INPUT_POST,  'subtitulo');
        $operadora = new Ope01(
            null,
            $_SESSION['usuario']->id_empresa,
            $descricao,
            $cadastro,
            $custos,
            $titulo,
            $subtitulo
        );
        Ope01::create($operadora);
        header('Location: cadastro_cartao.php');
        exit;
    }
    else if($target == 'bandeira') {
        $descricao = filter_input(INPUT_POST,  'nome');
        $id_operadora = filter_input(INPUT_POST,  'operadora_id');
        $tipo_bandeira = filter_input(INPUT_POST,  'tipo_bandeira');
        $bandeira = new Band01(
            null,
            $_SESSION['usuario']->id_empresa,
            $id_operadora,
            $descricao,
            $tipo_bandeira
        );

        
        Band01::create($bandeira);
        header('Location: cadastro_cartao.php');
        exit;
    }
    else if($target == 'prazo') {
        $parcela = filter_input(INPUT_POST,  'parcela');
        $prazo= filter_input(INPUT_POST,  'prazo');
        $taxa = filter_input(INPUT_POST,  'taxa');
        $taxa = str_replace('.', '', $taxa);
        $taxa = str_replace(',', '.', $taxa);
        
        $id_operadora = filter_input(INPUT_POST,  'operadora_id');
        $id_bandeira = filter_input(INPUT_POST,  'bandeira_id');
        $prazo = new Pra01(
            null,
            $_SESSION['usuario']->id_empresa,
            $id_operadora,
            $id_bandeira,
            $prazo,
            $parcela,
            $taxa
        );

        
        Pra01::create($prazo);
        header('Location: cadastro_cartao.php');
        exit;
    }
} else if($acao == 'editar') {
    if($target == 'operadora') {
        $id = filter_input(INPUT_POST,  'id');
        $descricao = filter_input(INPUT_POST,  'nome');
        $cadastro = filter_input(INPUT_POST,  'cadastro');
        $custos = filter_input(INPUT_POST,  'custos');
        $titulo = filter_input(INPUT_POST,  'titulo');
        $subtitulo = filter_input(INPUT_POST,  'subtitulo');
        
        $operadora = new Ope01(
            $id,
            $_SESSION['usuario']->id_empresa,
            $descricao,
            $cadastro,
            $custos,
            $titulo,
            $subtitulo
        );

        
        Ope01::update($operadora);
        header('Location: cadastro_cartao.php');
        exit;
    }
    else if($target == 'bandeira') {
        $id = filter_input(INPUT_POST,  'id');
        $descricao = filter_input(INPUT_POST,  'nome');
        $tipo_bandeira = filter_input(INPUT_POST,  'tipo_bandeira');
        
        $bandeira = new Band01(
            $id,
            $_SESSION['usuario']->id_empresa,
            null,
            $descricao,
            $tipo_bandeira
        );

        
        Band01::update($bandeira);
        header('Location: cadastro_cartao.php');
        exit;
    } else if($target == 'prazo') {
        $id = filter_input(INPUT_POST,  'id');
        $parcela = filter_input(INPUT_POST,  'parcela');
        $prazo_value= filter_input(INPUT_POST,  'prazo');
        $taxa = filter_input(INPUT_POST,  'taxa');
        $taxa = str_replace('.', '', $taxa);
        $taxa = str_replace(',', '.', $taxa);
        
        $prazo = new Pra01(
            $id,
            $_SESSION['usuario']->id_empresa,
            null,
            null,
            $prazo_value,
            $parcela,
            $taxa
        );

        
        Pra01::update($prazo);
        header('Location: cadastro_cartao.php');
        exit;
    }
} else if($acao == 'excluir') {
    if($target == 'operadora') {
        $id = filter_input(INPUT_POST,  'id');
        if(
            !Band01::read(id_empresa: $_SESSION['usuario']->id_empresa, id_operadora: $id)
            && !Pra01::read(id_operadora: $id, id_empresa: $_SESSION['usuario']->id_empresa)
        ) {
            Ope01::delete($id);
            header('Location: cadastro_cartao.php');
            exit;
        } else {
            header('Location: cadastro_cartao.php?erro=operadora_em_uso');
            exit;
        }
        
        
    }
    else if($target == 'bandeira') {
        $id = filter_input(INPUT_POST,  'id');
        if(!Pra01::read(id_bandeira: $id, id_empresa: $_SESSION['usuario']->id_empresa)) {
            Band01::delete($id);
            header('Location: cadastro_cartao.php');
            exit;
        } else {
            header('Location: cadastro_cartao.php?erro=bandeira_em_uso');
            exit;
        }
        
    } else if($target == 'prazo') {
        $id = filter_input(INPUT_POST,  'id');
        Pra01::delete($id);
        header('Location: cadastro_cartao.php');
        exit;
    }
}




?>