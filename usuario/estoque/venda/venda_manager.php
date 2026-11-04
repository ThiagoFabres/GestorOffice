<?php
require_once __DIR__ . '/../../../db/base.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/recebimentos.php';
require_once __DIR__ . '/../../../db/entities/estoque/cx01.php';
require_once __DIR__ . '/../../../db/entities/estoque/prod01.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov01.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov02.php';
require_once __DIR__ . '/../permissao.php';
session_start();

date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario']) || (int) $_SESSION['usuario']->cargo !== 3) {
    header('Location: /');
    exit;
}

if (!usuarioPodeAcessarEstoque()) {
    header('Location: /usuario/index.php?erro=permissao_estoque');
    exit;
}

if ((int) $_SESSION['usuario']->processar !== 1) {
    header('Location: cadastro/cadastro.php?erro=permissao');
    exit;
}

$idEmpresa = (int) $_SESSION['usuario']->id_empresa;
$idUsuario = (int) $_SESSION['usuario']->id;
$hoje = date('Y-m-d');

function redirecionarVenda(string $resultado): void {
    header('Location: venda.php?resultado=' . rawurlencode($resultado));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['acao'] ?? '') === 'buscar_produto') {
    header('Content-Type: application/json; charset=utf-8');
    $codigo = $_GET['codigo'] ?? '';
    if (!is_string($codigo) || trim($codigo) === '') {
        http_response_code(400);
        echo json_encode(['erro' => 'Informe um código de barras.']);
        exit;
    }

    $produtosEncontrados = Prod01::read(
        id_empresa: $idEmpresa,
        cod_barra: trim($codigo)
    );

    if (count($produtosEncontrados) !== 1) {
        http_response_code(count($produtosEncontrados) > 1 ? 409 : 404);
        echo json_encode([
            'erro' => count($produtosEncontrados) > 1
                ? 'Há mais de um produto com esse código de barras.'
                : 'Produto não encontrado pelo código de barras.',
        ]);
        exit;
    }

    $produto = $produtosEncontrados[0];
    echo json_encode([
        'produto' => [
            'id' => (int) $produto->id,
            'descricao' => (string) $produto->descricao,
            'unidade' => (string) ($produto->unidade ?? ''),
            'cod_barra' => (string) ($produto->cod_barra ?? ''),
            'preco_venda' => (float) $produto->preco_venda,
            'estoque' => (float) $produto->estoque,
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionarVenda('acao_invalida');
}

$tokenEnviado = $_POST['csrf_token'] ?? '';
if (!is_string($tokenEnviado)
    || !isset($_SESSION['csrf_venda'])
    || !hash_equals($_SESSION['csrf_venda'], $tokenEnviado)) {
    redirecionarVenda('sessao_expirada');
}

$acao = $_POST['acao'] ?? '';
if ($acao === 'abrir_caixa') {
    if (Cx01::read(
        id_empresa: $idEmpresa,
        id_usuario: $idUsuario,
        data: $hoje,
        read_duplicidade: true
    )) {
        redirecionarVenda('caixa_existente');
    }

    $fundo = $_POST['fundo_caixa'] ?? '';
    if (!is_string($fundo)) {
        redirecionarVenda('dados_invalidos');
    }
    $fundo = trim($fundo);
    if (!preg_match('/^\d{1,9}(?:[.,]\d{1,2})?$/', $fundo)) {
        redirecionarVenda('dados_invalidos');
    }
    $fundo = number_format((float) str_replace(',', '.', $fundo), 2, '.', '');

    $caixa = new Cx01(
        id_empresa: $idEmpresa,
        id_usuario: $idUsuario,
        horario_inicio: date('Y-m-d H:i:s'),
        horario_fim: null,
        fundo_caixa: $fundo
    );
    if (!Cx01::create($caixa)) {
        throw new RuntimeException('Não foi possível abrir o caixa.');
    }
    redirecionarVenda('caixa_aberto');
}

if ($acao !== 'finalizar_venda') {
    redirecionarVenda('acao_invalida');
}

$itensJson = $_POST['itens'] ?? '';
$itensRecebidos = is_string($itensJson) ? json_decode($itensJson, true) : null;
$tipoPagamentoInformado = $_POST['tipo_pagamento'] ?? '';
$clienteInformado = $_POST['cliente'] ?? 'consumidor_final';
$idTipoPagamento = is_string($tipoPagamentoInformado)
    ? filter_var($tipoPagamentoInformado, FILTER_VALIDATE_INT)
    : false;

if (!is_array($itensRecebidos)
    || $itensRecebidos === []
    || count($itensRecebidos) > 100
    || !is_string($tipoPagamentoInformado)
    || ($tipoPagamentoInformado !== 'crediario'
        && ($idTipoPagamento === false || $idTipoPagamento < 1))
    || !is_string($clienteInformado)) {
    redirecionarVenda('dados_invalidos');
}

$quantidades = [];
foreach ($itensRecebidos as $item) {
    if (!is_array($item)) {
        redirecionarVenda('dados_invalidos');
    }
    $idProduto = filter_var($item['produto'] ?? null, FILTER_VALIDATE_INT);
    $quantidade = $item['quantidade'] ?? null;
    if ($idProduto === false || $idProduto < 1 || !is_string($quantidade)) {
        redirecionarVenda('dados_invalidos');
    }
    $quantidade = trim($quantidade);
    if (!preg_match('/^\d{1,9}(?:[.,]\d{1,3})?$/', $quantidade)) {
        redirecionarVenda('dados_invalidos');
    }
    $quantidade = (float) str_replace(',', '.', $quantidade);
    if ($quantidade <= 0) {
        redirecionarVenda('dados_invalidos');
    }
    $quantidades[$idProduto] = round(($quantidades[$idProduto] ?? 0) + $quantidade, 3);
}

if ($quantidades === []) {
    redirecionarVenda('dados_invalidos');
}

$pdo = (new Database())->connect();
$pdo->beginTransaction();

try {
    if (Empresa::read(id: $idEmpresa, pdo: $pdo, forUpdate: true) === []) {
        throw new RuntimeException('Empresa da venda não encontrada.');
    }

    $caixaAberto = Cx01::read(
        id_empresa: $idEmpresa,
        id_usuario: $idUsuario,
        data: $hoje,
        read_duplicidade: true,
        pdo: $pdo,
        forUpdate: true
    )[0] ?? null;
    if ($caixaAberto === null) {
        throw new DomainException('CAIXA_FECHADO');
    }
    $idCaixa = (int) $caixaAberto->id;

    $tiposPagamento = TipoPagamento::read(idempresa: $idEmpresa, pdo: $pdo);
    $tipoPagamentoSelecionado = null;
    if ($tipoPagamentoInformado === 'crediario') {
        foreach ($tiposPagamento as $tipoPagamento) {
            if (TipoPagamento::ehCrediario($tipoPagamento->nome)) {
                $tipoPagamentoSelecionado = $tipoPagamento;
                break;
            }
        }
        if ($tipoPagamentoSelecionado === null) {
            $tipoPagamentoSelecionado = new TipoPagamento(
                id_empresa: $idEmpresa,
                nome: 'Crediário'
            );
            if (!TipoPagamento::create($tipoPagamentoSelecionado, $pdo)) {
                throw new RuntimeException('Não foi possível criar o tipo de pagamento Crediário.');
            }
        }
    } else {
        foreach ($tiposPagamento as $tipoPagamento) {
            if ((int) $tipoPagamento->id === (int) $idTipoPagamento) {
                $tipoPagamentoSelecionado = $tipoPagamento;
                break;
            }
        }
        if ($tipoPagamentoSelecionado === null) {
            throw new DomainException('TIPO_PAGAMENTO_INVALIDO');
        }
    }
    $idTipoPagamento = (int) $tipoPagamentoSelecionado->id;
    $crediario = TipoPagamento::ehCrediario($tipoPagamentoSelecionado->nome);

    if ($clienteInformado === 'consumidor_final') {
        $cadastros = Cadastro::read(
            id_empresa: $idEmpresa,
            nome: 'Consumidor Final',
            pdo: $pdo
        );
        $cliente = null;
        foreach ($cadastros as $cadastroEncontrado) {
            if (strcasecmp(trim((string) $cadastroEncontrado->nom_fant), 'Consumidor Final') === 0) {
                $cliente = $cadastroEncontrado;
                break;
            }
        }
        if ($cliente === null) {
            $cliente = new Cadastro(
                id_empresa: $idEmpresa,
                razao_soc: 'Consumidor Final',
                nom_fant: 'Consumidor Final',
                data_r: $hoje
            );
            if (!Cadastro::create($cliente, $pdo)) {
                throw new RuntimeException('Não foi possível criar o cadastro Consumidor Final.');
            }
        }
    } else {
        $idCliente = filter_var($clienteInformado, FILTER_VALIDATE_INT);
        if ($idCliente === false || $idCliente < 1) {
            throw new DomainException('CLIENTE_INVALIDO');
        }
        $cliente = Cadastro::read(
            id: $idCliente,
            id_empresa: $idEmpresa,
            pdo: $pdo,
            forUpdate: true
        )[0] ?? null;
        if ($cliente === null) {
            throw new DomainException('CLIENTE_INVALIDO');
        }
    }

    $idsProdutos = array_keys($quantidades);
    sort($idsProdutos, SORT_NUMERIC);
    $itensVenda = [];
    $totalVenda = 0.0;

    foreach ($idsProdutos as $idProduto) {
        $produto = Prod01::read(
            id: $idProduto,
            id_empresa: $idEmpresa,
            pdo: $pdo,
            forUpdate: true
        )[0] ?? null;
        if (!$produto) {
            throw new DomainException('PRODUTO_INVALIDO');
        }

        $quantidade = $quantidades[$idProduto];
        $estoqueAtual = (float) $produto->estoque;
        if ($estoqueAtual < $quantidade) {
            throw new DomainException('ESTOQUE_INSUFICIENTE');
        }

        $precoVenda = (float) $produto->preco_venda;
        if ($precoVenda < 0) {
            throw new RuntimeException('Produto com preço de venda inválido.');
        }
        $totalVenda += round($precoVenda * $quantidade, 2);
        $produto->estoque = number_format(round($estoqueAtual - $quantidade, 3), 3, '.', '');
        if (!Prod01::update($produto, $pdo)) {
            throw new RuntimeException('Não foi possível atualizar o estoque do produto.');
        }

        $itensVenda[] = [
            'produto' => $produto,
            'quantidade' => number_format($quantidade, 3, '.', ''),
            'preco_venda' => number_format($precoVenda, 2, '.', ''),
        ];
    }

    $movimento = new Mov01(
        id_empresa: $idEmpresa,
        data: date('Y-m-d H:i:s'),
        tipo: 'saida',
        operacional: 'V',
        id_cadastro: $cliente->id_cadastro
    );
    if (!Mov01::create($movimento, $pdo)) {
        throw new RuntimeException('Não foi possível criar a saída de estoque da venda.');
    }

    foreach ($itensVenda as $itemVenda) {
        $itemMovimento = new Mov02(
            id_empresa: $idEmpresa,
            id_produto: $itemVenda['produto']->id,
            id_mov01: $movimento->id,
            quantidade: $itemVenda['quantidade'],
            preco_custo: $itemVenda['produto']->preco_custo,
            preco_venda: $itemVenda['preco_venda']
        );
        if (!Mov02::create($itemMovimento, $pdo)) {
            throw new RuntimeException('Não foi possível registrar um produto na saída de estoque.');
        }
    }

    $recebimento = new Rec01(
        id_empresa: $idEmpresa,
        id_cadastro: $cliente->id_cadastro,
        documento: Rec01::proximoDocumento($idEmpresa, $pdo),
        descricao: 'Venda balcão',
        valor: round($totalVenda, 2),
        parcelas: 1,
        data_lanc: date('Y-m-d H:i:s'),
        id_usuario: $idUsuario
    );
    if (!Rec01::create($recebimento, $pdo)) {
        throw new RuntimeException('Não foi possível criar o contas a receber da venda.');
    }

    $parcela = new Rec02(
        id_empresa: $idEmpresa,
        id_rec01: $recebimento->id,
        valor_par: round($totalVenda, 2),
        parcela: 1,
        vencimento: $hoje,
        valor_pag: $crediario ? 0 : round($totalVenda, 2),
        data_pag: $crediario ? null : $hoje,
        id_pgto: $idTipoPagamento
    );
    if (!Rec02::create($parcela, $pdo)) {
        throw new RuntimeException('Não foi possível criar a parcela do contas a receber.');
    }

    $pdo->commit();
    redirecionarVenda($crediario ? 'venda_sucesso_crediario' : 'venda_sucesso_pago');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e instanceof DomainException) {
        $resultadosConhecidos = [
            'CAIXA_FECHADO' => 'caixa_fechado',
            'TIPO_PAGAMENTO_INVALIDO' => 'tipo_pagamento_invalido',
            'CLIENTE_INVALIDO' => 'cliente_invalido',
            'PRODUTO_INVALIDO' => 'produto_invalido',
            'ESTOQUE_INSUFICIENTE' => 'estoque_insuficiente',
        ];
        if (isset($resultadosConhecidos[$e->getMessage()])) {
            redirecionarVenda($resultadosConhecidos[$e->getMessage()]);
        }
    }

    error_log('Falha ao finalizar venda do estoque: ' . $e->getMessage());
    redirecionarVenda('falha_ao_finalizar');
}
