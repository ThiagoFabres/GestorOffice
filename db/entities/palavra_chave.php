<?php 
require_once __DIR__ . '/../base.php';

class Pal01 {
    public $id;
    public $palavra;
    public $id_empresa;
    public $id_con01;
    public $id_con02;
    public $tipo;

    public function __construct($id = null, $palavra = '', $id_empresa = null, $id_con01 = null, $id_con02 = null, $tipo = null) {
        $this->id = $id;
        $this->palavra = $palavra;
        $this->id_empresa = $id_empresa;
        $this->id_con01 = $id_con01;
        $this->id_con02 = $id_con02;
        $this->tipo = $tipo;
    }

    public static function create($palavraChave) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO pal01 (palavra, id_empresa, id_con01, id_con02, tipo) 
                            VALUES (:palavra, :id_empresa, :id_con01, :id_con02, :tipo)';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':palavra', $palavraChave->palavra);
        $stmt->bindValue(':id_empresa', $palavraChave->id_empresa);
        $stmt->bindValue(':id_con01', $palavraChave->id_con01);
        $stmt->bindValue(':id_con02', $palavraChave->id_con02);
        $stmt->bindValue(':tipo', $palavraChave->tipo);

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
        $sql .= ' ORDER BY palavra ASC';

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
            $results[] = new Pal01($row['id'], $row['palavra'], $row['id_empresa'], $row['id_con01'], $row['id_con02'], $row['tipo']);
        }
        return $results;
    }

    public static function update($palavraChave) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE pal01 SET palavra = :palavra, id_empresa = :id_empresa, id_con01 = :id_con01, id_con02 = :id_con02, tipo = :tipo WHERE id = :id';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':palavra', $palavraChave->palavra);
        $stmt->bindValue(':id_empresa', $palavraChave->id_empresa);
        $stmt->bindValue(':id_con01', $palavraChave->id_con01);
        $stmt->bindValue(':id_con02', $palavraChave->id_con02);
        $stmt->bindValue(':id', $palavraChave->id);
        $stmt->bindValue(':tipo', $palavraChave->tipo);

        $stmt->execute();
        return $stmt->rowCount() > 0;
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