<?php

class Ativ01 {
    public $id;
    public $id_empresa;
    public $data;
    public $hora;
    public $nome;
    public $localizacao;

    public function __construct($id = null, $id_empresa = null, $data = null, $hora = null, $nome = null, $localizacao = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->data = $data;
        $this->hora = $hora;
        $this->nome = $nome;
        $this->localizacao = $localizacao;
    }

    public static function create($ativ01) {
        $pdo = (new Database())->connect();

        $sql = 'INSERT INTO ativ01 (id_empresa, data, hora, nome, localizacao) 
                VALUES (:id_empresa, :data, :hora, :nome, :localizacao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $ativ01->id_empresa);
        $stmt->bindValue(':data', $ativ01->data);
        $stmt->bindValue(':hora', $ativ01->hora);
        $stmt->bindValue(':nome', $ativ01->nome);
        $stmt->bindValue(':localizacao', $ativ01->localizacao);

        
    
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $data = null): array {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM ativ01';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if($data != null) $conditions[] = 'data = :data';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY data DESC, hora DESC';

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if($data != null) $stmt->bindValue(':data', $data);
        $stmt->execute();

       return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}