<?php
require_once __DIR__ . '/../base.php';

Class Con01 {
    public $id;
    public $id_empresa;
    public $tipo;
    public $nome;

    public function __construct($id = null, $id_empresa = null,  $tipo = '', $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->tipo = $tipo;
        $this->nome = $nome;

    }

    public static function create($conta) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO con01 (id_empresa, tipo, nome) 
                VALUES (:id_empresa, :tipo, :nome)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $conta->id_empresa);
        $stmt->bindValue(':tipo', $conta->tipo);
        $stmt->bindValue(':nome', $conta->nome);

        return $stmt->execute();
    }

    public static function read($id = null, $idempresa = null, $tipo = null, $ordenar_por = null) : array {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM con01';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($tipo != null) $conditions[] = 'tipo = :tipo';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if($ordenar_por != null) {
            if($ordenar_por == 'tipo') {
                
                $query .= ' ORDER BY tipo asc ';
            }
            
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) $stmt->bindValue(':id', $id);
        if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);
        if ($tipo != null) $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Con01::class);
        return $stmt->fetchAll();
    }

    public static function update($conta) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE con01 
                SET tipo = :tipo, 
                    nome = :nome 
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $conta->id);
        $stmt->bindValue(':tipo', $conta->tipo);
        $stmt->bindValue(':nome', $conta->nome);

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM con01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}

Class Con02 {
    public $id;
    public $id_empresa;
    public $id_con01;
    public $nome;

    public function __construct($id = null, $id_empresa = null,  $id_con01 = null, $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_con01 = $id_con01;
        $this->nome = $nome;

    }

    public static function create($conta) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO con02 (id_empresa, id_con01, nome) 
                VALUES (:id_empresa, :id_con01, :nome)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $conta->id_empresa);
        $stmt->bindValue(':id_con01', $conta->id_con01);
        $stmt->bindValue(':nome', $conta->nome);

        return $stmt->execute();
    }
    public static function read($id = null, $idempresa = null, $con01_id = null, $filtro_data_inicial = null, $filtro_data_final = null): array {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM con02';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($con01_id != null) $conditions[] = 'id_con01 = :id_con01';
        if ($filtro_data_inicial != null) $conditions[] = 'data_r >= :data_inicial';
        if ($filtro_data_final != null) $conditions[] = 'data_r <= :data_final';


        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) $stmt->bindValue(':id', $id);
        if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);
        if ($con01_id != null) $stmt->bindValue(':id_con01', $con01_id);
        if ($filtro_data_inicial != null) $stmt->bindValue(':data_inicial', $filtro_data_inicial);
        if ($filtro_data_final != null) $stmt->bindValue(':data_final', $filtro_data_final);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Con02::class);
        return $stmt->fetchAll();
    }

    public static function update($conta) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE con02 
                SET id_con01 = :id_con01, 
                    nome = :nome 
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $conta->id);
        $stmt->bindValue(':id_con01', $conta->id_con01);
        $stmt->bindValue(':nome', $conta->nome);

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM con02 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}

?>