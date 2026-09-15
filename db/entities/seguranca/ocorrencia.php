<?php
Class Ocorrencia {
    public $id;
    public $id_empresa;
    public $id_turno;
    public $texto;

    public static function read($id = null, $id_empresa = null, $id_turno = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM ocorrencias';
        $conditions = [];

        if($id != null) $conditions[] = 'id = :id';
        if($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if($id_turno != null) $conditions[] = 'id_turno = :id_turno';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY id DESC';
    
        $stmt = $pdo->prepare($query);

        if($id != null) $stmt->bindValue(':id', $id);
        if($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if($id_turno != null) $stmt->bindValue(':id_turno', $id_turno);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);

    }
}