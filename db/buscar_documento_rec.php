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

    // Buscar todos os documentos existentes para a empresa e calcular
    // em PHP o menor inteiro positivo que ainda não foi usado.
    // Essa abordagem evita dependência de recursos SQL avançados e funciona
    // em qualquer SGBD suportado pelo PDO.
    $sql = "SELECT DISTINCT documento FROM rec01 WHERE id_empresa = :id_empresa";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":id_empresa" => $id_empresa]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Filtrar apenas valores numéricos inteiros >= 1
    $nums = [];
    if ($rows) {
        foreach ($rows as $d) {
            // ignorar valores nulos/empty e não numéricos
            if ($d === null) continue;
            // remover espaços
            $d = trim($d);
            if ($d === '') continue;
            // usar is_numeric para aceitar '001', etc.
            if (is_numeric($d)) {
                $n = (int)$d;
                if ($n >= 1) {
                    $nums[$n] = $n; // usar chave para evitar duplicatas
                }
            }
        }
    }

    if (empty($nums)) {
        $novoNumero = 1;
    } else {
        ksort($nums);
        $novoNumero = 1;
        foreach ($nums as $n) {
            if ($n === $novoNumero) {
                $novoNumero++;
                continue;
            }
            if ($n > $novoNumero) {
                // encontramos um gap: $novoNumero não existe na lista
                break;
            }
            // se $n < $novoNumero (ex.: números repetidos/menores), apenas continue
        }
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
