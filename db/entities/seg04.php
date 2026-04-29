<?php

class Seg04 {
    public $id;
    public $id_empresa;
    public $id_seg01;
    public $id_seg02;
    public $alarme;
    public $respondeu;
    public $correto;

    public function __construct($id = null, $id_empresa = null, $id_seg01 = null, $id_seg02 = null, $alarme = '', $respondeu = '', $correto = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_seg01 = $id_seg01;
        $this->id_seg02 = $id_seg02;
        $this->alarme =  $alarme;
        $this->respondeu =  $respondeu;
        $this->correto =  $correto;

    }

    public static function create($seg04) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO seg04 (id_empresa, id_seg01, id_seg02, alarme, respondeu, correto) 
                VALUES (:id_empresa, :id_seg01, :id_seg02, :alarme, :respondeu, :correto)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $seg04->id_empresa);
        $stmt->bindValue(':id_seg01', $seg04->id_seg01);
        $stmt->bindValue(':id_seg02', $seg04->id_seg02);
        $stmt->bindValue(':alarme',  $seg04->alarme);
        $stmt->bindValue(':respondeu',  $seg04->respondeu);
        $stmt->bindValue(':correto',  $seg04->correto);
        return $stmt->execute();

    }

    public static function read($id = null, $id_empresa = null, $id_seg01 = null, $id_seg02 = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM seg04';
        $conditions = [];
        if ($id != null) $conditions[] = 'id= :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_seg01 != null) $conditions[] = 'id_seg01 = :id_seg01';
        if ($id_seg02 != null) $conditions[] = 'id_seg02 = :id_seg02';
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_seg01 != null) $stmt->bindValue(':id_seg01', $id_seg01);
        if ($id_seg02 != null) $stmt->bindValue(':id_seg02', $id_seg02);
        $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($seg04) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE seg04 SET id_empresa = :id_empresa, id_seg01 = :id_seg01, id_seg02 = :id_seg02, alarme = :alarme, respondeu = :respondeu, correto = :correto WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $seg04->id);
        $stmt->bindValue(':id_empresa', $seg04->id_empresa);
        $stmt->bindValue(':id_seg01', $seg04->id_seg01);
        $stmt->bindValue(':id_seg02', $seg04->id_seg02);
        $stmt->bindValue(':alarme',  $seg04->alarme);
        $stmt->bindValue(':respondeu',  $seg04->respondeu);
        $stmt->bindValue(':correto',  $seg04->correto);
        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM seg04 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}