<?php
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/cadastro.php';
require_once __DIR__ . '/../../../db/entities/pagamento.php';
require_once __DIR__ . '/../../../db/entities/estoque/cx01.php';
require_once __DIR__ . '/../../../db/entities/estoque/prod01.php';
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

if (empty($_SESSION['csrf_venda'])) {
    $_SESSION['csrf_venda'] = bin2hex(random_bytes(32));
}

$idEmpresa = (int) $_SESSION['usuario']->id_empresa;
$idUsuario = (int) $_SESSION['usuario']->id;
$hoje = date('Y-m-d');
$caixaAberto = Cx01::read(
    id_empresa: $idEmpresa,
    id_usuario: $idUsuario,
    data: $hoje,
    read_duplicidade: true
)[0] ?? null;
$resultado = filter_input(INPUT_GET, 'resultado', FILTER_UNSAFE_RAW);
$mensagens = [
    'caixa_aberto' => ['success', 'Caixa aberto. Pronto para registrar as vendas.'],
    'caixa_existente' => ['warning', 'Já existe um caixa aberto hoje para este usuário.'],
    'caixa_fechado' => ['danger', 'O caixa não está mais aberto. Atualize a tela e tente novamente.'],
    'dados_invalidos' => ['danger', 'Confira os produtos, as quantidades e o tipo de pagamento.'],
    'estoque_insuficiente' => ['warning', 'Estoque insuficiente para um ou mais produtos. A venda não foi registrada.'],
    'tipo_pagamento_invalido' => ['danger', 'O tipo de pagamento selecionado não pertence à empresa.'],
    'cliente_invalido' => ['danger', 'O cliente selecionado não é válido para esta empresa.'],
    'produto_invalido' => ['danger', 'Um dos produtos não existe mais. Atualize a tela e tente novamente.'],
    'falha_ao_finalizar' => ['danger', 'Não foi possível finalizar a venda. Nenhuma alteração foi gravada.'],
    'sessao_expirada' => ['warning', 'A sessão expirou. Atualize a tela antes de continuar.'],
    'acao_invalida' => ['danger', 'A ação solicitada não é válida.'],
    'venda_sucesso_crediario' => ['success', 'Venda finalizada no crediário. A parcela ficou em aberto e o estoque foi atualizado.'],
    'venda_sucesso_pago' => ['success', 'Venda finalizada e registrada como paga. O estoque foi atualizado.'],
];
$mensagem = $mensagens[$resultado ?? ''] ?? null;

$produtos = $caixaAberto ? Prod01::read(id_empresa: $idEmpresa) : [];
$clientes = $caixaAberto ? Cadastro::read(id_empresa: $idEmpresa) : [];
$tiposPagamento = $caixaAberto ? TipoPagamento::read(idempresa: $idEmpresa) : [];
$clienteConsumidorFinal = null;
foreach ($clientes as $cliente) {
    if (strcasecmp(trim((string) $cliente->nom_fant), 'Consumidor Final') === 0) {
        $clienteConsumidorFinal = $cliente;
        break;
    }
}

function escaparVenda($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function moedaVenda($valor): string {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

$produtosJson = json_encode(
    array_map(static function ($produto): array {
        return [
            'id' => (int) $produto->id,
            'descricao' => (string) $produto->descricao,
            'unidade' => (string) ($produto->unidade ?? ''),
            'cod_barra' => (string) ($produto->cod_barra ?? ''),
            'preco_venda' => (float) $produto->preco_venda,
            'estoque' => (float) $produto->estoque,
        ];
    }, $produtos),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
);

$lateral_estoque = true;
$lateral_target = 'estoque_venda';
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
    <style>
        .pdv-container { max-width: 1500px; }
        .pdv-total { font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 700; }
        .pdv-catalogo { max-height: 430px; overflow-y: auto; }
        .pdv-produto { min-height: 100%; }
        .pdv-quantidade { max-width: 105px; }
        .pdv-barcode { font-size: 1.35rem; height: 58px; }
        .pdv-sticky { position: sticky; top: 1rem; }
        @media (max-width: 991px) {
            .pdv-sticky { position: static; }
            .pdv-catalogo { max-height: 320px; }
        }
    </style>
<title>Gestor Office Control</title>
</head>
<body id="body">
    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php'; ?>

    <main class="main" id="container">
        <div class="d-flex justify-content-center flex-column pdv-container py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h1 class="h3 mb-1">Venda de produtos</h1>
                    <div class="text-muted">
                        <?= $caixaAberto
                            ? 'Caixa aberto às ' . escaparVenda(date('d/m/Y H:i', strtotime($caixaAberto->horario_inicio)))
                                . ' · Fundo: ' . escaparVenda(moedaVenda($caixaAberto->fundo_caixa))
                            : 'Abra o caixa para começar a vender.' ?>
                    </div>
                </div>
                <?php if ($caixaAberto) { ?>
                    <span class="badge text-bg-success fs-6"><i class="bi bi-circle-fill me-1"></i> Caixa aberto</span>
                <?php } ?>
            </div>

            <?php if ($mensagem) { ?>
                <div class="alert alert-<?= escaparVenda($mensagem[0]) ?> alert-dismissible fade show" role="alert">
                    <?= escaparVenda($mensagem[1]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php } ?>

            <?php if (!$caixaAberto) { ?>
                <div class="card shadow-sm" style="max-width: 520px;">
                    <div class="card-body p-4">
                        <h2 class="h4">Nenhum caixa aberto hoje</h2>
                        <p class="text-muted">Informe o fundo inicial para abrir o caixa e iniciar as vendas.</p>
                        <form method="post" action="venda_manager.php">
                            <input type="hidden" name="csrf_token" value="<?= escaparVenda($_SESSION['csrf_venda']) ?>">
                            <input type="hidden" name="acao" value="abrir_caixa">
                            <label for="fundo-caixa" class="form-label">Fundo de caixa</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" style="height:100%">R$</span>
                                <input id="fundo-caixa" class="form-control" inputmode="decimal"
                                    name="fundo_caixa" value="0,00" style="height:100%" required>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-unlock me-1"></i> Abrir caixa
                            </button>
                        </form>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row g-3">
                    <div class="col-xl-7">
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <label for="entrada-produto" class="form-label fw-semibold">
                                    Código de barras ou nome do produto
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="height:100%"><i class="bi bi-upc-scan"></i></span>
                                    <input id="entrada-produto" class="form-control"
                                        autocomplete="off" placeholder="Bipe ou digite o código e pressione Enter" style="height:100%">
                                    <button id="adicionar-codigo" class="btn btn-primary" type="button" style="height:100%">
                                        Adicionar
                                    </button>
                                </div>
                                <div id="mensagem-produto" class="form-text" aria-live="polite">
                                    Leitores de código de barras que digitam no teclado funcionam diretamente neste campo.
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center gap-2">
                                <h2 class="h5 mb-0">Produtos</h2>
                                <span class="text-muted small"><?= count($produtos) ?> cadastrados</span>
                            </div>
                            <div class="card-body">
                                <input id="filtro-catalogo" class="form-control mb-3"
                                    placeholder="Filtrar por descrição ou código de barras">
                                <div class="pdv-catalogo">
                                    <?php if ($produtos === []) { ?>
                                        <div class="text-center text-muted py-5">
                                            Nenhum produto cadastrado no estoque.
                                        </div>
                                    <?php } else { ?>
                                        <div class="row row-cols-1 row-cols-md-2 g-2" id="lista-produtos">
                                            <?php foreach ($produtos as $produto) {
                                                $termoBusca = strtolower(
                                                    $produto->descricao . ' ' . ($produto->cod_barra ?? '')
                                                );
                                            ?>
                                                <div class="col item-catalogo" data-busca="<?= escaparVenda($termoBusca) ?>">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary text-start w-100 pdv-produto"
                                                        data-produto-id="<?= (int) $produto->id ?>">
                                                        <span class="d-flex justify-content-between gap-2">
                                                            <strong><?= escaparVenda($produto->descricao) ?></strong>
                                                            <span class="text-nowrap fw-semibold">
                                                                <?= escaparVenda(moedaVenda($produto->preco_venda)) ?>
                                                            </span>
                                                        </span>
                                                        <span class="d-block small text-muted mt-1">
                                                            <?= $produto->cod_barra
                                                                ? 'Código: ' . escaparVenda($produto->cod_barra) . ' · '
                                                                : '' ?>
                                                            Estoque: <?= escaparVenda(number_format((float) $produto->estoque, 3, ',', '.')) ?>
                                                            <?= escaparVenda($produto->unidade ?? '') ?>
                                                        </span>
                                                    </button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="card shadow-sm pdv-sticky">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h2 class="h5 mb-0"><i class="bi bi-cart3 me-1"></i> Venda atual</h2>
                                <button id="limpar-venda" type="button" class="btn btn-sm btn-outline-secondary">
                                    Limpar
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th class="text-end">Unitário</th>
                                                <th>Qtd.</th>
                                                <th class="text-end">Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="itens-venda">
                                            <tr id="venda-vazia">
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    Bipe um produto ou selecione-o na lista.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="border-top pt-3 mb-3 d-flex justify-content-between align-items-end">
                                    <span class="fs-5">Total</span>
                                    <span id="total-venda" class="pdv-total">R$ 0,00</span>
                                </div>

                                <form id="form-finalizar" method="post" action="venda_manager.php">
                                    <input type="hidden" name="csrf_token" value="<?= escaparVenda($_SESSION['csrf_venda']) ?>">
                                    <input type="hidden" name="acao" value="finalizar_venda">
                                    <input type="hidden" name="itens" id="itens-json">

                                    <div class="mb-3">
                                        <label for="cliente" class="form-label fw-semibold">Cliente</label>
                                        <select id="cliente" name="cliente" class="form-select">
                                            <option value="consumidor_final" selected>
                                                Consumidor Final<?= $clienteConsumidorFinal ? '' : ' (será cadastrado)' ?>
                                            </option>
                                            <?php foreach ($clientes as $cliente) {
                                                if ((int) $cliente->id_cadastro === (int) ($clienteConsumidorFinal->id_cadastro ?? 0)) {
                                                    continue;
                                                }
                                                $nomeCliente = trim((string) ($cliente->nom_fant ?: $cliente->razao_soc));
                                            ?>
                                                <option value="<?= (int) $cliente->id_cadastro ?>">
                                                    <?= escaparVenda($nomeCliente) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tipo-pagamento" class="form-label fw-semibold">Tipo de Pagamento</label>
                                        <select id="tipo-pagamento" name="tipo_pagamento" class="form-select" required>
                                            <option value="">Selecione antes de finalizar</option>
                                            <?php foreach ($tiposPagamento as $tipoPagamento) { ?>
                                                <?php if (!TipoPagamento::ehCrediario($tipoPagamento->nome)) { ?>
                                                    <option value="<?= (int) $tipoPagamento->id ?>" data-crediario="0">
                                                        <?= escaparVenda($tipoPagamento->nome) ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                            <option value="crediario" data-crediario="1">Crediário</option>
                                        </select>
                                        <div class="form-text" id="pagamento-ajuda">
                                            Selecione “Crediário” para deixar o valor em aberto; os demais tipos serão registrados como pagos.
                                        </div>
                                    </div>

                                    <button id="finalizar-venda" class="btn btn-success btn-lg w-100"
                                        type="submit">
                                        <i class="bi bi-check2-circle me-1"></i> Finalizar venda
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../../componentes/footer/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($caixaAberto) { ?>
        <script id="produtos-dados" type="application/json"><?= $produtosJson ?: '[]' ?></script>
        <script>
        (() => {
            const produtos = new Map(
                JSON.parse(document.getElementById('produtos-dados').textContent)
                    .map(produto => [String(produto.id), produto])
            );
            const carrinho = new Map();
            const entrada = document.getElementById('entrada-produto');
            const mensagem = document.getElementById('mensagem-produto');
            const corpoItens = document.getElementById('itens-venda');
            const totalEl = document.getElementById('total-venda');
            const form = document.getElementById('form-finalizar');
            const tipoPagamento = document.getElementById('tipo-pagamento');
            const pagamentoAjuda = document.getElementById('pagamento-ajuda');
            const moeda = valor => new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor);

            tipoPagamento.addEventListener('change', () => {
                const selecionado = tipoPagamento.selectedOptions[0];
                pagamentoAjuda.textContent = selecionado?.dataset.crediario === '1'
                    ? 'A parcela ficará em aberto para o cliente selecionado.'
                    : 'O valor total será registrado como pago para o cliente selecionado.';
            });

            function informar(texto, tipo = 'muted') {
                mensagem.className = `form-text text-${tipo}`;
                mensagem.textContent = texto;
            }

            function adicionarProduto(produto) {
                if (!produto) return;
                const item = carrinho.get(String(produto.id));
                const quantidadeAtual = item ? item.quantidade : 0;
                if (quantidadeAtual + 1 > Number(produto.estoque)) {
                    informar(`Estoque insuficiente para ${produto.descricao}.`, 'danger');
                    entrada.focus();
                    return;
                }
                carrinho.set(String(produto.id), {
                    produto,
                    quantidade: quantidadeAtual + 1
                });
                informar(`${produto.descricao} adicionado à venda.`, 'success');
                renderizarCarrinho();
                entrada.value = '';
                entrada.focus();
            }

            function renderizarCarrinho() {
                corpoItens.replaceChildren();
                let total = 0;
                if (carrinho.size === 0) {
                    const linha = document.createElement('tr');
                    linha.id = 'venda-vazia';
                    linha.innerHTML = '<td colspan="5" class="text-center text-muted py-4">Bipe um produto ou selecione-o na lista.</td>';
                    corpoItens.appendChild(linha);
                }

                carrinho.forEach(({ produto, quantidade }, id) => {
                    const subtotal = Number(produto.preco_venda) * quantidade;
                    total += subtotal;
                    const linha = document.createElement('tr');
                    const nome = document.createElement('td');
                    nome.textContent = produto.descricao;
                    const preco = document.createElement('td');
                    preco.className = 'text-end text-nowrap';
                    preco.textContent = moeda(Number(produto.preco_venda));
                    const celulaQuantidade = document.createElement('td');
                    const campoQuantidade = document.createElement('input');
                    campoQuantidade.type = 'number';
                    campoQuantidade.className = 'form-control form-control-sm pdv-quantidade';
                    campoQuantidade.min = '0.001';
                    campoQuantidade.max = String(produto.estoque);
                    campoQuantidade.step = '0.001';
                    campoQuantidade.value = String(quantidade);
                    campoQuantidade.setAttribute('aria-label', `Quantidade de ${produto.descricao}`);
                    campoQuantidade.addEventListener('change', () => {
                        const novaQuantidade = Number(campoQuantidade.value.replace(',', '.'));
                        if (!Number.isFinite(novaQuantidade) || novaQuantidade <= 0
                            || novaQuantidade > Number(produto.estoque)) {
                            informar(`Quantidade inválida ou acima do estoque de ${produto.descricao}.`, 'danger');
                            campoQuantidade.value = String(quantidade);
                            return;
                        }
                        carrinho.set(id, { produto, quantidade: novaQuantidade });
                        renderizarCarrinho();
                    });
                    celulaQuantidade.appendChild(campoQuantidade);
                    const subtotalEl = document.createElement('td');
                    subtotalEl.className = 'text-end text-nowrap';
                    subtotalEl.textContent = moeda(subtotal);
                    const celulaAcao = document.createElement('td');
                    const remover = document.createElement('button');
                    remover.type = 'button';
                    remover.className = 'btn btn-sm btn-outline-danger';
                    remover.setAttribute('aria-label', `Remover ${produto.descricao}`);
                    remover.innerHTML = '<i class="bi bi-trash"></i>';
                    remover.addEventListener('click', () => {
                        carrinho.delete(id);
                        renderizarCarrinho();
                        entrada.focus();
                    });
                    celulaAcao.appendChild(remover);
                    linha.append(nome, preco, celulaQuantidade, subtotalEl, celulaAcao);
                    corpoItens.appendChild(linha);
                });
                totalEl.textContent = moeda(total);
            }

            async function adicionarPorEntrada() {
                const termo = entrada.value.trim();
                if (!termo) return;
                const matchesCodigo = [...produtos.values()].filter(
                    produto => produto.cod_barra === termo
                );
                if (matchesCodigo.length === 1) {
                    adicionarProduto(matchesCodigo[0]);
                    return;
                }
                if (matchesCodigo.length > 1) {
                    informar('Código de barras duplicado no cadastro; corrija os produtos antes de vender.', 'danger');
                    return;
                }

                informar('Buscando código de barras...', 'muted');
                try {
                    const resposta = await fetch(
                        `venda_manager.php?acao=buscar_produto&codigo=${encodeURIComponent(termo)}`,
                        { headers: { Accept: 'application/json' } }
                    );
                    const dados = await resposta.json();
                    if (!resposta.ok) {
                        informar(dados.erro || 'Não foi possível localizar o produto.', 'danger');
                        return;
                    }
                    const produto = dados.produto;
                    produtos.set(String(produto.id), produto);
                    adicionarProduto(produto);
                } catch (erro) {
                    informar('Falha ao consultar o código de barras. Tente novamente.', 'danger');
                }
            }

            document.getElementById('adicionar-codigo').addEventListener('click', adicionarPorEntrada);
            entrada.addEventListener('keydown', evento => {
                if (evento.key === 'Enter') {
                    evento.preventDefault();
                    adicionarPorEntrada();
                }
            });
            document.getElementById('filtro-catalogo').addEventListener('input', evento => {
                const termo = evento.target.value.trim().toLocaleLowerCase('pt-BR');
                document.querySelectorAll('.item-catalogo').forEach(item => {
                    item.hidden = !item.dataset.busca.includes(termo);
                });
            });
            document.querySelectorAll('.pdv-produto').forEach(botao => {
                botao.addEventListener('click', () => adicionarProduto(
                    produtos.get(botao.dataset.produtoId)
                ));
            });
            document.getElementById('limpar-venda').addEventListener('click', () => {
                if (carrinho.size && !window.confirm('Limpar todos os produtos desta venda?')) return;
                carrinho.clear();
                renderizarCarrinho();
                informar('Venda limpa. Bipe ou selecione os produtos.', 'muted');
                entrada.focus();
            });
            form.addEventListener('submit', evento => {
                if (carrinho.size === 0) {
                    evento.preventDefault();
                    informar('Adicione ao menos um produto antes de finalizar.', 'danger');
                    entrada.focus();
                    return;
                }
                if (!document.getElementById('tipo-pagamento').value) {
                    evento.preventDefault();
                    document.getElementById('tipo-pagamento').focus();
                    return;
                }
                const itens = [...carrinho.values()].map(({ produto, quantidade }) => ({
                    produto: produto.id,
                    quantidade: String(quantidade)
                }));
                document.getElementById('itens-json').value = JSON.stringify(itens);
                document.getElementById('finalizar-venda').disabled = true;
            });
            entrada.focus();
        })();
        </script>
    <?php } ?>
</body>
</html>
