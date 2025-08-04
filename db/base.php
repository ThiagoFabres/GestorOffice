<?php

class Database {
    private $dsn = 'mysql:host=localhost;dbname=gestor_office';
    private $username = 'root';
    private $password = '';

    public function connect() {
        try {
            $conexao = new PDO($this->dsn, $this->username, $this->password);
            $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexao;
        } catch (PDOException $e) {
            return null;
        }
    }

}
   