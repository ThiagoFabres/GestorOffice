<?php

class Database {
    private $dsn = 'mysql:host=localhost;dbname=adri1000_gestor_office_control';
    private $username = 'adri1000_gestoroff_app';
    private $password = 'Sup@085951';

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