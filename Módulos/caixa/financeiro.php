<?php
include("conexao.php");

$sql = "SELECT 
SUM(total) as total,
SUM(custo) as custo,
SUM(lucro) as lucro
FROM vendas
WHERE MONTH(data)=MONTH(CURRENT_DATE())";

$r = mysqli_query($conexao,$sql);
$d = mysqli_fetch_assoc($r);

echo json_encode([
"total"=>$d['total']??0,
"custo"=>$d['custo']??0,
"lucro"=>$d['lucro']??0
]);