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
$descricao_padrao = filter_input(INPUT_GET, 'descricao', FILTER_SANITIZE_STRING);
$target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_STRING);
$id_empresa = $_SESSION['usuario']->id_empresa;

if (!$action || !$date) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$pdo = (new Database())->connect();

function extractNomeCaixa($descricao, $turno) {
    if (!preg_match('/^Turno\s*' . preg_quote($turno, '/') . '\s*-\s*(.+)$/i', $descricao, $matches)) {
        return '';
    }

    $rest = trim($matches[1]);
    if (strpos($rest, ' - ') !== false) {
        $parts = explode(' - ', $rest);
        return trim($parts[0]);
    }

    return $rest;
}

function extractDescricaoPagar($descricao, $turno) {
    if (!preg_match('/^Turno\s*' . preg_quote($turno, '/') . '\s*-\s*(.+)$/i', $descricao, $matches)) {
        return '';
    }

    $rest = trim($matches[1]);
    $parts = explode(' - ', $rest);
    if (count($parts) > 1) {
        array_shift($parts);
        return trim(implode(' - ', $parts));
    }

    return '';
}

if ($action === 'getTurnos') {
    $sql = 'SELECT DISTINCT descricao FROM rec01 WHERE id_empresa = :id_empresa AND DATE(data_lanc) = :data AND descricao LIKE :filtro_descricao'
         . ' UNION '
         . 'SELECT DISTINCT descricao FROM pag01 WHERE id_empresa = :id_empresa AND DATE(data_lanc) = :data AND descricao LIKE :filtro_descricao';
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

    $valores = [];
    $nome_caixa = '';
    $descricao_valor = 0.0;
    $descricao_final = '';

    if ($target === 'pagar') {
        $sqlDespesa = 'SELECT p1.descricao, p2.id_pgto, p2.valor_par
                FROM pag02 p2
                INNER JOIN pag01 p1 ON p2.id_pag01 = p1.id
                WHERE p1.id_empresa = :id_empresa
                  AND DATE(p1.data_lanc) = :data
                  AND p1.descricao LIKE :filtro_descricao';
        $stmtDespesa = $pdo->prepare($sqlDespesa);
        $stmtDespesa->bindValue(':id_empresa', $id_empresa);
        $stmtDespesa->bindValue(':data', $date);

        if ($descricao_padrao) {
            $stmtDespesa->bindValue(':filtro_descricao', 'Turno ' . $turno . ' - % - ' . trim($descricao_padrao));
        } else {
            $stmtDespesa->bindValue(':filtro_descricao', 'Turno ' . $turno . ' - %');
        }
        $stmtDespesa->execute();

        while ($row = $stmtDespesa->fetch(PDO::FETCH_ASSOC)) {
            if (!$nome_caixa) {
                $nome_caixa = extractNomeCaixa($row['descricao'], $turno);
                $descricao_final = extractDescricaoPagar($row['descricao'], $turno);
            }
            $valor = (float)$row['valor_par'];
            $descricao_valor += $valor;
        }

        echo json_encode(['success' => true, 'nome_caixa' => $nome_caixa, 'valor' => $descricao_valor, 'descricao' => $descricao_final]);
        exit;
    }

    $sqlReceita = 'SELECT r1.descricao, r2.id_pgto, r2.valor_par
            FROM rec02 r2
            INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id
            WHERE r1.id_empresa = :id_empresa
              AND DATE(r1.data_lanc) = :data
              AND r1.descricao LIKE :filtro_descricao';
    $stmtReceita = $pdo->prepare($sqlReceita);
    $stmtReceita->bindValue(':id_empresa', $id_empresa);
    $stmtReceita->bindValue(':data', $date);
    $stmtReceita->bindValue(':filtro_descricao', 'Turno ' . $turno . ' - %');
    $stmtReceita->execute();

    while ($row = $stmtReceita->fetch(PDO::FETCH_ASSOC)) {
        if (!$nome_caixa) {
            $nome_caixa = extractNomeCaixa($row['descricao'], $turno);
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
