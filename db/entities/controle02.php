<?php

class Controle02
{
    public $id;
    public $id_empresa;
    public $id_usuario;
    public $tipo;
    public $hora_esperada;
    public $hora_respondida;
    public $tolerancia;

    public function __construct(
        $id = null,
        $id_empresa = null,
        $id_usuario = null,
        $tipo = null,
        $hora_esperada = null,
        $hora_respondida = null,
        $tolerancia = null
    ) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_usuario = $id_usuario;
        $this->tipo = $tipo;
        $this->hora_esperada = $hora_esperada;
        $this->hora_respondida = $hora_respondida;
        $this->tolerancia = $tolerancia;
    }

    public static function read(
        $id = null,
        $id_empresa = null,
        $id_usuario = null,
        $tipo = null,
        $hora_inicio = null,
        $hora_fim = null
    ) {
        $pdo = (new Database())->connect();

        $query = 'SELECT * FROM controle02';
        $conditions = [];
        $parameters = [];

        if ($id !== null) {
            $conditions[] = 'id = :id';
            $parameters[':id'] = $id;
        }

        if ($id_empresa !== null) {
            $conditions[] = 'id_empresa = :id_empresa';
            $parameters[':id_empresa'] = $id_empresa;
        }

        if ($id_usuario !== null) {
            $conditions[] = 'id_usuario = :id_usuario';
            $parameters[':id_usuario'] = $id_usuario;
        }

        if ($tipo !== null) {
            $conditions[] = 'tipo = :tipo';
            $parameters[':tipo'] = $tipo;
        }

        if ($hora_inicio !== null) {
            $conditions[] = 'COALESCE(hora_esperada, hora_respondida) >= :hora_inicio';
            $parameters[':hora_inicio'] = self::normalizarLimite((string) $hora_inicio);
        }

        if ($hora_fim !== null) {
            $conditions[] = 'COALESCE(hora_esperada, hora_respondida) <= :hora_fim';
            $parameters[':hora_fim'] = self::normalizarLimite((string) $hora_fim);
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY COALESCE(hora_esperada, hora_respondida) ASC, id ASC';

        $stmt = $pdo->prepare($query);
        $stmt->execute($parameters);

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    private static function normalizarLimite(string $valor): string
    {
        $data = new DateTime($valor);
        $data->modify('-3 hours');

        return $data->format('Y-m-d H:i:s');
    }
}