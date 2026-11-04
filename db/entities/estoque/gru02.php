<?php 

Class Gru02 {
    public $id;
    public $id_empresa;
    public $id_grupo;
    public $descricao;

    public function __construct($id = null, $id_empresa = null, $id_grupo = null, $descricao = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_grupo = $id_grupo;
        $this->descricao = $descricao;
    }

    public static function create($grupo) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO gru02 (id_empresa, id_grupo, descricao) VALUES (:id_empresa, :id_grupo, :descricao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $grupo->id_empresa);
        $stmt->bindValue(':id_grupo', $grupo->id_grupo);
        $stmt->bindValue(':descricao', $grupo->descricao);
        return $stmt->execute();
    }

    public static function update($grupo) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE gru02 SET descricao = :descricao
                WHERE id = :id AND id_empresa = :id_empresa AND id_grupo = :id_grupo';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':descricao', $grupo->descricao);
        $stmt->bindValue(':id', $grupo->id);
        $stmt->bindValue(':id_empresa', $grupo->id_empresa);
        $stmt->bindValue(':id_grupo', $grupo->id_grupo);
        return $stmt->execute();
    }

    public static function delete($id, $id_empresa) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM gru02 WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':id_empresa', $id_empresa);
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_grupo = null, $descricao = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM gru02';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_grupo != null) $conditions[] = 'id_grupo = :id_grupo';
        if ($descricao != null) $conditions[] = 'descricao LIKE :descricao';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_grupo != null) $stmt->bindValue(':id_grupo', $id_grupo);
        if ($descricao != null) $stmt->bindValue(':descricao', '%' . $descricao . '%');

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}