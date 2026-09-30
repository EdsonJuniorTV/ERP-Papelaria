<?php
session_start();
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitização para evitar SQL Injection no login
    $login = mysqli_real_escape_string($conexao, $_POST['login']);
    $senha = $_POST['senha']; 

    // Busca o funcionário APENAS pelo login (Removemos a senha do SQL)
    $sql = "SELECT f.*, c.nome as cargo_nome 
            FROM funcionario f 
            JOIN cargo c ON f.id_cargo = c.id 
            WHERE f.login = '$login' AND f.status = 'Ativo'";
    
    $res = mysqli_query($conexao, $sql);
    $user = mysqli_fetch_assoc($res);

    // password_verify compara o que foi digitado com o hash salvo no banco
    if ($user && password_verify($senha, $user['senha'])) {
        // Grava os dados na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_cargo'] = $user['cargo_nome'];
        
        // Redireciona para o novo Dashboard unificado
        header("Location: dashboard.php");
        exit;
    } else {
        // Se errar usuário ou senha, volta para o index
        header("Location: index.php?erro=1#login");
        exit;
    }
}
?>