<?php
function cadastrarCliente($data, $conn) {
    try {
        mysqli_begin_transaction($conn);

        // 1. Inserir Endereço com Prepared Statements
        $sqlEnd = "INSERT INTO endereco (logradouro, cidade, estado, cep) VALUES (?, ?, ?, ?)";
        $stmtEnd = $conn->prepare($sqlEnd);
        $stmtEnd->bind_param("ssss", $data['logradouro'], $data['cidade'], $data['estado'], $data['cep']);
        $stmtEnd->execute();
        $id_end = $stmtEnd->insert_id;

        // 2. Inserir Cliente vinculado
        $sqlCli = "INSERT INTO cliente (cpf, id_end, nome, dt_nasc, fone, email) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtCli = $conn->prepare($sqlCli);
        $stmtCli->bind_param("sissss", $data['cpf'], $id_end, $data['nome'], $data['dt_nasc'], $data['fone'], $data['email']);
        
        if ($stmtCli->execute()) {
            mysqli_commit($conn);
            return ["status" => true, "mensagem" => "Cliente cadastrado com sucesso!"];
        } else {
            throw new Exception("Erro ao inserir cliente: " . $stmtCli->error);
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        return ["status" => false, "mensagem" => "Erro no cadastro: " . $e->getMessage()];
    }
}

function listarClientes($conn) {
    $sql = "SELECT c.*, e.logradouro, e.cidade, e.estado, e.cep 
    FROM cliente c LEFT JOIN endereco e ON c.id_end = e.id 
    ORDER BY c.nome ASC";
            
    $res = $conn->query($sql);
    return $res->fetch_all(MYSQLI_ASSOC);
}