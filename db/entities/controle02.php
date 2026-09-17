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

    /**
     * $hora_inicio e $hora_fim devem vir no mesmo fuso em que turnos/pontos são
     * gravados (UTC). São convertidos para o horário local (-3h) porque
     * controle02 é gravado em America/Sao_Paulo.
     */
    public static function read($id_usuario, $hora_inicio = null, $hora_fim = null)
    {
        $pdo = (new Database())->connect();

        // Referência da linha: o horário esperado quando existir, senão o respondido.
        // Assim os registros pendentes (hora_respondida = NULL) também entram na janela.
        $query = 'SELECT * FROM controle02 WHERE id_usuario = :id_usuario';
        $parameters = [':id_usuario' => $id_usuario];

        if ($hora_inicio !== null) {
            $query .= ' AND COALESCE(hora_esperada, hora_respondida) >= :hora_inicio';
            $parameters[':hora_inicio'] = self::normalizarLimite((string) $hora_inicio);
        }

        if ($hora_fim !== null) {
            $query .= ' AND COALESCE(hora_esperada, hora_respondida) <= :hora_fim';
            $parameters[':hora_fim'] = self::normalizarLimite((string) $hora_fim);
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