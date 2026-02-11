<?php
class Pra01{
    public $id;
    public $id_empresa;
    public $id_operadora;
    public $id_bandeira;
    public $prazo;
    public $parcela;
    public $taxa;


    public function __construct($id, $id_empresa, $id_operadora, $id_bandeira, $prazo, $parcela, $taxa){
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_operadora = $id_operadora;
        $this->id_bandeira = $id_bandeira;
        $this->prazo = $prazo;
        $this->parcela = $parcela;
        $this->taxa = $taxa;
    }

    public static function create($pra) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO pra01 (id_empresa, id_operadora, id_bandeira, prazo, parcela, taxa) 
                VALUES (:id_empresa, :id_operadora, :id_bandeira, :prazo, :parcela, :taxa)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $pra->id_empresa);
        $stmt->bindValue(':id_operadora', $pra->id_operadora);
        $stmt->bindValue(':id_bandeira', $pra->id_bandeira);
        $stmt->bindValue(':prazo', $pra->prazo);
        $stmt->bindValue(':parcela', $pra->parcela);
        $stmt->bindValue(':taxa', $pra->taxa);

        return $stmt->execute();
    }

    public static function read(
        $id = null,
        $id_operadora = null,
        $id_empresa = null,
        $id_bandeira = null,
        $parcela = null,
        $direcao = 'ASC'
        ) {
        $pdo = (new Database())->connect();
        $sql = 'SELECT * FROM pra01';

        $conditions = [];
        if  ($id !== null) $conditions[] = 'id = :id';
        if  ($id_empresa !== null) $conditions[] = 'id_empresa = :id_empresa';
        if  ($id_operadora !== null) $conditions[] = 'id_operadora = :id_operadora';
        if  ($id_bandeira !== null) $conditions[] = 'id_bandeira = :id_bandeira';
        if  ($parcela !== null) $conditions[] = 'parcela = :parcela';

        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY parcela '. $direcao;

        $stmt = $pdo->prepare($sql);
        if ($id !== null) $stmt->bindValue(':id', $id);
        if($id_empresa !== null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_operadora !== null) $stmt->bindValue(':id_operadora', $id_operadora);
        if ($id_bandeira !== null) $stmt->bindValue(':id_bandeira', $id_bandeira);
        if ($parcela !== null) $stmt->bindValue(':parcela', $parcela);
        
        $stmt->execute();
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new Pra01(
                        $row['id'],
                        $row['id_empresa'],
                        $row['id_operadora'],
                        $row['id_bandeira'], 
                        $row['prazo'], 
                        $row['parcela'], 
                        $row['taxa']
                        );
        }
        return $results;
    }
    public static function update($pra) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE pra01 SET 
                prazo = :prazo,
                parcela = :parcela,
                taxa = :taxa
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':prazo', $pra->prazo);
        $stmt->bindValue(':parcela', $pra->parcela);
        $stmt->bindValue(':taxa', $pra->taxa);
        $stmt->bindValue(':id', $pra->id);

        return $stmt->execute();
    }
    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM pra01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}