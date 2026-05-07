<?php
session_start();
require_once 'config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = mysqli_real_escape_string($conexao, $_POST['login']);
    $senha = $_POST['senha']; // No futuro, use password_hash

    $sql = "SELECT f.*, c.nome as cargo_nome FROM funcionario f 
            JOIN cargo c ON f.id_cargo = c.id 
            WHERE f.login = '$login' AND f.senha = '$senha' AND f.status = 'Ativo'";
    
    $res = mysqli_query($conexao, $sql);
    $user = mysqli_fetch_assoc($res);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_cargo'] = $user['cargo_nome'];
        
        header("Location: dashboard.php");
    } else {
        header("Location: index.php?erro=1#login");
    }
}
?>