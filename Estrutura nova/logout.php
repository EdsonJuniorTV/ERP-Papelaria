<?php
// 1. Inicia a sessão para ter acesso aos dados atuais
session_start();

// 2. Limpa todas as variáveis de sessão (ID, Nome, Cargo)
$_SESSION = array();

// 3. Se desejar matar a sessão completamente, apague também o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destrói a sessão no servidor
session_destroy();

// 5. Redireciona o utilizador de volta para a página inicial (Landing Page/Login)
header("Location: index.php");
exit;
?>