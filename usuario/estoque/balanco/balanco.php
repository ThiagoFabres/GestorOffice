<?php
require_once __DIR__ . '/../../../db/entities/usuarios.php';
require_once __DIR__ . '/../../../db/entities/empresas.php';
require_once __DIR__ . '/../../../db/entities/estoque/gru01.php';
require_once __DIR__ . '/../../../db/entities/estoque/gru02.php';
require_once __DIR__ . '/../../../db/entities/estoque/prod01.php';
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

if ((int) $_SESSION['usuario']->processar !== 1) {
    header('Location: cadastro/cadastro.php?erro=permissao');
    exit;
}

$idEmpresa = (int) $_SESSION['usuario']->id_empresa;
$grupos = Gru01::read(id_empresa: $idEmpresa);
$subgrupos = Gru02::read(id_empresa: $idEmpresa);
$produtos = Prod01::read(id_empresa: $idEmpresa);
$gruposPorId = [];
$subgruposPorId = [];

foreach ($grupos as $grupo) {
    $gruposPorId[(int) $grupo->id] = $grupo;
}

foreach ($subgrupos as $subgrupo) {
    $subgruposPorId[(int) $subgrupo->id] = $subgrupo;
}

$mensagens = [
    'processado' => ['success', 'Balanço processado. O estoque e as movimentações foram atualizados.'],
    'sem_alteracoes' => ['info', 'Balanço concluído: não houve diferenças para registrar.'],
    'nenhuma_contagem' => ['warning', 'Informe a contagem de pelo menos um produto.'],
    'dados_invalidos' => ['danger', 'Há uma contagem inválida ou um produto não pertence à sua empresa. Revise os dados.'],
    'falha' => ['danger', 'Não foi possível processar o balanço. Nenhuma alteração foi mantida. Tente novamente.'],
];
$codigoMensagem = filter_input(INPUT_GET, 'resultado');
$mensagem = $mensagens[$codigoMensagem ?? ''] ?? null;

function escaparBalanco($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$lateral_estoque = true;
$lateral_target = 'balanco';
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
        #filtrosBalanco .card-body,
        #tabelaBalanco .card-body {
            overflow: visible;
        }

        #filtrosBalanco .choices {
            position: relative;
            z-index: 1;
        }

        #filtrosBalanco .choices.is-open {
            z-index: 1080;
        }

        #tabelaBalanco input[type="number"] {
            max-width: 180px;
        }
    </style>
</head>
<body id="body">
    <?php require_once __DIR__ . '/../../../componentes/lateral/lateral.php'; ?>
    <?php require_once __DIR__ . '/../../../componentes/header/header.php'; ?>

    <main class="main" id="container">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h1 class="h3 mb-0">Balanço de estoque</h1>
            </div>

            <?php if ($mensagem) { ?>
                <div class="alert alert-<?= escaparBalanco($mensagem[0]) ?>" role="alert">
                    <?= escaparBalanco($mensagem[1]) ?>
                </div>
            <?php } ?>

            <?php if (!$produtos) { ?>
                <div class="alert alert-light border mb-0">Nenhum produto cadastrado para realizar o balanço.</div>
            <?php } else { ?>
                <div id="filtrosBalanco" class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="filtroGrupo" class="form-label">Grupo</label>
                                <select id="filtroGrupo" class="form-select">
                                    <option value="">Todos os grupos</option>
                                    <?php foreach ($grupos as $grupo) { ?>
                                        <option value="<?= (int) $grupo->id ?>"><?= escaparBalanco($grupo->descricao) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filtroSubgrupo" class="form-label">Subgrupo</label>
                                <select id="filtroSubgrupo" class="form-select">
                                    <option value="">Todos os subgrupos</option>
                                    <?php foreach ($subgrupos as $subgrupo) { ?>
                                        <option value="<?= (int) $subgrupo->id ?>" data-grupo-id="<?= (int) $subgrupo->id_grupo ?>">
                                            <?= escaparBalanco($subgrupo->descricao) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filtroProduto" class="form-label">Produto</label>
                                <select id="filtroProduto" class="form-select">
                                    <option value="">Todos os produtos</option>
                                    <?php foreach ($produtos as $produto) { ?>
                                        <option value="<?= (int) $produto->id ?>"
                                            data-grupo-id="<?= (int) $produto->id_grupo ?>"
                                            data-subgrupo-id="<?= (int) $produto->id_subgrupo ?>">
                                            <?= escaparBalanco($produto->descricao) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="limparFiltrosBalanco">
                                    Limpar filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="balanco_manager.php" method="post" id="formBalanco">
                    <div id="tabelaBalanco" class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Grupo</th>
                                            <th>Subgrupo</th>
                                            <th>Produto</th>
                                            <th>Estoque atual</th>
                                            <th>Quantidade contada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produtos as $produto) {
                                            $grupo = $gruposPorId[(int) $produto->id_grupo] ?? null;
                                            $subgrupo = $subgruposPorId[(int) $produto->id_subgrupo] ?? null;
                                            ?>
                                            <tr class="linha-produto-balanco"
                                                data-grupo-id="<?= (int) $produto->id_grupo ?>"
                                                data-subgrupo-id="<?= (int) $produto->id_subgrupo ?>"
                                                data-produto-id="<?= (int) $produto->id ?>">
                                                <td><?= escaparBalanco($grupo->descricao ?? '—') ?></td>
                                                <td><?= escaparBalanco($subgrupo->descricao ?? '—') ?></td>
                                                <td>
                                                    <?= escaparBalanco($produto->descricao) ?>
                                                    <?php if ($produto->unidade !== null && $produto->unidade !== '') { ?>
                                                        <span class="text-muted small">(<?= escaparBalanco($produto->unidade) ?>)</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?= number_format((float) $produto->estoque, 3, ',', '.') ?></td>
                                                <td>
                                                    <input type="number" class="form-control quantidade-contada"
                                                        name="contado[<?= (int) $produto->id ?>]"
                                                        min="0" max="999999999.999" step="0.001" inputmode="decimal"
                                                        aria-label="Quantidade contada de <?= escaparBalanco($produto->descricao) ?>"
                                                        placeholder="Informe a contagem">
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <p class="form-text mt-2">Deixe em branco os produtos que não foram contados. Ao pressionar Enter, o foco vai para o próximo produto visível.</p>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary" id="processarBalanco">Processar balanço</button>
                    </div>
                </form>
            <?php } ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../../componentes/footer/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="/choices/choices.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const grupo = document.getElementById('filtroGrupo');
            const subgrupo = document.getElementById('filtroSubgrupo');
            const produto = document.getElementById('filtroProduto');
            const linhas = Array.from(document.querySelectorAll('.linha-produto-balanco'));
            const forma = document.getElementById('formBalanco');

            if (!grupo || !subgrupo || !produto || !forma) return;

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

                function atualizarSubgrupos() {
                    const grupoId = grupo.value;
                    const opcoes = todosSubgrupos.filter((item) => !grupoId || item.grupoId === grupoId);
                    preencherFiltro(subgrupo, opcoes, 'Todos os subgrupos', subgrupo.value);
                    atualizarProdutos();
                    aplicarFiltros();
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

            function aplicarFiltros() {
                const grupoId = grupo.value;
                const subgrupoId = subgrupo.value;
                const produtoId = produto.value;
                linhas.forEach((linha) => {
                    linha.hidden = Boolean(
                        (grupoId && linha.dataset.grupoId !== grupoId) ||
                        (subgrupoId && linha.dataset.subgrupoId !== subgrupoId) ||
                        (produtoId && linha.dataset.produtoId !== produtoId)
                    );
                });
            }

            grupo.addEventListener('change', atualizarSubgrupos);
            subgrupo.addEventListener('change', () => {
                atualizarProdutos();
                aplicarFiltros();
            });
            produto.addEventListener('change', aplicarFiltros);

            document.getElementById('limparFiltrosBalanco').addEventListener('click', () => {
                setSelectValue(grupo, '');
                atualizarSubgrupos();
            });

            document.querySelectorAll('.quantidade-contada').forEach((input) => {
                input.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') return;
                    event.preventDefault();
                    const linhaAtual = input.closest('.linha-produto-balanco');
                    const proximaLinha = linhas.slice(linhas.indexOf(linhaAtual) + 1)
                        .find((linha) => !linha.hidden);
                    const proximoInput = proximaLinha?.querySelector('.quantidade-contada');
                    if (proximoInput) {
                        proximoInput.focus();
                        proximoInput.select();
                    } else {
                        document.getElementById('processarBalanco').focus();
                    }
                });
            });

            forma.addEventListener('submit', (event) => {
                const inputs = Array.from(document.querySelectorAll('.quantidade-contada'));
                const contagensPreenchidas = inputs.some((input) => input.value.trim() !== '');
                if (!contagensPreenchidas) {
                    event.preventDefault();
                    window.alert('Informe a contagem de pelo menos um produto.');
                    return;
                }
                inputs.filter((input) => input.value.trim() === '')
                    .forEach((input) => input.disabled = true);
            });
        });
    </script>
</body>
</html>
