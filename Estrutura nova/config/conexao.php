<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "papelaria";

$conexao = mysqli_connect($host, $user, $pass, $db);

if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}
// Define o charset para evitar erros de acentuação
mysqli_set_charset($conexao, "utf8");
?>