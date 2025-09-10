<?php
require_once '../db/base.php'; // sua conexão PDO

header('Content-Type: application/json');

try {
    $pdo = (new Database())->connect(); // ou sua função de conexão

    // Pegar o maior documento já usado
    $sql = "SELECT MAX(CAST(documento AS UNSIGNED)) AS ultimo FROM rec01";
    $stmt = $pdo->query($sql);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doc && $doc["ultimo"] !== null) {
        $novoNumero = $doc["ultimo"] + 1;
    } else {
        $novoNumero = 1; // Se não existir nenhum documento ainda
    }

    echo json_encode([
        "sucesso" => true,
        "numero" => $novoNumero
    ]);
} catch (Exception $e) {
    echo json_encode([
        "sucesso" => false,
        "erro" => $e->getMessage()
    ]);
}
