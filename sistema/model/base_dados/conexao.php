<?php
    define('host','localhost');
    define('user','root');
    define('pass','');
    define('db','papelaria');

    $conexao = mysqli_connect(host,user,pass,db);

    if (!$conexao) {
        die("Erro de conexão com o banco de dados: " . mysqli_connect_error());
    }

    mysqli_set_charset($conexao, "utf8mb4");
    date_default_timezone_set('America/Sao_Paulo');
?>