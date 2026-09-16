<?php 
Class Ronda {
    public $id;
    public $id_empresa;
    public $id_usuario;
    public $descricao;
    public $hora;
    public $created_at;
    public $updated_at;

    public function __construct(
        $id = null, 
        $id_empresa = null,
        $id_usuario = null, 
        $descricao = null, 
        $hora = null,
        $created_at = null,
        $updated_at = null
        ) {
            $this->id = $id;
            $this->id_empresa = $id_empresa;
            $this->id_usuario = $id_usuario;
            $this->descricao = $descricao;
            $this->hora = $hora;
            $this->created_at = $created_at;
            $this->updated_at = $updated_at;
        }
    
    public static function read($id = null, $id_usuario = null, $hora_inicio = null, $hora_fim = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM rondas';
        $conditions = [];

        if($id != null) $conditions[] = 'id = :id';
        if($id_usuario != null) $conditions[] = 'id_usuario = :id_usuario';
        if($hora_inicio != null) $conditions[] = 'hora >= :hora_inicio';
        if($hora_fim != null) $conditions[] = 'hora <= :hora_fim';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY hora DESC';

        $stmt = $pdo->prepare($query);

        if($id != null) $stmt->bindValue(':id', $id);
        if($id_usuario != null) $stmt->bindValue(':id_usuario', $id_usuario);
        if($hora_inicio != null) $stmt->bindValue(':hora_inicio', $hora_inicio);
        if($hora_fim != null) $stmt->bindValue(':hora_fim', $hora_fim);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);

        }

}