<?php
/**
 * Lógica para Tabelas Auxiliares (Cargo, Categoria, Marca)
 */

function cadastrarAuxiliar($tabela, $nome, $conn) {
    try {
        // Lista de tabelas permitidas para evitar SQL Injection via nome da tabela
        $tabelas_permitidas = ['cargo', 'categoria', 'marca'];
        
        if (!in_array($tabela, $tabelas_permitidas)) {
            throw new Exception("Tabela não permitida.");
        }

        // Sanitização e Preparação
        $sql = "INSERT INTO $tabela (nome) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nome);
        
        if ($stmt->execute()) {
            return ["status" => true, "mensagem" => "Item '$nome' cadastrado com sucesso em '$tabela'!"];
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        return ["status" => false, "mensagem" => "Erro ao cadastrar auxiliar: " . $e->getMessage()];
    }
}
?>