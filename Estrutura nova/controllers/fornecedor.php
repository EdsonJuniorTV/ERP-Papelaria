<?php
/**
 * Lógica de Gestão de Fornecedores
 */

function cadastrarFornecedor($data, $conn) {
    try {
        $conn->begin_transaction();

        // 1. Salva o endereço do fornecedor
        $sqlEnd = "INSERT INTO endereco (logradouro, cidade, estado, cep) VALUES (?, ?, ?, ?)";
        $stmtEnd = $conn->prepare($sqlEnd);
        $stmtEnd->bind_param("ssss", $data['logradouro'], $data['cidade'], $data['estado'], $data['cep']);
        $stmtEnd->execute();
        $id_end = $conn->insert_id;

        // 2. Salva o fornecedor vinculado ao endereço
        $sqlForn = "INSERT INTO fornecedor (nome, cnpj, fone, email, id_end) VALUES (?, ?, ?, ?, ?)";
        $stmtForn = $conn->prepare($sqlForn);
        $stmtForn->bind_param("ssssi", $data['nome'], $data['cnpj'], $data['fone'], $data['email'], $id_end);
        
        if ($stmtForn->execute()) {
            $conn->commit();
            return ["status" => true, "mensagem" => "Fornecedor cadastrado com sucesso!"];
        } else {
            throw new Exception($stmtForn->error);
        }

    } catch (Exception $e) {
        $conn->rollback();
        return ["status" => false, "mensagem" => "Erro ao salvar fornecedor: " . $e->getMessage()];
    }
}

function listarFornecedores($conn) {
    $sql = "SELECT f.*, e.logradouro, e.cidade, e.estado 
            FROM fornecedor f 
            JOIN endereco e ON f.id_end = e.id 
            ORDER BY f.nome ASC";
    $res = $conn->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
}
?>