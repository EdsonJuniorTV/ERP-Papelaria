<?php
include("conexao.php");

$id = $_POST['id'];
$qtd = $_POST['qtd'];

mysqli_query($conexao, "UPDATE produtos 
SET estoque_atual = estoque_atual - $qtd 
WHERE id = $id");

mysqli_query($conexao, "UPDATE estoque 
SET qtd_produto = qtd_produto - $qtd 
WHERE produto_id = $id");
?>