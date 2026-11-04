<?php 

class Mov02 {
    private static ?array $colunasRelacionamento = null;

    public $id;
    public $id_empresa;
    public $id_produto;
    public $id_mov01;
    public $quantidade;
    public $preco_custo;
    public $preco_venda;

    public function __construct($id = null, $id_empresa = null, $id_produto = null, $id_mov01 = null, $quantidade = null, $preco_custo = null, $preco_venda = null) {
        $this->id = $id;
        $this->id_empresa = $id_empresa;
        $this->id_produto = $id_produto;
        $this->id_mov01 = $id_mov01;
        $this->quantidade = $quantidade;
        $this->preco_custo = $preco_custo;
        $this->preco_venda = $preco_venda;
    }

    public static function create($movimento, ?PDO $pdo = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $colunas = self::colunasRelacionamento($pdo);
        $idMov01 = (int) $movimento->id_mov01;
        if ($idMov01 < 1) {
            return false;
        }

        $colunasInsert = ['id_empresa', 'id_produto'];
        $valoresInsert = [':id_empresa', ':id_produto'];
        if (isset($colunas['id_mov01'])) {
            $colunasInsert[] = 'id_mov01';
            $valoresInsert[] = ':id_mov01';
        }
        if (!isset($colunas['id_mov01']) || isset($colunas['documento'])) {
            $documento = self::documentoDoMovimento($idMov01, $movimento->id_empresa, $pdo);
            if ($documento === null) {
                return false;
            }
            $colunasInsert[] = 'documento';
            $valoresInsert[] = ':documento';
        }
        array_push($colunasInsert, 'quantidade', 'preco_custo', 'preco_venda');
        array_push($valoresInsert, ':quantidade', ':preco_custo', ':preco_venda');

        $sql = 'INSERT INTO mov02 (' . implode(', ', $colunasInsert) . ')
                VALUES (' . implode(', ', $valoresInsert) . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_empresa', $movimento->id_empresa);
        $stmt->bindValue(':id_produto', $movimento->id_produto);
        if (isset($colunas['id_mov01'])) {
            $stmt->bindValue(':id_mov01', $idMov01);
        }
        if (isset($documento)) {
            $stmt->bindValue(':documento', $documento);
        }
        $stmt->bindValue(':quantidade', $movimento->quantidade);
        $stmt->bindValue(':preco_custo', $movimento->preco_custo);
        $stmt->bindValue(':preco_venda', $movimento->preco_venda);
        return $stmt->execute();
    }

    public static function read($id = null, $id_empresa = null, $id_produto = null, $id_mov01 = null, ?PDO $pdo = null, bool $forUpdate = false) {
        $pdo = $pdo ?? (new Database())->connect();
        $colunas = self::colunasRelacionamento($pdo);
        $query = 'SELECT * FROM mov02';
        $conditions = [];

        if ($id !== null) $conditions[] = 'id = :id';
        if ($id_empresa !== null) $conditions[] = 'id_empresa = :id_empresa';
        if ($id_produto !== null) $conditions[] = 'id_produto = :id_produto';
        if ($id_mov01 !== null) {
            if (isset($colunas['id_mov01']) && isset($colunas['documento'])) {
                $documento = self::documentoDoMovimento($id_mov01, $id_empresa, $pdo);
                $conditions[] = '(id_mov01 = :id_mov01 OR (id_mov01 IS NULL AND documento = :documento))';
            } elseif (isset($colunas['id_mov01'])) {
                $conditions[] = 'id_mov01 = :id_mov01';
            } else {
                $documento = self::documentoDoMovimento($id_mov01, $id_empresa, $pdo);
                if ($documento === null) {
                    return [];
                }
                $conditions[] = 'documento = :documento';
            }
        }

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY id';
        if ($forUpdate) {
            $query .= ' FOR UPDATE';
        }

        $stmt = $pdo->prepare($query);
        if ($id !== null) $stmt->bindValue(':id', $id);
        if ($id_empresa !== null) $stmt->bindValue(':id_empresa', $id_empresa);
        if ($id_produto !== null) $stmt->bindValue(':id_produto', $id_produto);
        if ($id_mov01 !== null && isset($colunas['id_mov01'])) {
            $stmt->bindValue(':id_mov01', $id_mov01);
        }
        if (isset($documento)) {
            $stmt->bindValue(':documento', $documento);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, self::class);
    }

    public static function deleteByMov01($id_mov01, $id_empresa, ?PDO $pdo = null) {
        $pdo = $pdo ?? (new Database())->connect();
        $colunas = self::colunasRelacionamento($pdo);
        if (isset($colunas['id_mov01']) && isset($colunas['documento'])) {
            $documento = self::documentoDoMovimento($id_mov01, $id_empresa, $pdo);
            $stmt = $pdo->prepare(
                'DELETE FROM mov02
                  WHERE id_empresa = :id_empresa
                    AND (id_mov01 = :id_mov01 OR (id_mov01 IS NULL AND documento = :documento))'
            );
            $stmt->bindValue(':id_mov01', $id_mov01);
            $stmt->bindValue(':documento', $documento);
        } elseif (isset($colunas['id_mov01'])) {
            $stmt = $pdo->prepare('DELETE FROM mov02 WHERE id_mov01 = :id_mov01 AND id_empresa = :id_empresa');
            $stmt->bindValue(':id_mov01', $id_mov01);
        } else {
            $documento = self::documentoDoMovimento($id_mov01, $id_empresa, $pdo);
            if ($documento === null) {
                return true;
            }
            $stmt = $pdo->prepare('DELETE FROM mov02 WHERE documento = :documento AND id_empresa = :id_empresa');
            $stmt->bindValue(':documento', $documento);
        }
        $stmt->bindValue(':id_empresa', $id_empresa);
        return $stmt->execute();
    }

    private static function colunasRelacionamento(PDO $pdo): array {
        if (self::$colunasRelacionamento !== null) {
            return self::$colunasRelacionamento;
        }

        $stmt = $pdo->query('SHOW COLUMNS FROM mov02');
        $colunas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $nome) {
            $colunas[(string) $nome] = true;
        }

        if (!isset($colunas['id_mov01']) && !isset($colunas['documento'])) {
            throw new RuntimeException('mov02 precisa ter id_mov01 ou a coluna legada documento.');
        }

        self::$colunasRelacionamento = $colunas;
        return $colunas;
    }

    private static function documentoDoMovimento($id_mov01, $id_empresa, PDO $pdo): ?int {
        if ($id_empresa === null) {
            throw new InvalidArgumentException('A empresa é necessária para localizar os itens do movimento.');
        }

        $stmt = $pdo->prepare(
            'SELECT documento FROM mov01 WHERE id = :id_mov01 AND id_empresa = :id_empresa'
        );
        $stmt->bindValue(':id_mov01', $id_mov01);
        $stmt->bindValue(':id_empresa', $id_empresa);
        $stmt->execute();
        $documento = $stmt->fetchColumn();

        return $documento === false || $documento === null ? null : (int) $documento;
    }
}