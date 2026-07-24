<?php
require_once __DIR__ . '/../base.php';

class Usuario {
    public $id;
    public $id_empresa;
    public $nome;
    public $email;
    public $senha;
    public $processar;
    public $consultar;
    public $cargo;
    public $status;
    public $permissao_cartao;
    public $permissao_seguranca;
    public $permissao_financeiro;
    public $permissao_bancario;
    public $permissao_operacional;
    public $permissao_inicio;
    public $principal;


    public function __construct($id = null, $id_empresa = null, $nome = '', $email = '', $senha = '', $processar = 0, $consultar = 0, $cargo = null, $status = 0, $permissao_cartao = 1, $permissao_seguranca = 0, $permissao_financeiro = 1, $permissao_bancario = 1, $permissao_operacional = 1, $permissao_inicio = 1, $principal = 0) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->processar = $processar;
        $this->consultar = $consultar;
        $this->cargo = $cargo;
        $this->status = $status;
        $this->permissao_cartao = $permissao_cartao;
        $this->permissao_seguranca = $permissao_seguranca;
        $this->permissao_financeiro = $permissao_financeiro;
        $this->permissao_bancario = $permissao_bancario;
        $this->permissao_operacional = $permissao_operacional;
        $this->permissao_inicio = $permissao_inicio;
        $this->principal = $principal;
    }

public static function create($usuario) {
    $pdo = (new Database())->connect();

    // Senha padrão fixa "123456"
    $senhaHash = password_hash('123456', PASSWORD_DEFAULT);

    $sql = 'INSERT INTO usuario (id_empresa, nome, email, senha, processar, consultar, cargo, status, permissao_cartao, permissao_seguranca, permissao_financeiro, permissao_bancario, permissao_operacional, permissao_inicio, principal) 
            VALUES (:id_empresa, :nome, :email, :senha, :processar, :consultar, :cargo, :status, :permissao_cartao, :permissao_seguranca, :permissao_financeiro, :permissao_bancario, :permissao_operacional, :permissao_inicio, :principal)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_empresa', $usuario->id_empresa);
    $stmt->bindValue(':nome', $usuario->nome);
    $stmt->bindValue(':email', $usuario->email);
    $stmt->bindValue(':senha', $senhaHash);
    $stmt->bindValue(':processar', $usuario->processar, PDO::PARAM_INT);
    $stmt->bindValue(':consultar', $usuario->consultar, PDO::PARAM_INT);
    $stmt->bindValue(':cargo', $usuario->cargo);
    $stmt->bindValue(':status', $usuario->status, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_cartao', $usuario->permissao_cartao, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_seguranca', $usuario->permissao_seguranca, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_financeiro', $usuario->permissao_financeiro, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_bancario', $usuario->permissao_bancario, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_operacional', $usuario->permissao_operacional, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_inicio', $usuario->permissao_inicio, PDO::PARAM_INT);
    $stmt->bindValue(':principal', $usuario->principal, PDO::PARAM_INT);

    

    return $stmt->execute();
}

    public static function read($id = null, $email = null, $idempresa = null, $cargo = null, $nome = null, $filtro_data_inicial = null, $filtro_data_final = null, $principal = null): array {
    $pdo = (new Database())->connect();
    $query = 'SELECT * FROM usuario';
    $conditions = [];

    if ($id != null) $conditions[] = 'id = :id';
    if ($email != null) $conditions[] = 'email = :email';
    if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';
    if ($nome != null) $conditions[] = 'nome LIKE :nome';
    if ($filtro_data_inicial != null) $conditions[] = 'data_criacao >= :data_inicial';
    if ($filtro_data_final != null) $conditions[] = 'data_criacao <= :data_final';
    if ($cargo == 'usuario') {
        $conditions[] = 'cargo IN (3, 4)';
    }
    if($cargo != null && $cargo != 'usuario') {
        $conditions[] = 'cargo = :cargo';
    }
    if($principal != null) {
        if($principal) {
            $conditions[] = 'principal = 1';
        } else if (!$principal) {
            $conditions[] = 'principal = 0';
        }
    }


    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    if ($email != null) {
        $query .= ' LIMIT 1';
    }

    $stmt = $pdo->prepare($query);

    if ($id != null) $stmt->bindValue(':id', $id);
    if ($email != null) $stmt->bindValue(':email', $email);
    if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);
    if ($cargo != null && $cargo != 'usuario') $stmt->bindValue(':cargo', $cargo);
    if ($nome != null) $stmt->bindValue(':nome', '%' . $nome . '%');
    if ($filtro_data_inicial != null) $stmt->bindValue(':data_inicial', $filtro_data_inicial);
    if ($filtro_data_final != null) $stmt->bindValue(':data_final', $filtro_data_final);

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
                status = :status,
                cargo = :cargo,
                permissao_cartao = :permissao_cartao,
                permissao_seguranca = :permissao_seguranca,
                permissao_financeiro = :permissao_financeiro,
                permissao_bancario = :permissao_bancario,
                permissao_operacional = :permissao_operacional,
                permissao_inicio = :permissao_inicio,
                principal = :principal
            WHERE id = :id';

    $stmt = $pdo->prepare($sql);

    

    $stmt->bindValue(':nome', $usuario->nome);
    $stmt->bindValue(':email', $usuario->email);
    $stmt->bindValue(':processar', $usuario->processar, PDO::PARAM_INT);
    $stmt->bindValue(':consultar', $usuario->consultar, PDO::PARAM_INT);
    $stmt->bindValue(':status', $usuario->status, PDO::PARAM_INT);
    $stmt->bindValue(':cargo', $usuario->cargo);
    $stmt->bindValue(':id', $usuario->id, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_cartao', $usuario->permissao_cartao, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_seguranca', $usuario->permissao_seguranca, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_financeiro', $usuario->permissao_financeiro, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_bancario', $usuario->permissao_bancario, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_operacional', $usuario->permissao_operacional, PDO::PARAM_INT);
    $stmt->bindValue(':permissao_inicio', $usuario->permissao_inicio, PDO::PARAM_INT);
    $stmt->bindValue(':principal', $usuario->principal, PDO::PARAM_INT);

    return $stmt->execute();
}

public static function updateSenha($usuario) {
    $pdo = (new Database())->connect();

    $sql = 'UPDATE usuario 
            SET senha = :senha
            WHERE id = :id';

    $stmt = $pdo->prepare($sql);

    
    $stmt->bindValue(':senha', password_hash($usuario->senha, PASSWORD_DEFAULT));
    $stmt->bindValue(':id', $usuario->id, PDO::PARAM_INT);

    return $stmt->execute();
}


}
