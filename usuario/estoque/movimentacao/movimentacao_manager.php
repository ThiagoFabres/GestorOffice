<?php
require_once __DIR__ . '/../../../db/base.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov01.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov02.php';
require_once __DIR__ . '/../../../db/entities/estoque/prod01.php';
require_once __DIR__ . '/../permissao.php';
session_start();

function redirecionarMovimentacao(string $resultado): void {
    header('Location: movimentacao.php?resultado=' . rawurlencode($resultado));
    exit;
}

function decimalMovimentacaoValido($valor, int $casas): ?float {
    if (!is_string($valor)) {
        return null;
    }

    $valor = trim($valor);
    $padrao = $casas === 3
        ? '/^\d{1,9}(?:[.,]\d{1,3})?$/'
        : '/^\d{1,10}(?:[.,]\d{1,2})?$/';

    if ($valor === '' || !preg_match($padrao, $valor)) {
        return null;
    }

    return (float) str_replace(',', '.', $valor);
}

function lerItensMovimentacao($linhas): array {
    if (!is_array($linhas) || $linhas === [] || count($linhas) > 100) {
        redirecionarMovimentacao('dados_invalidos');
    }

    $itens = [];
    $quantidades = [];
    foreach ($linhas as $linha) {
        if (!is_array($linha)) {
            redirecionarMovimentacao('dados_invalidos');
        }

        $produtoId = filter_var($linha['produto'] ?? null, FILTER_VALIDATE_INT);
        $quantidade = decimalMovimentacaoValido($linha['quantidade'] ?? null, 3);
        $precoCusto = decimalMovimentacaoValido($linha['preco_custo'] ?? null, 2);
        $precoVenda = decimalMovimentacaoValido($linha['preco_venda'] ?? null, 2);

        if ($produtoId === false || $produtoId < 1
            || $quantidade === null || $quantidade <= 0
            || $precoCusto === null || $precoVenda === null) {
            redirecionarMovimentacao('dados_invalidos');
        }

        $itens[] = [
            'produto' => $produtoId,
            'quantidade' => number_format($quantidade, 3, '.', ''),
            'preco_custo' => number_format($precoCusto, 2, '.', ''),
            'preco_venda' => number_format($precoVenda, 2, '.', ''),
        ];
        $quantidades[$produtoId] = round(($quantidades[$produtoId] ?? 0) + $quantidade, 3);
    }

    return [$itens, $quantidades];
}

function sinalTipoMovimentacao(string $tipo): int {
    if ($tipo === 'entrada') {
        return 1;
    }
    if ($tipo === 'saida') {
        return -1;
    }
    throw new RuntimeException('Tipo de movimentação existente inválido.');
}

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

if (!usuarioPodeAcessarEstoque()) {
    header('Location: /usuario/index.php?erro=permissao_estoque');
    exit;
}

if ((int) $_SESSION['usuario']->processar !== 1) {
    redirecionarMovimentacao('permissao');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionarMovimentacao('dados_invalidos');
}

$idEmpresa = (int) $_SESSION['usuario']->id_empresa;
$acao = $_POST['acao'] ?? 'adicionar';
$idMovimento = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!is_string($acao) || !in_array($acao, ['adicionar', 'editar', 'excluir'], true)) {
    redirecionarMovimentacao('dados_invalidos');
}
if ($acao !== 'adicionar' && ($idMovimento === false || $idMovimento < 1)) {
    redirecionarMovimentacao('dados_invalidos');
}

$itensNovos = [];
$quantidadesNovas = [];
$tipoNovo = null;
$dataNova = null;
$cadastroNovo = null;
if ($acao !== 'excluir') {
    $tipoNovo = $_POST['tipo'] ?? '';
    $data = $_POST['data'] ?? '';
    $cadastroInformado = $_POST['cadastro'] ?? '';
    $tipoNovo = is_string($tipoNovo) ? $tipoNovo : '';
    $data = is_string($data) ? trim($data) : '';
    $cadastroInformado = is_string($cadastroInformado) ? $cadastroInformado : false;
    $cadastroNovo = $cadastroInformado === ''
        ? null
        : filter_var($cadastroInformado, FILTER_VALIDATE_INT);
    $dataObj = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
    $errosData = DateTimeImmutable::getLastErrors();

    if (!in_array($tipoNovo, ['entrada', 'saida'], true)
        || !$dataObj
        || $dataObj->format('Y-m-d') !== $data
        || ($errosData && ($errosData['warning_count'] > 0 || $errosData['error_count'] > 0))
        || ($cadastroInformado !== '' && ($cadastroNovo === false || $cadastroNovo < 1))
        || ($acao === 'adicionar' && ($cadastroNovo === null || $cadastroNovo === false || $cadastroNovo < 1))) {
        redirecionarMovimentacao('dados_invalidos');
    }
    $dataNova = $dataObj->format('Y-m-d') . ' 00:00:00';
    [$itensNovos, $quantidadesNovas] = lerItensMovimentacao($_POST['itens'] ?? null);

    if ($cadastroNovo !== null
        && !Cadastro::read(id: $cadastroNovo, id_empresa: $idEmpresa)) {
        redirecionarMovimentacao('dados_invalidos');
    }
}

$pdo = (new Database())->connect();
$pdo->beginTransaction();

try {
    $movimentoAnterior = null;
    $itensAnteriores = [];
    $quantidadesAnteriores = [];
    if ($acao !== 'adicionar') {
        $movimentoAnterior = Mov01::read(
            id: $idMovimento,
            id_empresa: $idEmpresa,
            pdo: $pdo,
            forUpdate: true
        )[0] ?? null;

        if (!$movimentoAnterior) {
            $pdo->rollBack();
            redirecionarMovimentacao('nao_encontrado');
        }

        $itensAnteriores = Mov02::read(
            id_empresa: $idEmpresa,
            id_mov01: (int) $movimentoAnterior->id,
            pdo: $pdo,
            forUpdate: true
        );
        foreach ($itensAnteriores as $itemAnterior) {
            $produtoId = (int) $itemAnterior->id_produto;
            $quantidadesAnteriores[$produtoId] = round(
                ($quantidadesAnteriores[$produtoId] ?? 0) + (float) $itemAnterior->quantidade,
                3
            );
        }
    }

    $idsProdutos = array_unique(array_merge(
        array_keys($quantidadesAnteriores),
        array_keys($quantidadesNovas)
    ));
    sort($idsProdutos, SORT_NUMERIC);
    foreach ($idsProdutos as $produtoId) {
        $produto = Prod01::read(
            id: $produtoId,
            id_empresa: $idEmpresa,
            pdo: $pdo,
            forUpdate: true
        )[0] ?? null;

        if (!$produto) {
            $pdo->rollBack();
            redirecionarMovimentacao('dados_invalidos');
        }

        $estoqueAtual = (float) $produto->estoque;
        if ($movimentoAnterior) {
            $estoqueAtual -= sinalTipoMovimentacao((string) $movimentoAnterior->tipo)
                * ($quantidadesAnteriores[$produtoId] ?? 0);
        }
        if ($acao !== 'excluir') {
            $estoqueAtual += sinalTipoMovimentacao((string) $tipoNovo)
                * ($quantidadesNovas[$produtoId] ?? 0);
        }
        $novoEstoque = round($estoqueAtual, 3);

        if ($novoEstoque < 0) {
            $pdo->rollBack();
            redirecionarMovimentacao('estoque_insuficiente');
        }
        if ($novoEstoque > 999999999.999) {
            $pdo->rollBack();
            redirecionarMovimentacao('dados_invalidos');
        }

        $produto->estoque = number_format($novoEstoque, 3, '.', '');
        if (!Prod01::update($produto, $pdo)) {
            throw new RuntimeException('Não foi possível atualizar o estoque do produto.');
        }
    }

    if ($acao === 'adicionar') {
        $movimento = new Mov01(
            id_empresa: $idEmpresa,
            data: $dataNova,
            tipo: $tipoNovo,
            operacional: 'M',
            id_cadastro: $cadastroNovo
        );
        if (!Mov01::create($movimento, $pdo)) {
            throw new RuntimeException('Não foi possível criar o movimento de estoque.');
        }
        $idMovimentoCriado = $movimento->id;
    } elseif ($acao === 'editar') {
        $movimentoAnterior->data = $dataNova;
        $movimentoAnterior->tipo = $tipoNovo;
        $movimentoAnterior->id_cadastro = $cadastroNovo;
        if (!Mov01::update($movimentoAnterior, $pdo)) {
            throw new RuntimeException('Não foi possível atualizar o movimento de estoque.');
        }
        $idMovimentoCriado = $movimentoAnterior->id;
        if (!Mov02::deleteByMov01($idMovimentoCriado, $idEmpresa, $pdo)) {
            throw new RuntimeException('Não foi possível substituir os itens do movimento.');
        }
    } else {
        $idMovimentoCriado = $movimentoAnterior->id;
        if (!Mov02::deleteByMov01($idMovimentoCriado, $idEmpresa, $pdo)
            || !Mov01::delete($idMovimento, $idEmpresa, $pdo)) {
            throw new RuntimeException('Não foi possível excluir o movimento de estoque.');
        }
    }

    if ($acao !== 'excluir') {
        foreach ($itensNovos as $item) {
            $movimentoItem = new Mov02(
                id_empresa: $idEmpresa,
                id_produto: $item['produto'],
                id_mov01: $idMovimentoCriado,
                quantidade: $item['quantidade'],
                preco_custo: $item['preco_custo'],
                preco_venda: $item['preco_venda']
            );
            if (!Mov02::create($movimentoItem, $pdo)) {
                throw new RuntimeException('Não foi possível salvar um item do movimento.');
            }
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Falha ao processar movimentação de estoque: ' . $e->getMessage());
    redirecionarMovimentacao('falha');
}

$resultado = [
    'adicionar' => 'salvo',
    'editar' => 'editado',
    'excluir' => 'excluido',
][$acao];
redirecionarMovimentacao($resultado);
