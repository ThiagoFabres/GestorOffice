<?php
require_once __DIR__ . '/../base.php';

class Usuario {
    public $id_usuario;
    public $id_empresa;
    public $nome;
    public $email;
    public $senha;
    public $processar;
    public $consultar;
    public $cargo;

    public function __construct($id_usuario = null, $id_empresa = null, $nome = '', $email = '', $senha = '', $processar = 0, $consultar = 0, $cargo = null) {
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->processar = $processar;
        $this->consultar = $consultar;
        $this->cargo = $cargo;
    }

    public static function create($usuario) {
        $pdo = (new Database())->connect();
        $sql = 'INSERT INTO usuario (id_empresa, nome, email, senha, processar, consultar, cargo) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $usuario->id_empresa,
            $usuario->nome,
            $usuario->email,
            $usuario->senha,
            $usuario->processar,
            $usuario->consultar,
            $usuario->cargo
        ]);
        return $pdo->lastInsertId();
    }

    public static function read($id = null) {
        $pdo = (new Database())->connect();
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM usuario where id_usuario = 1');
            $stmt->execute([$id]);
            return $stmt->fetchObject('Usuario');
        } else {
            $stmt = $pdo->query('SELECT * FROM usuario');
            return $stmt->fetchAll(PDO::FETCH_CLASS);
        }
    }

    public static function update($usuario) {
        $pdo = (new Database())->connect();
        $sql = 'UPDATE usuario SET id_empresa = ?, nome = ?, email = ?, senha = ?, processar = ?, consultar = ?, cargo = ? WHERE id_usuario = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $usuario->id_empresa,
            $usuario->nome,
            $usuario->email,
            $usuario->senha,
            $usuario->processar,
            $usuario->consultar,
            $usuario->cargo,
            $usuario->id_usuario
        ]);
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare('DELETE FROM usuario WHERE id_usuario = ?');
        return $stmt->execute([$id]);
    }
}
