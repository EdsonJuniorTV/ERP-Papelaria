<?php include("conexao.php"); ?>

<!DOCTYPE html>
<html>
<head>
<title>ERP Papelaria - Cadastro</title>

<style>
body { font-family: Arial; background: #f4f6f9; margin: 0; }
header { background: #007bff; color: white; padding: 15px; text-align: center; font-size: 22px; }

.topo { position: absolute; top: 15px; left: 15px; }
.topo a {
    margin-right: 10px;
    color: white;
    text-decoration: none;
    background: #0056b3;
    padding: 8px 12px;
    border-radius: 5px;
}

.container {
    display: flex;
    justify-content: space-around;
    margin-top: 40px;
}

.box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    width: 45%; 
    box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
}

label { display: block; margin-top: 10px; font-weight: bold; color: #333; }

input {
    width: 95%;
    padding: 10px;
    margin: 5px 0 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    background: #28a745;
    color: white;
    padding: 12px;
    border: none;
    width: 100%;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

button:hover { background: #218838; }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

th {
    background: #007bff;
    color: white;
}

tr:nth-child(even) { background-color: #f2f2f2; }
</style>
</head>

<body>

<header>ERP Papelaria</header>

<div class="topo">
    <a href="retirada.php"> Retirar</a>
    <a href="historico_produtos.php"> Histórico</a>
</div>

<div class="container">

<div class="box">
<h2>Cadastro de Produtos</h2>

<form action="salvar.php" method="POST">
    <label>Nome do Produto</label>
    <input type="text" name="nome" placeholder="Ex: Caderno 10 Matérias" required>
    
    <label>Categoria</label>
    <input type="text" name="categoria" placeholder="Ex: Escrita, Papéis" required>
    
    <label>Preço de Custo (R$)</label>
    <input type="number" name="preco_custo" step="0.01" placeholder="0.00" required>

    <label>Quantidade em Estoque</label>
    <input type="number" name="quantidade" placeholder="Qtd inicial" required>

    <button type="submit">Confirmar Cadastro</button>
</form>
</div>

<div class="box">
<h2>Lista de Produtos</h2>

<table>
<tr>
<th>ID</th>
<th>Nome</th>
<th>Preço Custo</th>
<th>Estoque</th>
</tr>

<?php
// Consulta buscando todos os produtos
$result = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY id DESC");

while ($linha = mysqli_fetch_assoc($result)) {
    // Formata o preço para o padrão brasileiro
    $preco_formatado = "R$ " . number_format($linha['preco_custo'], 2, ',', '.');
    
    echo "<tr>
    <td>{$linha['id']}</td>
    <td>{$linha['nome']}</td>
    <td>{$preco_formatado}</td>
    <td>{$linha['estoque_atual']}</td>
    </tr>";
}
?>

</table>
</div>

</div>
</body>
</html>