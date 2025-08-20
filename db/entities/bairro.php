<?php
require_once __DIR__ . '/../base.php';

class Bairro {
    public $id;
    public $id_empresa;
    public $nome;


    public function __construct($id = null, $id_empresa = null, $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;

    }

public static function create($bairro) {
    $pdo = (new Database())->connect();

    $sql = 'INSERT INTO bairro (id_empresa, nome) 
            VALUES (:id_empresa, :nome)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_empresa', $bairro->id_empresa);
    $stmt->bindValue(':nome', $bairro->nome);


    

    return $stmt->execute();
}

    public static function read($id = null, $idempresa = null): array {
    $pdo = (new Database())->connect();
    $query = 'SELECT * FROM bairro';
    $conditions = [];

    if ($id != null) $conditions[] = 'id = :id';
    if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }


    $stmt = $pdo->prepare($query);

    if ($id != null) $stmt->bindValue(':id', $id);
    if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);

    $stmt->execute();

   $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Bairro::class);
return $stmt->fetchAll();
}

    public static function update($bairro) {
    $pdo = (new Database())->connect();

    $sql = 'UPDATE bairro 
            SET nome = :nome 
            WHERE id = :id';

    $stmt = $pdo->prepare($sql);

    

    $stmt->bindValue(':nome', $bairro->nome);
    $stmt->bindValue(':id', $bairro->id);

    return $stmt->execute();
}
        public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM bairro WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }


}
