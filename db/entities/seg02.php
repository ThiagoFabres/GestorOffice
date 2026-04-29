<?php 
class Seg02 {
    public $id;
    public $id_empresa;
    public $id_seg01;
    public $data_ini_prev;
    public $hora_ini_prev;
    public $data_fim_prev;
    public $hora_fim_prev;
    public $id_usuario_prev;
    public $hora_ini_real;
    public $hora_fim_real;
    public $id_usuario_real;
    public $obs;

    public function __construct($id = null, $id_empresa = null, $id_seg01 = null, $data_ini_prev = '', $hora_ini_prev = '', $data_fim_prev = '', $hora_fim_prev = '', $id_usuario_prev = null, $hora_ini_real = '', $hora_fim_real = '', $id_usuario_real = null, $obs = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_seg01 = $id_seg01;
        $this->data_ini_prev = $data_ini_prev;
        $this->hora_ini_prev = $hora_ini_prev;
        $this->data_fim_prev = $data_fim_prev;
        $this->hora_fim_prev = $hora_fim_prev;
        $this->id_usuario_prev = $id_usuario_prev;
        $this->hora_ini_real = $hora_ini_real;
        $this->hora_fim_real = $hora_fim_real;
        $this->id_usuario_real =  $id_usuario_real;
        $this->obs =  $obs;

    }

    public static function create($seg02) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO seg02 (id_empresa, id_seg01, data_ini_prev, hora_ini_prev, data_fim_prev, hora_fim_prev, id_usuario_prev, hora_ini_real, hora_fim_real, id_usuario_real, obs) 
                VALUES (:id_empresa, :id_seg01, :data_ini_prev, :hora_ini_prev, :data_fim_prev, :hora_fim_prev, :id_usuario_prev, :hora_ini_real, :hora_fim_real, :id_usuario_real, :obs)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $seg02->id_empresa);
        $stmt->bindValue(':id_seg01', $seg02->id_seg01);
        $stmt->bindValue(':data_ini_prev', $seg02->data_ini_prev);
        $stmt->bindValue(':hora_ini_prev', $seg02->hora_ini_prev);
        $stmt->bindValue(':data_fim_prev', $seg02->data_fim_prev);
        $stmt->bindValue(':hora_fim_prev', $seg02->hora_fim_prev);
        $stmt->bindValue(':id_usuario_prev', $seg02->id_usuario_prev);
        $stmt->bindValue(':hora_ini_real', $seg02->hora_ini_real);
        $stmt->bindValue(':hora_fim_real', $seg02->hora_fim_real);
        $stmt->bindValue(':id_usuario_real',  $seg02->id_usuario_real);
        $stmt->bindValue(':obs',  $seg02->obs);
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_seg01 = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM seg02';
        $conditions = [];
        if ($id != null) $conditions[] = 'id= :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_seg01 != null) $conditions[] = 'id_seg01 = :id_seg01';
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_seg01 != null) $stmt->bindValue(':id_seg01', $id_seg01);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($seg02) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE seg02 SET id_empresa = :id_empresa, id_seg01 = :id_seg01, data_ini_prev = :data_ini_prev, hora_ini_prev = :hora_ini_prev, data_fim_prev = :data_fim_prev, hora_fim_prev = :hora_fim_prev, id_usuario_prev = :id_usuario_prev, hora_ini_real = :hora_ini_real, hora_fim_real = :hora_fim_real, id_usuario_real = :id_usuario_real, obs = :obs WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $seg02->id);
        $stmt->bindValue(':id_empresa', $seg02->id_empresa);
        $stmt->bindValue(':id_seg01', $seg02->id_seg01);
        $stmt->bindValue(':data_ini_prev', $seg02->data_ini_prev);
        $stmt->bindValue(':hora_ini_prev', $seg02->hora_ini_prev);
        $stmt->bindValue(':data_fim_prev', $seg02->data_fim_prev);
        $stmt->bindValue(':hora_fim_prev', $seg02->hora_fim_prev);
        $stmt->bindValue(':id_usuario_prev', $seg02->id_usuario_prev);
        $stmt->bindValue(':hora_ini_real', $seg02->hora_ini_real);
        $stmt->bindValue(':hora_fim_real', $seg02->hora_fim_real);
        $stmt->bindValue(':id_usuario_real',  $seg02->id_usuario_real);
        $stmt->bindValue(':obs',  $seg02->obs);
        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM seg02 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

}   