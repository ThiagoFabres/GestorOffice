<?php
class Ope01{
    public $id;
    public $id_empresa;
    public $descricao;
    public $id_cliente;
    public $id_custos;
    public $id_con01;
    public $id_con02;


    public function __construct($id, $id_empresa, $descricao, $id_cliente, $id_custos, $id_con01, $id_con02 ){
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->descricao = $descricao;
        $this->id_cliente = $id_cliente;
        $this->id_custos = $id_custos;
        $this->id_con01 = $id_con01;
        $this->id_con02 = $id_con02;
    }

    public static function create($ope) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO ope01 (id_empresa, descricao, id_cliente, id_custos, id_con01, id_con02) 
                VALUES (:id_empresa, :descricao, :id_cliente, :id_custos, :id_con01, :id_con02)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $ope->id_empresa);
        $stmt->bindValue(':descricao', $ope->descricao);
        $stmt->bindValue(':id_cliente', $ope->id_cliente);
        $stmt->bindValue(':id_custos', $ope->id_custos);
        $stmt->bindValue(':id_con01', $ope->id_con01);
        $stmt->bindValue(':id_con02', $ope->id_con02);

        return $stmt->execute();
    }

    public static function read(
        $id = null,
        $id_empresa = null,
        ) {
        $pdo = (new Database())->connect();
        $sql = 'SELECT * FROM ope01';

        $conditions = [];
        if($id != null) {
            $conditions[] = 'id = :id';
        }
        if ($id_empresa !== null) {
            $conditions[] = 'id_empresa = :id_empresa';
        }
        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }


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
            $results[] = new Ope01(
                $row['id'], 
                $row['id_empresa'],
                $row['descricao'], 
                $row['id_cliente'],
                $row['id_custos'],
                $row['id_con01'] ,
                $row['id_con02'] 
                 );
        }
        return $results;
    }
    public static function update($ope) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE ope01 SET 
        descricao = :descricao,
        id_cliente = :id_cliente,
        id_custos = :id_custos,
        id_con01 = :id_con01,
        id_con02 = :id_con02
        
        
        WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':descricao', $ope->descricao);
        $stmt->bindValue(':id_cliente', $ope->id_cliente);
        $stmt->bindValue(':id_custos', $ope->id_custos);
        $stmt->bindValue(':id_con01', $ope->id_con01);
        $stmt->bindValue(':id_con02', $ope->id_con02);
        $stmt->bindValue(':id', $ope->id);

        return $stmt->execute();
    }
    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM ope01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}