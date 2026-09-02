<?php

Class Procedimento {
    public $id;
    public $id_empresa;
    public $texto;

    public function __construct(
        $id = null, 
        $id_empresa = null, 
        $texto = null
        ) {
            $this->id = $id;
            $this->id_empresa = $id_empresa;
            $this->texto = $texto;
        }
    public static function create($procedimento) {
        $pdo = (new Database())->connect();
        $query = 'INSERT INTO procedimentos (id_empresa, texto) VALUES (:id_empresa, :texto)';
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':id_empresa', $procedimento->id_empresa);
        $stmt->bindValue(':texto', $procedimento->texto);
        return $stmt->execute();
    }
    public static function read($id_empresa = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM procedimentos';
        $conditions = [];

        if($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);

    }

    public static function update($procedimento) {
        $pdo = (new Database())->connect();
        $query = 'UPDATE procedimentos SET texto = :texto WHERE id = :id';
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':id', $procedimento->id);
        $stmt->bindValue(':texto', $procedimento->texto);
        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $query = 'DELETE FROM procedimentos WHERE id = :id';
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}