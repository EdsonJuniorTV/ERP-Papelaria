<?php
include("conexao.php");

$sql = "SELECT * FROM historico ORDER BY data DESC";
$result = mysqli_query($conexao, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Histórico</title>

<style>
body {
    font-family: Arial;
    background: #f4f6f9;
    text-align: center;
}

table {
    margin: auto;
    border-collapse: collapse;
    width: 70%;
    background: white;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
}

th {
    background: #007bff;
    color: white;
}
</style>
</head>

<body>

<h2>Histórico de Retiradas</h2>

<a href="index.php">⬅ Voltar</a><br><br>

<table>
<tr>
    <th>Nome</th>
    <th>Produto</th>
    <th>Quantidade</th>
    <th>Data</th>
</tr>

<?php
while ($linha = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$linha['nome_pessoa']}</td>
        <td>{$linha['produto']}</td>
        <td>{$linha['quantidade']}</td>
        <td>{$linha['data']}</td>
    </tr>";
}
?>

</table>

</body>
</html>