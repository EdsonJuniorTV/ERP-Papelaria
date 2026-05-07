<?php
// Garante que a sessão inicie apenas se já não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a tela de entrada caso não esteja logado
    header("Location: index.html");
    exit;
}

// Função para validar se o cargo tem permissão para acessar a página
function verificarPermissao($cargos_permitidos) {
    // Se o cargo do usuário não estiver na lista de permitidos
    if (!isset($_SESSION['user_cargo']) || !in_array($_SESSION['user_cargo'], $cargos_permitidos)) {
        // Redirecionamento limpo via servidor
        header("Location: dashboard.php?erro=acesso_negado");
        exit;
    }
}
?>