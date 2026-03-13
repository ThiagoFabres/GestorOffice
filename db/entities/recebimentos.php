<?php

require_once __DIR__ . '/../base.php';

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
    public $centro_custos;
    public $id_convertido;
    public $valor_b;
    public $valor_liq_go;
    public function __construct($id = null, $id_empresa = null, $id_cadastro = null, $id_con01 = null, $id_con02 = null, $documento = '', $descricao = '', $valor = 0.0, $parcelas = 1, $data_lanc = null, $id_usuario = null, $centro_custos = null, $id_convertido = null, $valor_b = null, $valor_liq_go = null) {
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
        $this->centro_custos = $centro_custos;
        $this->id_convertido = $id_convertido;
        $this->valor_b = $valor_b;
        $this->valor_liq_go = $valor_liq_go;
    }

    public static function create($rec01) {
        $pdo = (new Database())->connect();

        $sql = 'INSERT INTO rec01 (centro_custos, id_empresa, id_cadastro, id_con01, id_con02, documento, descricao, valor, parcelas, data_lanc, id_usuario, id_convertido, valor_b, valor_liq_go) 
                VALUES (:centro_custos, :id_empresa, :id_cadastro, :id_con01, :id_con02, :documento, :descricao, :valor, :parcelas, :data_lanc, :id_usuario, :id_convertido, :valor_b, :valor_liq_go)';
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
        $stmt->bindValue(':centro_custos', $rec01->centro_custos);
        $stmt->bindValue(':id_convertido', $rec01->id_convertido);
        $stmt->bindValue(':valor_b', $rec01->valor_b);
        $stmt->bindValue(':valor_liq_go', $rec01->valor_liq_go);

        

        return $stmt->execute();
    }

    public static function read(
        $id = null, 
        $id_empresa = null, 
        $id_cadastro = null, 
        $documento = null, 
        $con01 = null, 
        $con02 = null,
        $read_vendas = null,
        $read_diferencas = null,
        $read_paginas = null,
        $numero_exibir = null,
        $numero_pagina = null,
        $filtro_data_inicial = null,
        $filtro_data_final = null,
        $filtro_custos = null,
        ) {

        $pdo = (new Database())->connect();
        if($read_paginas === true) {
            $query = 'SELECT COUNT(*) FROM rec01';
        }else {
            $query = 'SELECT * FROM rec01';
        }
        
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_cadastro != null) $conditions[] = 'id_cadastro = :id_cadastro';
        if ($documento != null) $conditions[] = 'documento = :documento';
        if ($con01 != null) $conditions[] = 'id_con01 = :con01';
        if ($con02 != null) $conditions[] = 'id_con02 = :con02';
        if ($read_vendas) $conditions[] = 'valor_b IS NOT NULL';
        if ($read_diferencas) {
            $conditions[] =
                'ROUND((((valor_b - valor_liq_go) * 100) / NULLIF(valor_b,0)),2) <> '
                . 'ROUND((((valor_b - valor) / NULLIF(valor_b,0)) * 100),2)';
        }
        if ($filtro_data_inicial != null) $conditions[] = 'data_lanc >= :filtro_data_inicial';
        if ($filtro_data_final != null) $conditions[] = 'data_lanc <= :filtro_data_final';
        if ($filtro_custos != null) $conditions[] = 'centro_custos = :centro_custos';


        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        if($numero_exibir != null) {
            $query .= ' LIMIT ' . $numero_exibir;
        }
        if($numero_pagina > 1) {
            $query .= ' OFFSET ' . $numero_exibir *( $numero_pagina - 1);
        }
        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_cadastro != null) $stmt->bindValue(':id_cadastro', $id_cadastro);
        if ($documento != null) $stmt->bindValue(':documento', $documento);
        if ($con01 != null) $stmt->bindValue(':con01', $con01);
        if ($con02 != null) $stmt->bindValue(':con02', $con02);
        if ($filtro_data_inicial != null) $stmt->bindValue(':filtro_data_inicial', $filtro_data_inicial);
        if ($filtro_data_final != null) $stmt->bindValue(':filtro_data_final', $filtro_data_final);
        if ($filtro_custos != null) $stmt->bindValue(':centro_custos', $filtro_custos);
        // if ($read_paginas === null){
        //     echo $query;
        //     exit;
        // }   
        $stmt->execute();

        if(isset($read_paginas)) {
            return $stmt->fetchColumn();
        } else {
            return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
        }
    }

    public static function update($rec01) {
        $pdo = (new Database())->connect();

        $sql = 'UPDATE rec01 
                SET 
                centro_custos = :centro_custos, 
                id_cadastro = :id_cadastro, 
                id_con01 = :id_con01, 
                id_con02 = :id_con02, 
                documento = :documento, 
                descricao = :descricao, 
                valor = :valor, 
                parcelas = :parcelas, 
                data_lanc = :data_lanc, 
                id_usuario = :id_usuario
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
        $stmt->bindValue(':centro_custos', $rec01->centro_custos);

        

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM rec01 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
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

    public static function read(
        $id = null, 
        $id_empresa = null, 
        $id_rec01 = null, 
        $data = null, 
        $parcela = null, 
        $filtro_data_inicial = null,  
        $filtro_data_final = null, 
        $filtro_documento = null, 
        $filtro_opcao = null, 
        $filtro_por = null, 
        $filtro_pagamento = null,
        $dash_quitado = false, 
        $dash_tipo = null, 
        $numero_exibir = null,
        $read_paginas = null,
        $numero_pagina = null,
        $ordenar_por = null,
        $direcao = 'asc',
        $filtro_con01 = null,
        $filtro_con02 = null,
        $filtro_cadastro = null,
        $filtro_custos = null,
        $read_totais = null,
        $read_vendas = null
    ) {
        $pdo = (new Database())->connect();

        if(isset($read_paginas) && $read_paginas) {
            $query = 'SELECT COUNT(*) 
                FROM rec02 r2
                INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id
            ';
        
        } else if($ordenar_por === 'nome') {
               $query = 'SELECT r2.*, r1.documento, r1.id_con02, c.razao_soc 
               FROM rec02 r2 INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id 
               INNER JOIN cadastro c ON r1.id_cadastro = c.id_cadastro';
            
            } else if($read_totais) {
                $query = 'SELECT r2.valor_par, r2.valor_pag, r1.documento, r1.id_con02
                FROM rec02 r2
                INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id
            ';
            } else {
                $query = 'SELECT r2.*, r1.documento, r1.id_con02
                FROM rec02 r2
                INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id
            ';
            }
        
        
        

        
        $conditions = [];
        if($filtro_opcao != null && $filtro_opcao != '') {
            switch($filtro_opcao) {
                case 'abertos':
                    $conditions[] = 'r2.valor_pag = 0';
                    break;
                case 'quitados':
                    $conditions[] = 'r2.valor_pag > 0';
                    break;
            }
        }
        if($filtro_por != null) {
            switch($filtro_por) {
            case 'lancamento':
                    $filtro_por_data = 'r1.data_lanc';
                break;
            case 'vencimento':
                    $filtro_por_data = 'r2.vencimento';
                break;
            case 'pagamento':
                    $filtro_por_data = 'r2.data_pag';
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
                $conditions[] = 'r2.vencimento = :filtro_data_inicial';
                break;
            case 'a_vencer':
                $conditions[] = 'r2.vencimento > :filtro_data_inicial AND r2.vencimento <= :filtro_data_final AND r2.valor_pag <= 0';
                break;
            case 'venceu':
                $conditions[] = 'r2.vencimento < :filtro_data_inicial';
                break;
        }
    }
    if($filtro_con01 != null) {
        $conditions[] = 'r1.id_con01 = :filtro_con01';
    }
    if($filtro_con02 != null) {
        $conditions[] = 'r1.id_con02 = :filtro_con02';
    }
    if($read_vendas != null) {
        $conditions[] = 'r1.valor_b IS NOT NULL';
    }
        

        if ($id != null) $conditions[] = 'r2.id = :id';
        if ($id_empresa != null) {
    if (strpos($query, 'rec01') !== false) {
        $conditions[] = 'r1.id_empresa = :id_empresa';
    } else {
        $conditions[] = 'r2.id_empresa = :id_empresa';
    }
}


        if($filtro_por == 'pagamento') $conditions[] = 'r2.data_pag IS NOT NULL';
        if ($id_rec01 != null) $conditions[] = 'r2.id_rec01 = :id_rec01';
        if ($data != null) $conditions[] = 'MONTH(r2.vencimento) = MONTH(:data) AND YEAR(r2.vencimento) = YEAR(:data)';
        if ($parcela != null) $conditions[] = 'r2.parcela = :parcela';
        if ($filtro_pagamento != null) $conditions[] = 'r2.id_pgto = :filtro_pagamento';
        if ($dash_quitado == true) $conditions[] = 'r2.valor_pag <= 0';

        
        if ($filtro_documento != null) $conditions[] = 'r1.documento LIKE :filtro_documento';
        if ($filtro_cadastro != null) $conditions[] = 'r1.id_cadastro LIKE :filtro_cadastro';
        if ($filtro_custos != null) $conditions[] = 'r1.centro_custos = :filtro_custos';



        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

switch($ordenar_por) {
    case 'documento':
        if (strpos($query, 'rec01') === false) {
            $query = str_replace(
                'FROM rec02',
                'FROM rec02 r2 INNER JOIN rec01 r1 ON r2.id_rec01 = r1.id',
                $query
            );
        }
        $query .= ' ORDER BY r1.documento '. $direcao . ', r1.data_lanc desc';
        break;

    case 'data_lancamento':   
        $query .= ' ORDER BY r1.data_lanc '. $direcao . ', r1.data_lanc desc';
        break;

    case 'nome':
        $query .= ' ORDER BY c.razao_soc '. $direcao . ', r1.data_lanc desc';
        break;
    case 'valor':
        $query .= ' ORDER BY r1.valor '. $direcao . ', r1.data_lanc desc';
        break;
    case 'valor_parcela':
        $query .= ' ORDER BY r2.valor_par '. $direcao . ', r1.data_lanc desc';
        break;

    case 'data_vencimento':
        $query .= ' ORDER BY r2.vencimento '. $direcao . ', r1.data_lanc desc';
        break;

    case 'data_pagamento':
        $query .= ' ORDER BY r2.data_pag '. $direcao . ', r1.data_lanc desc';
        break;

    case 'valor_pagamento':
        $query .= ' ORDER BY r2.valor_pag '. $direcao . ', r1.data_lanc desc';
        break;
    case 'tipo_pagamento':

        break;
    case 'parcela':
        $query .= ' ORDER BY r2.parcela asc, r1.data_lanc asc';
        break;
    default:
        $query .= ' ORDER BY r1.data_lanc desc';
        break;
    }
    

        if($numero_exibir != null) {
            $query .= ' LIMIT ' . $numero_exibir;
        }
        if($numero_pagina > 1) {
            $query .= ' OFFSET ' . $numero_exibir *( $numero_pagina - 1);
        }
        
        


        // if(!$read_paginas){
        // echo $query;
        // exit;
        // }
        // if($filtro_opcao != null) {
        //     echo $query;
        //     exit;
        // }

        $stmt = $pdo->prepare($query);

        // Helper para verificar se marcador existe na query
        $hasParam = function($param) use ($query) {
            return strpos($query, $param) !== false;
        };

        if ($id != null && $hasParam(':id')) $stmt->bindValue(':id', $id);
        if ($id_empresa != null && $hasParam(':id_empresa')) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_rec01 != null && $hasParam(':id_rec01')) $stmt->bindValue(':id_rec01', $id_rec01);
        if ($parcela != null && $hasParam(':parcela')) $stmt->bindValue(':parcela', $parcela);
        if ($data != null && $hasParam(':data')) {
            if ($data instanceof DateTime) {
                $data = $data->format('Y-m-d');
            }
            $stmt->bindValue(':data', $data);
        }
        if($filtro_data_inicial != null && $hasParam(':filtro_data_inicial')) $stmt->bindValue(':filtro_data_inicial', $filtro_data_inicial);
        if($filtro_data_final != null && $hasParam(':filtro_data_final')) $stmt->bindValue(':filtro_data_final', $filtro_data_final);
        if($filtro_documento != null && $hasParam(':filtro_documento')) $stmt->bindValue(':filtro_documento', '%' . $filtro_documento . '%');
        if($filtro_cadastro != null && $hasParam(':filtro_cadastro')) $stmt->bindValue(':filtro_cadastro', $filtro_cadastro);
        if($filtro_pagamento != null && $hasParam(':filtro_pagamento')) $stmt->bindValue(':filtro_pagamento', $filtro_pagamento);
        if($filtro_con01 != null && $hasParam(':filtro_con01')) $stmt->bindValue(':filtro_con01', $filtro_con01);
        if($filtro_con02 != null && $hasParam(':filtro_con02')) $stmt->bindValue(':filtro_con02', $filtro_con02);
        if($filtro_custos != null && $hasParam(':filtro_custos')) $stmt->bindValue(':filtro_custos', $filtro_custos);

        $stmt->execute();

        if(isset($read_paginas)) {
            return $stmt->fetchColumn();
        } else {
            return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
        }
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

        return $stmt->execute();
}
public static function deletebyrec01($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM rec02 WHERE id_rec01 = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
}
public static function readPagos() {
        $pdo = (new Database())->connect();
        $stmt = $pdo->query("SELECT DISTINCT id_rec01 FROM rec02 WHERE valor_pag > 0");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $ids;

}
}

class Rec03 {
    public $id;
    public $id_empresa;
    public $data;
    public $operadora_id;
    public $bandeira_id;
    public $tipo_id;
    public $prazo_id;

    public function __construct($id = null, $id_empresa = null, $data = null, $operadora_id = null, $bandeira_id = null, $tipo_id = null, $prazo_id = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->data = $data;
        $this->operadora_id = $operadora_id;
        $this->bandeira_id = $bandeira_id;
        $this->tipo_id = $tipo_id;
        $this->prazo_id = $prazo_id;
    }
     public static function create($rec03) {
        $pdo = (new Database())->connect();

        $sql = "
INSERT INTO rec03 (
    id_empresa,
    data_lanc,
    operadora_id,
    bandeira_id,
    prazo_id
) VALUES (
    :id_empresa,
    :data_lanc,
    :operadora_id,
    :bandeira_id,
    :prazo_id
)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $rec03->id_empresa);
        $stmt->bindValue(':data_lanc', $rec03->data);
        $stmt->bindValue(':operadora_id', $rec03->operadora_id);
        $stmt->bindValue(':bandeira_id', $rec03->bandeira_id);
        $stmt->bindValue(':prazo_id', $rec03->prazo_id);
        

        return $stmt->execute();
    }
    public static function read(
    $id = null, 
    $id_empresa = null, 
    $data = null, 
    $data_inicial = null,
    $data_final = null,
    $operadora_id = null, 
    $bandeira_id = null, 
    $tipo_id = null,
    $prazo_id = null,
    ): array {

        $pdo = (new Database())->connect();
        $query = 'SELECT * FROM rec03';
        $conditions = [];

        if ($id != null) $conditions[] = 'id = :id';
        if ($id_empresa != null) $conditions[] = 'id_empresa = :id_empresa';
        if ($data != null) $conditions[] = 'data_lanc = :data_lanc';
        if ($data_inicial != null) $conditions[] = ' data_lanc >= :data_inicial';
        if ($data_final != null) $conditions[] = ' data_lanc >= :data_final';
        if ($operadora_id != null) $conditions[] = 'operadora_id = :operadora_id';
        if ($bandeira_id != null) $conditions[] = 'bandeira_id = :bandeira_id';
        if ($tipo_id != null) $conditions[] = 'tipo_id = :tipo_id';
        if ($prazo_id != null) $conditions[] = 'prazo_id = :prazo_id';


        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($query);

        if ($id != null) $stmt->bindValue(':id', $id);
        if ($id_empresa != null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($data != null) $stmt->bindValue(':data_lanc', $data);
        if ($operadora_id != null) $stmt->bindValue(':operadora_id', $operadora_id);
        if ($bandeira_id != null) $stmt->bindValue(':bandeira_id', $bandeira_id);
        if ($tipo_id != null) $stmt->bindValue(':tipo_id', $tipo_id);
        if ($prazo_id != null) $stmt->bindValue(':prazo_id', $prazo_id);
        if ($data_inicial != null) $stmt->bindValue(':data_inicial', $data_inicial);
        if ($data_final != null) $stmt->bindValue(':data_final', $data_final);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM rec03 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
}
}
?>