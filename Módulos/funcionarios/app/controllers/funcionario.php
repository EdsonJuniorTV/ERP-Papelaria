<?php
    function cadastrar($data, $conn) {

        if (empty($data)) throw new Exception("Preencha o formulário!");

        $campos = [];
        $valores = [];

        foreach($data as $campo => $valor) {

            if (!isset($valor) || trim($valor) === '') throw new Exception("O campo $campo é obrigatório!");

            $campos[] = $campo;
            $valores[] = $valor;
        }

        $params = implode(", ", $campos);
        $placeholders = rtrim(str_repeat("?, ", count($campos)), ", ");

        $sql = "INSERT INTO funcionarios ($params) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute($valores)) throw new Exception(($stmt->errorInfo())[2], 500);

        return [
            "mensagem" => "Cadastro realizado com  sucesso!",
            "entidade" => [
                "id" => $conn->lastInsertId(),
                "nome" => $data['nome']
            ]
        ];
    }

    function listar($conn) {

        $sql = "SELECT id, nome FROM funcionarios";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute()) {
            throw new Exception(($stmt->errorInfo())[2], 500);
        }

        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "mensagem" => "Listagem concluída!",
            "entidade" => $registros
        ];
    }

    function listarPorId(int $id, $conn) {

        $sql = "SELECT * FROM funcionarios WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute([$id])) {
            throw new Exception(($stmt->errorInfo())[2], 500);
        }

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            throw new Exception("Funcionário não encontrado!", 404);
        }

        return [
            "mensagem" => "Busca concluída!",
            "entidade" => $registro
        ];
    }

    function alterar($data, $conn) {

        if (empty($data)) {
            throw new Exception("Formulário vazio!", 400);
        }

        if (empty($data['id'])) {
            throw new Exception("Id inválido!", 400);
        }

        $id = (int) $data['id'];

        $values = [];
        $campos = [];

        foreach($data as $campo => $valor) {
            if ($campo !== 'id') {
                $campos[] = "$campo = ?";
                $values[] = $valor;
            }
        }

        if (empty($campos)) {
            throw new Exception("Nenhm campo foi informado!");
        }

        $values[] = $id;

        $params = implode(", ", $campos);

        $sql = "UPDATE funcionarios SET $params WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute($values)) {
            throw new Exception(($stmt->errorInfo())[2], 500);
        }

        $linhasAfetadas = $stmt->rowCount();

        return [
            "mensagem" => $linhasAfetadas > 0 
                ? "Registro atualizado com sucesso!"
                : "Nenhum  registro atualizado!",
            "entidade" => [
                "id" => $id,
                "linhas_afetadas" => $linhasAfetadas
            ]
        ];
    }

    function excluir(int $data, $conn) {

        if ($data <= 0) {
            throw new Exception("Id inválido para exclusão!", 400);
        }

        $id = (int) $data;

        $sql = "DELETE FROM funcionarios WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute([$id])) {
            throw new Exception(($stmt->errorInfo())[2], 500);
        }

        $linhasAfetadas = $stmt->rowCount();

        if ($linhasAfetadas === 0) {
            throw new Exception("Funcionário não encontrado para exclusão!", 404);
        }

        return [
            "mensagem" => "Registro deletado com sucesso!",
            "entidade" => [
                "id" => $id,
                "linhas_afetadas" => $linhasAfetadas
            ]
        ];
    }
?>