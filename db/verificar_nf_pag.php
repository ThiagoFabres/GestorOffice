<?php
require_once __DIR__ . '/base.php';
require_once __DIR__ . '/entities/usuarios.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

$nf = filter_input(INPUT_POST, 'nf');
$id_atual = filter_input(INPUT_POST, 'id');
$id_empresa = $_SESSION['usuario']->id_empresa ?? null;

if (!$nf || !$id_empresa) {
    echo json_encode(['duplicado' => false]);
    exit;
}
$pdo = (new Database())->connect();
$sql = "SELECT id, documento, nf, descricao, valor FROM pag01 WHERE nf = :nf AND id_empresa = :id_empresa";
$params = [':nf' => $nf, ':id_empresa' => $id_empresa];

if ($id_atual) {
    $sql .= " AND id != :id_atual";
    $params[':id_atual'] = $id_atual;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'duplicado' => (bool) $resultado,
    'info' => $resultado ?: null,
]);