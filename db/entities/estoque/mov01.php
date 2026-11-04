<?php 

class Mov01 {
    public $id;
    public $id_empresa;
    public $documento;
    public $data;
    public $tipo;
    public $operacional;
    public $id_cadastro;

    public function __construct($id = null, $id_empresa = null, $data = null, $tipo = null, $operacional = null, $id_cadastro = null, $documento = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->documento = $documento;
        $this->data = $data;
        $this->tipo = $tipo;
        $this->operacional = $operacional;
        $this->id_cadastro = $id_cadastro;
    }

    public static function create($movimento, ?PDO $pdo = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $gerenciarTransacao = !$pdo->inTransaction();
        if ($gerenciarTransacao) {
            $pdo->beginTransaction();
        }

        try {
            if ($movimento->documento === null) {
                $stmt = $pdo->prepare('SELECT id FROM empresas WHERE id = :id_empresa FOR UPDATE');
                $stmt->bindValue(':id_empresa', $movimento->id_empresa);
                $stmt->execute();
                if ($stmt->fetchColumn() === false) {
                    if ($gerenciarTransacao) {
                        $pdo->rollBack();
                    }
                    return false;
                }

                $stmt = $pdo->prepare(
                    'SELECT MAX(CAST(documento AS UNSIGNED)) FROM mov01 WHERE id_empresa = :id_empresa'
                );
                $stmt->bindValue(':id_empresa', $movimento->id_empresa);
                $stmt->execute();
                $ultimoDocumento = $stmt->fetchColumn();
                $movimento->documento = $ultimoDocumento === false || $ultimoDocumento === null
                    ? 1
                    : (int) $ultimoDocumento + 1;
            }

            $sql = 'INSERT INTO mov01 (id_empresa, documento, data, tipo, operacional, id_cadastro)
                    VALUES (:id_empresa, :documento, :data, :tipo, :operacional, :id_cadastro)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_empresa', $movimento->id_empresa);
            $stmt->bindValue(':documento', $movimento->documento);
            $stmt->bindValue(':data', $movimento->data);
            $stmt->bindValue(':tipo', $movimento->tipo);
            $stmt->bindValue(':operacional', $movimento->operacional);
            $stmt->bindValue(':id_cadastro', $movimento->id_cadastro);
            $criado = $stmt->execute();

            if (!$criado) {
                if ($gerenciarTransacao) {
                    $pdo->rollBack();
                }
                return false;
            }

            $movimento->id = (int) $pdo->lastInsertId();
            if ($gerenciarTransacao) {
                $pdo->commit();
            }

            return true;
        } catch (Throwable $e) {
            if ($gerenciarTransacao && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function update($movimento, ?PDO $pdo = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $sql = 'UPDATE mov01 SET documento = :documento, data = :data, tipo = :tipo,
                    operacional = :operacional, id_cadastro = :id_cadastro
                WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':documento', $movimento->documento);
        $stmt->bindValue(':data', $movimento->data);
        $stmt->bindValue(':tipo', $movimento->tipo);
        $stmt->bindValue(':operacional', $movimento->operacional);
        $stmt->bindValue(':id_cadastro', $movimento->id_cadastro);
        $stmt->bindValue(':id', $movimento->id);
        $stmt->bindValue(':id_empresa', $movimento->id_empresa);
        return $stmt->execute();
    }

    public static function delete($id, $id_empresa, ?PDO $pdo = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $stmt = $pdo->prepare('DELETE FROM mov01 WHERE id = :id AND id_empresa = :id_empresa');
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':id_empresa', $id_empresa);
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $data_inicial = null, $data_final = null, $tipo = null, ?PDO $pdo = null, bool $forUpdate = false, $id_cadastro = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $query = 'SELECT * FROM mov01';
        $conditions = [];

        if ($id !== null) $conditions[] = 'id = :id';
        if ($id_empresa !== null) $conditions[] = 'id_empresa = :id_empresa';
        if ($data_inicial !== null) $conditions[] = '`data` >= :data_inicial';
        if ($data_final !== null) $conditions[] = '`data` < :data_final';
        if ($tipo !== null) $conditions[] = 'tipo = :tipo';
        if ($id_cadastro !== null) $conditions[] = 'id_cadastro = :id_cadastro';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY `data` DESC, id DESC';
        if ($forUpdate) {
            $query .= ' FOR UPDATE';
        }

        $stmt = $pdo->prepare($query);
        if ($id !== null) $stmt->bindValue(':id', $id);
        if ($id_empresa !== null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($data_inicial !== null) $stmt->bindValue(':data_inicial', $data_inicial);
        if ($data_final !== null) $stmt->bindValue(':data_final', $data_final);
        if ($tipo !== null) $stmt->bindValue(':tipo', $tipo);
        if ($id_cadastro !== null) $stmt->bindValue(':id_cadastro', $id_cadastro);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}