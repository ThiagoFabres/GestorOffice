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
    public $cnpj_cpf;
    public $email;
    public $celular;
    public $fixo;
    public $contato;
    public $ativo;

    public function __construct($id = null, $razao_soc = '', $nom_fant = '', $rua = '', $bairro = '', $cidade = '', $estado = '', $cnpj_cpf = '', $email = '', $celular = '', $fixo = '', $contato = '', $ativo = 1) {
        $this->id = $id;
        $this->razao_soc = $razao_soc;
        $this->nom_fant = $nom_fant;
        $this->rua = $rua;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->cnpj_cpf = $cnpj_cpf;
        $this->email = $email;
        $this->celular = $celular;
        $this->fixo = $fixo;
        $this->contato = $contato;
        $this->ativo = $ativo;
    }

    public static function create($empresa) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO empresas (razao_soc, nom_fant, rua, bairro, cidade, estado, cnpj_cpf, email, celular, fixo, contato, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $empresa->razao_soc,
            $empresa->nom_fant,
            $empresa->rua,
            $empresa->bairro,
            $empresa->cidade,
            $empresa->estado,
            $empresa->cnpj_cpf,
            $empresa->email,
            $empresa->celular,
            $empresa->fixo,
            $empresa->contato,
            $empresa->ativo
        ]);
        return $pdo->lastInsertId();
    }

    public static function read($id = null) {
        $pdo = (new Database())->connect();
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM empresas WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetchObject('Empresa');
        } else {
            $stmt = $pdo->query('SELECT * FROM empresas');
            return $stmt->fetchAll(PDO::FETCH_CLASS);
        }
    }

    public static function update($empresa) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE empresas SET razao_soc = ?, nom_fant = ?, rua = ?, bairro = ?, cidade = ?, estado = ?, cnpj_cpf = ?, email = ?, celular = ?, fixo = ?, contato = ?, ativo = ? WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $empresa->razao_soc,
            $empresa->nom_fant,
            $empresa->rua,
            $empresa->bairro,
            $empresa->cidade,
            $empresa->estado,
            $empresa->cnpj_cpf,
            $empresa->email,
            $empresa->celular,
            $empresa->fixo,
            $empresa->contato,
            $empresa->ativo,
            $empresa->id
        ]);
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare('DELETE FROM empresas WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
