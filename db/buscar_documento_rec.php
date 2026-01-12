<?php 

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 3) {
    header('Location: /');
    exit;
}
function buscarDocumento(){

$pdo = (new Database())->connect();
$sql = "SELECT MAX(CAST(documento AS UNSIGNED)) AS ultimo 
              FROM rec01 
             WHERE id_empresa = :id_empresa";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":id_empresa" => $_SESSION['usuario']->id_empresa]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doc && $doc["ultimo"] !== null) {
        $novoNumero = $doc["ultimo"] + 1;
    } else {
        $novoNumero = 1; // se não existir nenhum documento ainda nessa empresa
    }
return $novoNumero;
}
?>