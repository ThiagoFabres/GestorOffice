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
    public $cnpj_principal;
    public $permissao_cartao;
    public $permissao_seguranca;
    public $permissao_financeiro;
    public $permissao_bancario;
    public $ativ_inicio;
    public $tolerancia;
    public $celular1_atividade;
    public $celular2_atividade;

    public function __construct($id = null, $razao_soc = '', $nom_fant = '', $rua = '', $bairro = '', $cidade = '', $estado = '', $cpf = '',$cnpj = '', $email = '', $celular = '', $fixo = '',  $status = 1, $data_r = '', $cep = '', $cnpj_principal = '', $permissao_cartao = 1, $permissao_seguranca = 0, $permissao_financeiro = 1, $permissao_bancario = 1, 
     $ativ_inicio = null,
     $tolerancia = null,
     $celular1_atividade = null,
     $celular2_atividade = null,
     ) {
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
        $this->cnpj_principal = $cnpj_principal;
        $this->permissao_cartao = $permissao_cartao;
        $this->permissao_seguranca = $permissao_seguranca;
        $this->permissao_financeiro = $permissao_financeiro;
        $this->permissao_bancario = $permissao_bancario;
        $this->ativ_inicio = $ativ_inicio;
        $this->tolerancia = $tolerancia;
        $this->celular1_atividade = $celular1_atividade;
        $this->celular2_atividade = $celular2_atividade;
    }

    public static function create($empresa) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO empresas (razao_soc, nom_fant, rua, bairro, cidade, estado, cpf, cnpj, email, celular, fixo, status, data_r, cep, cnpj_principal, permissao_cartao, permissao_seguranca, permissao_financeiro, permissao_bancario, ativ_inicio, tolerancia, celular1_atividade, celular2_atividade) 
        VALUES (:razao_soc, :nom_fant, :rua, :bairro, :cidade, :estado, :cpf, :cnpj, :email, :celular, :fixo, :status, :data_r, :cep, :cnpj_principal, :permissao_cartao, :permissao_seguranca, :permissao_financeiro, :permissao_bancario, :ativ_inicio, :tolerancia, :celular1_atividade, :celular2_atividade)';
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
        $stmt->bindValue(':cnpj_principal', $empresa->cnpj_principal);
        $stmt->bindValue(':permissao_cartao', $empresa->permissao_cartao);
        $stmt->bindValue(':permissao_seguranca', $empresa->permissao_seguranca);
        $stmt->bindValue(':permissao_financeiro', $empresa->permissao_financeiro);
        $stmt->bindValue(':permissao_bancario', $empresa->permissao_bancario);
        $stmt->bindValue(':ativ_inicio', $empresa->ativ_inicio);
        $stmt->bindValue(':tolerancia', $empresa->tolerancia);
        $stmt->bindValue(':celular1_atividade', $empresa->celular1_atividade);
        $stmt->bindValue(':celular2_atividade', $empresa->celular2_atividade);

       return $stmt->execute(); 
    }

        public static function read(
            $id = null, 
            $email = null, 
            $nome = null, 
            $filtro_data_inicial = null, 
            $filtro_data_final = null, 
            $estado = null, 
            $cidade = null, 
            $bairro = null, 
            $cnpj_principal = null): array {

        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM empresas';
        $conditions = [];
        if ($id != null) $conditions[] = 'id = :id';
        if ($email != null) $conditions[] = 'email = :email';
        if ($nome != null) $conditions[] = 'nom_fant LIKE :nome';
        if ($filtro_data_inicial != null) $conditions[] = 'data_r >= :data_inicial';
        if ($filtro_data_final != null) $conditions[] = 'data_r <= :data_final';
        if ($estado != null) $conditions[] = 'estado = :estado';
        if ($cidade != null) $conditions[] = 'cidade = :cidade';
        if ($bairro != null) $conditions[] = 'bairro = :bairro';
        if ($cnpj_principal != null) $conditions[] = 'cnpj_principal = :cnpj_principal';
        
        
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
        if ($nome != null) {
            $stmt->bindValue(':nome', '%' . $nome . '%');
        }
        if ($filtro_data_inicial != null) {
            $stmt->bindValue(':data_inicial', $filtro_data_inicial);
        }
        if ($filtro_data_final != null) {
            $stmt->bindValue(':data_final', $filtro_data_final);
        }
        if ($estado != null) {
            $stmt->bindValue(':estado', $estado);
        }
        if ($cidade != null) {
            $stmt->bindValue(':cidade', $cidade);
        }
        if ($bairro != null) {
            $stmt->bindValue(':bairro', $bairro);
        }
        if($cnpj_principal != null) {
            $stmt->bindValue(':cnpj_principal', $cnpj_principal);
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
        data_r = :data_r,
        cnpj_principal = :cnpj_principal,
        permissao_cartao = :permissao_cartao,
        permissao_seguranca = :permissao_seguranca,
        permissao_financeiro = :permissao_financeiro,
        permissao_bancario = :permissao_bancario,
        ativ_inicio = :ativ_inicio,
        tolerancia = :tolerancia,
        celular1_atividade = :celular1_atividade,
        celular2_atividade = :celular2_atividade


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
    $stmt->bindValue(':id', $empresa->id, PDO::PARAM_INT); 
    $stmt->bindValue(':cnpj_principal', $empresa->cnpj_principal);
    $stmt->bindValue(':permissao_cartao', $empresa->permissao_cartao);
    $stmt->bindValue(':permissao_seguranca', $empresa->permissao_seguranca);
    $stmt->bindValue(':permissao_financeiro', $empresa->permissao_financeiro);
    $stmt->bindValue(':permissao_bancario', $empresa->permissao_bancario);
    $stmt->bindValue(':ativ_inicio', $empresa->ativ_inicio);
    $stmt->bindValue(':tolerancia', $empresa->tolerancia);
    $stmt->bindValue(':celular1_atividade', $empresa->celular1_atividade);
    $stmt->bindValue(':celular2_atividade', $empresa->celular2_atividade);

       return $stmt->execute(); 
        
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare('DELETE FROM empresas WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
