<?php
require_once __DIR__ . '/../base.php';

class Usuario {
    public $id_usuario;
    public $id_empresa;
    public $nome;
    public $email;
    public $senha;
    public $processar;
    public $consultar;
    public $cargo;
    public $status;
    


    public function __construct($id_usuario = null, $id_empresa = null, $nome = '', $email = '', $senha = '', $processar = 0, $consultar = 0, $cargo = null, $status = 0) {
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->processar = $processar;
        $this->consultar = $consultar;
        $this->cargo = $cargo;
        $this->status = $status;
    }

public static function create($usuario) {
    $pdo = (new Database())->connect();

    // Senha padrão fixa "123456"
    $senhaHash = password_hash('123456', PASSWORD_DEFAULT);

    $sql = 'INSERT INTO usuario (id_empresa, nome, email, senha, processar, consultar, cargo, status) 
            VALUES (:id_empresa, :nome, :email, :senha, :processar, :consultar, :cargo, :status)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_empresa', $usuario->id_empresa);
    $stmt->bindValue(':nome', $usuario->nome);
    $stmt->bindValue(':email', $usuario->email);
    $stmt->bindValue(':senha', $senhaHash);
    $stmt->bindValue(':processar', $usuario->processar, PDO::PARAM_INT);
    $stmt->bindValue(':consultar', $usuario->consultar, PDO::PARAM_INT);
    $stmt->bindValue(':cargo', $usuario->cargo);
    $stmt->bindValue(':status', $usuario->status, PDO::PARAM_INT);

    

    return $stmt->execute();
}

    public static function read($id = null, $email = null, $idempresa = null, $cargo = null): array {
    $pdo = (new Database())->connect();
    $query = 'SELECT * FROM usuario';
    $conditions = [];

    if ($id != null) $conditions[] = 'id_usuario = :id_usuario';
    if ($email != null) $conditions[] = 'email = :email';
    if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';
    if ($cargo != null) $conditions[] = 'cargo = :cargo';

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    if ($email != null) {
        $query .= ' LIMIT 1';
    }

    $stmt = $pdo->prepare($query);

    if ($id != null) $stmt->bindValue(':id_usuario', $id);
    if ($email != null) $stmt->bindValue(':email', $email);
    if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);
    if ($cargo != null) $stmt->bindValue(':cargo', $cargo);

    $stmt->execute();

   $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Usuario::class);
return $stmt->fetchAll();
}

    public static function update($usuario) {
    $pdo = (new Database())->connect();

    $sql = 'UPDATE usuario 
            SET nome = :nome, 
                email = :email, 
                processar = :processar, 
                consultar = :consultar, 
                status = :status 
            WHERE id_usuario = :id_usuario';

    $stmt = $pdo->prepare($sql);

    

    $stmt->bindValue(':nome', $usuario->nome);
    $stmt->bindValue(':email', $usuario->email);
    $stmt->bindValue(':processar', $usuario->processar, PDO::PARAM_INT);
    $stmt->bindValue(':consultar', $usuario->consultar, PDO::PARAM_INT);
    $stmt->bindValue(':status', $usuario->status, PDO::PARAM_INT);
    $stmt->bindValue(':id_usuario', $usuario->id_usuario, PDO::PARAM_INT);

    return $stmt->execute();
}


}
