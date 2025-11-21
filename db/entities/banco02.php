<?php
class Ban02 {
    public $id;
    public $id_empresa;
    public $id_ban01;
    public $valor_par;
    public $parcela;
    public $vencimento;
    public $valor_pag;
    public $data_pag;
    public $obs;
    public $id_pgto;

    public function __construct($id = null, $id_empresa = null, $id_ban01 = null, $valor_par = 0.0, $parcela = 1, $vencimento = null, $valor_pag = 0.0, $data_pag = null, $obs = '', $id_pgto = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_ban01 = $id_ban01;
        $this->valor_par = $valor_par;
        $this->parcela = $parcela;
        $this->vencimento = $vencimento;
        $this->valor_pag = $valor_pag;
        $this->data_pag = $data_pag ? new DateTime($data_pag) : null;
        $this->obs = $obs;
        $this->id_pgto = $id_pgto;
    }

    public static function create($ban02) {
        $pdo = (new Database())->connect();

        $sql = 'INSERT INTO ban02 (id_empresa, id_ban01, valor_par, parcela, vencimento, valor_pag, data_pag, obs, id_pgto) 
                VALUES (:id_empresa, :id_ban01, :valor_par, :parcela, :vencimento, :valor_pag, :data_pag, :obs, :id_pgto)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $ban02->id_empresa);
        $stmt->bindValue(':id_ban01', $ban02->id_ban01);
        $stmt->bindValue(':valor_par', $ban02->valor_par);
        $stmt->bindValue(':parcela', $ban02->parcela);
        $stmt->bindValue(':vencimento', $ban02->vencimento);
        $stmt->bindValue(':valor_pag', $ban02->valor_pag);
        $stmt->bindValue(':data_pag', $ban02->data_pag ? $ban02->data_pag->format('Y-m-d') : null);
        $stmt->bindValue(':obs', $ban02->obs);
        $stmt->bindValue(':id_pgto', $ban02->id_pgto);

        

        return $stmt->execute();
    }

    public static function read(
        $id = null, 
        $id_empresa = null, 
        $id_ban01 = null, 
        $data = null, 
        $parcela = null, 
        $filtro_data_inicial = null,  
        $filtro_data_final = null, 
        $filtro_documento= null, 
        $filtro_opcao = null, 
        $filtro_por = null, 
        $filtro_pagamento = null,
        $dash_quitado = null, 
        $dash_tipo = null, 
        $numero_exibir = null,
        $read_paginas = null,
        $numero_pagina = null,
        $ordenar_por = null,
        $direcao = 'asc',
        $filtro_con01 = null,
        $filtro_con02 = null,
        $filtro_cadastro = null,
        $filtro_custos = null

    ) {
        $pdo = (new Database())->connect();

        if(isset($read_paginas) && $read_paginas) {
            $query = 'SELECT COUNT(*) 
                FROM ban02 p2
                INNER JOIN ban01 p1 ON p2.id_ban01 = p1.id
            ';
        
        } else if($ordenar_por === 'nome') {
               $query = 'SELECT p2.*, p1.documento, p1.id_con02, c.razao_soc FROM ban02 p2 INNER JOIN ban01 p1 ON p2.id_ban01 = p1.id INNER JOIN cadastro c ON p1.id_cadastro = c.id_cadastro';
            
            } else {
                $query = 'SELECT p2.*, p1.documento, p1.id_con02 
                FROM ban02 p2
                INNER JOIN ban01 p1 ON p2.id_ban01 = p1.id
            ';
            }
        
        
        

        
        $conditions = [];
        if($filtro_opcao != null && $filtro_opcao != '') {
            switch($filtro_opcao) {
                case 'abertos':
                    $conditions[] = 'p2.valor_pag = 0';
                    break;
                case 'quitados':
                    $conditions[] = 'p2.valor_pag > 0';
                    break;
            }
        }
        if($filtro_por != null) {
            switch($filtro_por) {
            case 'lancamento':
                    $filtro_por_data = 'p1.data_lanc';
                break;
            case 'vencimento':
                    $filtro_por_data = 'p2.vencimento';
                break;
            case 'pagamento':
                    $filtro_por_data = 'p2.data_pag';
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
                $conditions[] = 'p2.vencimento = :filtro_data_inicial AND p2.valor_pag = 0';
                break;
            case 'a_vencer':
                $conditions[] = 'p2.vencimento > :filtro_data_inicial AND p2.valor_pag = 0';
                break;
            case 'venceu':
                $conditions[] = 'p2.vencimento < :filtro_data_inicial AND p2.valor_pag = 0';
                break;
        }
    }

    if($filtro_con01 != null) {
        $conditions[] = 'p1.id_con01 = :filtro_con01';
    }
    
    if($filtro_con02 != null) {
        $conditions[] = 'p1.id_con02 = :filtro_con02';
    }
        

        if ($id != null) $conditions[] = 'p2.id = :id';
        if ($id_empresa != null) {
    if (strpos($query, 'ban01') !== false) {
        $conditions[] = 'p1.id_empresa = :id_empresa';
    } else {
        $conditions[] = 'p2.id_empresa = :id_empresa';
    }
}


        
        if ($id_ban01 != null) $conditions[] = 'p2.id_ban01 = :id_ban01';
        if ($data != null) $conditions[] = 'MONTH(p2.vencimento) = MONTH(:data) AND YEAR(p2.vencimento) = YEAR(:data)';
        if ($parcela != null) $conditions[] = 'p2.parcela = :parcela';
        if ($filtro_pagamento != null) $conditions[] = 'p2.id_pgto = :filtro_pagamento';
        if ($dash_quitado != null && $dash_quitado == true) $conditions[] = 'p2.valor_pag != p2.valor_par';

        
        if ($filtro_documento != null) $conditions[] = 'p1.documento LIKE :filtro_documento';
        if ($filtro_cadastro != null) $conditions[] = 'p1.id_cadastro LIKE :filtro_cadastro';
        if ($filtro_custos != null) $conditions[] = 'p1.centro_custos = :filtro_custos';



        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

switch($ordenar_por) {
    case 'documento':
        if (strpos($query, 'ban01') === false) {
            $query = str_replace(
                'FROM ban02',
                'FROM ban02 p2 INNER JOIN ban01 p1 ON p2.id_ban01 = p1.id',
                $query
            );
        }
        $query .= ' ORDER BY p1.documento '. $direcao . ', p1.data_lanc desc';
        break;

    case 'data_lancamento':   
        $query .= ' ORDER BY p1.data_lanc '. $direcao . ', p1.data_lanc desc';
        break;

    case 'nome':
        $query .= ' ORDER BY c.razao_soc '. $direcao . ', p1.data_lanc desc';
        break;
    case 'valor':
        $query .= ' ORDER BY p1.valor '. $direcao . ', p1.data_lanc desc';
        break;
    case 'valor_parcela':
        $query .= ' ORDER BY p2.valor_par '. $direcao . ', p1.data_lanc desc';
        break;

    case 'data_vencimento':
        $query .= ' ORDER BY p2.vencimento '. $direcao . ', p1.data_lanc desc';
        break;

    case 'data_pagamento':
        $query .= ' ORDER BY p2.data_pag '. $direcao . ', p1.data_lanc desc';
        break;

    case 'valor_pagamento':
        $query .= ' ORDER BY p2.valor_pag '. $direcao . ', p1.data_lanc desc';
        break;
    case 'tipo_pagamento':

        break;
    case 'parcela':
        $query .= ' ORDER BY p2.parcela asc, p1.data_lanc asc';
        break;
    default:
        $query .= ' ORDER BY p1.data_lanc desc';
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

        $stmt = $pdo->prepare($query);

        // Helper para verificar se marcador existe na query
        $hasParam = function($param) use ($query) {
            return strpos($query, $param) !== false;
        };

        if ($id != null && $hasParam(':id')) $stmt->bindValue(':id', $id);
        if ($id_empresa != null && $hasParam(':id_empresa')) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_ban01 != null && $hasParam(':id_ban01')) $stmt->bindValue(':id_ban01', $id_ban01);
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
        if($filtro_cadastro != null && $hasParam(':filtro_cadastro')) $stmt->bindValue(':filtro_cadastro',$filtro_cadastro);
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

    public static function update($ban02) {
        $pdo = (new Database())->connect();

        $sql = 'UPDATE ban02 
                SET valor_par = :valor_par, parcela = :parcela, vencimento = :vencimento, valor_pag = :valor_pag, data_pag = :data_pag, obs = :obs, id_pgto = :id_pgto 
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $ban02->id);
        $stmt->bindValue(':valor_par', $ban02->valor_par);
        $stmt->bindValue(':parcela', $ban02->parcela);
        $stmt->bindValue(':vencimento', $ban02->vencimento);
        $stmt->bindValue(':valor_pag', $ban02->valor_pag);
        $stmt->bindValue(':data_pag', $ban02->data_pag ? $ban02->data_pag->format('Y-m-d') : null);
        $stmt->bindValue(':obs', $ban02->obs);
        $stmt->bindValue(':id_pgto', $ban02->id_pgto); 

        

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM ban02 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
}

    public static function deletebyban01($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM ban02 WHERE id_ban01 = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
}

public static function readPagos() {
        $pdo = (new Database())->connect();
        $stmt = $pdo->query("SELECT DISTINCT id_ban01 FROM ban02 WHERE valor_pag > 0");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $ids;

}

}


?>