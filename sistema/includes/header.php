<?php
// O __DIR__ garante que ele sempre ache a pasta config, não importa de onde o header seja chamado
require_once __DIR__ . '/../config/conexao.php';

// Inicia a sessão se necessário para pegar os dados do usuário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conta quantos produtos estão com estoque igual ou abaixo do mínimo
$sql_alerta = "SELECT COUNT(*) as total FROM estoque WHERE qtd <= qtd_minima";
$res_alerta = mysqli_query($conexao, $sql_alerta);
$dados_alerta = mysqli_fetch_assoc($res_alerta);
$total_alertas = $dados_alerta['total'] ?? 0;

// Pega o nome do usuário logado (ou mostra 'Usuário' por precaução)
$nome_usuario = $_SESSION['user_nome'] ?? 'Usuário';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/ERP-Papelaria/sistema/public/css/css.css">
    <title>Papelaria - Gestão</title>
</head>
<body>
    <header class="top-header">
        <div class="main-header">
            <div class="logo">
                <h2><span>✏️</span> Papelaria Central</h2>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="dashboard.php">📊 Dashboard</a></li>
                    <li><a href="caixa.php">🛒 Caixa</a></li>
                    <li><a href="configuracoes.php">⚙ Configurações</a></li>
                    <li>
                        <a href="estoque.php">
                            📦 Estoque 
                            <?php if($total_alertas > 0): ?>
                                <span style="background: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 10px; font-weight: bold; margin-left: 5px;">
                                    <?= $total_alertas ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="cadastro.php">👥 Clientes</a></li>
                    <li><a href="fornecedores_gestao.php">🚚 Fornecedores</a></li>
                    <li><a href="funcionarios_gestao.php">👔 RH</a></li>
                </ul>
            </nav>
            <div class="user-info">
                <span class="user-name">👋 <?= htmlspecialchars($nome_usuario) ?></span>
                <button class="logout-btn" onclick="location.href='logout.php'">🚪 Sair</button>
            </div>
        </div>
    </header>
</body>
</html>