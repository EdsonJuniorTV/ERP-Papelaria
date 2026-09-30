<?php
    function cadastrarFuncionario($data, $conn) {
        try {
            $conn->begin_transaction();

            // 1. Salva o Endereço
            $sqlEnd = "INSERT INTO endereco (logradouro, cidade, estado, cep) VALUES (?, ?, ?, ?)";
            $stmtEnd = $conn->prepare($sqlEnd);
            
            $stmtEnd->bind_param("ssss", 
                $data['logradouro'],  
                $data['cidade'], 
                $data['estado'], 
                $data['cep']
            );
            $stmtEnd->execute();
            $id_end = $stmtEnd->insert_id;

            // 2. Salva o Funcionário
            $sqlFunc = "INSERT INTO funcionario (id_cargo, id_end, cpf, nome, login, senha, dt_nasc, dt_admissao, status, fone, email) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmtFunc = $conn->prepare($sqlFunc);
            $status = 'Ativo';
            
            // CRIPTOGRAFA A SENHA AQUI:
            $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);

            $stmtFunc->bind_param("iisssssssss", 
                $data['id_cargo'], 
                $id_end, 
                $data['cpf'], 
                $data['nome'], 
                $data['login'], 
                $senha_hash, // Passa o hash em vez da senha pura
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

    /**
     * Consulta todos os funcionários trazendo o nome do cargo e dados do endereço
     */
    function listarFuncionarios($conn, $idExcluirLogado = null) {
        $sql = "
            SELECT f.*, c.nome AS cargo, 
                e.logradouro, e.cidade, e.estado
            FROM funcionario f
            JOIN cargo c ON f.id_cargo = c.id
            LEFT JOIN endereco e ON f.id_end = e.id
        ";
        
        if ($idExcluirLogado !== null) {
            $sql .= " WHERE f.id <> " . (int)$idExcluirLogado;
        }
        
        $sql .= " ORDER BY f.nome ASC";

        $res = $conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Busca um único funcionário por ID incluindo seus dados de endereço
     */
    function buscarFuncionarioPorId($id, $conn) {
        $sql = "
            SELECT f.*, c.nome AS cargo, 
                e.logradouro, e.cidade, e.estado
            FROM funcionario f
            JOIN cargo c ON f.id_cargo = c.id
            LEFT JOIN endereco e ON f.id_end = e.id
            WHERE f.id = ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function editarFuncionario($data, $conn) {
        try {
            $conn->begin_transaction();

            // 1. Atualiza dados principais
            $sqlFunc = "UPDATE funcionario SET id_cargo = ?, nome = ?, login = ?, fone = ?, email = ? WHERE id = ?";
            $stmtFunc = $conn->prepare($sqlFunc);
            $stmtFunc->bind_param("issssi", $data['id_cargo'], $data['nome'], $data['login'], $data['fone'], $data['email'], $data['id']);
            $stmtFunc->execute();

            // Atualização opcional de senha (Só atualiza se o usuário digitou algo novo)
            if (!empty($data['senha'])) {
                // CRIPTOGRAFA A NOVA SENHA AQUI:
                $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
                $sqlSenha = "UPDATE funcionario SET senha = ? WHERE id = ?";
                $stmtSenha = $conn->prepare($sqlSenha);
                $stmtSenha->bind_param("si", $senha_hash, $data['id']);
                $stmtSenha->execute();
            }

            // 2. Atualiza endereço associado
            $funcAtual = buscarFuncionarioPorId($data['id'], $conn);
            if ($funcAtual && !empty($funcAtual['id_end'])) {
                $sqlEnd = "UPDATE endereco SET logradouro = ?, cidade = ?, estado = ?, cep = ? WHERE id = ?";
                $stmtEnd = $conn->prepare($sqlEnd);
                
                $stmtEnd->bind_param("ssssi", 
                    $data['logradouro'], 
                    $data['cidade'], 
                    $data['estado'], 
                    $data['cep'], 
                    $funcAtual['id_end']
                );
                $stmtEnd->execute();
            }

            $conn->commit();
            return ["status" => true, "mensagem" => "Funcionário atualizado com sucesso!"];
        } catch (Exception $e) {
            $conn->rollback();
            return ["status" => false, "mensagem" => "Erro ao editar: " . $e->getMessage()];
        }
    }

    function excluirFuncionario($id, $conn) {
        try {
            $sql = "UPDATE funcionario SET status = 'Inativo' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return ["status" => true, "mensagem" => "Funcionário desativado com sucesso!"];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            return ["status" => false, "mensagem" => "Erro ao desativar: " . $e->getMessage()];
        }
    }

    function reativarFuncionario($id, $conn) {
        try {
            $sql = "UPDATE funcionario SET status = 'Ativo' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return ["status" => true, "mensagem" => "Funcionário reativado com sucesso!"];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            return ["status" => false, "mensagem" => "Erro ao reativar: " . $e->getMessage()];
        }
    }

    function removerFuncionario($id, $conn) {
        try {
            $conn->begin_transaction();

            $stmtHist = $conn->prepare("DELETE FROM historico_funcionario WHERE id_func = ?");
            $stmtHist->bind_param("i", $id);
            $stmtHist->execute();
            $stmtHist->close();

            $stmtFunc = $conn->prepare("DELETE FROM funcionario WHERE id = ?");
            $stmtFunc->bind_param("i", $id);
            $stmtFunc->execute();
            $stmtFunc->close();

            $conn->commit();
            return ["status" => true, "mensagem" => "Funcionário removido com sucesso!"];

        } catch (Exception $e) {
            $conn->rollback();
            return ["status" => false, "mensagem" => "Erro ao remover: " . $e->getMessage()];
        }
    }
?>