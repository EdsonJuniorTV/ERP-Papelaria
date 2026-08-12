<?php
function cadastrarProduto($dados, $conn) {
    try {
        // 1. Iniciar uma Transação (Garante que se o estoque falhar, o produto não seja salvo sozinho)
        $conn->begin_transaction();

        // 2. Preparar os dados
        $nome       = mysqli_real_escape_string($conn, $dados['nome']);
        $id_forn    = (int)$dados['id_forn'];
        $id_cat     = (int)$dados['id_cat'];
        $id_marca   = (int)$dados['id_marca'];
        $custo      = (float)$dados['custo'];
        $preco      = (float)$dados['preco'];
        $qtd_minima = isset($dados['qtd_minima']) ? (int)$dados['qtd_minima'] : 5;

        // 3. Inserir na tabela Produto
        $sqlProd = "INSERT INTO produto (id_forn, id_cat, id_marca, nome, preco, custo) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmtProd = $conn->prepare($sqlProd);
        $stmtProd->bind_param("iiisdd", $id_forn, $id_cat, $id_marca, $nome, $preco, $custo);
        
        if (!$stmtProd->execute()) {
            throw new Exception("Erro ao inserir produto: " . $stmtProd->error);
        }

        // 4. Pegar o ID do produto que acabou de ser criado
        $id_novo_produto = $conn->insert_id;

        // 5. Criar o registro na tabela Estoque com quantidade ZERO
        $sqlEstoque = "INSERT INTO estoque (id_prod, qtd, qtd_minima) 
        VALUES (?, 0, ?) on duplicate key update qtd_minima = values(qtd_minima)";

        $stmtEstoque = $conn->prepare($sqlEstoque);
        $stmtEstoque->bind_param("ii", $id_novo_produto, $qtd_minima);

        if (!$stmtEstoque->execute()) {
            throw new Exception("Erro ao inicializar estoque.");
        }

        // 6. Confirmar tudo no banco
        $conn->commit();

        return [
            "status" => true, 
            "mensagem" => "Produto '$nome' cadastrado com sucesso! Agora você pode dar entrada no estoque."
        ];

    } catch (Exception $e) {
        // Se algo deu errado, desfaz as alterações
        $conn->rollback();
        return ["status" => false, "mensagem" => $e->getMessage()];
    }
}
?>