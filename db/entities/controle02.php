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
            $query .= ' AND (
                (hora_esperada IS NOT NULL AND hora_esperada >= :hora_inicio_esperada)
                OR (hora_esperada IS NULL AND hora_respondida >= :hora_inicio_respondida)
            )';
            $horaInicio = self::normalizarLimite((string) $hora_inicio);
            $parameters[':hora_inicio_esperada'] = $horaInicio;
            $parameters[':hora_inicio_respondida'] = $horaInicio;
        }

        if ($hora_fim !== null) {
            $query .= ' AND (
                (hora_esperada IS NOT NULL AND hora_esperada <= :hora_fim_esperada)
                OR (hora_esperada IS NULL AND hora_respondida <= :hora_fim_respondida)
            )';
            $horaFim = self::normalizarLimite((string) $hora_fim);
            $parameters[':hora_fim_esperada'] = $horaFim;
            $parameters[':hora_fim_respondida'] = $horaFim;
        }

        $query .= ' ORDER BY COALESCE(hora_respondida, hora_esperada) ASC, id ASC';
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