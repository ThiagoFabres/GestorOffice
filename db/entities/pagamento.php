<?php
require_once __DIR__ . '/../base.php';

class TipoPagamento {
    public $id;
    public $id_empresa;
    public $nome;


    public function __construct($id = null, $id_empresa = null, $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;

    }

public static function create($tipo_pag) {
    $pdo = (new Database())->connect();

    $sql = 'INSERT INTO tipo_pag (id_empresa, nome) 
            VALUES (:id_empresa, :nome)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_empresa', $tipo_pag->id_empresa);
    $stmt->bindValue(':nome', $tipo_pag->nome);


    

    return $stmt->execute();
}

    public static function read($id = null, $idempresa = null, $nome = null): array {
    $pdo = (new Database())->connect();
    $query = 'SELECT * FROM tipo_pag';
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

   $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Bairro::class);
return $stmt->fetchAll();
}

    public static function update($tipo_pag) {
    $pdo = (new Database())->connect();

    $sql = 'UPDATE tipo_pag 
            SET nome = :nome 
            WHERE id = :id';

    $stmt = $pdo->prepare($sql);

    

    $stmt->bindValue(':nome', $tipo_pag->nome);
    $stmt->bindValue(':id', $tipo_pag->id);

    return $stmt->execute();
}
        public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM pagamento WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }


}
