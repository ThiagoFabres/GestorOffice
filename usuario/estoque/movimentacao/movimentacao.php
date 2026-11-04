<?php
require_once __DIR__ . '/../../../db/base.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov01.php';
require_once __DIR__ . '/../../../db/entities/estoque/mov02.php';
require_once __DIR__ . '/../../../db/entities/estoque/prod01.php';
require_once __DIR__ . '/../../../db/entities/estoque/gru01.php';
require_once __DIR__ . '/../../../db/entities/estoque/gru02.php';
require_once __DIR__ . '/../permissao.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}

if (!usuarioPodeAcessarEstoque()) {
    header('Location: /usuario/index.php?erro=permissao_estoque');
    exit;
}

$idEmpresa = (int) $_SESSION['usuario']->id_empresa;
$hoje = new DateTimeImmutable('today');
$dataInicial = filter_input(INPUT_GET, 'data_inicial', FILTER_UNSAFE_RAW);
$dataFinal = filter_input(INPUT_GET, 'data_final', FILTER_UNSAFE_RAW);
$tipo = filter_input(INPUT_GET, 'tipo', FILTER_UNSAFE_RAW);
$numeroExibir = filter_input(INPUT_GET, 'numero_exibido', FILTER_VALIDATE_INT);
$numeroPagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
$opcoesNumeroExibir = [5, 10, 20, 30, 40, 50];
$numeroExibir = in_array($numeroExibir, $opcoesNumeroExibir, true) ? $numeroExibir : 10;
$numeroPagina = is_int($numeroPagina) && $numeroPagina > 0 ? $numeroPagina : 1;
$dataInicial = is_string($dataInicial) ? trim($dataInicial) : '';
$dataFinal = is_string($dataFinal) ? trim($dataFinal) : '';
$tipo = is_string($tipo) ? trim($tipo) : '';
$filtroGrupo = filter_input(INPUT_GET, 'grupo', FILTER_VALIDATE_INT);
$filtroSubgrupo = filter_input(INPUT_GET, 'subgrupo', FILTER_VALIDATE_INT);
$filtroProduto = filter_input(INPUT_GET, 'produto', FILTER_VALIDATE_INT);
$filtroCadastro = filter_input(INPUT_GET, 'cadastro', FILTER_VALIDATE_INT);
$filtroGrupo = is_int($filtroGrupo) && $filtroGrupo > 0 ? $filtroGrupo : null;
$filtroSubgrupo = is_int($filtroSubgrupo) && $filtroSubgrupo > 0 ? $filtroSubgrupo : null;
$filtroProduto = is_int($filtroProduto) && $filtroProduto > 0 ? $filtroProduto : null;
$filtroCadastro = is_int($filtroCadastro) && $filtroCadastro > 0 ? $filtroCadastro : null;
$erroFiltro = false;

function dataValidaMovimentacao(string $data): bool {
    $dataObj = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
    $erros = DateTimeImmutable::getLastErrors();
    return $dataObj !== false
        && $dataObj->format('Y-m-d') === $data
        && (!$erros || ($erros['warning_count'] === 0 && $erros['error_count'] === 0));
}

function escaparMovimentacao($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function formatarQuantidadeMovimentacao($valor): string {
    return number_format((float) $valor, 3, ',', '.');
}

function formatarValorMovimentacao($valor): string {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}


$dataFinalExclusiva = (new DateTimeImmutable($dataFinal))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
$produtos = Prod01::read(id_empresa: $idEmpresa);
$cadastros = Cadastro::read(id_empresa: $idEmpresa);
$cadastrosPorId = [];
foreach ($cadastros as $cadastro) {
    $cadastrosPorId[(int) $cadastro->id_cadastro] = $cadastro;
}
$filtroCadastro = isset($cadastrosPorId[$filtroCadastro ?? 0]) ? $filtroCadastro : null;
$produtosPorId = [];
foreach ($produtos as $produto) {
    $produtosPorId[(int) $produto->id] = $produto;
}
$grupos = Gru01::read(id_empresa: $idEmpresa);
$gruposPorId = [];
foreach ($grupos as $grupo) {
    $gruposPorId[(int) $grupo->id] = $grupo;
}
$subgrupos = Gru02::read(id_empresa: $idEmpresa);
$subgruposPorId = [];
foreach ($subgrupos as $subgrupo) {
    $subgruposPorId[(int) $subgrupo->id] = $subgrupo;
}
$filtroGrupo = isset($gruposPorId[$filtroGrupo ?? 0]) ? $filtroGrupo : null;
$filtroSubgrupo = isset($subgruposPorId[$filtroSubgrupo ?? 0]) ? $filtroSubgrupo : null;
$filtroProduto = isset($produtosPorId[$filtroProduto ?? 0]) ? $filtroProduto : null;
$movimentosObjetos = Mov01::read(
    id_empresa: $idEmpresa,
    data_inicial: $dataInicial . ' 00:00:00',
    data_final: $dataFinalExclusiva,
    tipo: $tipo !== '' ? $tipo : null,
    id_cadastro: $filtroCadastro
);

$movimentacoes = [];
$itensPorMovimento = [];
$itensEditaveisPorMovimento = [];
$totalEntradas = 0.0;
$totalSaidas = 0.0;
foreach ($movimentosObjetos as $movimento) {
    $idMovimento = (int) $movimento->id;
    $documento = (int) ($movimento->documento ?? $movimento->id);
    $cadastroMovimento = $cadastrosPorId[(int) ($movimento->id_cadastro ?? 0)] ?? null;
    $nomeCadastroMovimento = $cadastroMovimento
        ? trim((string) ($cadastroMovimento->nom_fant ?: $cadastroMovimento->razao_soc))
        : '';
    $itens = Mov02::read(id_empresa: $idEmpresa, id_mov01: $idMovimento);
    $itensEditaveisPorMovimento[$idMovimento] = array_map(
        static fn (Mov02 $item): array => [
            'id_produto' => (int) $item->id_produto,
            'quantidade' => (float) $item->quantidade,
            'preco_custo' => (float) $item->preco_custo,
            'preco_venda' => (float) $item->preco_venda,
        ],
        $itens
    );
    $quantidadeTotal = 0.0;
    $valorCustoTotal = 0.0;
    $valorVendaTotal = 0.0;
    $itensDetalhados = [];

    foreach ($itens as $item) {
        $produto = $produtosPorId[(int) $item->id_produto] ?? null;
        if (($filtroGrupo !== null && (int) ($produto->id_grupo ?? 0) !== $filtroGrupo)
            || ($filtroSubgrupo !== null && (int) ($produto->id_subgrupo ?? 0) !== $filtroSubgrupo)
            || ($filtroProduto !== null && (int) $item->id_produto !== $filtroProduto)) {
            continue;
        }
        $grupo = $produto ? ($gruposPorId[(int) $produto->id_grupo] ?? null) : null;
        $subgrupo = $produto ? ($subgruposPorId[(int) $produto->id_subgrupo] ?? null) : null;
        $quantidade = (float) $item->quantidade;
        $precoCusto = (float) $item->preco_custo;
        $precoVenda = (float) $item->preco_venda;
        $quantidadeTotal += $quantidade;
        $valorCustoTotal += $quantidade * $precoCusto;
        $valorVendaTotal += $quantidade * $precoVenda;
        $itensDetalhados[] = [
            'produto' => $produto->descricao ?? 'Produto removido',
            'unidade' => $produto->unidade ?? null,
            'grupo' => $grupo->descricao ?? '—',
            'subgrupo' => $subgrupo->descricao ?? '—',
            'quantidade' => $quantidade,
            'preco_custo' => $precoCusto,
            'preco_venda' => $precoVenda,
        ];
    }

    if ($movimento->tipo === 'entrada') {
        $totalEntradas += $quantidadeTotal;
    } elseif ($movimento->tipo === 'saida') {
        $totalSaidas += $quantidadeTotal;
    }

    $movimentacoes[] = [
        'id' => $movimento->id,
        'documento' => $movimento->documento,
        'data' => $movimento->data,
        'tipo' => $movimento->tipo,
        'cadastro_id' => $movimento->id_cadastro,
        'cadastro' => $nomeCadastroMovimento !== '' ? $nomeCadastroMovimento : null,
        'quantidade_itens' => count($itensDetalhados),
        'quantidade_total' => $quantidadeTotal,
        'valor_custo_total' => $valorCustoTotal,
        'valor_venda_total' => $valorVendaTotal,
    ];
    $itensPorMovimento[$idMovimento] = $itensDetalhados;
}
$haFiltroItens = $filtroGrupo !== null || $filtroSubgrupo !== null || $filtroProduto !== null;
$movimentacoes = array_values(array_filter(
    $movimentacoes,
    static fn (array $movimentacao): bool => !$haFiltroItens || $movimentacao['quantidade_itens'] > 0
));
$totalItens = array_sum(array_column($movimentacoes, 'quantidade_itens'));
$totalMovimentacoes = count($movimentacoes);
$totalPaginas = max(1, (int) ceil($totalMovimentacoes / $numeroExibir));
$numeroPagina = min($numeroPagina, $totalPaginas);
$inicioExibicao = ($numeroPagina - 1) * $numeroExibir;
$movimentacoesExibidas = array_slice($movimentacoes, $inicioExibicao, $numeroExibir);
$dadosExportacao = array_map(
    static function (array $movimentacao) use ($itensPorMovimento): array {
        $idMovimento = (int) $movimentacao['id'];
        $tipo = (string) $movimentacao['tipo'];

        return [
            'documento' => (string) ($movimentacao['documento'] ?? $movimentacao['id']),
            'data' => (new DateTimeImmutable($movimentacao['data']))->format('d/m/Y H:i'),
            'tipo' => $tipo === 'entrada' ? 'Entrada' : ($tipo === 'saida' ? 'Saída' : ucfirst($tipo)),
            'cadastro' => $movimentacao['cadastro'] ?? '—',
            'quantidade_itens' => (int) $movimentacao['quantidade_itens'],
            'quantidade_total' => (float) $movimentacao['quantidade_total'],
            'valor_custo_total' => (float) $movimentacao['valor_custo_total'],
            'valor_venda_total' => (float) $movimentacao['valor_venda_total'],
            'itens' => $itensPorMovimento[$idMovimento] ?? [],
        ];
    },
    $movimentacoes
);
$resultadoMovimentacao = filter_input(INPUT_GET, 'resultado', FILTER_UNSAFE_RAW) ?? '';
if (!is_string($resultadoMovimentacao)) {
    $resultadoMovimentacao = '';
}
$mensagensMovimentacao = [
    'salvo' => ['success', 'Movimentação e itens salvos; o estoque foi atualizado.'],
    'editado' => ['success', 'Movimentação atualizada e estoque recalculado.'],
    'excluido' => ['success', 'Movimentação excluída e estoque recalculado.'],
    'dados_invalidos' => ['danger', 'Confira os dados informados e selecione produtos válidos.'],
    'estoque_insuficiente' => ['warning', 'Esta movimentação deixaria o estoque negativo para um ou mais produtos.'],
    'falha' => ['danger', 'Não foi possível salvar a movimentação. Tente novamente.'],
    'permissao' => ['warning', 'Seu usuário não tem permissão para registrar movimentações.'],
    'nao_encontrado' => ['warning', 'A movimentação não foi encontrada ou não pertence à sua empresa.'],
];
$mensagemMovimentacao = $mensagensMovimentacao[$resultadoMovimentacao] ?? null;

$lateral_estoque = true;
$lateral_target = 'estoque_movimentacao';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="/gestor-office.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="/choices/choices.css">
    <style>
        .detalhes-movimento summary {
            cursor: pointer;
            color: #0d6efd;
        }

        .detalhes-movimento .tabela-itens-movimento th,
        .detalhes-movimento .tabela-itens-movimento td {
            background-color: #fff !important;
            color: #212529 !important;
        }

        #filtrosMovimentacao .card-body {
            overflow: visible;
        }

        #filtrosMovimentacao .choices {
            position: relative;
            z-index: 1;
        }

        #filtrosMovimentacao .choices.is-open {
            z-index: 1080;
        }

        .resumo-movimentacoes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .resumo-movimentacoes .card {
            min-height: 90px;
        }
    </style>
</head>
<body id="body">
    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php'; ?>

    <main class="main" id="container">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h1 class="h3 mb-0">Movimentação de estoque</h1>
                <div class="d-flex gap-2">
                    <?php if ((int) $_SESSION['usuario']->processar === 1) { ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalNovaMovimentacao">
                            <i class="bi bi-plus-lg"></i> Nova movimentação
                        </button>
                    <?php } ?>
                </div>
            </div>

            <?php if ($mensagemMovimentacao) { ?>
                <div class="alert alert-<?= escaparMovimentacao($mensagemMovimentacao[0]) ?>" role="alert">
                    <?= escaparMovimentacao($mensagemMovimentacao[1]) ?>
                </div>
            <?php } ?>

            <?php if ($erroFiltro) { ?>
                <div class="alert alert-warning" role="alert">
                    O período ou tipo informado é inválido. Foram exibidos os movimentos deste mês.
                </div>
            <?php } ?>

            <form method="get" id="filtrosMovimentacao" class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="data_inicial" class="form-label">Data inicial</label>
                            <input type="date" id="data_inicial" name="data_inicial" class="form-control"
                                value="<?= escaparMovimentacao($dataInicial) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="data_final" class="form-label">Data final</label>
                            <input type="date" id="data_final" name="data_final" class="form-control"
                                value="<?= escaparMovimentacao($dataFinal) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo de movimento</label>
                            <select id="tipo" name="tipo" class="form-select">
                                <option value="" <?= $tipo === '' ? 'selected' : '' ?>>Todos</option>
                                <option value="entrada" <?= $tipo === 'entrada' ? 'selected' : '' ?>>Entradas</option>
                                <option value="saida" <?= $tipo === 'saida' ? 'selected' : '' ?>>Saídas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtroGrupo" class="form-label">Grupo</label>
                            <select id="filtroGrupo" name="grupo" class="form-select">
                                <option value="">Todos os grupos</option>
                                <?php foreach ($grupos as $grupo) { ?>
                                    <option value="<?= (int) $grupo->id ?>"
                                        <?= $filtroGrupo === (int) $grupo->id ? 'selected' : '' ?>>
                                        <?= escaparMovimentacao($grupo->descricao) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroCadastro" class="form-label">Cliente/fornecedor</label>
                            <select id="filtroCadastro" name="cadastro" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($cadastros as $cadastro) {
                                    $nomeCadastro = trim((string) ($cadastro->nom_fant ?: $cadastro->razao_soc));
                                    ?>
                                    <option value="<?= (int) $cadastro->id_cadastro ?>"
                                        <?= $filtroCadastro === (int) $cadastro->id_cadastro ? 'selected' : '' ?>>
                                        <?= escaparMovimentacao($nomeCadastro) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroSubgrupo" class="form-label">Subgrupo</label>
                            <select id="filtroSubgrupo" name="subgrupo" class="form-select">
                                <option value="">Todos os subgrupos</option>
                                <?php foreach ($subgrupos as $subgrupo) { ?>
                                    <option value="<?= (int) $subgrupo->id ?>"
                                        data-grupo-id="<?= (int) $subgrupo->id_grupo ?>"
                                        <?= $filtroSubgrupo === (int) $subgrupo->id ? 'selected' : '' ?>>
                                        <?= escaparMovimentacao($subgrupo->descricao) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroProduto" class="form-label">Produto</label>
                            <select id="filtroProduto" name="produto" class="form-select">
                                <option value="">Todos os produtos</option>
                                <?php foreach ($produtos as $produto) { ?>
                                    <option value="<?= (int) $produto->id ?>"
                                        data-grupo-id="<?= (int) $produto->id_grupo ?>"
                                        data-subgrupo-id="<?= (int) $produto->id_subgrupo ?>"
                                        <?= $filtroProduto === (int) $produto->id ? 'selected' : '' ?>>
                                        <?= escaparMovimentacao($produto->descricao) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <button type="button" class="btn btn-outline-secondary" id="limparFiltrosMovimentacao">
                                Limpar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_adicionar_movimentacao.php'; ?>
            <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_excluir_movimentacao.php'; ?>

            <template id="modeloItemMovimentacao">
                <tr>
                    <td>
                        <select class="form-select" data-campo="produto" required>
                            <option value="">Selecione</option>
                            <?php foreach ($produtos as $produto) { ?>
                                <option value="<?= (int) $produto->id ?>"
                                    data-preco-custo="<?= escaparMovimentacao(number_format((float) $produto->preco_custo, 2, '.', '')) ?>"
                                    data-preco-venda="<?= escaparMovimentacao(number_format((float) $produto->preco_venda, 2, '.', '')) ?>">
                                    <?= escaparMovimentacao($produto->descricao) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <input class="form-control" data-campo="quantidade" type="number" min="0.001"
                            step="0.001" value="1" required>
                    </td>
                    <td>
                        <input class="form-control" data-campo="preco_custo" type="number" min="0"
                            step="0.01" value="0.00" required>
                    </td>
                    <td>
                        <input class="form-control" data-campo="preco_venda" type="number" min="0"
                            step="0.01" value="0.00" required>
                    </td>
                    <td class="text-end" data-total-item>R$ 0,00</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remover-item
                            aria-label="Remover produto">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            </template>

            <template id="modeloItemEdicaoMovimentacao">
                <tr data-item-editor>
                    <td>
                        <select class="form-select" data-campo="produto" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto) { ?>
                                <option value="<?= (int) $produto->id ?>"
                                    data-preco-custo="<?= escaparMovimentacao(number_format((float) $produto->preco_custo, 2, '.', '')) ?>"
                                    data-preco-venda="<?= escaparMovimentacao(number_format((float) $produto->preco_venda, 2, '.', '')) ?>">
                                    <?= escaparMovimentacao($produto->descricao) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <input class="form-control" type="number" data-campo="quantidade"
                            min="0.001" step="0.001" value="1.000" required>
                    </td>
                    <td>
                        <input class="form-control" type="number" data-campo="preco_custo"
                            min="0" step="0.01" value="0.00" required>
                    </td>
                    <td>
                        <input class="form-control" type="number" data-campo="preco_venda"
                            min="0" step="0.01" value="0.00" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            data-remover-item-editor aria-label="Remover produto">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            </template>

            <?php if (!$movimentacoes) { ?>
                <div class="alert alert-light border mb-0">
                    Nenhuma movimentação encontrada no período selecionado.
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Cliente/fornecedor</th>
                                <th>Itens</th>
                                <th>Quantidade total</th>
                                <th>Valor de custo</th>
                                <th>Valor de venda</th>
                                <th>Detalhes</th>
                                <?php if ((int) $_SESSION['usuario']->processar === 1) { ?>
                                    <th>Ações</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoesExibidas as $indice => $movimentacao) {
                                $idMovimento = (int) $movimentacao['id'];
                                $itens = $itensPorMovimento[$idMovimento] ?? [];
                                $tipoMovimento = (string) $movimentacao['tipo'];
                                $ehEntrada = $tipoMovimento === 'entrada';
                                $ehSaida = $tipoMovimento === 'saida';
                                $badge = $ehEntrada ? 'success' : ($ehSaida ? 'danger' : 'secondary');
                                $rotuloTipo = $ehEntrada ? 'Entrada' : ($ehSaida ? 'Saída' : ucfirst($tipoMovimento));
                                $itensEditaveis = $itensEditaveisPorMovimento[$idMovimento] ?? [];
                                $data = escaparMovimentacao((new DateTimeImmutable($movimentacao['data']))->format('d/m/Y'));
                                $hora = escaparMovimentacao((new DateTimeImmutable($movimentacao['data']))->format('H:i'));
                                if($hora != '00:00') {
                                    $dataHoraFormatada = $data . ' ' . $hora;
                                } else {
                                    $dataHoraFormatada = $data;
                                }
                                ?>
                                <tr class="<?= $rotuloTipo == 'Entrada' ? 'parcela_cor_verde' : 'parcela_cor_vermelha' ?>">
                                    <td><?= escaparMovimentacao($dataHoraFormatada) ?></td>
                                    <td><?= escaparMovimentacao($movimentacao['documento'] ?? $movimentacao['id']) ?></td>
                                    <td><span class="badge text-bg-<?= $badge ?>"><?= escaparMovimentacao($rotuloTipo) ?></span></td>
                                    <td><?= escaparMovimentacao($movimentacao['cadastro'] ?? '—') ?></td>
                                    <td><?= (int) $movimentacao['quantidade_itens'] ?></td>
                                    <td><?= formatarQuantidadeMovimentacao($movimentacao['quantidade_total']) ?></td>
                                    <td><?= formatarValorMovimentacao($movimentacao['valor_custo_total']) ?></td>
                                    <td><?= formatarValorMovimentacao($movimentacao['valor_venda_total']) ?></td>
                                    <td>
                                        <details class="detalhes-movimento">
                                            <summary>Ver produtos</summary>
                                            <?php if (!$itens) { ?>
                                                <p class="small text-muted mt-2 mb-0">Este movimento não possui itens associados.</p>
                                            <?php } else { ?>
                                                <div class="table-responsive mt-2">
                                                    <table class="table table-striped table-bordered align-middle mb-0 tabela-itens-movimento">
                                                        <thead>
                                                            <tr>
                                                                <th>Produto</th>
                                                                <th>Grupo</th>
                                                                <th>Subgrupo</th>
                                                                <th>Quantidade</th>
                                                                <th>Custo unitário</th>
                                                                <th>Venda unitária</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($itens as $item) { ?>
                                                                <tr>
                                                                    <td>
                                                                        <?= escaparMovimentacao($item['produto'] ?? 'Produto removido') ?>
                                                                    </td>
                                                                    <td><?= escaparMovimentacao($item['grupo'] ?? '—') ?></td>
                                                                    <td><?= escaparMovimentacao($item['subgrupo'] ?? '—') ?></td>
                                                                    <td><?= formatarQuantidadeMovimentacao($item['quantidade']) ?></td>
                                                                    <td><?= formatarValorMovimentacao($item['preco_custo']) ?></td>
                                                                    <td><?= formatarValorMovimentacao($item['preco_venda']) ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php } ?>
                                        </details>
                                    </td>
                                    <?php if ((int) $_SESSION['usuario']->processar === 1) { ?>
                                        <td class="text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-alternar-editor="<?= $idMovimento ?>"
                                                aria-controls="editorMovimentacao<?= $idMovimento ?>"
                                                aria-expanded="false" title="Editar na linha">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-excluir-movimentacao="<?= $idMovimento ?>"
                                                data-documento="<?= escaparMovimentacao($movimentacao['documento'] ?? $idMovimento) ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalExcluirMovimentacao"
                                                title="Excluir movimentação">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    <?php } ?>
                                </tr>
                                <?php if ((int) $_SESSION['usuario']->processar === 1) { ?>
                                    <tr id="editorMovimentacao<?= $idMovimento ?>" class="d-none">
                                        <td colspan="<?= (int) $_SESSION['usuario']->processar === 1 ? 10 : 9 ?>">
                                            <form method="post" action="movimentacao_manager.php"
                                                id="formEditorMovimentacao<?= $idMovimento ?>">
                                                <input type="hidden" name="acao" value="editar">
                                                <input type="hidden" name="id" value="<?= $idMovimento ?>">
                                                <div class="border rounded p-3">
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label"
                                                                for="tipoEditor<?= $idMovimento ?>">Tipo</label>
                                                            <select class="form-select" id="tipoEditor<?= $idMovimento ?>"
                                                                name="tipo" required>
                                                                <option value="entrada" <?= $tipoMovimento === 'entrada' ? 'selected' : '' ?>>Entrada</option>
                                                                <option value="saida" <?= $tipoMovimento === 'saida' ? 'selected' : '' ?>>Saída</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label"
                                                                for="dataEditor<?= $idMovimento ?>">Data</label>
                                                            <input class="form-control" type="date"
                                                                id="dataEditor<?= $idMovimento ?>" name="data"
                                                                value="<?= escaparMovimentacao((new DateTimeImmutable($movimentacao['data']))->format('Y-m-d')) ?>"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label"
                                                                for="cadastroEditor<?= $idMovimento ?>">Cliente/fornecedor</label>
                                                            <select class="form-select"
                                                                id="cadastroEditor<?= $idMovimento ?>" name="cadastro">
                                                                <option value="">Não informado</option>
                                                                <?php foreach ($cadastros as $cadastro) {
                                                                    $nomeCadastro = trim((string) ($cadastro->nom_fant ?: $cadastro->razao_soc));
                                                                    ?>
                                                                    <option value="<?= (int) $cadastro->id_cadastro ?>"
                                                                        <?= (int) ($movimentacao['cadastro_id'] ?? 0) === (int) $cadastro->id_cadastro ? 'selected' : '' ?>>
                                                                        <?= escaparMovimentacao($nomeCadastro) ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                                        <strong>Itens do movimento</strong>
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            data-adicionar-item-editor>
                                                            <i class="bi bi-plus-lg"></i> Adicionar produto
                                                        </button>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered align-middle">
                                                            <thead>
                                                                <tr>
                                                                    <th>Produto</th>
                                                                    <th>Quantidade</th>
                                                                    <th>Custo unitário</th>
                                                                    <th>Venda unitária</th>
                                                                    <th>Ação</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody data-itens-editor>
                                                                <?php foreach ($itensEditaveis as $indiceItem => $itemEditavel) { ?>
                                                                    <tr data-item-editor>
                                                                        <td>
                                                                            <select class="form-select"
                                                                                name="itens[<?= (int) $indiceItem ?>][produto]"
                                                                                data-campo="produto" required>
                                                                                <?php foreach ($produtos as $produto) { ?>
                                                                                    <option value="<?= (int) $produto->id ?>"
                                                                                        data-preco-custo="<?= escaparMovimentacao(number_format((float) $produto->preco_custo, 2, '.', '')) ?>"
                                                                                        data-preco-venda="<?= escaparMovimentacao(number_format((float) $produto->preco_venda, 2, '.', '')) ?>"
                                                                                        <?= (int) $itemEditavel['id_produto'] === (int) $produto->id ? 'selected' : '' ?>>
                                                                                        <?= escaparMovimentacao($produto->descricao) ?>
                                                                                    </option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input class="form-control" type="number"
                                                                                name="itens[<?= (int) $indiceItem ?>][quantidade]"
                                                                                data-campo="quantidade" min="0.001"
                                                                                step="0.001"
                                                                                value="<?= escaparMovimentacao(number_format($itemEditavel['quantidade'], 3, '.', '')) ?>"
                                                                                required>
                                                                        </td>
                                                                        <td>
                                                                            <input class="form-control" type="number"
                                                                                name="itens[<?= (int) $indiceItem ?>][preco_custo]"
                                                                                data-campo="preco_custo" min="0"
                                                                                step="0.01"
                                                                                value="<?= escaparMovimentacao(number_format($itemEditavel['preco_custo'], 2, '.', '')) ?>"
                                                                                required>
                                                                        </td>
                                                                        <td>
                                                                            <input class="form-control" type="number"
                                                                                name="itens[<?= (int) $indiceItem ?>][preco_venda]"
                                                                                data-campo="preco_venda" min="0"
                                                                                step="0.01"
                                                                                value="<?= escaparMovimentacao(number_format($itemEditavel['preco_venda'], 2, '.', '')) ?>"
                                                                                required>
                                                                        </td>
                                                                        <td>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-danger"
                                                                                data-remover-item-editor
                                                                                aria-label="Remover produto">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            data-cancelar-editor="<?= $idMovimento ?>">Cancelar</button>
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            Salvar edição
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
                
                <script type="application/json" id="dados-exportacao-movimentacao"><?= json_encode(
                    $dadosExportacao,
                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
                ) ?></script>
                <div class="resumo-movimentacoes mb-3">
                <div class="card"><div class="card-body">
                    <div class="text-muted small">Movimentos</div>
                    <strong><?= $totalMovimentacoes ?></strong>
                </div></div>
                <div class="card"><div class="card-body">
                    <div class="text-muted small">Itens movimentados</div>
                    <strong><?= $totalItens ?></strong>
                </div></div>
                <div class="card"><div class="card-body">
                    <div class="text-muted small">Quantidade de entrada</div>
                    <strong><?= formatarQuantidadeMovimentacao($totalEntradas) ?></strong>
                </div></div>
                <div class="card"><div class="card-body">
                    <div class="text-muted small">Quantidade de saída</div>
                    <strong><?= formatarQuantidadeMovimentacao($totalSaidas) ?></strong>
                </div></div>
            </div>
            <div class="card-footer">
                <div class="card-select-pagina">
                    <form method="get">
                        <input type="hidden" name="data_inicial" value="<?= escaparMovimentacao($dataInicial) ?>">
                        <input type="hidden" name="data_final" value="<?= escaparMovimentacao($dataFinal) ?>">
                        <input type="hidden" name="tipo" value="<?= escaparMovimentacao($tipo) ?>">
                        <input type="hidden" name="cadastro" value="<?= escaparMovimentacao($filtroCadastro) ?>">
                        <input type="hidden" name="grupo" value="<?= escaparMovimentacao($filtroGrupo) ?>">
                        <input type="hidden" name="subgrupo" value="<?= escaparMovimentacao($filtroSubgrupo) ?>">
                        <input type="hidden" name="produto" value="<?= escaparMovimentacao($filtroProduto) ?>">
                        <input type="hidden" name="numero_exibido" value="<?= $numeroExibir ?>">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++) {
                            if ((($i === ($numeroPagina + 4) || $i === ($numeroPagina - 4)) && $i !== 1)
                                && $i !== $totalPaginas) { ?>
                                ...
                                <?php continue;
                            }
                            if ((($i > ($numeroPagina + 4) || $i < ($numeroPagina - 4))
                                && $i < $totalPaginas) && $i !== 1) {
                                continue;
                            }
                            ?>
                            <button type="submit" name="pagina" class="form-control"
                                <?= $numeroPagina === $i ? 'disabled' : '' ?>
                                value="<?= $i ?>"><?= $i ?></button>
                        <?php } ?>
                    </form>
                </div>
                <div class="card-select-numero">
                    <div>
                        <form method="get">
                            <input type="hidden" name="data_inicial" value="<?= escaparMovimentacao($dataInicial) ?>">
                            <input type="hidden" name="data_final" value="<?= escaparMovimentacao($dataFinal) ?>">
                            <input type="hidden" name="tipo" value="<?= escaparMovimentacao($tipo) ?>">
                            <input type="hidden" name="cadastro" value="<?= escaparMovimentacao($filtroCadastro) ?>">
                            <input type="hidden" name="grupo" value="<?= escaparMovimentacao($filtroGrupo) ?>">
                            <input type="hidden" name="subgrupo" value="<?= escaparMovimentacao($filtroSubgrupo) ?>">
                            <input type="hidden" name="produto" value="<?= escaparMovimentacao($filtroProduto) ?>">
                            <select class="form-control" onchange="this.form.submit()" name="numero_exibido">
                                <?php foreach ($opcoesNumeroExibir as $opcaoNumeroExibir) { ?>
                                    <option value="<?= $opcaoNumeroExibir ?>"
                                        <?= $numeroExibir === $opcaoNumeroExibir ? 'selected' : '' ?>>
                                        <?= $opcaoNumeroExibir ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div style="float:left;" class="d-flex gap-2 mb-3 mt-3 w-100">
                    <button type="button" class="btn btn-outline-danger" id="botao-gerar-pdf">
                        Gerar PDF
                    </button>
                    <button type="button" class="btn btn-outline-success" id="botao-gerar-excel">
                        Gerar Excel
                    </button>
                </div>
    </main>

    <?php require_once __DIR__ . '/../../../componentes/footer/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="/choices/choices.js"></script>
    <script src="gerar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('formNovaMovimentacao');
            const linhas = document.getElementById('itensMovimentacao');
            const modelo = document.getElementById('modeloItemMovimentacao');
            const adicionar = document.getElementById('adicionarItemMovimentacao');
            const quantidadeItens = document.getElementById('quantidadeItensMovimentacao');
            const totalCusto = document.getElementById('totalCustoMovimentacao');
            if (!form || !linhas || !modelo || !adicionar || !quantidadeItens || !totalCusto) return;

            const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

            function atualizarTotais() {
                let total = 0;
                Array.from(linhas.rows).forEach((linha) => {
                    const quantidade = Number(linha.querySelector('[data-campo="quantidade"]').value) || 0;
                    const custo = Number(linha.querySelector('[data-campo="preco_custo"]').value) || 0;
                    const subtotal = quantidade * custo;
                    linha.querySelector('[data-total-item]').textContent = moeda.format(subtotal);
                    total += subtotal;
                });
                quantidadeItens.textContent = String(linhas.rows.length);
                totalCusto.textContent = moeda.format(total);
                adicionar.disabled = linhas.rows.length >= 100;
                linhas.querySelectorAll('[data-remover-item]').forEach((botao) => {
                    botao.disabled = linhas.rows.length === 1;
                });
            }

            function reindexarLinhas() {
                Array.from(linhas.rows).forEach((linha, indice) => {
                    linha.querySelectorAll('[data-campo]').forEach((campo) => {
                        campo.name = `itens[${indice}][${campo.dataset.campo}]`;
                    });
                });
                atualizarTotais();
            }

            function adicionarLinha() {
                if (linhas.rows.length >= 100) return;
                const fragmento = modelo.content.cloneNode(true);
                const linha = fragmento.querySelector('tr');
                linhas.appendChild(linha);
                reindexarLinhas();
                linha.querySelector('[data-campo="produto"]').focus();
            }

            adicionar.addEventListener('click', adicionarLinha);
            linhas.addEventListener('input', atualizarTotais);
            linhas.addEventListener('change', (evento) => {
                if (evento.target.matches('[data-campo="produto"]')) {
                    const opcao = evento.target.selectedOptions[0];
                    const linha = evento.target.closest('tr');
                    linha.querySelector('[data-campo="preco_custo"]').value =
                        opcao.dataset.precoCusto || '0.00';
                    linha.querySelector('[data-campo="preco_venda"]').value =
                        opcao.dataset.precoVenda || '0.00';
                    atualizarTotais();
                }
            });
            linhas.addEventListener('click', (evento) => {
                const botao = evento.target.closest('[data-remover-item]');
                if (!botao || linhas.rows.length === 1) return;
                botao.closest('tr').remove();
                reindexarLinhas();
            });
            form.addEventListener('submit', (evento) => {
                if (linhas.rows.length === 0) {
                    evento.preventDefault();
                    adicionarLinha();
                }
            });

            adicionarLinha();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modelo = document.getElementById('modeloItemEdicaoMovimentacao');
            if (!modelo) return;

            function atualizarItensEditor(editor) {
                const itens = editor.querySelector('[data-itens-editor]');
                const total = itens.rows.length;
                editor.querySelectorAll('[data-item-editor]').forEach((linha, indice) => {
                    linha.querySelectorAll('[data-campo]').forEach((campo) => {
                        campo.name = `itens[${indice}][${campo.dataset.campo}]`;
                    });
                    const remover = linha.querySelector('[data-remover-item-editor]');
                    remover.disabled = total === 1;
                });
                editor.querySelector('[data-adicionar-item-editor]').disabled = total >= 100;
            }

            function fecharEditor(editor) {
                editor.classList.add('d-none');
                const botao = document.querySelector(`[data-alternar-editor="${editor.id.replace('editorMovimentacao', '')}"]`);
                if (botao) botao.setAttribute('aria-expanded', 'false');
            }

            document.querySelectorAll('[data-itens-editor]').forEach((itens) => {
                atualizarItensEditor(itens.closest('form'));
            });

            document.addEventListener('click', (evento) => {
                const alternar = evento.target.closest('[data-alternar-editor]');
                if (alternar) {
                    const editor = document.getElementById(`editorMovimentacao${alternar.dataset.alternarEditor}`);
                    const abrir = editor.classList.contains('d-none');
                    document.querySelectorAll('[id^="editorMovimentacao"]').forEach((outroEditor) => {
                        if (outroEditor !== editor) fecharEditor(outroEditor);
                    });
                    editor.classList.toggle('d-none', !abrir);
                    alternar.setAttribute('aria-expanded', String(abrir));
                    return;
                }

                const cancelar = evento.target.closest('[data-cancelar-editor]');
                if (cancelar) {
                    fecharEditor(document.getElementById(`editorMovimentacao${cancelar.dataset.cancelarEditor}`));
                    return;
                }

                const adicionarItem = evento.target.closest('[data-adicionar-item-editor]');
                if (adicionarItem) {
                    const editor = adicionarItem.closest('form');
                    const itens = editor.querySelector('[data-itens-editor]');
                    if (itens.rows.length >= 100) return;
                    itens.appendChild(modelo.content.cloneNode(true));
                    atualizarItensEditor(editor);
                    return;
                }

                const removerItem = evento.target.closest('[data-remover-item-editor]');
                if (removerItem) {
                    const editor = removerItem.closest('form');
                    const itens = editor.querySelector('[data-itens-editor]');
                    if (itens.rows.length <= 1) return;
                    removerItem.closest('tr').remove();
                    atualizarItensEditor(editor);
                }
            });

            document.addEventListener('change', (evento) => {
                if (!evento.target.matches('[data-itens-editor] [data-campo="produto"]')) return;
                const linha = evento.target.closest('tr');
                const opcao = evento.target.selectedOptions[0];
                linha.querySelector('[data-campo="preco_custo"]').value = opcao.dataset.precoCusto || '0.00';
                linha.querySelector('[data-campo="preco_venda"]').value = opcao.dataset.precoVenda || '0.00';
            });

            const modalExcluir = document.getElementById('modalExcluirMovimentacao');
            modalExcluir?.addEventListener('show.bs.modal', (evento) => {
                const botao = evento.relatedTarget;
                if (!botao) return;
                document.getElementById('idMovimentacaoExcluir').value =
                    botao.dataset.excluirMovimentacao || '';
                document.getElementById('documentoMovimentacaoExcluir').textContent =
                    botao.dataset.documento || '';
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const grupo = document.getElementById('filtroGrupo');
            const subgrupo = document.getElementById('filtroSubgrupo');
            const produto = document.getElementById('filtroProduto');
            const cadastro = document.getElementById('filtroCadastro');
            const limpar = document.getElementById('limparFiltrosMovimentacao');

            if (!grupo || !subgrupo || !produto || !cadastro || !limpar) return;

            function setSelectValue(select, value) {
                const choices = window.getChoicesInstance?.(select);
                if (choices) {
                    choices.setChoiceByValue(String(value));
                } else {
                    select.value = value;
                }
            }

            const todosSubgrupos = Array.from(subgrupo.options)
                .slice(1)
                .map((option) => ({
                    id: option.value,
                    nome: option.textContent.trim(),
                    grupoId: option.dataset.grupoId
                }));
            const todosProdutos = Array.from(produto.options)
                .slice(1)
                .map((option) => ({
                    id: option.value,
                    nome: option.textContent.trim(),
                    grupoId: option.dataset.grupoId,
                    subgrupoId: option.dataset.subgrupoId
                }));

            function preencherFiltro(select, opcoes, textoInicial, valorAtual) {
                const valorMantido = opcoes.some((opcao) => String(opcao.id) === valorAtual)
                    ? valorAtual
                    : '';
                const escolhas = [
                    { value: '', label: textoInicial, selected: valorMantido === '' },
                    ...opcoes.map((opcao) => ({
                        value: String(opcao.id),
                        label: opcao.nome,
                        selected: String(opcao.id) === valorMantido
                    }))
                ];
                const instancia = window.getChoicesInstance?.(select);
                if (instancia) {
                    instancia.clearStore();
                    instancia.setChoices(escolhas, 'value', 'label', true);
                    instancia.setChoiceByValue(valorMantido);
                } else {
                    select.replaceChildren(...escolhas.map((opcao) => new Option(opcao.label, opcao.value)));
                    select.value = valorMantido;
                }
                return valorMantido;
            }

            function atualizarProdutos() {
                const grupoId = grupo.value;
                const subgrupoId = subgrupo.value;
                const opcoes = todosProdutos.filter((item) =>
                    (!grupoId || item.grupoId === grupoId) &&
                    (!subgrupoId || item.subgrupoId === subgrupoId)
                );
                preencherFiltro(produto, opcoes, 'Todos os produtos', produto.value);
            }

            function atualizarSubgrupos() {
                const grupoId = grupo.value;
                const opcoes = todosSubgrupos.filter((item) => !grupoId || item.grupoId === grupoId);
                preencherFiltro(subgrupo, opcoes, 'Todos os subgrupos', subgrupo.value);
                atualizarProdutos();
            }

            grupo.addEventListener('change', () => {
                atualizarSubgrupos();
                produto.dispatchEvent(new Event('change'));
            });
            subgrupo.addEventListener('change', atualizarProdutos);

            limpar.addEventListener('click', () => {
                setSelectValue(grupo, '');
                setSelectValue(subgrupo, '');
                setSelectValue(produto, '');
                setSelectValue(cadastro, '');
                atualizarSubgrupos();
            });

            atualizarSubgrupos();
        });
    </script>
</body>
</html>
