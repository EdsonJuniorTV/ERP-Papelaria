<?php
include("conexao.php");

// Recebe os dados do formulário
$nome = $_POST['nome'];
$categoria = $_POST['categoria'];
$preco_custo = $_POST['preco_custo']; // Novo campo
$qtd = $_POST['quantidade'];

// 1. Insere o produto na tabela 'produtos' (incluindo o preço de custo)
// Nota: Usamos aspas simples para valores de texto e deixamos sem para números
$sql_produto = "INSERT INTO produtos (nome, categoria, preco_custo, estoque_atual) 
                VALUES ('$nome', '$categoria', '$preco_custo', '$qtd')";

mysqli_query($conexao, $sql_produto);

// 2. Pega o ID que o banco de dados gerou automaticamente para este novo produto
$id_gerado = mysqli_insert_id($conexao);

// 3. Insere na tabela 'estoque' para manter a consistência do sistema
$sql_estoque = "INSERT INTO estoque (produto_id, qtd_produto) 
                 VALUES ('$id_gerado', '$qtd')";

mysqli_query($conexao, $sql_estoque);

// 4. (Opcional mas recomendado) Se você tiver a tabela 'historico', 
// é bom registrar a entrada inicial lá também:
mysqli_query($conexao, "INSERT INTO historico (nome_pessoa, produto, quantidade) 
                        VALUES ('Sistema', '$nome', '$qtd')");

// Redireciona de volta para a página principal
header("Location: index.php");
exit;
?>