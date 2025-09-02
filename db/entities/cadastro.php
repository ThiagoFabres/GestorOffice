<?php

require_once __DIR__ . '/../base.php';

class Cadastro {
    public $id_empresa;
    public $id_cadastro;
    public $razao_soc;
    public $nom_fant;
    public $cep;
    public $rua;
    public $id_bairro;
    public $id_cidade;
    public $estado;
    public $cpf;
    public $cnpj;
    public $email;
    public $celular;
    public $fixo;
    public $id_categoria;


    public function __construct($id_empresa = null,$id_cadastro = '', $razao_soc = '', $nom_fant = '', $rua = '', $id_bairro = '', $id_cidade = '', $estado = '', $cpf = '',$cnpj = '', $email = '', $celular = '', $fixo = '',  $cep = '', $id_categoria = '') {
        $this->id_empresa = $id_empresa;
        $this->id_cadastro = $id_cadastro;
        $this->razao_soc = $razao_soc;
        $this->nom_fant = $nom_fant;
        $this->cep = $cep;
        $this->rua = $rua;
        $this->id_bairro = $id_bairro;
        $this->id_cidade = $id_cidade;
        $this->estado = $estado;
        $this->cpf = $cpf;
        $this->cnpj = $cnpj;
        $this->email = $email;
        $this->celular = $celular;
        $this->fixo = $fixo;
        $this->id_categoria= $id_categoria;
        
    }

    public static function create($cadastro) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO cadastro (id_empresa, razao_soc, nom_fant, cep, rua, id_bairro, id_cidade, estado, cpf, cnpj, email, celular, fixo, id_categoria) 
                            VALUES (:id_empresa, :razao_soc, :nom_fant, :cep, :rua, :bairro, :cidade, :estado, :cpf, :cnpj, :email, :celular, :fixo, :id_categoria)';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id_empresa', $cadastro->id_empresa);
        $stmt->bindValue(':razao_soc', $cadastro->razao_soc);
        $stmt->bindValue(':nom_fant', $cadastro->nom_fant);
        $stmt->bindValue(':rua', $cadastro->rua);
        $stmt->bindValue(':bairro', $cadastro->id_bairro);
        $stmt->bindValue(':cidade', $cadastro->id_cidade);
        $stmt->bindValue(':estado', $cadastro->estado);
        $stmt->bindValue(':cpf', $cadastro->cpf);
        $stmt->bindValue(':cnpj', $cadastro->cnpj);
        $stmt->bindValue(':email', $cadastro->email);
        $stmt->bindValue(':celular', $cadastro->celular);
        $stmt->bindValue(':fixo', $cadastro->fixo);
        $stmt->bindValue(':cep', $cadastro->cep);
        $stmt->bindvalue(':id_categoria', $cadastro->id_categoria);

       return $stmt->execute(); 
    }

            public static function read($id = null, $email = null, $idempresa = null, $nome = null, $filtro_data_inicial = null, $filtro_data_final = null,$filto_estado = null, $filtro_cidade = null, $filtro_bairro = null): array {
    $pdo = (new Database())->connect();
    $query = 'SELECT * FROM cadastro';
    $conditions = [];

    if ($id != null) $conditions[] = 'id_cadastro = :id_cadastro';
    if ($email != null) $conditions[] = 'email = :email';
    if ($idempresa != null) $conditions[] = 'id_empresa = :id_empresa';
    if ($nome != null) $conditions[] = 'nom_fant LIKE :nome';
    if ($filtro_data_inicial != null) $conditions[] = 'data_r >= :data_inicial';
    if ($filtro_data_final != null) $conditions[] = 'data_r <= :data_final';
    if ($filto_estado != null) $conditions[] = 'estado = :estado';
    if ($filtro_cidade != null) $conditions[] = 'id_cidade = :cidade';
    if ($filtro_bairro != null) $conditions[] = 'id_bairro = :bairro';

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    if($filtro_data_inicial != null) {
        $query .= ' ORDER BY data_r desc';
    } else if($filtro_data_final != null) {
        $query .= ' ORDER BY data_r asc';
    }
    else {
        $query .= ' ORDER BY nom_fant ASC';
    }

    $stmt = $pdo->prepare($query);

    if ($id != null) $stmt->bindValue(':id_cadastro', $id);
    if ($email != null) $stmt->bindValue(':email', $email);
    if ($idempresa != null) $stmt->bindValue(':id_empresa', $idempresa);
    if ($nome != null) $stmt->bindValue(':nome', '%' . $nome . '%');
    if ($filtro_data_inicial != null) $stmt->bindValue(':data_inicial', $filtro_data_inicial);
    if ($filtro_data_final != null) $stmt->bindValue(':data_final', $filtro_data_final);
    if ($filto_estado != null) $stmt->bindValue(':estado', $filto_estado);
    if ($filtro_cidade != null) $stmt->bindValue(':cidade', $filtro_cidade);
    if ($filtro_bairro != null) $stmt->bindValue(':bairro', $filtro_bairro);

    $stmt->execute();

   $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Cadastro::class);
return $stmt->fetchAll();
}

    public static function update($empresa) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE cadastro SET 
        razao_soc = :razao_soc, 
        nom_fant = :nom_fant, 
        rua = :rua, 
        id_bairro = :bairro, 
        id_cidade = :cidade, 
        estado = :estado, 
        cpf = :cpf, 
        cnpj = :cnpj, 
        email = :email, 
        celular = :celular, 
        fixo = :fixo, 
        cep = :cep,
        id_categoria = :id_categoria

    WHERE id_cadastro = :id';

        $stmt = $pdo->prepare($sql);

 $stmt->bindValue(':razao_soc', $empresa->razao_soc);
    $stmt->bindValue(':nom_fant', $empresa->nom_fant);
    $stmt->bindValue(':rua', $empresa->rua);
    $stmt->bindValue(':bairro', $empresa->id_bairro);
    $stmt->bindValue(':cidade', $empresa->id_cidade);
    $stmt->bindValue(':estado', $empresa->estado);
    $stmt->bindValue(':cpf', $empresa->cpf);
    $stmt->bindValue(':cnpj', $empresa->cnpj);
    $stmt->bindValue(':email', $empresa->email);
    $stmt->bindValue(':celular', $empresa->celular);
    $stmt->bindValue(':fixo', $empresa->fixo);
    $stmt->bindValue(':cep', $empresa->cep);
    $stmt->bindValue(':id', $empresa->id_cadastro); 
    $stmt->bindValue(':id_categoria', $empresa->id_categoria);
        
        $stmt->execute(); 

    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM cadastro WHERE id_cadastro = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
    
}
