<?php 

class Cx01{

    public $id;
    public $id_empresa;
    public $id_usuario;
    public $horario_inicio;
    public $horario_fim;
    public $fundo_caixa;

    public function __construct($id = null, $id_empresa = null, $id_usuario = null, $horario_inicio = null, $horario_fim = null, $fundo_caixa = null)
    {
       $this->id = $id;
       $this->id_empresa = $id_empresa;
       $this->id_usuario = $id_usuario;
       $this->horario_inicio = $horario_inicio;
       $this->horario_fim = $horario_fim;
       $this->fundo_caixa = $fundo_caixa;
    }

    public static function create($cx01) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO cx01 (id_empresa, id_usuario, horario_inicio, horario_fim, fundo_caixa) 
                    VALUES (:id_empresa, :id_usuario, :horario_inicio, :horario_fim, :fundo_caixa)';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $cx01->id_empresa);
        $stmt->bindValue(':id_usuario', $cx01->id_usuario);
        $stmt->bindValue(':horario_inicio', $cx01->horario_inicio);
        $stmt->bindValue(':horario_fim', $cx01->horario_fim);
        $stmt->bindValue(':fundo_caixa', $cx01->fundo_caixa);
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_usuario = null, $horario_inicio = null, $horario_fim = true, $descricao = null, $data = null, $read_duplicidade = null, ?PDO $pdo = null, bool $forUpdate = false) {
        $pdo = $pdo ?? (new Database())->connect();
        $query = 'SELECT * FROM cx01';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($descricao != null) $conditions[] = 'descricao LIKE :descricao';
        if ($data != null) $conditions[] = 'DATE(horario_inicio) = :data';
        if ($read_duplicidade != null) $conditions[] = 'horario_fim IS NULL';
        if ($id_usuario != null) $conditions[] = 'id_usuario = :id_usuario';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY id DESC';
        if ($forUpdate) {
            $query .= ' FOR UPDATE';
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($descricao != null) $stmt->bindValue(':descricao', '%' . $descricao . '%');
        if ($data != null) $stmt->bindValue(':data', $data);
        if ($id_usuario != null) $stmt->bindValue(':id_usuario', $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

     public static function update($grupo) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE cx01 SET descricao = :descricao WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':descricao', $grupo->descricao);
        $stmt->bindValue(':id', $grupo->id);
        $stmt->bindValue(':id_empresa', $grupo->id_empresa);
        return $stmt->execute();
    }

    public static function delete($id, $id_empresa) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM cx01 WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':id_empresa', $id_empresa);
        return $stmt->execute();
    }

    
}