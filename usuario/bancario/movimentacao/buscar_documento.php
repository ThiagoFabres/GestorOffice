<?php 
require_once __DIR__ . '/../../../db/base.php';

require_once __DIR__ . '/../../../db/entities/usuarios.php';
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
function buscarDocumento(){

$pdo = (new Database())->connect();
$sql = 'SELECT DISTINCT documento from ban02 WHERE id_empresa = :id_empresa';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_empresa' => $_SESSION['usuario']->id_empresa]);
$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
return $novoNumero;
}

function buscarData($conta = null) {
    $pdo = (new Database())->connect();
    $sql = 'SELECT MAX(data) as ultima_data FROM ban02_imp WHERE id_empresa = :id_empresa';

    if($conta != null) {
        $sql .= ' AND id_ban01 = :conta';
    }

    $stmt = $pdo->prepare($sql);
    if($conta != null) {
        $stmt->bindValue(':conta', $conta);
    }
    $stmt->bindValue(':id_empresa', $_SESSION['usuario']->id_empresa);    

    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['ultima_data']) {
        return $row['ultima_data'];
    } else {
        return null;
    }
}

?>