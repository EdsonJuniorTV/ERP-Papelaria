<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papelaria Central - Bem-vindo</title>
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="public/css/cadastrar.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }
        .login-section {
            background: #f4f7f6;
            padding: 60px 20px;
        }
        .card-noticia {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <header class="hero">
        <h1>✏️ Papelaria Central</h1>
        <p>Sistema Interno de Gestão de Vendas e Estoque</p>
    </header>

    <main class="container" style="max-width: 1000px; display: grid; grid-template-columns: 1fr 400px; gap: 40px; margin-top: -40px;">
        
        <section>
            <div class="card-noticia">
                <h3>📢 Comunicado Interno</h3>
                <p>O inventário geral será realizado no próximo sábado. Certifiquem-se de atualizar todas as entradas de nota fiscal até sexta-feira.</p>
            </div>
            <div class="card-noticia">
                <h3>🚀 Atualização de Segurança</h3>
                <p>A partir de agora, o sistema utiliza sessões protegidas por cargo. Caso tenha problemas no acesso, procure o administrador.</p>
            </div>
        </section>

        <section id="login" class="card" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <h2 style="text-align: center; margin-bottom: 20px;">Acesso Restrito</h2>
            
            <form action="processa_login.php" method="POST">
                <div class="form-group">
                    <label>Usuário / Login</label>
                    <input type="text" name="login" required placeholder="Digite seu usuário" style="width: 100%; padding: 12px; margin-top: 5px;">
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label>Senha</label>
                    <input type="password" name="senha" required placeholder="********" style="width: 100%; padding: 12px; margin-top: 5px;">
                </div>

                <button type="submit" class="btn-submit" style="width: 100%; margin-top: 25px; background: #27ae60;">
                    🔓 Entrar no Sistema
                </button>
            </form>

            <script>
                // Verifica erro de login via URL
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('erro')) {
                    alert('Usuário ou senha incorretos, ou conta inativa.');
                }
            </script>
        </section>

    </main>

    <?php include 'includes/footer.php'; ?>