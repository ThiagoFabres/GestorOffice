<?php

require_once __DIR__ . '/../../../db/base.php';
require_once __DIR__ . '/../../../db/entities/usuarios.php';
session_start();

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING);
$turno = filter_input(INPUT_GET, 'turno', FILTER_SANITIZE_NUMBER_INT);
$id_empresa = $_SESSION['usuario']->id_empresa;

if (!$action || !$date) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$pdo = (new Database())->connect();

if ($action === 'getTurnos') {
    $sql = 'SELECT DISTINCT r1.descricao
            FROM rec01 r1
            WHERE r1.id_empresa = :id_empresa
              AND DATE(r1.data_lanc) = :data
              AND r1.descricao LIKE :filtro_descricao';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_empresa', $id_empresa);
    $stmt->bindValue(':data', $date);
    $stmt->bindValue(':filtro_descricao', 'Turno %');
    $stmt->execute();
    $turnos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (preg_match('/^Turno\s*(\d+)/i', $row['descricao'], $matches)) {
            $turnos[(int)$matches[1]] = (int)$matches[1];
        }
    }
    ksort($turnos);
    echo json_encode(['success' => true, 'turnos' => array_values($turnos)]);
    exit;
}

if ($action === 'getDadosTurno') {
    if (!$turno) {
        echo json_encode(['success' => false, 'message' => 'Turno inválido']);
        exit;
    }

    $sql = 'SELECT r1.descricao, r2.id_pgto, r2.valor_par
            FROM rec02 r2
            INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id
            WHERE r1.id_empresa = :id_empresa
              AND DATE(r1.data_lanc) = :data
              AND r1.descricao LIKE :filtro_descricao';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_empresa', $id_empresa);
    $stmt->bindValue(':data', $date);
    $stmt->bindValue(':filtro_descricao', 'Turno ' . $turno . ' - %');
    $stmt->execute();

    $valores = [];
    $nome_caixa = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!$nome_caixa && preg_match('/^Turno\s*' . preg_quote($turno, '/') . '\s*-\s*(.+)$/i', $row['descricao'], $matches)) {
            $nome_caixa = trim($matches[1]);
        }
        $tipo = (string)$row['id_pgto'];
        $valor = (float)$row['valor_par'];
        if (!isset($valores[$tipo])) {
            $valores[$tipo] = $valor;
        } else {
            $valores[$tipo] += $valor;
        }
    }

    echo json_encode(['success' => true, 'nome_caixa' => $nome_caixa, 'valores' => $valores]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação desconhecida']);
