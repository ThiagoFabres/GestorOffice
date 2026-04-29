<?php
require_once __DIR__ . '/../base.php';

Class Seg01{
    public $id;
    public $id_empresa;
    public $id_cadastro;
    public $contato;
    public $telefone;
    public $endereco;
    public $bairro;
    public $cidade;
    public $estado;
    public $referencia;
    public $descricao;
    public $cel1;
    public $nome1;
    public $cargo1;
    public $cel2;
    public $nome2;
    public $cargo2;
    public $cel3;
    public $nome3;
    public $cargo3;

    public function __construct($id = null, $id_empresa = null, $id_cadastro = null, $contato = '', $telefone = '', $endereco = '', $bairro = '', $cidade = '', $estado = '', $referencia = '', $descricao = '', $cel1 = '', $nome1 = '', $cargo1 = '', $cel2 = '', $nome2 = '', $cargo2 = '', $cel3 = '', $nome3 = '', $cargo3 = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_cadastro = $id_cadastro;
        $this->contato = $contato;
        $this->telefone = $telefone;
        $this->endereco = $endereco;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->referencia = $referencia;
        $this->descricao = $descricao;
        $this->cel1 = $cel1;
        $this->nome1 = $nome1;
        $this->cargo1 = $cargo1;
        $this->cel2 = $cel2;
        $this->nome2 = $nome2;
        $this->cargo2 = $cargo2;
        $this->cel3 = $cel3;
        $this->nome3 = $nome3;
        $this->cargo3 =  $cargo3;

    }

    public static function create($seg01) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO seg01 (id_empresa, id_cadastro, contato, telefone, endereco, bairro, cidade, estado, referencia, descricao, cel1, nome1, cargo1, cel2, nome2, cargo2, cel3, nome3, cargo3) 
                VALUES (:id_empresa, :id_cadastro, :contato, :telefone, :endereco, :bairro, :cidade, :estado, :referencia, :descricao, :cel1, :nome1, :cargo1, :cel2, :nome2, :cargo2, :cel3, :nome3, :cargo3)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $seg01->id_empresa);
        $stmt->bindValue(':id_cadastro', $seg01->id_cadastro);
        $stmt->bindValue(':contato', $seg01->contato);
        $stmt->bindValue(':telefone', $seg01->telefone);
        $stmt->bindValue(':endereco', $seg01->endereco);
        $stmt->bindValue(':bairro', $seg01->bairro);
        $stmt->bindValue(':cidade', $seg01->cidade);
        $stmt->bindValue(':estado', $seg01->estado);
        $stmt->bindValue(':referencia', $seg01->referencia);
        $stmt->bindValue(':descricao', $seg01->descricao);
        $stmt->bindValue(':cel1', $seg01->cel1);
        $stmt->bindValue(':nome1', $seg01->nome1);
        $stmt->bindValue(':cargo1', $seg01->cargo1);
        $stmt->bindValue(':cel2', $seg01->cel2);
        $stmt->bindValue(':nome2', $seg01->nome2);
        $stmt->bindValue(':cargo2', $seg01->cargo2);
        $stmt->bindValue(':cel3', $seg01->cel3);
        $stmt->bindValue(':nome3', $seg01->nome3);
        $stmt->bindValue(':cargo3',  $seg01->cargo3);

        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_cadastro = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM seg01';
        $conditions = [];
        if ($id != null) $conditions[] = 'id= :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_cadastro != null) $conditions[] = 'id_cadastro = :id_cadastro';
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_cadastro != null) $stmt->bindValue(':id_cadastro', $id_cadastro);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($seg01) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE seg01 SET id_empresa = :id_empresa, id_cadastro = :id_cadastro, contato = :contato, telefone = :telefone, endereco = :endereco, bairro = :bairro, cidade = :cidade, estado = :estado, referencia = :referencia, descricao = :descricao, cel1 = :cel1, nome1 = :nome1, cargo1 = :cargo1, cel2 = :cel2, nome2 = :nome2, cargo2 = :cargo2, cel3 = :cel3, nome3 = :nome3, cargo3 =  :cargo3 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $seg01->id);
        $stmt->bindValue(':id_empresa', $seg01->id_empresa);
        $stmt->bindValue(':id_cadastro', $seg01->id_cadastro);
        $stmt->bindValue(':contato', $seg01->contato);
        $stmt->bindValue(':telefone', $seg01->telefone);
        $stmt->bindValue(':endereco', $seg01->endereco);
        $stmt->bindValue(':bairro', $seg01->bairro);
        $stmt->bindValue(':cidade', $seg01->cidade);
        $stmt->bindValue(':estado', $seg01->estado);
        $stmt->bindValue(':referencia', $seg01->referencia);
        $stmt->bindValue(':descricao', $seg01->descricao);
        $stmt->bindValue(':cel1', $seg01->cel1);
        $stmt->bindValue(':nome1', $seg01->nome1);
        $stmt->bindValue(':cargo1', $seg01->cargo1);
        $stmt->bindValue(':cel2', $seg01->cel2);
        $stmt->bindValue(':nome2', $seg01->nome2);
        $stmt->bindValue(':cargo2',  $seg01->cargo2);
        $stmt->bindValue(':cel3',  $seg01->cel3);
        $stmt->bindValue(':nome3',  $seg01->nome3);
        $stmt->bindValue(':cargo3',  $seg01->cargo3);
    }

    public function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM seg01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }


}
?>