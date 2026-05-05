<?php
include("conexao.php");

$nome = $_POST['nome'];
$produto = $_POST['produto'];
$qtd = $_POST['quantidade'];

// buscar produto
$sql = "SELECT id, estoque_atual FROM produtos WHERE nome='$produto'";
$result = mysqli_query($conexao, $sql);
$dado = mysqli_fetch_assoc($result);

$id_produto = $dado['id'];
$estoque = $dado['estoque_atual'];

if ($qtd > $estoque) {

    echo "<h2 style='color:red; text-align:center; margin-top:50px;'>
    $nome, o estoque é de $estoque unidades de $produto e você pediu $qtd.
    </h2>
    <div style='text-align:center;'>
    <a href='retirada.php'>Voltar</a>
    </div>";

} else {

    $novo = $estoque - $qtd;

    // atualiza produtos
    mysqli_query($conexao, "UPDATE produtos 
    SET estoque_atual = $novo 
    WHERE id = $id_produto");

    // atualiza estoque
    mysqli_query($conexao, "UPDATE estoque 
    SET qtd_produto = $novo 
    WHERE produto_id = $id_produto");

    // salva histórico (se quiser manter)
    mysqli_query($conexao, "INSERT INTO historico (nome_pessoa, produto, quantidade)
    VALUES ('$nome', '$produto', $qtd)");

    echo "<h2 style='color:green; text-align:center; margin-top:50px;'>
    Retirada realizada com sucesso!
    </h2>
    <div style='text-align:center;'>
    <a href='index.php'>Voltar</a>
    </div>";
}
?>