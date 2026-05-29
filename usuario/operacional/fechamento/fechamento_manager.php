<?php

require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/fecha01.php';
require_once __DIR__ . '/../../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/banco01.php';
require_once __DIR__ . '/../../../db/entities/banco02.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

$post_tipo_pagamento_lista = $_POST['tipo_pagamento'];
$post_valor_lista = $_POST['valor'];
$post_nome_caixa = filter_input(INPUT_POST, 'nome_caixa');
$post_turno = filter_input(INPUT_POST, 'turno');
$data = filter_input(INPUT_POST, 'data');
$fecha01 = Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa)[0] ?? null;

if($post_turno == '' || $post_nome_caixa == '') {
    header('Location: fechamento.php?erro=parametros');
    exit;
}

$tipo_dinheiro = TipoPagamento::read(idempresa: $_SESSION['usuario']->id_empresa, nome: 'dinheiro')[0] ?? null;
$ban01_dinheiro = Ban01::read(id_empresa: $_SESSION['usuario']->id_empresa, nome: 'dinheiro')[0] ?? null;

$rec02_criado = Rec02::read(
    id_empresa: $_SESSION['usuario']->id_empresa,
    filtro_descricao: 'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
    filtro_data_inicial: $data,
    filtro_data_final: $data,
    filtro_por: 'pagamento',
) ?? null;

$excluir = true;
foreach($post_valor_lista as $valor) {
    if($valor != 0) {
        $excluir = false;
        break;
    }
}

if($excluir) {
    $acao = 'excluir';
} else if($rec02_criado) {
    $acao = 'atualizar';
} else {
    $acao = 'adicionar';
}

$valor_total = 0;
$grupos = [];

if($acao != 'excluir') {
    for($i = 0; $i < count($post_tipo_pagamento_lista); $i++) {
        $tipo_pagamento_id = $post_tipo_pagamento_lista[$i];
        $valor = $post_valor_lista[$i];
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valor = floatval($valor);
        if($valor) {
            $valor_total += $valor;
        }
        if($tipo_pagamento_id && $valor && $valor >= 0) {
            if(!isset($grupos[$tipo_pagamento_id])) {
                $grupos[$tipo_pagamento_id] = [
                    'tipo_pagamento' => $tipo_pagamento_id,
                    'valor' => $valor
                ];
            } else {
                $grupos[$tipo_pagamento_id]['valor'] += $valor;
            }
        }
    }
} else {
    for($i = 0; $i < count($post_tipo_pagamento_lista); $i++) {
        $tipo_pagamento_id = $post_tipo_pagamento_lista[$i];
        $valor = $post_valor_lista[$i];
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valor = floatval($valor);
        if($valor) {
            $valor_total += $valor;
        }
        if($tipo_pagamento_id && $valor) {
            if(!isset($grupos[$tipo_pagamento_id])) {
                $grupos[$tipo_pagamento_id] = [
                    'tipo_pagamento' => $tipo_pagamento_id,
                    'valor' => $valor
                ];
            } else {
                $grupos[$tipo_pagamento_id]['valor'] += $valor;
            }
        }
    }
}

if(empty($grupos) && $acao != 'excluir') {
    header('Location: fechamento.php?erro=vazio');
    exit;
}

function criarBan02Dinheiro($ban01_dinheiro, $valor, $data, $descricao, $documento, $id_empresa, $fecha01 = null) {
    if(!$ban01_dinheiro) return;
    $ban02 = new Ban02(
        null,
        $id_empresa,
        $ban01_dinheiro->id,
        $data,
        $documento,
        $fecha01->id_titulo ?? null,
        $fecha01->id_subtitulo ?? null,
        'Venda em Dinheiro' . ' - ' . $descricao,
        null,
        $valor,
        null,
        1
    );
    Ban02::create($ban02);
}

function excluirBan02Dinheiro($ban01_dinheiro, $documento, $id_empresa) {
    if(!$ban01_dinheiro) return;
    $ban02_lista = Ban02::read(
        id_empresa: $id_empresa,
        filtro_conta: $ban01_dinheiro->id,
        filtro_documento: $documento,
    );
    foreach($ban02_lista as $ban02) {
        Ban02::delete($ban02->id);
    }
}

if($acao == 'adicionar') {
    require_once __DIR__ . '/../../../db/buscar_documento_rec.php';
    $documento = buscarDocumentoRec();

    if($fecha01) {
        $rec01 = new Rec01(
            id_empresa: $_SESSION['usuario']->id_empresa,
            id_cadastro: $fecha01->id_cadastro,
            id_con01: $fecha01->id_titulo,
            id_con02: $fecha01->id_subtitulo,
            documento: $documento,
            descricao: 'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
            valor: $valor_total,
            parcelas: count($grupos),
            data_lanc: $data,
            id_usuario: $_SESSION['usuario']->id,
            centro_custos: $fecha01->id_custos,
        );
        Rec01::create($rec01);
        $rec01_id = Rec01::read(id_empresa: $_SESSION['usuario']->id_empresa, documento: $documento)[0]->id;

        $i = 1;
        foreach($grupos as $item) {
            $rec02 = new Rec02(
                id_empresa: $_SESSION['usuario']->id_empresa,
                id_rec01: $rec01_id,
                valor_par: $item['valor'],
                parcela: $i,
                vencimento: $data,
                valor_pag: $item['valor'],
                data_pag: $data,
                obs: '',
                id_pgto: $item['tipo_pagamento'],
            );
            Rec02::create($rec02);

            if($tipo_dinheiro && $item['tipo_pagamento'] == $tipo_dinheiro->id && $ban01_dinheiro) {
                criarBan02Dinheiro(
                    $ban01_dinheiro,
                    $item['valor'],
                    $data,
                    'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
                    $documento,
                    $_SESSION['usuario']->id_empresa,
                    $fecha01
                );
            }

            $i++;
        }

        header('Location: fechamento.php?sucesso=adicionado');
        exit;
    } else {
        header('Location: fechamento.php?erro=parametros');
        exit;
    }

} else if($acao == 'atualizar') {

    $rec02_base = $rec02_criado[0];
    $rec01 = Rec01::read(
        id_empresa: $_SESSION['usuario']->id_empresa,
        id: $rec02_base->id_rec01
    )[0];

    $rec01->valor = $valor_total;
    Rec01::update($rec01);

    $rec02_existentes = Rec02::read(
        id_empresa: $_SESSION['usuario']->id_empresa,
        id_rec01: $rec01->id
    );

    $map_existentes = [];
    foreach($rec02_existentes as $r) {
        $map_existentes[$r->id_pgto] = $r;
    }

    $parcela = count($rec02_existentes) + 1;

    foreach($grupos as $item) {
        $tipo_pagamento_id = $item['tipo_pagamento'];
        $valor = $item['valor'];
        $is_dinheiro = $tipo_dinheiro && $tipo_pagamento_id == $tipo_dinheiro->id && $ban01_dinheiro;

        if(isset($map_existentes[$tipo_pagamento_id])) {
            $rec02 = $map_existentes[$tipo_pagamento_id];
            $rec02->valor_par = $valor;
            $rec02->valor_pag = $valor;
            Rec02::update($rec02);

            if($is_dinheiro) {
                excluirBan02Dinheiro($ban01_dinheiro, $rec01->documento, $_SESSION['usuario']->id_empresa);
                criarBan02Dinheiro(
                    $ban01_dinheiro,
                    $valor,
                    $data,
                    'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
                    $rec01->documento,
                    $_SESSION['usuario']->id_empresa,
                    $fecha01
                );
            }
        } else {
            $novo = new Rec02(
                id_empresa: $_SESSION['usuario']->id_empresa,
                id_rec01: $rec01->id,
                valor_par: $valor,
                parcela: $parcela++,
                vencimento: $data,
                valor_pag: $valor,
                data_pag: $data,
                obs: '',
                id_pgto: $tipo_pagamento_id,
            );
            Rec02::create($novo);

            if($is_dinheiro) {
                criarBan02Dinheiro(
                    $ban01_dinheiro,
                    $valor,
                    $data,
                    'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
                    $rec01->documento,
                    $_SESSION['usuario']->id_empresa,
                    $fecha01
                );
            }
        }
    }

    header('Location: fechamento.php?sucesso=atualizado');
    exit;

} else if($acao == 'excluir') {

    if($rec02_criado) {
        $rec02_base = $rec02_criado[0];
        $rec01_id = $rec02_base->id_rec01;

        $rec01 = Rec01::read(
            id_empresa: $_SESSION['usuario']->id_empresa,
            id: $rec01_id
        )[0];

        $rec02_lista = Rec02::read(
            id_empresa: $_SESSION['usuario']->id_empresa,
            id_rec01: $rec01_id
        );

        foreach($rec02_lista as $rec02) {
            Rec02::delete($rec02->id);
        }

        Rec01::delete($rec01_id);

        if($ban01_dinheiro) {
            excluirBan02Dinheiro($ban01_dinheiro, $rec01->documento, $_SESSION['usuario']->id_empresa);
        }

        header('Location: fechamento.php?sucesso=excluido');
        exit;
    } else {
        header('Location: fechamento.php?erro=nao_encontrado');
        exit;
    }
}