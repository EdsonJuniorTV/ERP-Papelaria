<?php
include("conexao.php");

$sql_vendas = "
SELECT 
SUM(total) as total,
SUM(comissao) as comissao
FROM vendas
WHERE MONTH(data)=MONTH(CURRENT_DATE())
";

$r = mysqli_query($conexao,$sql_vendas);

$d = mysqli_fetch_assoc($r);

$total = $d['total'] ?? 0;
$comissao = $d['comissao'] ?? 0;

$sql_financeiro = "SELECT * FROM financeiro LIMIT 1";

$r2 = mysqli_query($conexao,$sql_financeiro);

$f = mysqli_fetch_assoc($r2);

$investimento = $f['investimento_total'];
$meta = $f['meta'];

$ganho_real = $total - $comissao;

$resultado = $ganho_real - $investimento;

echo json_encode([
"investimento"=>$investimento,
"meta"=>$meta,
"vendas"=>$total,
"comissao"=>$comissao,
"ganho_real"=>$ganho_real,
"resultado"=>$resultado
]);
?>