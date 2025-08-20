<?php
require_once __DIR__ . '/../base.php';

class Cidade {
    public $id;
    public $id_empresa;
    public $nome;


    public function __construct($id = null, $id_empresa = null, $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;

    }

public static function create($cidade) {
    $pdo = (new Database())->connect();

    $sql = 'INSERT INTO cidade (id_empresa, nome) 
            VALUES (:id_empresa, :nome)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_empresa', $cidade->id_empresa);
    $stmt->bindValue(':nome', $cidade->nome);


    

    return $stmt->execute();
}

    public static function read($id = null, $idempresa = null, $cidade = null): array {
    $pdo = (new Database())->connect();
    $query = 'SELECT * FROM cidade';
    $conditions = [];

    if ($id != null) $conditions[] = 'id = :id';
    if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';
    if ($cidade != null) $conditions[] = 'nome = :nome';

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }


    $stmt = $pdo->prepare($query);

    if ($id != null) $stmt->bindValue(':id', $id);
    if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);
    if ($cidade != null) $stmt->bindValue(':nome', $cidade);

    $stmt->execute();

   $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Cidade::class);
return $stmt->fetchAll();
}

    public static function update($cidade) {
    $pdo = (new Database())->connect();

    $sql = 'UPDATE cidade 
            SET nome = :nome
            WHERE id = :id';

    $stmt = $pdo->prepare($sql);

    

    $stmt->bindValue(':nome', $cidade->nome);
    $stmt->bindValue(':id', $cidade->id);

    return $stmt->execute();
}
        public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM cidade WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

}
