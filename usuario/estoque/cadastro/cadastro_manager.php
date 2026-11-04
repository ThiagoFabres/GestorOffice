<?php
require_once __DIR__ . '/../../../db/entities/usuarios.php';
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

if ((int) $_SESSION['usuario']->processar !== 1) {
    header('Location: cadastro.php?erro=permissao');
    exit;
}

function redirecionarEstoque($erro = null, $sucesso = 'adicionado') {
    $url = 'cadastro.php';
    if ($erro !== null) {
        $url .= '?erro=' . rawurlencode($erro);
    } else {
        $url .= '?sucesso=' . rawurlencode($sucesso);
    }
    header('Location: ' . $url);
    exit;
}

function campoDecimalValido($nome, $minimo = 0) {
    $valor = trim((string) ($_POST[$nome] ?? ''));
    $valor = str_replace(',', '.', $valor);
    if ($valor === '' || !is_numeric($valor) || (float) $valor < $minimo) {
        return null;
    }
    return (float) $valor;
}

$acao = $_POST['acao'] ?? '';
$target = $_POST['target'] ?? '';
$idEmpresa = $_SESSION['usuario']->id_empresa;

if (!in_array($acao, ['adicionar', 'atualizar', 'excluir'], true)) {
    redirecionarEstoque('acao_invalida');
}

function nomeDuplicado($registros, $descricao, $idIgnorado = null) {
    foreach ($registros as $registro) {
        if ((string) $registro->id !== (string) $idIgnorado &&
            strcasecmp(trim($registro->descricao), $descricao) === 0) {
            return true;
        }
    }
    return false;
}

if ($target === 'grupo') {
    if ($acao === 'excluir') {
        $grupoId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $grupo = $grupoId ? Gru01::read(id: $grupoId, id_empresa: $idEmpresa) : [];
        if (!$grupo) {
            redirecionarEstoque('dados_invalidos');
        }
        if (
            Gru02::read(id_empresa: $idEmpresa, id_grupo: $grupoId) ||
            Prod01::read(id_empresa: $idEmpresa, id_grupo: $grupoId)
        ) {
            redirecionarEstoque('grupo_com_cadastros');
        }
        if (!Gru01::delete($grupoId, $idEmpresa)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque(null, 'excluido');
    }

    $descricao = trim((string) ($_POST['nome'] ?? ''));
    $grupoId = $acao === 'atualizar' ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) : null;
    $grupoAtual = $grupoId ? Gru01::read(id: $grupoId, id_empresa: $idEmpresa) : [];
    if ($descricao === '' || ($acao === 'atualizar' && !$grupoAtual)) {
        redirecionarEstoque('dados_invalidos');
    }

    $gruposExistentes = Gru01::read(id_empresa: $idEmpresa);
    if (nomeDuplicado($gruposExistentes, $descricao, $grupoId)) {
        redirecionarEstoque('grupo_existente');
    }

    if ($acao === 'atualizar') {
        $grupo = new Gru01($grupoId, $idEmpresa, $descricao);
        if (!Gru01::update($grupo)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque(null, 'atualizado');
    }

    $grupo = new Gru01(null, $idEmpresa, $descricao);
    if (!Gru01::create($grupo)) {
        redirecionarEstoque('falha_ao_salvar');
    }
    redirecionarEstoque();
}

if ($target === 'subgrupo') {
    $subgrupoId = $acao !== 'adicionar' ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) : null;
    $subgrupoAtual = $subgrupoId ? Gru02::read(id: $subgrupoId, id_empresa: $idEmpresa) : [];

    if ($acao === 'excluir') {
        if (!$subgrupoAtual) {
            redirecionarEstoque('dados_invalidos');
        }
        if (Prod01::read(id_empresa: $idEmpresa, id_subgrupo: $subgrupoId)) {
            redirecionarEstoque('subgrupo_com_produtos');
        }
        if (!Gru02::delete($subgrupoId, $idEmpresa)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque(null, 'excluido');
    }

    $descricao = trim((string) ($_POST['nome'] ?? ''));
    $grupoId = $acao === 'atualizar'
        ? (int) ($subgrupoAtual[0]->id_grupo ?? 0)
        : filter_input(INPUT_POST, 'grupo_id', FILTER_VALIDATE_INT);
    $grupo = $grupoId
        ? Gru01::read(id: $grupoId, id_empresa: $idEmpresa)
        : [];

    if ($descricao === '' || !$grupo || ($acao === 'atualizar' && !$subgrupoAtual)) {
        redirecionarEstoque('grupo_invalido');
    }

    $subgruposExistentes = Gru02::read(id_empresa: $idEmpresa, id_grupo: $grupoId);
    if (nomeDuplicado($subgruposExistentes, $descricao, $subgrupoId)) {
        redirecionarEstoque('subgrupo_existente');
    }

    if ($acao === 'atualizar') {
        $subgrupo = new Gru02($subgrupoId, $idEmpresa, $grupoId, $descricao);
        if (!Gru02::update($subgrupo)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque(null, 'atualizado');
    }

    $subgrupo = new Gru02(null, $idEmpresa, $grupoId, $descricao);
    if (!Gru02::create($subgrupo)) {
        redirecionarEstoque('falha_ao_salvar');
    }
    redirecionarEstoque();
}

if ($target === 'produto') {
    $produtoId = $acao !== 'adicionar' ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) : null;
    $produtoAtual = $produtoId ? Prod01::read(id: $produtoId, id_empresa: $idEmpresa) : [];

    if ($acao === 'excluir') {
        if (!$produtoAtual) {
            redirecionarEstoque('dados_invalidos');
        }
        if (!Prod01::delete($produtoId, $idEmpresa)) {
            redirecionarEstoque('produto_com_movimentacao');
        }
        redirecionarEstoque(null, 'excluido');
    }

    $descricao = trim((string) ($_POST['nome'] ?? ''));
    $unidade = trim((string) ($_POST['unidade'] ?? ''));
    $codigoBarras = trim((string) ($_POST['cod_barra'] ?? ''));
    $subgrupoId = $acao === 'atualizar'
        ? (int) ($produtoAtual[0]->id_subgrupo ?? 0)
        : filter_input(INPUT_POST, 'subgrupo_id', FILTER_VALIDATE_INT);
    $subgrupo = $subgrupoId ? Gru02::read(id: $subgrupoId, id_empresa: $idEmpresa) : [];
    $precoCusto = campoDecimalValido('preco_custo');
    $markup = campoDecimalValido('markup');
    $precoVenda = campoDecimalValido('preco_venda');
    $estoque = campoDecimalValido('estoque');

    if (
        $descricao === '' ||
        $unidade === '' ||
        !$subgrupo ||
        $precoCusto === null ||
        $markup === null ||
        $precoVenda === null ||
        $estoque === null ||
        ($acao === 'atualizar' && !$produtoAtual)
    ) {
        redirecionarEstoque('dados_invalidos');
    }

    $produto = new Prod01(
        $produtoId,
        $idEmpresa,
        $subgrupo[0]->id_grupo,
        $subgrupoId,
        $unidade,
        $codigoBarras !== '' ? $codigoBarras : null,
        $precoCusto,
        $markup,
        $precoVenda,
        $estoque,
        $descricao
    );

    if ($acao === 'atualizar') {
        if (!Prod01::update($produto)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque(null, 'atualizado');
    } else {
        if (!Prod01::create($produto)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque();
    }
}

if ($target === 'componente') {
    if ($acao === 'excluir') {
        $componenteId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $componente = $componenteId
            ? Comp01::read(id: $componenteId, id_empresa: $idEmpresa)
            : [];
        if (!$componente) {
            redirecionarEstoque('dados_invalidos');
        }
        if (!Comp01::delete($componenteId, $idEmpresa)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque(null, 'excluido');
    }

    if ($acao === 'atualizar') {
        $componenteId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $componenteAtual = $componenteId
            ? Comp01::read(id: $componenteId, id_empresa: $idEmpresa)
            : [];
        $item = trim((string) ($_POST['item'] ?? ''));
        $quantidade = campoDecimalValido('quantidade', 0.001);
        $precoUnidade = campoDecimalValido('preco_unidade');

        if (!$componenteAtual || $item === '' || $quantidade === null || $precoUnidade === null) {
            redirecionarEstoque('dados_invalidos');
        }

        $componente = new Comp01(
            $componenteId,
            $idEmpresa,
            $componenteAtual[0]->id_produto,
            $item,
            $quantidade,
            $precoUnidade
        );
        if (!Comp01::update($componente)) {
            redirecionarEstoque('falha_ao_salvar');
        }
        redirecionarEstoque(null, 'atualizado');
    }

    if ($acao !== 'adicionar') {
        redirecionarEstoque('acao_invalida');
    }

    $item = trim((string) ($_POST['item'] ?? ''));
    $produtoId = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
    $produto = $produtoId
        ? Prod01::read(id: $produtoId, id_empresa: $idEmpresa)
        : [];
    $quantidade = campoDecimalValido('quantidade', 0.001);
    $precoUnidade = campoDecimalValido('preco_unidade');

    if ($item === '' || !$produto || $quantidade === null || $precoUnidade === null) {
        redirecionarEstoque('dados_invalidos');
    }

    $componente = new Comp01(null, $idEmpresa, $produtoId, $item, $quantidade, $precoUnidade);
    if (!Comp01::create($componente)) {
        redirecionarEstoque('falha_ao_salvar');
    }
    redirecionarEstoque();
}

redirecionarEstoque('destino_invalido');
