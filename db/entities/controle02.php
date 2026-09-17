<?php

class Controle02
{
    public $id;
    public $id_empresa;
    public $id_usuario;
    public $hora_esperada;
    public $hora_respondida;
    public $tolerancia;

    public function __construct(
        $id = null,
        $id_empresa = null,
        $id_usuario = null,
        $hora_esperada = null,
        $hora_respondida = null,
        $tolerancia = null
    ) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_usuario = $id_usuario;
        $this->hora_esperada = $hora_esperada;
        $this->hora_respondida = $hora_respondida;
        $this->tolerancia = $tolerancia;
    }

    public static function read($id_usuario, $hora_inicio = null, $hora_fim = null)
    {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM controle02 WHERE id_usuario = :id_usuario';
        $parameters = [':id_usuario' => $id_usuario];

        if ($hora_inicio !== null) {
            $query .= ' AND hora_esperada >= :hora_inicio';
            $parameters[':hora_inicio'] = substr((string) $hora_inicio, 11, 8);
        }

        if ($hora_fim !== null) {
            $query .= ' AND hora_esperada <= :hora_fim';
            $parameters[':hora_fim'] = substr((string) $hora_fim, 11, 8);
        }

        $query .= ' ORDER BY hora_esperada ASC, id ASC';
        $stmt = $pdo->prepare($query);
        $stmt->execute($parameters);

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}