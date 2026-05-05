<?php

require_once __DIR__ . '/../base.php';

class Logo {
    public $id;
    public $id_empresa;
    public $foto;

    public function __construct(
        $id = null,
        $id_empresa = null,
        $foto = ''
    ) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->foto = $foto;
    }

    public static function create($logo) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO logo (id_empresa, foto) 
                VALUES (:id_empresa, :foto)';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id_empresa', $logo->id_empresa, PDO::PARAM_INT);
        
        // Para BLOB, usar stream se disponível, senão bindValue direto
        if (is_resource($logo->foto)) {
            $stmt->bindParam(':foto', $logo->foto, PDO::PARAM_LOB);
        } else {
            $stmt->bindValue(':foto', $logo->foto, PDO::PARAM_LOB);
        }

        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null) {
        $pdo = (new Database())->connect();
        
        if ($id) {
            $sql = 'SELECT * FROM logo WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        } elseif ($id_empresa) {
            $sql = 'SELECT * FROM logo WHERE id_empresa = :id_empresa ORDER BY id DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_empresa', $id_empresa, PDO::PARAM_INT);
        } else {
            $sql = 'SELECT * FROM logo';
            $stmt = $pdo->prepare($sql);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Logo(
                $row['id'] ?? null,
                $row['id_empresa'] ?? null,
                $row['foto'] ?? ''
            );
        }

        return $result;
    }

    public static function update($logo) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE logo SET 
                id_empresa = :id_empresa,
                foto = :foto
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $logo->id, PDO::PARAM_INT);
        $stmt->bindValue(':id_empresa', $logo->id_empresa);
        $stmt->bindValue(':foto', $logo->foto, PDO::PARAM_LOB);

        return $stmt->execute();
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $sql = 'DELETE FROM logo WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?>
