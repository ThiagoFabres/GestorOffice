<?php
require_once __DIR__ . '/../base.php';
class CentroContas {
    public $id;
    public $id_empresa;
    public $nome;
    public function __construct($id = null, $id_empresa = null, $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;
    }
    public static function create($centrocontas) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO contas (id_empresa, nome) 
                VALUES (:id_empresa, :nome)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $centrocontas->id_empresa);
        $stmt->bindValue(':nome', $centrocontas->nome);
        return $stmt->execute();
    }
    public static function read($id = null, $idempresa = null, $nome = null): array {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM contas';
        $conditions = [];
        if ($id != null) $conditions[] = 'id= :id';
        if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($nome != null) $conditions[] = 'nome = :nome';
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) $stmt->bindValue(':id', $id);
        if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);
        if ($nome != null) $stmt->bindValue(':nome', $nome);
        $stmt->execute();
       $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, CentroContas::class);
    return $stmt->fetchAll();
    }
    public static function update($centrocontas) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE contas SET id_empresa = :id_empresa, nome = :nome WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $centrocontas->id);
        $stmt->bindValue(':id_empresa', $centrocontas->id_empresa);
        $stmt->bindValue(':nome', $centrocontas->nome);
        return $stmt->execute();
    }
    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM contas WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
?>