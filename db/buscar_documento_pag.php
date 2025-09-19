<?php
require_once '../db/base.php'; // sua conexão PDO
require_once '../db/entities/usuarios.php';
session_start();

header('Content-Type: application/json');

try {
    $pdo = (new Database())->connect(); // sua função de conexão

    // id_empresa do usuário logado
    $id_empresa = $_SESSION['usuario']->id_empresa ?? null;

    if (!$id_empresa) {
        echo json_encode([
            "sucesso" => false,
            "erro" => "Empresa não definida para o usuário."
        ]);
        exit;
    }

    // Pegar o maior documento já usado dentro da empresa do usuário
    $sql = "SELECT MAX(CAST(documento AS UNSIGNED)) AS ultimo 
              FROM pag01 
             WHERE id_empresa = :id_empresa";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":id_empresa" => $id_empresa]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doc && $doc["ultimo"] !== null) {
        $novoNumero = $doc["ultimo"] + 1;
    } else {
        $novoNumero = 1; // se não existir nenhum documento ainda nessa empresa
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
