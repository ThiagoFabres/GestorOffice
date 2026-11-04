<?php
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/estoque/gru01.php';
require_once __DIR__ . '/../../../db/entities/estoque/gru02.php';
require_once __DIR__ . '/../../../db/entities/estoque/prod01.php';
require_once __DIR__ . '/../../../db/entities/estoque/comp01.php';
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

$idEmpresa = $_SESSION['usuario']->id_empresa;
$empresaUsuario = Empresa::read($idEmpresa)[0] ?? null;
$nomeEmpresa = $empresaUsuario->nom_fant ?? 'Gestor Office';
$podeProcessar = (int) $_SESSION['usuario']->processar === 1;
$lateral_estoque = true;
$lateral_target = 'estoque_cadastro';
$grupos = Gru01::read(id_empresa: $idEmpresa);
$mensagens = [
    'adicionado' => ['success', 'Cadastro realizado com sucesso.'],
    'atualizado' => ['success', 'Cadastro atualizado com sucesso.'],
    'excluido' => ['success', 'Cadastro excluído com sucesso.'],
    'grupo_existente' => ['warning', 'Já existe um grupo com esse nome.'],
    'subgrupo_existente' => ['warning', 'Já existe um subgrupo com esse nome neste grupo.'],
    'grupo_com_cadastros' => ['warning', 'Não é possível excluir este grupo enquanto houver subgrupos ou produtos vinculados.'],
    'subgrupo_com_produtos' => ['warning', 'Não é possível excluir este subgrupo enquanto houver produtos cadastrados.'],
    'produto_com_movimentacao' => ['warning', 'Não é possível excluir este produto porque ele possui movimentações de estoque.'],
    'grupo_invalido' => ['danger', 'O grupo selecionado não existe ou não pertence à sua empresa.'],
    'dados_invalidos' => ['danger', 'Confira os dados informados e tente novamente.'],
    'falha_ao_salvar' => ['danger', 'Não foi possível salvar o cadastro. Tente novamente.'],
    'acao_invalida' => ['danger', 'A ação solicitada não é válida.'],
    'destino_invalido' => ['danger', 'O tipo de cadastro solicitado não é válido.'],
    'permissao' => ['danger', 'Você não tem permissão para cadastrar itens no estoque.'],
];
$codigoSucesso = filter_input(INPUT_GET, 'sucesso');
$codigoErro = filter_input(INPUT_GET, 'erro');
$mensagem = $mensagens[$codigoSucesso ?? $codigoErro ?? ''] ?? null;

function escaparEstoque($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function valorEstoque($valor, $decimais = 2) {
    return number_format((float) $valor, $decimais, ',', '.');
}
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="/../../../../choices/choices.css"></link>
    <style>
        #container #accordionGrupos {
            width: 80%;
        }

        @media (max-width: 768px) {
            #container #accordionGrupos {
                width: 100%;
            }
        }

        .linha-editar-componente {
            cursor: pointer;
        }

        .linha-editar-componente:hover > * {
            background-color: #e9f2ff;
        }

        .linha-editar-componente:focus-visible {
            outline: 2px solid #0d6efd;
            outline-offset: -2px;
        }

        #filtrosEstoque {
            width: 80%;
        }

        #filtrosEstoque .card-body {
            overflow: visible;
        }

        #filtrosEstoque .choices {
            position: relative;
            z-index: 1;
        }

        #filtrosEstoque .choices.is-open {
            z-index: 1080;
        }

        #filtrosEstoque .choices__inner {
            display: flex;
            align-items: center;
            min-height: 38px;
            height: 38px;
            padding: 0 12px;
            background-color: #fff;
            border: 1px solid #ced4da;
        }

        #filtrosEstoque .choices__list--single {
            padding: 0;
        }

        #filtrosEstoque .choices__list--single .choices__item {
            margin: 0;
            line-height: 1.5;
        }

        #filtrosEstoque .choices__list--dropdown {
            z-index: 1081;
        }

        #filtrosEstoque .choices.is-disabled .choices__inner {
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            #filtrosEstoque {
                width: 100%;
            }
        }
    </style>
    <title>Gestor Office Control</title>
</head>
<body id="body" data-nome-empresa="<?= escaparEstoque($nomeEmpresa) ?>">
    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php'; ?>

    <main class="main" id="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div class="d-flex flex-wrap gap-2">
            <?php if ($podeProcessar) { ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_adicionar_grupo">
                    Adicionar Grupo
                </button>
            <?php } ?>
               
            </div>
        </div>

        <?php if ($mensagem) { ?>
            <div class="alert alert-<?= escaparEstoque($mensagem[0]) ?> alert-dismissible fade show" role="alert">
                <?= escaparEstoque($mensagem[1]) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php } ?>

        <?php if (!$grupos) { ?>
            <div class="alert alert-light border">
                Nenhum grupo cadastrado.<?php if ($podeProcessar) { ?> Use <strong>Adicionar Grupo</strong> para iniciar a estrutura do estoque.<?php } ?>
            </div>
        <?php } else { ?>
            <div id="filtrosEstoque" class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="filtroGrupo" class="form-label">Grupo</label>
                            <select id="filtroGrupo" class="form-select">
                                <option value="">Todos os grupos</option>
                                <?php foreach ($grupos as $grupo) { ?>
                                    <option value="<?= (int) $grupo->id ?>"><?= escaparEstoque($grupo->descricao) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroSubgrupo" class="form-label">Subgrupo</label>
                            <select id="filtroSubgrupo" class="form-select" disabled>
                                <option value="">Todos os subgrupos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroProduto" class="form-label">Produto</label>
                            <select id="filtroProduto" class="form-select" disabled>
                                <option value="">Todos os produtos</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                             <button type="button" class="btn btn-sm" id="botao-gerar-pdf" onclick="prepararGeracaoEstoque('pdf')">
                                Gerar PDF
                            </button>
                            <button type="button" class="btn btn-sm" id="botao-gerar-excel" onclick="prepararGeracaoEstoque('excel')">
                                Gerar Excel
                            </button>
                            <button type="button" id="limparFiltrosEstoque" class="btn btn-outline-secondary btn-sm">Limpar filtros</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion" id="accordionGrupos">
                <?php foreach ($grupos as $grupo) {
                    $grupoId = (int) $grupo->id;
                    $subgrupos = Gru02::read(id_empresa: $idEmpresa, id_grupo: $grupoId);
                    ?>
                    <section class="accordion-item item-grupo-estoque" data-grupo-id="<?= $grupoId ?>">
                        <h2 class="accordion-header" id="headingGrupo<?= $grupoId ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGrupo<?= $grupoId ?>" aria-expanded="false" aria-controls="collapseGrupo<?= $grupoId ?>" style="color:black;">
                                <?= escaparEstoque($grupo->descricao) ?>
                            </button>
                        </h2>
                        <div id="collapseGrupo<?= $grupoId ?>" class="accordion-collapse collapse"
                            aria-labelledby="headingGrupo<?= $grupoId ?>" data-bs-parent="#accordionGrupos">
                            <div class="accordion-body">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <?php if ($podeProcessar) { ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm botao-editar-grupo"
                                            data-id="<?= $grupoId ?>" data-nome="<?= escaparEstoque($grupo->descricao) ?>"
                                            data-bs-toggle="modal" data-bs-target="#modal_editar_grupo">Editar Grupo</button>
                                    <?php } ?>
                                    <?php if ($podeProcessar) { ?>
                                        <button type="button" class="btn btn-primary btn-sm botao-adicionar-subgrupo"
                                            data-grupo-id="<?= $grupoId ?>" data-bs-toggle="modal"
                                            data-bs-target="#modal_adicionar_subgrupo">Adicionar Subgrupo</button>
                                    <?php } ?>
                                </div>

                                <?php if (!$subgrupos) { ?>
                                    <p class="text-muted mb-0">Este grupo ainda não possui subgrupos.</p>
                                <?php } else { ?>
                                    <div class="accordion" id="accordionSubgrupos<?= $grupoId ?>">
                                        <?php foreach ($subgrupos as $subgrupo) {
                                            $subgrupoId = (int) $subgrupo->id;
                                            $produtos = Prod01::read(id_empresa: $idEmpresa, id_subgrupo: $subgrupoId);
                                            ?>
                                            <section class="accordion-item item-subgrupo-estoque"
                                                data-grupo-id="<?= $grupoId ?>" data-subgrupo-id="<?= $subgrupoId ?>">
                                                <h3 class="accordion-header" id="headingSubgrupo<?= $subgrupoId ?>">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseSubgrupo<?= $subgrupoId ?>" aria-expanded="false"
                                                        aria-controls="collapseSubgrupo<?= $subgrupoId ?>"
                                                        style="color:black;"
                                                        >
                                                        <?= escaparEstoque($subgrupo->descricao) ?>
                                                    </button>
                                                </h3>
                                                <div id="collapseSubgrupo<?= $subgrupoId ?>" class="accordion-collapse collapse"
                                                    aria-labelledby="headingSubgrupo<?= $subgrupoId ?>"
                                                    data-bs-parent="#accordionSubgrupos<?= $grupoId ?>">
                                                    <div class="accordion-body">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                                            <?php if ($podeProcessar) { ?>
                                                                <button type="button" class="btn btn-outline-secondary btn-sm botao-editar-subgrupo"
                                                                    data-id="<?= $subgrupoId ?>" data-nome="<?= escaparEstoque($subgrupo->descricao) ?>"
                                                                    data-grupo-id="<?= $grupoId ?>" data-bs-toggle="modal"
                                                                    data-bs-target="#modal_editar_subgrupo">Editar Subgrupo</button>
                                                            <?php } ?>
                                                            <?php if ($podeProcessar) { ?>
                                                                <button type="button" class="btn btn-primary btn-sm botao-adicionar-produto"
                                                                    data-subgrupo-id="<?= $subgrupoId ?>" data-bs-toggle="modal"
                                                                    data-bs-target="#modal_adicionar_produto">Adicionar Produto</button>
                                                            <?php } ?>
                                                        </div>

                                                        <?php if (!$produtos) { ?>
                                                            <p class="text-muted mb-0">Este subgrupo ainda não possui produtos.</p>
                                                        <?php } else { ?>
                                                            <?php foreach ($produtos as $produto) {
                                                                $produtoId = (int) $produto->id;
                                                                $componentes = Comp01::read(id_empresa: $idEmpresa, id_produto: $produtoId);
                                                                ?>
                                                                <article class="card mb-3 item-produto-estoque"
                                                                    data-grupo-id="<?= $grupoId ?>"
                                                                    data-subgrupo-id="<?= $subgrupoId ?>"
                                                                    data-produto-id="<?= $produtoId ?>"
                                                                    data-produto-nome="<?= escaparEstoque($produto->descricao) ?>"
                                                                    data-unidade="<?= escaparEstoque($produto->unidade) ?>"
                                                                    data-codigo-barras="<?= escaparEstoque($produto->cod_barra) ?>"
                                                                    data-preco-custo="<?= escaparEstoque($produto->preco_custo) ?>"
                                                                    data-markup="<?= escaparEstoque($produto->markup) ?>"
                                                                    data-preco-venda="<?= escaparEstoque($produto->preco_venda) ?>"
                                                                    data-estoque="<?= escaparEstoque($produto->estoque) ?>">
                                                                    <div class="card-body">
                                                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                                                            <div>
                                                                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                                                    <h5 class="card-title mb-0"><?= escaparEstoque($produto->descricao) ?></h5>
                                                                                    <?php if ($podeProcessar) { ?>
                                                                                        <button type="button" class="btn btn-outline-secondary btn-sm botao-editar-produto"
                                                                                            data-id="<?= $produtoId ?>"
                                                                                            data-nome="<?= escaparEstoque($produto->descricao) ?>"
                                                                                            data-unidade="<?= escaparEstoque($produto->unidade) ?>"
                                                                                            data-cod-barra="<?= escaparEstoque($produto->cod_barra) ?>"
                                                                                            data-preco-custo="<?= escaparEstoque($produto->preco_custo) ?>"
                                                                                            data-markup="<?= escaparEstoque($produto->markup) ?>"
                                                                                            data-preco-venda="<?= escaparEstoque($produto->preco_venda) ?>"
                                                                                            data-estoque="<?= escaparEstoque($produto->estoque) ?>"
                                                                                            data-bs-toggle="modal" data-bs-target="#modal_editar_produto">Editar</button>
                                                                                    <?php } ?>
                                                                                </div>
                                                                                <div class="small text-muted">
                                                                                    Unidade: <?= escaparEstoque($produto->unidade) ?>
                                                                                    <?php if ($produto->cod_barra !== null && $produto->cod_barra !== '') { ?>
                                                                                        <span class="mx-1">|</span>
                                                                                        Código de barras: <?= escaparEstoque($produto->cod_barra) ?>
                                                                                    <?php } ?>
                                                                                </div>
                                                                            </div>
                                                                            <?php if ($podeProcessar) { ?>
                                                                                <button type="button" class="btn btn-outline-primary btn-sm botao-adicionar-componente"
                                                                                    data-produto-id="<?= $produtoId ?>" data-bs-toggle="modal"
                                                                                    data-bs-target="#modal_adicionar_componente">Adicionar Item</button>
                                                                            <?php } ?>
                                                                        </div>

                                                                        <div class="small text-muted mt-2">
                                                                            Custo: R$ <?= valorEstoque($produto->preco_custo) ?>
                                                                            <span class="mx-1">|</span>
                                                                            Markup: <?= valorEstoque($produto->markup) ?>%
                                                                            <span class="mx-1">|</span>
                                                                            Venda: R$ <?= valorEstoque($produto->preco_venda) ?>
                                                                            <span class="mx-1">|</span>
                                                                            Estoque: <?= valorEstoque($produto->estoque, 3) ?>
                                                                        </div>

                                                                        <?php if (!$componentes) { ?>
                                                                            <p class="small text-muted mt-3 mb-0">Este produto ainda não possui Itens.</p>
                                                                        <?php } else { ?>
                                                                            <div class="table-responsive mt-3">
                                                                                <table class="table table-sm table-striped align-middle mb-0">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Item</th>
                                                                                            <th>Quantidade</th>
                                                                                            <th>Preço por unidade</th>
                                                                                            <th>Preço Total</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php foreach ($componentes as $componente) { ?>
                                                                                            <tr
                                                                                                <?php if ($podeProcessar) { ?>
                                                                                                    class="linha-editar-componente" role="button" tabindex="0"
                                                                                                    title="Clique para editar este item"
                                                                                                    data-id="<?= (int) $componente->id ?>"
                                                                                                    data-item="<?= escaparEstoque($componente->item) ?>"
                                                                                                    data-quantidade="<?= escaparEstoque($componente->quantidade) ?>"
                                                                                                    data-preco-unidade="<?= escaparEstoque($componente->preco_unidade) ?>"
                                                                                                <?php } ?>
                                                                                            >
                                                                                                <td><?= escaparEstoque($componente->item) ?></td>
                                                                                                <td><?= valorEstoque($componente->quantidade, 3) ?></td>
                                                                                                <td>R$ <?= valorEstoque($componente->preco_unidade) ?></td>
                                                                                                <td>R$ <?= valorEstoque($componente->quantidade * $componente->preco_unidade) ?></td>
                                                                                            </tr>
                                                                                        <?php } ?>
                                                                                        <tr>
                                                                                            <td></td>
                                                                                            <td></td>
                                                                                            <td><strong>Total:</strong></td>
                                                                                            <td><strong>R$ <?= valorEstoque(array_reduce($componentes, fn($carry, $item) => $carry + ($item->quantidade * $item->preco_unidade), 0)) ?></strong></td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        <?php } ?>
                                                                    </div>
                                                                </article>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </section>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </section>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($grupos) { ?>
            <div id="nenhumResultadoFiltro" class="alert alert-info mt-3" role="status" hidden>
                Nenhum produto encontrado para os filtros selecionados.
            </div>
        <?php } ?>
    </main>

    <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_editar_grupo.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_editar_subgrupo.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_editar_produto.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_adicionar_grupo.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_adicionar_subgrupo.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_adicionar_produto.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_adicionar_componente.php'; ?>
    <?php if ($podeProcessar) { ?>
        <?php require_once __DIR__ . '/../../../componentes/modais/estoque/modal_editar_componente.php'; ?>
    <?php } ?>
    <?php require_once __DIR__ . '/../../../componentes/footer/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="/../../../choices/choices.js"></script>
    <script src="gerar.js"></script>
    <script>
        const filtroGrupo = document.getElementById('filtroGrupo');
        const filtroSubgrupo = document.getElementById('filtroSubgrupo');
        const filtroProduto = document.getElementById('filtroProduto');
        const gruposEstoque = Array.from(document.querySelectorAll('.item-grupo-estoque'));
        const subgruposEstoque = Array.from(document.querySelectorAll('.item-subgrupo-estoque'));
        const produtosEstoque = Array.from(document.querySelectorAll('.item-produto-estoque'));
        const nenhumResultadoFiltro = document.getElementById('nenhumResultadoFiltro');

        function preencherOpcoes(select, opcoes, textoInicial, valorSelecionado, desabilitado) {
            const choices = [
                {
                    value: '',
                    label: textoInicial,
                    selected: !opcoes.some((opcao) => String(opcao.id) === valorSelecionado)
                },
                ...opcoes.map((opcao) => ({ value: opcao.id, label: opcao.nome }))
            ];
            const valorValido = opcoes.some((opcao) => String(opcao.id) === valorSelecionado);
            const novoValor = valorValido ? valorSelecionado : '';
            const instancia = window.getChoicesInstance?.(select);

            if (instancia) {
                instancia.clearStore();
                instancia.setChoices(choices, 'value', 'label', true);
                instancia.setChoiceByValue(novoValor);
                if (desabilitado) {
                    instancia.disable();
                } else {
                    instancia.enable();
                }
            } else {
                select.replaceChildren(...choices.map((opcao) => new Option(opcao.label, opcao.value)));
                select.value = novoValor;
                select.disabled = desabilitado;
            }

            return novoValor;
        }

        function atualizarOpcoesSubgrupos() {
            const grupoId = filtroGrupo.value;
            const subgrupos = subgruposEstoque
                .filter((subgrupo) => !grupoId || subgrupo.dataset.grupoId === grupoId)
                .map((subgrupo) => ({
                    id: subgrupo.dataset.subgrupoId,
                    nome: subgrupo.querySelector('.accordion-button').textContent.trim()
                }));

            const subgrupoId = preencherOpcoes(
                filtroSubgrupo,
                subgrupos,
                'Todos os subgrupos',
                filtroSubgrupo.value,
                subgrupos.length === 0
            );
            atualizarOpcoesProdutos(subgrupoId);
        }

        function atualizarOpcoesProdutos(subgrupoSelecionado = filtroSubgrupo.value) {
            const grupoId = filtroGrupo.value;
            const produtoSelecionado = filtroProduto.value;
            const produtos = produtosEstoque
                .filter((produto) =>
                    (!grupoId || produto.dataset.grupoId === grupoId) &&
                    (!subgrupoSelecionado || produto.dataset.subgrupoId === subgrupoSelecionado)
                )
                .map((produto) => ({
                    id: produto.dataset.produtoId,
                    nome: produto.dataset.produtoNome
                }));

            preencherOpcoes(
                filtroProduto,
                produtos,
                'Todos os produtos',
                produtoSelecionado,
                produtos.length === 0
            );
            aplicarFiltrosEstoque();
        }

        function aplicarFiltrosEstoque() {
            const grupoId = filtroGrupo.value;
            const subgrupoId = filtroSubgrupo.value;
            const produtoId = filtroProduto.value;
            let produtosVisiveis = 0;

            gruposEstoque.forEach((grupo) => {
                grupo.hidden = Boolean(grupoId && grupo.dataset.grupoId !== grupoId);
            });

            subgruposEstoque.forEach((subgrupo) => {
                const grupoCorresponde = !grupoId || subgrupo.dataset.grupoId === grupoId;
                const subgrupoCorresponde = !subgrupoId || subgrupo.dataset.subgrupoId === subgrupoId;
                const produtoCorresponde = !produtoId || produtosEstoque.some((produto) =>
                    produto.dataset.produtoId === produtoId &&
                    produto.dataset.subgrupoId === subgrupo.dataset.subgrupoId
                );
                subgrupo.hidden = !grupoCorresponde || !subgrupoCorresponde || !produtoCorresponde;
            });

            produtosEstoque.forEach((produto) => {
                const grupoCorresponde = !grupoId || produto.dataset.grupoId === grupoId;
                const subgrupoCorresponde = !subgrupoId || produto.dataset.subgrupoId === subgrupoId;
                const produtoCorresponde = !produtoId || produto.dataset.produtoId === produtoId;
                const visivel = grupoCorresponde && subgrupoCorresponde && produtoCorresponde;
                produto.hidden = !visivel;
                if (visivel) produtosVisiveis++;
            });

            if (produtoId) {
                const produtoSelecionado = produtosEstoque.find((produto) => produto.dataset.produtoId === produtoId);
                if (produtoSelecionado) {
                    const grupo = gruposEstoque.find((item) => item.dataset.grupoId === produtoSelecionado.dataset.grupoId);
                    const subgrupo = subgruposEstoque.find((item) => item.dataset.subgrupoId === produtoSelecionado.dataset.subgrupoId);
                    const grupoCorresponde = !grupoId || produtoSelecionado.dataset.grupoId === grupoId;
                    const subgrupoCorresponde = !subgrupoId || produtoSelecionado.dataset.subgrupoId === subgrupoId;
                    if (grupo && grupoCorresponde && subgrupoCorresponde) {
                        bootstrap.Collapse.getOrCreateInstance(grupo.querySelector('.accordion-collapse'), { toggle: false }).show();
                    }
                    if (subgrupo && grupoCorresponde && subgrupoCorresponde) {
                        bootstrap.Collapse.getOrCreateInstance(subgrupo.querySelector('.accordion-collapse'), { toggle: false }).show();
                    }
                }
            }

            nenhumResultadoFiltro.hidden = produtosVisiveis > 0 || (!grupoId && !subgrupoId && !produtoId);

            if (grupoId) {
                const grupo = gruposEstoque.find((item) => item.dataset.grupoId === grupoId);
                if (grupo) {
                    bootstrap.Collapse.getOrCreateInstance(grupo.querySelector('.accordion-collapse'), { toggle: false }).show();
                }
            }
            if (subgrupoId) {
                const subgrupo = subgruposEstoque.find((item) => item.dataset.subgrupoId === subgrupoId);
                if (subgrupo) {
                    bootstrap.Collapse.getOrCreateInstance(subgrupo.querySelector('.accordion-collapse'), { toggle: false }).show();
                }
            }
        }

        filtroGrupo.addEventListener('change', atualizarOpcoesSubgrupos);
        filtroSubgrupo.addEventListener('change', () => atualizarOpcoesProdutos(filtroSubgrupo.value));
        filtroProduto.addEventListener('change', aplicarFiltrosEstoque);
        atualizarOpcoesSubgrupos();

        document.getElementById('limparFiltrosEstoque').addEventListener('click', () => {
            const choicesGrupo = window.getChoicesInstance?.(filtroGrupo);
            if (choicesGrupo) {
                choicesGrupo.setChoiceByValue('');
            } else {
                filtroGrupo.value = '';
            }
            atualizarOpcoesSubgrupos();
        });

        document.querySelectorAll('.botao-editar-grupo').forEach((botao) => {
            botao.addEventListener('click', () => {
                document.getElementById('editarGrupoId').value = botao.dataset.id;
                document.getElementById('editarNomeGrupo').value = botao.dataset.nome;
                document.getElementById('excluirGrupoId').value = botao.dataset.id;
            });
        });

        document.querySelectorAll('.botao-editar-subgrupo').forEach((botao) => {
            botao.addEventListener('click', () => {
                document.getElementById('editarSubgrupoId').value = botao.dataset.id;
                document.getElementById('editarNomeSubgrupo').value = botao.dataset.nome;
                document.getElementById('excluirSubgrupoId').value = botao.dataset.id;
            });
        });

        document.querySelectorAll('.botao-editar-produto').forEach((botao) => {
            botao.addEventListener('click', () => {
                document.getElementById('editarProdutoId').value = botao.dataset.id;
                document.getElementById('editarNomeProduto').value = botao.dataset.nome;
                document.getElementById('editarUnidadeProduto').value = botao.dataset.unidade;
                document.getElementById('editarCodigoBarrasProduto').value = botao.dataset.codBarra;
                document.getElementById('editarCustoProduto').value = botao.dataset.precoCusto;
                document.getElementById('editarMarkupProduto').value = botao.dataset.markup;
                document.getElementById('editarVendaProduto').value = botao.dataset.precoVenda;
                document.getElementById('editarEstoqueProduto').value = botao.dataset.estoque;
                document.getElementById('excluirProdutoId').value = botao.dataset.id;
            });
        });

        document.querySelectorAll('.botao-adicionar-subgrupo').forEach((botao) => {
            botao.addEventListener('click', () => {
                document.getElementById('grupoSubGrupoId').value = botao.dataset.grupoId;
            });
        });

        document.querySelectorAll('.botao-adicionar-produto').forEach((botao) => {
            botao.addEventListener('click', () => {
                document.getElementById('produtoSubgrupoId').value = botao.dataset.subgrupoId;
            });
        });

        document.querySelectorAll('.botao-adicionar-componente').forEach((botao) => {
            botao.addEventListener('click', () => {
                document.getElementById('componenteProdutoId').value = botao.dataset.produtoId;
            });
        });

        <?php if ($podeProcessar) { ?>
            const modalEditarComponente = new bootstrap.Modal(document.getElementById('modal_editar_componente'));

            document.querySelectorAll('.linha-editar-componente').forEach((linha) => {
                const abrirEdicao = () => {
                    document.getElementById('editarComponenteId').value = linha.dataset.id;
                    document.getElementById('excluirComponenteId').value = linha.dataset.id;
                    document.getElementById('editarNomeComponente').value = linha.dataset.item;
                    document.getElementById('editarQuantidadeComponente').value = linha.dataset.quantidade;
                    document.getElementById('editarPrecoComponente').value = linha.dataset.precoUnidade;
                    modalEditarComponente.show();
                };

                linha.addEventListener('click', abrirEdicao);
                linha.addEventListener('keydown', (evento) => {
                    if (evento.key === 'Enter' || evento.key === ' ') {
                        evento.preventDefault();
                        abrirEdicao();
                    }
                });
            });
        <?php } ?>
    </script>
</body>
</html>
