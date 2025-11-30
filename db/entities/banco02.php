<?php
class Ban02 {
    public $id;
    public $id_empresa;
    public $id_ban01;
    public $data;
    public $documento;
    public $id_con01;
    public $id_con02;
    public $descricao; 
    public $descricao_comp;
    public $valor;
    public $id_original;    
    public $ativo;
    
    

    public function __construct($id = null, $id_empresa = null,  $id_ban01 = null , $data = null, $documento = null, $id_con01 = null,
    $id_con02 = null, $descricao = null, $descricao_comp = null, $valor = null, $id_original = null, $ativo = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_ban01 = $id_ban01;
        if ($data instanceof DateTime) {
            $this->data = $data->format('Y-m-d');
        } else {
            $this->data = $data;
        }
        $this->documento = $documento;
        $this->id_con01 = $id_con01;
        $this->id_con02 = $id_con02;
        $this->descricao = $descricao;
        $this->descricao_comp = $descricao_comp;
        $this->valor = $valor;
        $this->id_original = $id_original;
        $this->ativo = $ativo; 
    }
    public static function create($ban02) {
        $pdo = (new Database())->connect();

        $sql = 'INSERT INTO ban02 (id_empresa, id_ban01, valor, descricao, documento, ativo, data, id_original, descricao_comp) 
                  VALUES (:id_empresa, :id_ban01, :valor, :descricao, :documento, :ativo, :data, :id_original, :descricao_comp)';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $ban02->id_empresa);
        $stmt->bindValue(':id_ban01', $ban02->id_ban01);
        $stmt->bindValue(':valor', $ban02->valor);
        $stmt->bindValue(':descricao', $ban02->descricao);
        $stmt->bindValue(':ativo', $ban02->ativo);
        $stmt->bindValue(':data', $ban02->data); // já está formatado no construtor
        $stmt->bindValue(':documento', $ban02->documento);
        $stmt->bindValue(':id_original', $ban02->id_original); 
        $stmt->bindValue(':descricao_comp', $ban02->descricao_comp);

        return $stmt->execute();
    }

    public static function read(
        $id = null,
        $id_empresa = null,
        $documento = null,
        $tipo = null,
        $palavra = null,
        $numero_pagina = null,
        $read_paginas = null,
        $numero_exibir = null
        ) {

        $pdo = (new Database())->connect();
        if($read_paginas != null && $read_paginas) {
            $sql = 'SELECT COUNT(*)
            FROM ban02';
        } else {
            $sql = 'SELECT * FROM ban02';
        }
        $conditions = [];

        if ($id !== null) {
            $conditions[] = ' id = :id';
        }
        if ($id_empresa !== null) {
            $conditions[] =  ' id_empresa = :id_empresa';
        }
        if($documento != null) {
            $conditions[] =  ' LOWER(documento) = :documento';
        }

        if($tipo != null) {
            if($tipo == 'C') {
                $conditions[] =  ' valor >= 0';
            } else if($tipo == 'D') {
                $conditions[] =  ' valor <= 0';
            }
        }
        if($palavra != null) {
            $conditions[] = ' id_con01 IS NULL';
            $conditions[] = ' id_con02 IS NULL';
            $conditions[] = ' descricao LIKE :palavra';
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY data DESC';

        if($numero_exibir != null) {
            $sql .= ' LIMIT ' . intval($numero_exibir);
        }
        if($numero_pagina > 1) {
            $sql .= ' OFFSET ' . intval($numero_exibir *( $numero_pagina - 1));
        }

        $stmt = $pdo->prepare($sql);

        if ($id !== null) {
            $stmt->bindValue(':id', $id);
        }
        if ($id_empresa !== null) {
            $stmt->bindValue(':id_empresa', $id_empresa);
        }
        if ($documento !== null) {
            $stmt->bindValue(':documento', $documento);
        }
        if($palavra != null) {
            $stmt->bindValue(':palavra', '%' . strtolower($palavra) . '%');
        }


        $stmt->execute();
        if(isset($read_paginas)) {
            return $stmt->fetchColumn();
        } else {
            return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
        }
    }

    public static function update($ban02) {
        $pdo = (new Database())->connect();

        $sql = 'UPDATE ban02 SET 
                    id_empresa = :id_empresa,
                    id_ban01 = :id_ban01,
                    valor = :valor,
                    descricao = :descricao,
                    ativo = :ativo,
                    data = :data,
                    id_con01 = :id_con01,
                    id_con02 = :id_con02,
                    documento = :documento,
                    id_original = :id_original,
                    descricao_comp = :descricao_comp
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $ban02->id_empresa);
        $stmt->bindValue(':id_con01', $ban02->id_con01);
        $stmt->bindValue(':id_con02', $ban02->id_con02);
        $stmt->bindValue(':id_empresa', $ban02->id_empresa);
        $stmt->bindValue(':id_ban01', $ban02->id_ban01);
        $stmt->bindValue(':valor', $ban02->valor);
        $stmt->bindValue(':descricao', $ban02->descricao);
        $stmt->bindValue(':ativo', $ban02->ativo);
        $stmt->bindValue(':data', $ban02->data);
        $stmt->bindValue(':documento', $ban02->documento);
        $stmt->bindValue(':id_original', $ban02->id_original); 
        $stmt->bindValue(':descricao_comp', $ban02->descricao_comp);
        $stmt->bindValue(':id', $ban02->id);

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


}

class Ban02Imp {
    public $id_empresa;
    public $id_ban01;
    public $data;

    public function __construct($id_empresa = null, $id_ban01 = null, $data = null) {
        $this->id_empresa = $id_empresa;
        $this->id_ban01 = $id_ban01;
         if ($data instanceof DateTime) {
            $this->data = $data->format('Y-m-d');
        } else {
            $this->data = $data;
        }
    }

    public static function create($ban02) {
        $pdo = (new Database())->connect();

        $sql = 'INSERT INTO ban02_imp (id_empresa, id_ban01, data) 
                VALUES (:id_empresa, :id_ban01, :data)';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $ban02->id_empresa);
        $stmt->bindValue(':id_ban01', $ban02->id_ban01);
        $stmt->bindValue(':data', $ban02->data); 

        return $stmt->execute();
    }
    public static function read($id_empresa = null, $id_ban01 = null, $data = null) {
        $pdo = (new Database())->connect();
        $sql = 'SELECT * FROM ban02_imp WHERE 1=1';
        
        if ($id_ban01 !== null) {
            $sql .= ' AND id_ban01 = :id_ban01';
        }
        if ($id_empresa !== null) {
            $sql .= ' AND id_empresa = :id_empresa';
        }
        if($data != null){
            $sql .= ' AND data = :data';
        }

        $sql .= ' ORDER BY data DESC';

        

        $stmt = $pdo->prepare($sql);

        if ($id_ban01 !== null) {
            $stmt->bindValue(':id_ban01', $id_ban01);
        }
        if ($id_empresa !== null) {
            $stmt->bindValue(':id_empresa', $id_empresa);
        }
        if ($data !== null) {
            $stmt->bindValue(':data', $data);
        }

        

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }
}


?>