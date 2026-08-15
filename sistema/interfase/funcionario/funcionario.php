<?php
// Nota: A verificação de permissão deve ser feita na página (view), 
// mas a lógica de banco fica aqui.

function cadastrarFuncionario($data, $conn) {
    try {
        $conn->begin_transaction();

        // 1. Salva o Endereço
        $sqlEnd = "INSERT INTO endereco (logradouro, cidade, estado, cep) VALUES (?, ?, ?, ?)";
        $stmtEnd = $conn->prepare($sqlEnd);
        $stmtEnd->bind_param("ssss", $data['logradouro'], $data['cidade'], $data['estado'], $data['cep']);
        $stmtEnd->execute();
        $id_end = $stmtEnd->insert_id;

        // 2. Salva o Funcionário (incluindo cargo e senha)
        // Dica: Em produção, use password_hash() para a senha.
        $sqlFunc = "INSERT INTO funcionario (id_cargo, id_end, cpf, nome, login, senha, dt_nasc, dt_admissao, status, fone, email) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmtFunc = $conn->prepare($sqlFunc);
        $status = 'Ativo';
        $stmtFunc->bind_param("iisssssssss", 
            $data['id_cargo'], 
            $id_end, 
            $data['cpf'], 
            $data['nome'], 
            $data['login'], 
            $data['senha'], 
            $data['dt_nasc'], 
            $data['dt_admissao'], 
            $status, 
            $data['fone'], 
            $data['email']
        );

        if ($stmtFunc->execute()) {
            $conn->commit();
            return ["status" => true, "mensagem" => "Funcionário cadastrado com sucesso!"];
        } else {
            throw new Exception($stmtFunc->error);
        }

    } catch (Exception $e) {
        $conn->rollback();
        return ["status" => false, "mensagem" => "Erro ao salvar funcionário: " . $e->getMessage()];
    }
}

function listarFuncionarios($conn) {
    $res = $conn->query("SELECT f.*, c.nome as cargo FROM funcionario f JOIN cargo c ON f.id_cargo = c.id");
    return $res->fetch_all(MYSQLI_ASSOC);
}