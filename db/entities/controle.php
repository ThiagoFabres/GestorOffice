<?php

Class Controle {
    public $id;
    public $id_empresa;
    public $hora;
    public $tolerancia;
    public $tipo;

    public function __construct($id = null, $id_empresa = null, $hora = null, $tolerancia = null, $tipo = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->hora = $hora;
        $this->tolerancia = $tolerancia;
        $this->tipo = $tipo;
    }

    public static function create(Controle $controle) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("INSERT INTO controle (id_empresa, hora, tolerancia, tipo) VALUES (:id_empresa, :hora, :tolerancia, :tipo)");
        $stmt->bindParam(':id_empresa', $controle->id_empresa);
        $stmt->bindParam(':hora', $controle->hora);
        $stmt->bindParam(':tolerancia', $controle->tolerancia);
        $stmt->bindParam(':tipo', $controle->tipo);
        $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $tipo = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM controle';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($tipo != null) $conditions[] = 'tipo = :tipo';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY hora ASC';

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($tipo != null) $stmt->bindValue(':tipo', $tipo);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update(Controle $controle) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("UPDATE controle SET
        hora = :hora, 
        tolerancia = :tolerancia,
        tipo = :tipo
        WHERE id = :id");

        $stmt->bindParam(':hora', $controle->hora);
        $stmt->bindParam(':tolerancia', $controle->tolerancia);
        $stmt->bindParam(':id', $controle->id);
        $stmt->bindParam(':tipo', $controle->tipo);
        $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("DELETE FROM controle WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

}