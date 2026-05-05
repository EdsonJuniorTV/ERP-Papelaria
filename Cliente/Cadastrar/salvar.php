<?php

require "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);

    // validação
    if (empty($nome) || empty($email)) {
        header("Location: index.php?msg=Preencha os campos obrigatórios&tipo=erro");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?msg=Email inválido&tipo=erro");
        exit;
    }

    // prepared statement
    $stmt = $conn->prepare("INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)");

    if ($stmt === false) {
        header("Location: index.php?msg=Erro na preparação&tipo=erro");
        exit;
    }

    $stmt->bind_param("sss", $nome, $email, $telefone);

    if ($stmt->execute()) {
        header("Location: index.php?msg=Cliente cadastrado com sucesso!&tipo=sucesso");
    } else {
        header("Location: index.php?msg=Erro ao cadastrar&tipo=erro");
    }

    $stmt->close();
    $conn->close();
}
?>