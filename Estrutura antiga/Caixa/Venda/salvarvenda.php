<?php
include("conexao.php");

$total = $_POST['total'];
$custo = $_POST['custo'];
$lucro = $_POST['lucro'];

mysqli_query($conexao, "INSERT INTO vendas (total, custo, lucro)
VALUES ('$total','$custo','$lucro')");
?>