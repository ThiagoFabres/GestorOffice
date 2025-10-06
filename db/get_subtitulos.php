<?php
require_once __DIR__ . '/../db/base.php';
require_once __DIR__ . '/../db/entities/usuarios.php';
session_start();

$db = new Database();
$pdo = $db->connect();

if (isset($_GET['titulo_id'])) {
    $titulo_id = intval($_GET['titulo_id']);
    $id_empresa = $_SESSION['usuario']->id_empresa;

    $stmt = $pdo->prepare("
        SELECT id, nome 
        FROM con02
        WHERE id_con01 = :id
          AND id_empresa = :empresa
    ");
    $stmt->execute([
        ':id' => $titulo_id,
        ':empresa' => $id_empresa
    ]);

    $subtitulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($subtitulos);
}
