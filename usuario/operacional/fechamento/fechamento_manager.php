<?php

require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/fecha01.php';
require_once __DIR__ . '/../../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/pagar.php';
require_once __DIR__ . '/../../../db/entities/banco01.php';
require_once __DIR__ . '/../../../db/entities/banco02.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: /');
    exit;
}
function checarNull($valor, $padrao) {
    return ($valor === 'Selecione' || $valor === '' || $valor === null)
        ? $padrao
        : $valor;
}
$empresa_usuario_id = $_SESSION['usuario']->id_empresa;
$empresa_usuario_obj = Empresa::read($empresa_usuario_id)[0];
if ($_SESSION['usuario']->cargo != 3 || $_SESSION['usuario']->permissao_operacional != 1 || $empresa_usuario_obj->permissao_operacional != 1) {
    header('Location: /');
    exit;
}

$post_nome_caixa = trim(filter_input(INPUT_POST, 'nome_caixa'));
$post_turno = filter_input(INPUT_POST, 'turno');
$data = filter_input(INPUT_POST, 'data');
$target = filter_input(INPUT_POST, 'target');
$fecha01_rec = Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo: 'C')[0] ?? null;
$fecha01_pag = Fecha01::read(id_empresa: $_SESSION['usuario']->id_empresa, tipo: 'D')[0] ?? null;

if ($post_turno === '' || $post_nome_caixa === '') {
    header('Location: fechamento.php?erro=parametros');
    exit;
}

function parseBrazilianDecimal($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') {
        return 0.0;
    }

    $commaCount = substr_count($valor, ',');
    $dotCount = substr_count($valor, '.');

    if ($commaCount > 0 && $dotCount > 0) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif ($commaCount > 0) {
        $valor = str_replace(',', '.', $valor);
    }

    return floatval($valor);
}

function criarBan02Dinheiro($ban01_dinheiro, $valor, $data, $descricao, $documento, $id_empresa, $fecha01 = null) {
    if (!$ban01_dinheiro) {
        return;
    }

    $ban02 = new Ban02(
        null,
        $id_empresa,
        $ban01_dinheiro->id,
        $data,
        $documento,
        $fecha01->id_titulo ?? null,
        $fecha01->id_subtitulo ?? null,
        'Venda em Dinheiro - ' . $descricao,
        null,
        $valor,
        null,
        1
    );
    Ban02::create($ban02);
}

function excluirBan02Dinheiro($ban01_dinheiro, $documento, $id_empresa) {
    if (!$ban01_dinheiro) {
        return;
    }
    $ban02_lista = Ban02::read(
        id_empresa: $id_empresa,
        filtro_conta: $ban01_dinheiro->id,
        filtro_documento: $documento
    );
    foreach ($ban02_lista as $ban02) {
        Ban02::delete($ban02->id);
    }
}

$hasAnyValue = false;
$hasCreated = false;
$hasUpdated = false;
$hasDeleted = false;

if ($fecha01_rec) {
    $post_tipo_pagamento_lista = $_POST['tipo_pagamento_receita'] ?? [];
    $post_valor_lista = $_POST['valor_receita'] ?? [];
    $tipo_dinheiro = TipoPagamento::read(idempresa: $_SESSION['usuario']->id_empresa, nome: 'dinheiro')[0] ?? null;
    $ban01_dinheiro = Ban01::read(id_empresa: $_SESSION['usuario']->id_empresa, nome: 'dinheiro')[0] ?? null;



$campos = [
    'id_cadastro'  => 'cadastro_receitas',
    'id_custos'    => 'centro_custos_receitas',
    'id_titulo'    => 'titulo_receitas',
    'id_subtitulo' => 'subtitulo_receitas',
];

foreach ($campos as $propriedade => $post) {
    $fecha01_rec->$propriedade = checarNull(
        filter_input(INPUT_POST, $post),
        $fecha01_rec->$propriedade
    );
}

    $rec02_criado = Rec02::read(
        id_empresa: $_SESSION['usuario']->id_empresa,
        filtro_descricao: 'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
        filtro_data_inicial: $data,
        filtro_data_final: $data,
        filtro_por: 'pagamento'
    ) ?? null;

    $grupos = [];
    $valor_total = 0;

    for ($i = 0; $i < count($post_tipo_pagamento_lista); $i++) {
        $tipo_pagamento_id = $post_tipo_pagamento_lista[$i];
        $valor = $post_valor_lista[$i] ?? '0';
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valor = floatval($valor);
        if ($valor > 0) {
            $valor_total += $valor;
            $hasAnyValue = true;
            if ($tipo_pagamento_id) {
                if (!isset($grupos[$tipo_pagamento_id])) {
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

    $acao_receita = null;
    if (empty($grupos)) {
        if ($rec02_criado) {
            $acao_receita = 'excluir';
        }
    } else {
        $acao_receita = $rec02_criado ? 'atualizar' : 'adicionar';
    }

    if ($acao_receita === 'adicionar') {
        require_once __DIR__ . '/../../../db/buscar_documento_rec.php';
        $documento = buscarDocumentoRec();

        $rec01 = new Rec01(
            id_empresa: $_SESSION['usuario']->id_empresa,
            id_cadastro: $fecha01_rec->id_cadastro,
            id_con01: $fecha01_rec->id_titulo,
            id_con02: $fecha01_rec->id_subtitulo,
            documento: $documento,
            descricao: 'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
            valor: $valor_total,
            parcelas: count($grupos),
            data_lanc: $data,
            id_usuario: $_SESSION['usuario']->id,
            centro_custos: $fecha01_rec->id_custos
        );
        Rec01::create($rec01);
        $rec01_id = Rec01::read(id_empresa: $_SESSION['usuario']->id_empresa, documento: $documento)[0]->id;

        $i = 1;
        foreach ($grupos as $item) {
            $rec02 = new Rec02(
                id_empresa: $_SESSION['usuario']->id_empresa,
                id_rec01: $rec01_id,
                valor_par: $item['valor'],
                parcela: $i,
                vencimento: $data,
                valor_pag: $item['valor'],
                data_pag: $data,
                obs: '',
                id_pgto: $item['tipo_pagamento']
            );
            Rec02::create($rec02);

            if ($tipo_dinheiro && $item['tipo_pagamento'] == $tipo_dinheiro->id && $ban01_dinheiro) {
                criarBan02Dinheiro(
                    $ban01_dinheiro,
                    $item['valor'],
                    $data,
                    'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
                    $documento,
                    $_SESSION['usuario']->id_empresa,
                    $fecha01_rec
                );
            }

            $i++;
        }

        $hasCreated = true;
    } elseif ($acao_receita === 'atualizar') {
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
        foreach ($rec02_existentes as $r) {
            $map_existentes[$r->id_pgto] = $r;
        }

        $parcela = count($rec02_existentes) + 1;

        foreach ($grupos as $item) {
            $tipo_pagamento_id = $item['tipo_pagamento'];
            $valor = $item['valor'];
            $is_dinheiro = $tipo_dinheiro && $tipo_pagamento_id == $tipo_dinheiro->id && $ban01_dinheiro;

            if (isset($map_existentes[$tipo_pagamento_id])) {
                $rec02 = $map_existentes[$tipo_pagamento_id];
                $rec02->valor_par = $valor;
                $rec02->valor_pag = $valor;
                Rec02::update($rec02);

                if ($is_dinheiro) {
                    excluirBan02Dinheiro($ban01_dinheiro, $rec01->documento, $_SESSION['usuario']->id_empresa);
                    criarBan02Dinheiro(
                        $ban01_dinheiro,
                        $valor,
                        $data,
                        'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
                        $rec01->documento,
                        $_SESSION['usuario']->id_empresa,
                        $fecha01_rec
                    );
                }

                unset($map_existentes[$tipo_pagamento_id]);
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
                    id_pgto: $tipo_pagamento_id
                );
                Rec02::create($novo);

                if ($is_dinheiro) {
                    criarBan02Dinheiro(
                        $ban01_dinheiro,
                        $valor,
                        $data,
                        'Turno ' . $post_turno . ' - ' . $post_nome_caixa,
                        $rec01->documento,
                        $_SESSION['usuario']->id_empresa,
                        $fecha01_rec
                    );
                }
            }
        }

        foreach ($map_existentes as $tipo_pagamento_id => $rec02) {
            if ($tipo_dinheiro && $tipo_pagamento_id == $tipo_dinheiro->id && $ban01_dinheiro) {
                excluirBan02Dinheiro($ban01_dinheiro, $rec01->documento, $_SESSION['usuario']->id_empresa);
            }
            Rec02::delete($rec02->id);
        }

        $hasUpdated = true;
    } elseif ($acao_receita === 'excluir') {
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

        foreach ($rec02_lista as $rec02) {
            Rec02::delete($rec02->id);
        }

        Rec01::delete($rec01_id);

        if ($ban01_dinheiro) {
            excluirBan02Dinheiro($ban01_dinheiro, $rec01->documento, $_SESSION['usuario']->id_empresa);
        }

        $hasDeleted = true;
    }
}

if ($fecha01_pag) {
    $post_descricao_lista = $_POST['descricao_despesa'] ?? [];
    $post_valor_lista = $_POST['valor_despesa'] ?? [];


    $campos = [
        'id_cadastro'  => 'cadastro_despesas',
        'id_custos'    => 'centro_custos_despesas',
        'id_titulo'    => 'titulo_despesas',
        'id_subtitulo' => 'subtitulo_despesas',
        'tipo_pagamento' => 'tipo_pagamento_despesas'
    ];

    foreach ($campos as $propriedade => $post) {
        $fecha01_pag->$propriedade = checarNull(
            filter_input(INPUT_POST, $post),
            $fecha01_pag->$propriedade
        );
    }

    foreach ($post_descricao_lista as $i => $descricao) {
        $descricao = trim((string) $descricao);
        if ($descricao === '') {
            continue;
        }

        $valor = $post_valor_lista[$i] ?? '0';
        $valor = parseBrazilianDecimal($valor);
        $descricao_pag01 = 'Turno ' . $post_turno . ' - ' . $post_nome_caixa . ' - ' . $descricao;
        $descricao_busca = $descricao_pag01;

        $pag02_existentes = Pag02::read(
            id_empresa: $_SESSION['usuario']->id_empresa,
            filtro_descricao: $descricao_busca,
            filtro_data_inicial: $data,
            filtro_data_final: $data,
            filtro_por: 'pagamento'
        ) ?? [];

        if ($valor <= 0) {
            if (!empty($pag02_existentes)) {
                foreach ($pag02_existentes as $pag02_item) {
                    Pag02::delete($pag02_item->id);
                    Pag01::delete($pag02_item->id_pag01);
                }
                $hasDeleted = true;
            }
            continue;
        }

        $hasAnyValue = true;

        if (empty($pag02_existentes)) {
            require_once __DIR__ . '/../../../db/buscar_documento_pag.php';
            $documento = buscarDocumentoPag();

            $pag01 = new Pag01(
                id_empresa: $_SESSION['usuario']->id_empresa,
                id_cadastro: $fecha01_pag->id_cadastro,
                id_con01: $fecha01_pag->id_titulo,
                id_con02: $fecha01_pag->id_subtitulo,
                documento: $documento,
                descricao: $descricao_pag01,
                valor: $valor,
                parcelas: 1,
                data_lanc: $data,
                id_usuario: $_SESSION['usuario']->id,
                centro_custos: $fecha01_pag->id_custos
            );
            Pag01::create($pag01);
            $pag01_id = Pag01::read(id_empresa: $_SESSION['usuario']->id_empresa, documento: $documento)[0]->id;

            $pag02 = new Pag02(
                null,
                $_SESSION['usuario']->id_empresa,
                $pag01_id,
                $valor,
                1,
                $data,
                $valor,
                $data,
                $descricao,
                $fecha01_pag->tipo_pagamento
            );
            Pag02::create($pag02);
            $hasCreated = true;
        } else {
            $pag02_base = $pag02_existentes[0];
            $pag01 = Pag01::read(
                id_empresa: $_SESSION['usuario']->id_empresa,
                id: $pag02_base->id_pag01
            )[0];

            if ($pag01) {
                $pag01->valor = $valor;
                Pag01::update($pag01);
            }

            $pag02_base->valor_par = $valor;
            $pag02_base->valor_pag = $valor;
            $pag02_base->vencimento = $data;
            $pag02_base->data_pag = $data;
            Pag02::update($pag02_base);

            for ($j = 1; $j < count($pag02_existentes); $j++) {
                Pag02::delete($pag02_existentes[$j]->id);
            }

            $hasUpdated = true;
        }
    }
}

if (!$hasAnyValue && !$hasDeleted) {
    header('Location: fechamento.php?erro=vazio');
    exit;
}

$successType = 'atualizado';
if ($hasCreated && !$hasUpdated && !$hasDeleted) {
    $successType = 'adicionado';
} elseif ($hasDeleted && !$hasCreated && !$hasUpdated) {
    $successType = 'excluido';
}

header('Location: fechamento.php?sucesso=' . $successType);
exit;
