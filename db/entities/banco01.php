<?php 

require_once __DIR__ . '/../base.php';

class Ban01 {
    public $id;
    public $id_empresa;
    public $banco;
    public $agencia;
    public $conta;
    public $nome;
    public function __construct($id = null, $id_empresa = null, $banco = null, $agencia = null, $conta = null, $nome = '') {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->banco = $banco;
        $this->agencia = $agencia;
        $this->conta = $conta;
        $this->nome = $nome;
    }

    public static function create($ban01) {
        $pdo = (new Database())->connect();

    $sql = 'INSERT INTO ban01 (id_empresa, banco, agencia, conta, nome) 
        VALUES (:id_empresa, :banco, :agencia, :conta, :nome)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $ban01->id_empresa);
        $stmt->bindValue(':banco', $ban01->banco);
        $stmt->bindValue(':agencia', $ban01->agencia);
        $stmt->bindValue(':conta', $ban01->conta);
        $stmt->bindValue(':nome', $ban01->nome);

        

        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $banco = null, $nome = null, $con01 = null, $con02 = null): array {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM ban01';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($banco != null) $conditions[] = 'banco = :banco';
        if ($nome != null) $conditions[] = 'nome = :nome';
        if ($con01 != null) $conditions[] = 'agencia = :con01';
        if ($con02 != null) $conditions[] = 'conta = :con02';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($banco != null) $stmt->bindValue(':banco', $banco);
        if ($nome != null) $stmt->bindValue(':nome', $nome);
        if ($con01 != null) $stmt->bindValue(':con01', $con01);
        if ($con02 != null) $stmt->bindValue(':con02', $con02);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($ban01) {
        $pdo = (new Database())->connect();

        $sql = 'UPDATE ban01 
                SET banco = :banco, agencia = :agencia, conta = :conta, nome = :nome
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $ban01->id);
        $stmt->bindValue(':banco', $ban01->banco);
        $stmt->bindValue(':agencia', $ban01->agencia);
        $stmt->bindValue(':conta', $ban01->conta);
        $stmt->bindValue(':nome', $ban01->nome);

        

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM ban01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
}
}
?>