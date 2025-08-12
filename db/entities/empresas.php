<?php

require_once __DIR__ . '/../base.php';

class Empresa {
    public $id;
    public $razao_soc;
    public $nom_fant;
    public $rua;
    public $bairro;
    public $cidade;
    public $estado;
    public $cpf;
    public $cnpj;
    public $email;
    public $celular;
    public $fixo;
    public $status;

    public $data_r;

    public $cep;

    public function __construct($id = null, $razao_soc = '', $nom_fant = '', $rua = '', $bairro = '', $cidade = '', $estado = '', $cpf = '',$cnpj = '', $email = '', $celular = '', $fixo = '',  $status = 1, $data_r = '', $cep = '') {
        $this->id = $id;
        $this->razao_soc = $razao_soc;
        $this->nom_fant = $nom_fant;
        $this->rua = $rua;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->cpf = $cpf;
        $this->cnpj = $cnpj;
        $this->email = $email;
        $this->celular = $celular;
        $this->fixo = $fixo;
        $this->status = $status;
        $this->data_r = $data_r;
        $this->cep = $cep;
    }

    public static function create($empresa) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO empresas (razao_soc, nom_fant, rua, bairro, cidade, estado, cpf, cnpj, email, celular, fixo, status, data_r, cep) VALUES (:razao_soc, :nom_fant, :rua, :bairro, :cidade, :estado, :cpf, :cnpj, :email, :celular, :fixo, :status, :data_r, :cep)';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':razao_soc', $empresa->razao_soc);
        $stmt->bindValue(':nom_fant', $empresa->nom_fant);
        $stmt->bindValue(':rua', $empresa->rua);
        $stmt->bindValue(':bairro', $empresa->bairro);
        $stmt->bindValue(':cidade', $empresa->cidade);
        $stmt->bindValue(':estado', $empresa->estado);
        $stmt->bindValue(':cpf', $empresa->cpf);
        $stmt->bindValue(':cnpj', $empresa->cnpj);
        $stmt->bindValue(':email', $empresa->email);
        $stmt->bindValue(':celular', $empresa->celular);
        $stmt->bindValue(':fixo', $empresa->fixo);
        $stmt->bindValue(':status', $empresa->status);
        $stmt->bindValue(':data_r', $empresa->data_r);
        $stmt->bindValue(':cep', $empresa->cep);

       return $stmt->execute(); 
    }

        public static function read($id = null, $email = null) {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM empresas';
        $conditions = [];
        if ($id != null) $conditions[] = 'id = :id';
        if ($email != null) $conditions[] = 'email = :email';
        
        
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        if ($email != null) {
            $query .= ' LIMIT 1';
        }
        $stmt = $pdo->prepare($query);
        if ($id != null) {
            $stmt->bindValue(':id', $id);
        }
        if ($email != null) {
            $stmt->bindValue(':email', $email);
        }
        $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function update($empresa) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE empresas SET 
        razao_soc = :razao_soc, 
        nom_fant = :nom_fant, 
        rua = :rua, 
        bairro = :bairro, 
        cidade = :cidade, 
        estado = :estado, 
        cpf = :cpf, 
        cnpj = :cnpj, 
        email = :email, 
        celular = :celular, 
        fixo = :fixo, 
        status = :status, 
        cep = :cep, 
        data_r = :data_r 
    WHERE id = :id';

        $stmt = $pdo->prepare($sql);

 $stmt->bindValue(':razao_soc', $empresa->razao_soc);
    $stmt->bindValue(':nom_fant', $empresa->nom_fant);
    $stmt->bindValue(':rua', $empresa->rua);
    $stmt->bindValue(':bairro', $empresa->bairro);
    $stmt->bindValue(':cidade', $empresa->cidade);
    $stmt->bindValue(':estado', $empresa->estado);
    $stmt->bindValue(':cpf', $empresa->cpf);
    $stmt->bindValue(':cnpj', $empresa->cnpj);
    $stmt->bindValue(':email', $empresa->email);
    $stmt->bindValue(':celular', $empresa->celular);
    $stmt->bindValue(':fixo', $empresa->fixo);
    $stmt->bindValue(':status', $empresa->status);
    $stmt->bindValue(':cep', $empresa->cep);
    $stmt->bindValue(':data_r', $empresa->data_r);
    $stmt->bindValue(':id', $empresa->id, PDO::PARAM_INT); // ← Faltava esse bind

       return $stmt->execute(); 
        
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare('DELETE FROM empresas WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
