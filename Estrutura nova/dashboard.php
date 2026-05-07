<?php 
require_once 'includes/auth.php'; 
$cargo = $_SESSION['user_cargo'];
$admin = ['Gerente', 'Dono', 'Programador'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="public/css/cadastrar.css">
    <title>Papelaria - Home</title>
</head>
<body>
    <header class="top-header">
        <div class="main-header">
            <h2>Olá, <?php echo $_SESSION['user_nome']; ?></h2>
            <nav class="nav-menu">
                <ul>
                    <li><a href="caixa.php">🛒 Caixa</a></li>
                    <li><a href="clientes.php">👥 Clientes</a></li>
                    <li><a href="estoque.php">📦 Estoque</a></li>
                    <?php if(in_array($cargo, $admin)): ?>
                        <li><a href="funcionarios.php">👔 Funcionários</a></li>
                        <li><a href="financeiro.php">💰 Financeiro</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">🚪 Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="wrap">
        <h1>Novidades da Empresa</h1>
        <div class="container">
            <div class="header">
                <p>Confira os novos itens de papelaria escolar que chegaram esta semana!</p>
            </div>
            </div>
    </main>
</body>
</html>