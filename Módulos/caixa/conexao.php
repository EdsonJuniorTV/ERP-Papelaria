<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "erp_papelaria1";

$conexao = mysqli_connect($host, $user, $pass, $db);

if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error());
}

?>