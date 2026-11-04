<?php 

class Prod01 {
    public $id;
    public $id_empresa;
    public $id_grupo;
    public $id_subgrupo;
    public $descricao;
    public $unidade;
    public $cod_barra;
    public $preco_custo;
    public $markup;
    public $preco_venda;
    public $estoque;

    public function __construct($id = null, $id_empresa = null, $id_grupo = null, $id_subgrupo = null, $unidade = null, $cod_barra = null, $preco_custo = null, $markup = null, $preco_venda = null, $estoque = null, $descricao = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_grupo = $id_grupo;
        $this->id_subgrupo = $id_subgrupo;
        $this->descricao = $descricao;
        $this->unidade = $unidade;
        $this->cod_barra = $cod_barra;
        $this->preco_custo = $preco_custo;
        $this->markup = $markup;
        $this->preco_venda = $preco_venda;
        $this->estoque = $estoque;
    }

    public static function create($produto) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO prod01 (id_empresa, id_grupo, id_subgrupo, descricao, unidade, cod_barra, preco_custo, markup, preco_venda, estoque)
                VALUES (:id_empresa, :id_grupo, :id_subgrupo, :descricao, :unidade, :cod_barra, :preco_custo, :markup, :preco_venda, :estoque)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $produto->id_empresa);
        $stmt->bindValue(':id_grupo', $produto->id_grupo);
        $stmt->bindValue(':id_subgrupo', $produto->id_subgrupo);
        $stmt->bindValue(':descricao', $produto->descricao);
        $stmt->bindValue(':unidade', $produto->unidade);
        $stmt->bindValue(':cod_barra', $produto->cod_barra);
        $stmt->bindValue(':preco_custo', $produto->preco_custo);
        $stmt->bindValue(':markup', $produto->markup);
        $stmt->bindValue(':preco_venda', $produto->preco_venda);
        $stmt->bindValue(':estoque', $produto->estoque);
        return $stmt->execute();
    }

    public static function update($produto, ?PDO $pdo = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $sql = 'UPDATE prod01 SET descricao = :descricao, unidade = :unidade, cod_barra = :cod_barra,
                    preco_custo = :preco_custo, markup = :markup, preco_venda = :preco_venda, estoque = :estoque
                WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':descricao', $produto->descricao);
        $stmt->bindValue(':unidade', $produto->unidade);
        $stmt->bindValue(':cod_barra', $produto->cod_barra);
        $stmt->bindValue(':preco_custo', $produto->preco_custo);
        $stmt->bindValue(':markup', $produto->markup);
        $stmt->bindValue(':preco_venda', $produto->preco_venda);
        $stmt->bindValue(':estoque', $produto->estoque);
        $stmt->bindValue(':id', $produto->id);
        $stmt->bindValue(':id_empresa', $produto->id_empresa);
        return $stmt->execute();
    }

    public static function delete($id, $id_empresa) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM mov02 WHERE id_produto = :id AND id_empresa = :id_empresa');
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':id_empresa', $id_empresa);
        $stmt->execute();

        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM comp01 WHERE id_produto = :id AND id_empresa = :id_empresa');
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':id_empresa', $id_empresa);
            $stmt->execute();

            $stmt = $pdo->prepare('DELETE FROM prod01 WHERE id = :id AND id_empresa = :id_empresa');
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':id_empresa', $id_empresa);
            $stmt->execute();
            $excluido = $stmt->rowCount() > 0;
            $pdo->commit();
            return $excluido;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    public static function read($id = null, $id_empresa = null, $id_grupo = null, $id_subgrupo = null, $descricao = null, ?PDO $pdo = null, bool $forUpdate = false, $cod_barra = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $query = 'SELECT * FROM prod01';
        $conditions = [];

        if ($id !== null) $conditions[] = 'id = :id';
        if ($id_empresa !== null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_grupo !== null) $conditions[] = 'id_grupo = :id_grupo';
        if ($id_subgrupo !== null) $conditions[] = 'id_subgrupo = :id_subgrupo';
        if ($descricao !== null) $conditions[] = 'descricao LIKE :descricao';
        if ($cod_barra !== null) $conditions[] = 'cod_barra = :cod_barra';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY descricao';
        if ($forUpdate) {
            $query .= ' FOR UPDATE';
        }

        $stmt = $pdo->prepare($query);
        if ($id !== null) $stmt->bindValue(':id', $id);
        if ($id_empresa !== null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_grupo !== null) $stmt->bindValue(':id_grupo', $id_grupo);
        if ($id_subgrupo !== null) $stmt->bindValue(':id_subgrupo', $id_subgrupo);
        if ($descricao !== null) $stmt->bindValue(':descricao', '%' . $descricao . '%');
        if ($cod_barra !== null) $stmt->bindValue(':cod_barra', $cod_barra);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}
