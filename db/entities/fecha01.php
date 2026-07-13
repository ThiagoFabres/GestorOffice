<?php
class Fecha01 {
    public $id;
    public $id_empresa;
    public $id_cadastro;
    public $id_custos;
    public $id_titulo;
    public $id_subtitulo;
    public $tipo;
    public $tipo_pagamento;
    public $descricao;
    public function __construct($id = null, $id_empresa = null, $id_cadastro = null, $id_custos = null, $id_titulo = null, $id_subtitulo = null, $tipo = null, $tipo_pagamento = null, $descricao = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa ?? $_SESSION['usuario']->id_empresa;
        $this->id_cadastro = $id_cadastro;
        $this->id_custos = $id_custos;
        $this->id_titulo = $id_titulo;
        $this->id_subtitulo = $id_subtitulo;
        $this->tipo = $tipo;
        $this->tipo_pagamento = $tipo_pagamento;
        $this->descricao = $descricao;
    }

    public static function create($fecha01) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO fecha01 (id_empresa, id_cadastro, id_custos, id_titulo, id_subtitulo, tipo, tipo_pagamento, descricao) 
        VALUES (:id_empresa, :id_cadastro, :id_custos, :id_titulo, :id_subtitulo, :tipo, :tipo_pagamento, :descricao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $fecha01->id_empresa);
        $stmt->bindValue(':id_cadastro', $fecha01->id_cadastro);
        $stmt->bindValue(':id_custos', $fecha01->id_custos);
        $stmt->bindValue(':id_titulo', $fecha01->id_titulo);
        $stmt->bindValue(':id_subtitulo', $fecha01->id_subtitulo);
        $stmt->bindValue(':tipo', $fecha01->tipo);
        $stmt->bindValue(':tipo_pagamento', $fecha01->tipo_pagamento);
        $stmt->bindValue(':descricao', $fecha01->descricao);

        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_cadastro = null, $id_custos = null, $id_titulo = null, $id_subtitulo = null, $tipo = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM fecha01';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_cadastro != null) $conditions[] = 'id_cadastro = :id_cadastro';
        if ($id_custos != null) $conditions[] = 'id_custos = :id_custos';
        if ($id_titulo != null) $conditions[] = 'id_titulo = :id_titulo';
        if ($id_subtitulo != null) $conditions[] = 'id_subtitulo = :id_subtitulo';
        if ($tipo != null) $conditions[] = 'tipo = :tipo';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_cadastro != null) $stmt->bindValue(':id_cadastro', $id_cadastro);
        if ($id_custos != null) $stmt->bindValue(':id_custos', $id_custos);
        if ($id_titulo != null) $stmt->bindValue(':id_titulo', $id_titulo);
        if ($id_subtitulo != null) $stmt->bindValue(':id_subtitulo', $id_subtitulo);
        if ($tipo != null) $stmt->bindValue(':tipo', $tipo);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($fecha01) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE fecha01 SET
                id_cadastro = :id_cadastro,
                id_custos = :id_custos,
                id_titulo = :id_titulo,
                id_subtitulo = :id_subtitulo,
                tipo = :tipo,
                tipo_pagamento = :tipo_pagamento,
                descricao = :descricao
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_cadastro', $fecha01->id_cadastro);
        $stmt->bindValue(':id_custos', $fecha01->id_custos);
        $stmt->bindValue(':id_titulo', $fecha01->id_titulo);
        $stmt->bindValue(':id_subtitulo', $fecha01->id_subtitulo);
        $stmt->bindValue(':tipo', $fecha01->tipo);
        $stmt->bindValue(':tipo_pagamento', $fecha01->tipo_pagamento);
        $stmt->bindValue(':descricao', $fecha01->descricao);
        $stmt->bindValue(':id', $fecha01->id);

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM fecha01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}