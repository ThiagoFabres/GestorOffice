<?php

class Seg03 {
    public $id;
    public $id_empresa;
    public $id_seg01;
    public $id_seg02;
    public $id_usuario;
    public $data;
    public $hora;
    public $descricao;
    public $obs;
    public $localizacao;

    public function __construct($id = null, $id_empresa = null, $id_seg01 = null, $id_seg02 = null, $id_usuario = null, $data = '', $hora = '', $descricao = '', $obs = '', $localizacao = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_seg01 = $id_seg01;
        $this->id_seg02 = $id_seg02;
        $this->id_usuario =  $id_usuario;
        $this->data =  $data;
        $this->hora =  $hora;
        $this->descricao =  $descricao;
        $this->obs =  $obs;
        $this->localizacao =  $localizacao;

    }

    public static function create($seg03) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO seg03 (id_empresa, id_seg01, id_seg02, id_usuario, data, hora, descricao, obs, localizacao) 
                VALUES (:id_empresa, :id_seg01, :id_seg02, :id_usuario, :data, :hora, :descricao, :obs, :localizacao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $seg03->id_empresa);
        $stmt->bindValue(':id_seg01', $seg03->id_seg01);
        $stmt->bindValue(':id_seg02', $seg03->id_seg02);
        $stmt->bindValue(':id_usuario',  $seg03->id_usuario);
        $stmt->bindValue(':data',  $seg03->data);
        $stmt->bindValue(':hora',  $seg03->hora);
        $stmt->bindValue(':descricao',  $seg03->descricao);
        $stmt->bindValue(':obs',  $seg03->obs);
        $stmt->bindValue(':localizacao',  $seg03->localizacao);
        return $stmt->execute();

    }

    public static function read($id = null, $id_empresa = null, $id_seg01 = null, $id_seg02 = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM seg03';
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
       $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Seg03::class);
    }

    public static function update($seg03) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE seg03 SET id_empresa = :id_empresa, id_seg01 = :id_seg01, id_seg02 = :id_seg02, id_usuario = :id_usuario, data = :data, hora = :hora, descricao = :descricao, obs = :obs, localizacao = :localizacao WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $seg03->id);
        $stmt->bindValue(':id_empresa', $seg03->id_empresa);
        $stmt->bindValue(':id_seg01', $seg03->id_seg01);
        $stmt->bindValue(':id_seg02', $seg03->id_seg02);
        $stmt->bindValue(':id_usuario',  $seg03->id_usuario);
        $stmt->bindValue(':data',  $seg03->data);
        $stmt->bindValue(':hora',  $seg03->hora);
        $stmt->bindValue(':descricao',  $seg03->descricao);
        $stmt->bindValue(':obs',  $seg03->obs);
        $stmt->bindValue(':localizacao',  $seg03->localizacao);
        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM seg03 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
