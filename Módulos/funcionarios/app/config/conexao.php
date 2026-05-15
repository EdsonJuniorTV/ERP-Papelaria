<?php
    function getConnection() {

        $host = 'localhost';
        $name = 'funcionario';
        $user = 'root';
        $password = '';
        $charset = 'utf8mb4';
        
        try {

            $conn = new PDO("mysql:host=$host;dbname=$name;charset=$charset", $user, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $conn;

        } catch(PDOException $e) {

            error_log("Erro de conexão: " . $e->getMessage());
            die("Erro ao conectar com o banco de dados. Contate o administrador.");
        }
    }
?>