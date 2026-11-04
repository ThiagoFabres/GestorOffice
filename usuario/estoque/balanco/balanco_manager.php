<?php
require_once __DIR__ . '/../../../db/base.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/estoque/prod01.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov01.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov02.php';
require_once __DIR__ . '/../permissao.php';
session_start();

function redirecionarBalanco(string $resultado): void {
    header('Location: balanco.php?resultado=' . rawurlencode($resultado));
    exit;
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
    header('Location: cadastro/cadastro.php?erro=permissao');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: balanco.php');
    exit;
}

$contagens = $_POST['contado'] ?? null;
if (!is_array($contagens) || $contagens === []) {
    redirecionarBalanco('nenhuma_contagem');
}

$contagensValidas = [];
foreach ($contagens as $idProduto => $valor) {
    if (!is_scalar($valor)) {
        redirecionarBalanco('dados_invalidos');
    }

    $valor = trim((string) $valor);
    if ($valor === '') {
        continue;
    }

    if (!ctype_digit((string) $idProduto) || (int) $idProduto < 1
        || !preg_match('/^\d{1,9}(?:[.,]\d{1,3})?$/', $valor)) {
        redirecionarBalanco('dados_invalidos');
    }

    $contagem = (float) str_replace(',', '.', $valor);
    if ($contagem < 0 || $contagem > 999999999.999) {
        redirecionarBalanco('dados_invalidos');
    }

    $contagensValidas[(int) $idProduto] = number_format($contagem, 3, '.', '');
}

if ($contagensValidas === []) {
    redirecionarBalanco('nenhuma_contagem');
}

$idEmpresa = (int) $_SESSION['usuario']->id_empresa;
$pdo = (new Database())->connect();
$pdo->beginTransaction();

try {
    $produtos = [];
    foreach ($contagensValidas as $idProduto => $quantidadeContada) {
        $produto = Prod01::read(
            id: $idProduto,
            id_empresa: $idEmpresa,
            pdo: $pdo,
            forUpdate: true
        )[0] ?? null;

        if (!$produto) {
            $pdo->rollBack();
            redirecionarBalanco('dados_invalidos');
        }

        $diferenca = round((float) $quantidadeContada - (float) $produto->estoque, 3);
        $produtos[] = [
            'produto' => $produto,
            'quantidade_contada' => $quantidadeContada,
            'diferenca' => $diferenca,
        ];
    }

    $entradas = [];
    $saidas = [];
    foreach ($produtos as $registro) {
        $produto = $registro['produto'];
        $diferenca = $registro['diferenca'];

        if ($diferenca > 0) {
            $entradas[] = [$produto, number_format($diferenca, 3, '.', '')];
        } elseif ($diferenca < 0) {
            $saidas[] = [$produto, number_format(abs($diferenca), 3, '.', '')];
        }

        if ($diferenca !== 0.0) {
            $produto->estoque = $registro['quantidade_contada'];
            if (!Prod01::update($produto, $pdo)) {
                throw new RuntimeException('Não foi possível atualizar o estoque do produto.');
            }
        }
    }

    $cadastroBalanco = null;
    if ($entradas !== [] || $saidas !== []) {
        $cadastros = Cadastro::read(id_empresa: $idEmpresa, pdo: $pdo);
        foreach ($cadastros as $cadastro) {
            if (strcasecmp(trim((string) $cadastro->nom_fant), 'Balanço') === 0
                || strcasecmp(trim((string) $cadastro->razao_soc), 'Balanço') === 0) {
                $cadastroBalanco = $cadastro;
                break;
            }
        }

        if (!$cadastroBalanco) {
            $cadastroBalanco = new Cadastro(
                id_empresa: $idEmpresa,
                razao_soc: 'Balanço',
                nom_fant: 'Balanço',
                data_r: date('Y-m-d')
            );
            if (!Cadastro::create($cadastroBalanco, $pdo)) {
                throw new RuntimeException('Não foi possível criar o cadastro do balanço.');
            }
        }
    }

    foreach ([
        'entrada' => $entradas,
        'saida' => $saidas,
    ] as $tipo => $itens) {
        if ($itens === []) {
            continue;
        }

        $movimento = new Mov01(
            id_empresa: $idEmpresa,
            data: date('Y-m-d H:i:s'),
            tipo: $tipo,
            operacional: 'B',
            id_cadastro: $cadastroBalanco->id_cadastro
        );
        if (!Mov01::create($movimento, $pdo)) {
            throw new RuntimeException('Não foi possível criar o movimento de estoque.');
        }

        foreach ($itens as [$produto, $quantidade]) {
            $item = new Mov02(
                id_empresa: $idEmpresa,
                id_produto: $produto->id,
                id_mov01: $movimento->id,
                quantidade: $quantidade,
                preco_custo: $produto->preco_custo,
                preco_venda: $produto->preco_venda
            );
            if (!Mov02::create($item, $pdo)) {
                throw new RuntimeException('Não foi possível criar um item do movimento.');
            }
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Falha ao processar balanço de estoque: ' . $e->getMessage());
    redirecionarBalanco('falha');
}

redirecionarBalanco($entradas === [] && $saidas === [] ? 'sem_alteracoes' : 'processado');
