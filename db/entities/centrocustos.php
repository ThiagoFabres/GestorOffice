<?php
require_once __DIR__ . '/../base.php';
class CentroCustos {
    public $id;
    public $id_empresa;
    public $nome;
    public function __construct($id = null, $id_empresa = null, $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;
    }
    public static function create($centrocustos) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO custos (id_empresa, nome) 
                VALUES (:id_empresa, :nome)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $centrocustos->id_empresa);
        $stmt->bindValue(':nome', $centrocustos->nome);
        return $stmt->execute();
    }
    public static function read($id = null, $id_empresa = null, $nome = null): array {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM custos';
        $conditions = [];
        if ($id != null) $conditions[] = 'id= :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($nome != null) $conditions[] = 'nome = :nome';
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($nome != null) $stmt->bindValue(':nome', $nome);
        $stmt->execute();
       $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, CentroCustos::class);
    return $stmt->fetchAll();
    }
    public static function update($centrocustos) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE custos SET id_empresa = :id_empresa, nome = :nome WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $centrocustos->id);
        $stmt->bindValue(':id_empresa', $centrocustos->id_empresa);
        $stmt->bindValue(':nome', $centrocustos->nome);
        return $stmt->execute();
    }
    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM custos WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
?>