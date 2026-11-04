<?php 

Class Gru01 {
    public $id;
    public $id_empresa;
    public $descricao;

    public function __construct($id = null, $id_empresa = null, $descricao = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->descricao = $descricao;
    }

    public static function create($grupo) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO gru01 (id_empresa, descricao) VALUES (:id_empresa, :descricao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $grupo->id_empresa);
        $stmt->bindValue(':descricao', $grupo->descricao);
        return $stmt->execute();
    }

    public static function update($grupo) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE gru01 SET descricao = :descricao WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':descricao', $grupo->descricao);
        $stmt->bindValue(':id', $grupo->id);
        $stmt->bindValue(':id_empresa', $grupo->id_empresa);
        return $stmt->execute();
    }

    public static function delete($id, $id_empresa) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM gru01 WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':id_empresa', $id_empresa);
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $descricao = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM gru01';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($descricao != null) $conditions[] = 'descricao LIKE :descricao';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($descricao != null) $stmt->bindValue(':descricao', '%' . $descricao . '%');

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}