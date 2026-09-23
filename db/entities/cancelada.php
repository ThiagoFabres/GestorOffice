<?php

class Cancelada {
    public $id;
    public $id_empresa;
    public $data;
    public $bandeira;
    public $tipo;
    public $estado;
    public $valor;
    public $comprovante;

    public function __construct($id = null, $id_empresa = null, $data = null, $bandeira = null, $tipo = null, $estado = null, $valor = null, $comprovante = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa ?? $_SESSION['usuario']->id_empresa;
        $this->data = $data;
        $this->bandeira = $bandeira;
        $this->tipo = $tipo;
        $this->estado = $estado;
        $this->valor = $valor;
        $this->comprovante = $comprovante;
    }

    public static function create($cancelada) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO canceladas (id_empresa, data, bandeira, tipo, estado, valor, comprovante) 
                VALUES (:id_empresa, :data, :bandeira, :tipo, :estado, :valor, :comprovante)';
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $cancelada->id_empresa);
        $stmt->bindValue(':data', $cancelada->data);
        $stmt->bindValue(':bandeira', $cancelada->bandeira);
        $stmt->bindValue(':tipo', $cancelada->tipo);
        $stmt->bindValue(':estado', $cancelada->estado);
        $stmt->bindValue(':valor', $cancelada->valor);
        $stmt->bindValue(':comprovante', $cancelada->comprovante);

        return $stmt->execute();
    }

    public static function read(
        $id = null, 
        $id_empresa = null, 
        $data = null, 
        $bandeira = null, 
        $tipo = null, 
        $estado = null, 
        $comprovante = null,
        $filtro_data_inicial = null,
        $filtro_data_final = null,
        ) {


        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM canceladas';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($data != null) $conditions[] = 'data = :data';
        if ($filtro_data_inicial != null) $conditions[] = 'data >= :filtro_data_inicial';
        if ($filtro_data_final != null) $conditions[] = 'data <= :filtro_data_final';
        if ($bandeira != null) $conditions[] = 'bandeira = :bandeira';
        if ($tipo != null) $conditions[] = 'tipo = :tipo';
        if ($estado != null) $conditions[] = 'estado = :estado';
        if ($comprovante != null) $conditions[] = 'comprovante = :comprovante';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($data != null) $stmt->bindValue(':data', $data);
        if ($filtro_data_inicial != null) $stmt->bindValue(':filtro_data_inicial', $filtro_data_inicial);
        if ($filtro_data_final != null) $stmt->bindValue(':filtro_data_final', $filtro_data_final);
        if ($bandeira != null) $stmt->bindValue(':bandeira', $bandeira);
        if ($tipo != null) $stmt->bindValue(':tipo', $tipo);
        if ($estado != null) $stmt->bindValue(':estado', $estado);
        if ($comprovante != null) $stmt->bindValue(':comprovante', $comprovante);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($cancelada) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE canceladas SET
                id_empresa = :id_empresa,
                data = :data,
                bandeira = :bandeira,
                tipo = :tipo,
                estado = :estado,
                valor = :valor,
                comprovante = :comprovante
                WHERE id = :id';
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $cancelada->id_empresa);
        $stmt->bindValue(':data', $cancelada->data);
        $stmt->bindValue(':bandeira', $cancelada->bandeira);
        $stmt->bindValue(':tipo', $cancelada->tipo);
        $stmt->bindValue(':estado', $cancelada->estado);
        $stmt->bindValue(':valor', $cancelada->valor);
        $stmt->bindValue(':comprovante', $cancelada->comprovante);
        $stmt->bindValue(':id', $cancelada->id);

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM canceladas WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}