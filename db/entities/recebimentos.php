<?php

require_once '../db/base.php';

class Rec01 {
    public $id;
    public $id_empresa;
    public $id_cadastro;
    public $id_con01;
    public $id_con02;
    public $documento;
    public $descricao;
    public $valor;
    public $parcelas;
    public $data_lanc;
    public $id_usuario;
    public function __construct($id = null, $id_empresa = null, $id_cadastro = null, $id_con01 = null, $id_con02 = null, $documento = '', $descricao = '', $valor = 0.0, $parcelas = 1, $data_lanc = null, $id_usuario = null, $obs = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_cadastro = $id_cadastro;
        $this->id_con01 = $id_con01;
        $this->id_con02 = $id_con02;
        $this->documento = $documento;
        $this->descricao = $descricao;
        $this->valor = $valor;
        $this->parcelas = $parcelas;
        $this->data_lanc = $data_lanc ? new DateTime($data_lanc) : new DateTime();
        $this->id_usuario = $id_usuario;
    }

    public static function create($rec01) {
        $pdo = (new Database())->connect();

        $sql = 'INSERT INTO rec01 (id_empresa, id_cadastro, id_con01, id_con02, documento, descricao, valor, parcelas, data_lanc, id_usuario) 
                VALUES (:id_empresa, :id_cadastro, :id_con01, :id_con02, :documento, :descricao, :valor, :parcelas, :data_lanc, :id_usuario)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $rec01->id_empresa);
        $stmt->bindValue(':id_cadastro', $rec01->id_cadastro);
        $stmt->bindValue(':id_con01', $rec01->id_con01);
        $stmt->bindValue(':id_con02', $rec01->id_con02);
        $stmt->bindValue(':documento', $rec01->documento);
        $stmt->bindValue(':descricao', $rec01->descricao);
        $stmt->bindValue(':valor', $rec01->valor);
        $stmt->bindValue(':parcelas', $rec01->parcelas);
        $stmt->bindValue(':data_lanc', $rec01->data_lanc->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id_usuario', $rec01->id_usuario);

        

        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_cadastro = null, $documento = null): array {
        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM rec01';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_cadastro != null) $conditions[] = 'id_cadastro = :id_cadastro';
        if ($documento != null) $conditions[] = 'documento = :documento';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_cadastro != null) $stmt->bindValue(':id_cadastro', $id_cadastro);
        if ($documento != null) $stmt->bindValue(':documento', $documento);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($rec01) {
        $pdo = (new Database())->connect();

        $sql = 'UPDATE rec01 
                SET id_cadastro = :id_cadastro, id_con01 = :id_con01, id_con02 = :id_con02, documento = :documento, descricao = :descricao, valor = :valor, parcelas = :parcelas, data_lanc = :data_lanc, id_usuario = :id_usuario 
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $rec01->id);
        $stmt->bindValue(':id_cadastro', $rec01->id_cadastro);
        $stmt->bindValue(':id_con01', $rec01->id_con01);
        $stmt->bindValue(':id_con02', $rec01->id_con02);
        $stmt->bindValue(':documento', $rec01->documento);
        $stmt->bindValue(':descricao', $rec01->descricao);
        $stmt->bindValue(':valor', $rec01->valor);
        $stmt->bindValue(':parcelas', $rec01->parcelas);
        $stmt->bindValue(':data_lanc', $rec01->data_lanc->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id_usuario', $rec01->id_usuario);

        

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM rec01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
}
}

//parcelas
class Rec02 {
    public $id;
    public $id_empresa;
    public $id_rec01;
    public $valor_par;
    public $parcela;
    public $vencimento;
    public $valor_pag;
    public $data_pag;
    public $obs;
    public $id_pgto;

    public function __construct($id = null, $id_empresa = null, $id_rec01 = null, $valor_par = 0.0, $parcela = 1, $vencimento = null, $valor_pag = 0.0, $data_pag = null, $obs = '', $id_pgto = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_rec01 = $id_rec01;
        $this->valor_par = $valor_par;
        $this->parcela = $parcela;
        $this->vencimento = $vencimento;
        $this->valor_pag = $valor_pag;
        $this->data_pag = $data_pag ? new DateTime($data_pag) : null;
        $this->obs = $obs;
        $this->id_pgto = $id_pgto;
    }

    public static function create($rec02) {
        $pdo = (new Database())->connect();

        $sql = 'INSERT INTO rec02 (id_empresa, id_rec01, valor_par, parcela, vencimento, valor_pag, data_pag, obs, id_pgto) 
                VALUES (:id_empresa, :id_rec01, :valor_par, :parcela, :vencimento, :valor_pag, :data_pag, :obs, :id_pgto)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $rec02->id_empresa);
        $stmt->bindValue(':id_rec01', $rec02->id_rec01);
        $stmt->bindValue(':valor_par', $rec02->valor_par);
        $stmt->bindValue(':parcela', $rec02->parcela);
        $stmt->bindValue(':vencimento', $rec02->vencimento);
        $stmt->bindValue(':valor_pag', $rec02->valor_pag);
        $stmt->bindValue(':data_pag', $rec02->data_pag ? $rec02->data_pag->format('Y-m-d') : null);
        $stmt->bindValue(':obs', $rec02->obs);
        $stmt->bindValue(':id_pgto', $rec02->id_pgto);

        

        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_rec01 = null, $data = null, $parcela = null, 
    $filtro_data_inicial = null,  $filtro_data_final = null, $filtro_nome = null, $filtro_opcao = null, $filtro_por = null, $filtro_pagamento = null,
    $dash_quitado = false, $dash_tipo = null
    ): array {
        $pdo = (new Database())->connect();
        if(isset($filtro_nome)) {
            $query = '
                SELECT r2.*, r1.documento 
                FROM rec02 r2
                INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id
            ';
        } else {
            $query = 'SELECT * FROM rec02';
        }
        $conditions = [];
        if($filtro_opcao != null && $filtro_opcao != '') {
            switch($filtro_opcao) {
                case 'abertos':
                    $conditions[] = 'valor_pag < valor_par';
                    break;
                case 'quitados':
                    $conditions[] = 'valor_pag = valor_par';
                    break;
            }
        }
        if($filtro_por != null) {
            switch($filtro_por) {
            case 'lancamento':
                if($filtro_nome != null) {
                    $filtro_por_data = 'r2.data_lanc';
                } else {
                    $filtro_por_data = 'r2.data_lanc';
                }
                
                break;
            case 'vencimento':
                if($filtro_nome != null) {
                    $filtro_por_data = 'r2.vencimento';
                } else {
                    $filtro_por_data = 'r2.vencimento';
                }
                break;
            case 'pagamento':
                if($filtro_nome != null) {
                    $filtro_por_data = 'r2.data_pag';
                } else {
                    $filtro_por_data = 'r2.data_pag';
                }
                break;
            }

            if($filtro_data_inicial != null) {
                $conditions[] = $filtro_por_data . ' >= :filtro_data_inicial';
            }
            if($filtro_data_final != null) {
                $conditions[] = $filtro_por_data . ' <= :filtro_data_final';
            }
            
        }
        if ($dash_tipo != null && $filtro_data_inicial != null) {
        switch ($dash_tipo) {
            case 'hoje':
                $conditions[] = 'vencimento = :filtro_data_inicial';
                break;
            case 'semana':
                $conditions[] = 'vencimento > :filtro_data_inicial';
                $conditions[] = 'vencimento <= :filtro_data_final';
                break;
            case 'venceu':
                $conditions[] = 'vencimento < :filtro_data_inicial';
                break;
        }
    }
        

        if ($id != null) $conditions[] = 'id = :id';
        if(isset($filtro_nome)) {
            if ($id_empresa != null) $conditions[] = 'r1.id_empresa = :id_empresa';
        } else {
            if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        }
        
        if ($id_rec01 != null) $conditions[] = 'id_rec01 = :id_rec01';
        if ($data != null) $conditions[] = 'MONTH(vencimento) = MONTH(:data) AND YEAR(vencimento) = YEAR(:data)';
        if ($parcela != null) $conditions[] = 'parcela = :parcela';
        if ($filtro_pagamento != null) $conditions[] = 'id_pgto = :filtro_pagamento';
        if ($dash_quitado == true) $conditions[] = 'valor_pag != valor_par';

        
        if ($filtro_nome != null) $conditions[] = 'r1.documento LIKE :filtro_nome';

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_rec01 != null) $stmt->bindValue(':id_rec01', $id_rec01);
        if ($parcela != null) $stmt->bindValue(':parcela', $parcela);
        if ($data != null) {
        if ($data instanceof DateTime) {
            $data = $data->format('Y-m-d'); // ou 'Y-m' se só quiser comparar mês
        }
        $stmt->bindValue(':data', $data);
        }
        if($filtro_data_inicial != null) $stmt->bindValue(':filtro_data_inicial', $filtro_data_inicial);
        if($filtro_data_final != null) $stmt->bindValue(':filtro_data_final', $filtro_data_final);
        if($filtro_nome != null) $stmt->bindValue(':filtro_nome', '%' . $filtro_nome . '%');
        if($filtro_pagamento != null) $stmt->bindValue(':filtro_pagamento', $filtro_pagamento);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function update($rec02) {
        $pdo = (new Database())->connect();

        $sql = 'UPDATE rec02 
                SET valor_par = :valor_par, parcela = :parcela, vencimento = :vencimento, valor_pag = :valor_pag, data_pag = :data_pag, obs = :obs, id_pgto = :id_pgto 
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $rec02->id);
        $stmt->bindValue(':valor_par', $rec02->valor_par);
        $stmt->bindValue(':parcela', $rec02->parcela);
        $stmt->bindValue(':vencimento', $rec02->vencimento);
        $stmt->bindValue(':valor_pag', $rec02->valor_pag);
        $stmt->bindValue(':data_pag', $rec02->data_pag ? $rec02->data_pag->format('Y-m-d') : null);
        $stmt->bindValue(':obs', $rec02->obs);
        $stmt->bindValue(':id_pgto', $rec02->id_pgto);

        

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM rec02 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
}
public static function deletebyrec01($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM rec02 WHERE id_rec01 = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
}

}
?>