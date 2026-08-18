<?php 
Class Ronda {
    public $id;
    public $id_usuario;
    public $started_at;
    public $ended_at;
    public $status;
    public $localicazao_inicial;
    public $localizacao_final;
    public $created_at;
    public $updated_at;

    public function __construct(
        $id = null, 
        $id_usuario = null, 
        $started_at = null, 
        $ended_at = null,
        $status = null,
        $localicazao_inicial = null,
        $localizacao_final = null,
        $created_at = null,
        $updated_at = null
        ) {
            $this->id = $id;
            $this->id_usuario = $id_usuario;
            $this->started_at = $started_at;
            $this->ended_at = $ended_at;
            $this->status = $status;
            $this->localicazao_inicial = $localicazao_inicial;
            $this->localizacao_final = $localizacao_final;
            $this->created_at = $created_at;
            $this->updated_at = $updated_at;
        }
    
    public static function read($id = null, $id_usuario = null, $filtro_hora_inicio = null, $filtro_hora_final = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM rondas';
        $conditions = [];

        if($id != null) $conditions[] = 'id = :id';
        if($filtro_hora_inicio != null) $conditions[] = 'started_at >= :filtro_hora_inicio';
        if($filtro_hora_final != null) $conditions[] = 'started_at <= :filtro_hora_final';
        if($id_usuario != null) $conditions[] = 'id_usuario = :id_usuario';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY started_at DESC';

        $stmt = $pdo->prepare($query);

        if($id != null) $stmt->bindValue(':id', $id);
        if($id_usuario != null) $stmt->bindValue(':id_usuario', $id_usuario);
        if($filtro_hora_inicio != null) $stmt->bindValue(':filtro_hora_inicio', $filtro_hora_inicio);
        if($filtro_hora_final != null) $stmt->bindValue(':filtro_hora_final', $filtro_hora_final);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);

        }

}