<?php

Class Controle {
    public $id;
    public $id_empresa;
    public $hora;
    public $tolerancia;

    public function __construct($id, $id_empresa, $hora, $tolerancia) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->hora = $hora;
        $this->tolerancia = $tolerancia;
    }

    public static function create(Controle $controle) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("INSERT INTO controle (id_empresa, hora, tolerancia) VALUES (:id_empresa, :hora, :tolerancia)");
        $stmt->bindParam(':id_empresa', $controle->id_empresa);
        $stmt->bindParam(':hora', $controle->hora);
        $stmt->bindParam(':tolerancia', $controle->tolerancia);
        $stmt->execute();
    }

    public static function read($id_empresa) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("SELECT * FROM controle WHERE id_empresa = :id_empresa");
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $controles = [];
        foreach ($result as $row) {
            $controles[] = new Controle($row['id'], $row['id_empresa'], $row['hora'], $row['tolerancia']);
        }
        return $controles;
    }

    public static function update(Controle $controle) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("UPDATE controle SET hora = :hora, tolerancia = :tolerancia WHERE id = :id");
        $stmt->bindParam(':hora', $controle->hora);
        $stmt->bindParam(':tolerancia', $controle->tolerancia);
        $stmt->bindParam(':id', $controle->id);
        $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("DELETE FROM controle WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

}