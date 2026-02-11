<?php
class Band01{
    public $id;
    public $id_empresa;
    public $id_operadora;
    public $descricao;
    public $tipo;


    public function __construct($id, $id_empresa, $id_operadora, $descricao, $tipo = null){
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_operadora = $id_operadora;
        $this->descricao = $descricao;
        $this->tipo = $tipo;
    }

    public static function create($band) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO band01 (id_empresa, id_operadora, descricao, tipo) 
                VALUES (:id_empresa, :id_operadora, :descricao, :tipo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $band->id_empresa);
        $stmt->bindValue(':id_operadora', $band->id_operadora);
        $stmt->bindValue(':descricao', $band->descricao);
        $stmt->bindValue(':tipo', $band->tipo);

        return $stmt->execute();
    }

    public static function read(
        $id = null,
        $id_empresa = null,
        $id_operadora = null,
        ) {
        $pdo = (new Database())->connect();
        $sql = 'SELECT * FROM band01';

        $conditions = [];
        
        if ($id !== null) $conditions[] = 'id = :id';
        if ($id_operadora !== null) $conditions[] = 'id_operadora = :id_operadora';
        if ($id_empresa !== null) $conditions[] = 'id_empresa = :id_empresa';
        
        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY descricao ASC, Tipo ASC';

        $stmt = $pdo->prepare($sql);
        if ($id !== null) $stmt->bindValue(':id', $id);
        if ($id_empresa !== null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_operadora !== null) $stmt->bindValue(':id_operadora', $id_operadora);


        
        $stmt->execute();
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new Band01(
                $row['id'], 
                $row['id_empresa'], 
                $row['id_operadora'], 
                $row['descricao'],
                $row['tipo']
                );
        }
        return $results;
    }
    public static function update($band) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE band01 SET
                descricao = :descricao,
                tipo = :tipo
                WHERE id = :id'; 
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':descricao', $band->descricao);
        $stmt->bindValue(':id', $band->id);
        $stmt->bindValue(':tipo', $band->tipo); 

        return $stmt->execute();
    }
    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM band01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}