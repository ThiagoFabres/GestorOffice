<?php
require_once __DIR__ . '/../../db/entities/empresas.php';

function usuarioPodeAcessarEstoque(): bool {
    if (!isset($_SESSION['usuario'])
        || (int) $_SESSION['usuario']->cargo !== 3
        || (int) ($_SESSION['usuario']->permissao_estoque ?? 0) !== 1) {
        return false;
    }

    $empresa = Empresa::read((int) $_SESSION['usuario']->id_empresa)[0] ?? null;
    return $empresa !== null && (int) ($empresa->permissao_estoque ?? 0) === 1;
}
