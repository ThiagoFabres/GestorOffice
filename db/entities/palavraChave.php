<?php 
require_once __DIR__ . '/../base.php';

class Pal01 {
    public $id;
    public $nome;
    public $id_empresa;
    public $con01_id;
    public $con02_id;

    public function __construct($id = null, $nome = '', $id_empresa = null, $con01_id = null, $con02_id = null) {
        $this->id = $id;
        $this->nome = $nome;
        $this->id_empresa = $id_empresa;
        $this->con01_id = $con01_id;
        $this->con02_id = $con02_id;
    }

    public static function create($palavraChave) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO pal01 (nome, id_empresa, con01_id, con02_id) 
                            VALUES (:nome, :id_empresa, :con01_id, :con02_id)';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':nome', $palavraChave->nome);
        $stmt->bindValue(':id_empresa', $palavraChave->id_empresa);
        $stmt->bindValue(':con01_id', $palavraChave->con01_id);
        $stmt->bindValue(':con02_id', $palavraChave->con02_id);

        $stmt->execute();
        return $pdo->lastInsertId();
    }

    public static function read($id = null, $id_empresa = null) {
        $pdo = (new Database())->connect();
        $sql = 'SELECT * FROM pal01 WHERE 1=1';
        
        if ($id !== null) {
            $sql .= ' AND id = :id';
        }
        if ($id_empresa !== null) {
            $sql .= ' AND id_empresa = :id_empresa';
        }

        $stmt = $pdo->prepare($sql);

        if ($id !== null) {
            $stmt->bindValue(':id', $id);
        }
        if ($id_empresa !== null) {
            $stmt->bindValue(':id_empresa', $id_empresa);
        }

        $stmt->execute();
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new Pal01($row['id'], $row['nome'], $row['id_empresa'], $row['con01_id'], $row['con02_id']);
        }
        return $results;
    }

    public static function update($palavraChave) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE pal01 SET nome = :nome, id_empresa = :id_empresa, con01_id = :con01_id, con02_id = :con02_id WHERE id = :id';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':nome', $palavraChave->nome);
        $stmt->bindValue(':id_empresa', $palavraChave->id_empresa);
        $stmt->bindValue(':con01_id', $palavraChave->con01_id);
        $stmt->bindValue(':con02_id', $palavraChave->con02_id);
        $stmt->bindValue(':id', $palavraChave->id);

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM pal01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
}
}
?>