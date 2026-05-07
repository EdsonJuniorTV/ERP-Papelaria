<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Função para validar se o cargo tem permissão
function verificarPermissao($cargos_permitidos) {
    if (!in_array($_SESSION['user_cargo'], $cargos_permitidos)) {
        echo "<script>alert('Acesso negado!'); window.location.href='dashboard.php';</script>";
        exit;
    }
}
?>