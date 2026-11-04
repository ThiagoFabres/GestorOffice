<?php

class Comp01 {
    public $id;
    public $id_empresa;
    public $id_produto;
    public $item;
    public $quantidade;
    public $preco_unidade;

    public function __construct($id = null, $id_empresa = null, $id_produto = null, $item = null, $quantidade = null, $preco_unidade = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_produto = $id_produto;
        $this->item = $item;
        $this->quantidade = $quantidade;
        $this->preco_unidade = $preco_unidade;
    }

    public static function create($componente) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO comp01 (id_empresa, id_produto, item, quantidade, preco_unidade)
                VALUES (:id_empresa, :id_produto, :item, :quantidade, :preco_unidade)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $componente->id_empresa);
        $stmt->bindValue(':id_produto', $componente->id_produto);
        $stmt->bindValue(':item', $componente->item);
        $stmt->bindValue(':quantidade', $componente->quantidade);
        $stmt->bindValue(':preco_unidade', $componente->preco_unidade);
        return $stmt->execute();
    }

    public static function update($componente) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE comp01 SET item = :item, quantidade = :quantidade, preco_unidade = :preco_unidade
                WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':item', $componente->item);
        $stmt->bindValue(':quantidade', $componente->quantidade);
        $stmt->bindValue(':preco_unidade', $componente->preco_unidade);
        $stmt->bindValue(':id', $componente->id);
        $stmt->bindValue(':id_empresa', $componente->id_empresa);
        return $stmt->execute();
    }

    public static function delete($id, $id_empresa) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM comp01 WHERE id = :id AND id_empresa = :id_empresa';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':id_empresa', $id_empresa);
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_produto = null, $item = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM comp01';
        $conditions = [];

        if ($id !== null) $conditions[] = 'id = :id';
        if ($id_empresa !== null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_produto !== null) $conditions[] = 'id_produto = :id_produto';
        if ($item !== null) $conditions[] = 'item LIKE :item';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY item';

        $stmt = $pdo->prepare($query);
        if ($id !== null) $stmt->bindValue(':id', $id);
        if ($id_empresa !== null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_produto !== null) $stmt->bindValue(':id_produto', $id_produto);
        if ($item !== null) $stmt->bindValue(':item', '%' . $item . '%');

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}