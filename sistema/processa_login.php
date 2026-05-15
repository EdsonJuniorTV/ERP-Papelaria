<?php
session_start();
require_once 'config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitização para evitar SQL Injection
    $login = mysqli_real_escape_string($conexao, $_POST['login']);
    $senha = $_POST['senha']; 

    // Busca o funcionário e o nome do cargo (essencial para o auth.php)
    $sql = "SELECT f.*, c.nome as cargo_nome 
            FROM funcionario f 
            JOIN cargo c ON f.id_cargo = c.id 
            WHERE f.login = '$login' AND f.senha = '$senha' AND f.status = 'Ativo'";
    
    $res = mysqli_query($conexao, $sql);
    $user = mysqli_fetch_assoc($res);

    if ($user) {
        // Grava os dados na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_cargo'] = $user['cargo_nome']; // Ex: 'Gerente', 'Vendedor'
        
        // Redireciona para o novo Dashboard unificado
        header("Location: dashboard.php");
        exit;
    } else {
        // Se errar, volta para o index com um alerta de erro
        header("Location: index.php?erro=1#login");
        exit;
    }
}
?>