<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papelaria Central</title>
    <link rel="stylesheet" href="public/css/css.css">
    <style>
        body { background: var(--bg); }

        .login-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #1a56db 100%);
            padding: 60px 24px;
            text-align: center;
        }
        .hero h1 {
            color: #fff;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .hero p {
            color: rgba(255,255,255,.65);
            margin-top: 8px;
            font-size: .95rem;
        }

        .login-body {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 32px;
            max-width: 1000px;
            width: 100%;
            margin: 40px auto;
            padding: 0 24px 40px;
        }

        .noticias .card-noticia {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 20px 22px;
            box-shadow: var(--shadow-sm);
            border-left: 3px solid var(--brand);
            margin-bottom: 16px;
        }
        .noticias .card-noticia h3 {
            font-size: .95rem;
            margin-bottom: 6px;
        }
        .noticias .card-noticia p {
            font-size: .85rem;
            color: var(--text-muted);
        }

        .login-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 36px 32px;
            box-shadow: var(--shadow-lg);
            height: fit-content;
        }
        .login-card h2 {
            text-align: center;
            margin-bottom: 28px;
            font-size: 1.3rem;
        }
        .login-card .form-group {
            margin-bottom: 18px;
        }
        .login-card .form-group label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text);
            display: block;
            margin-bottom: 6px;
        }
        .login-card .form-group input {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-size: .9rem;
            font-family: var(--font-sans);
            transition: border-color .15s, box-shadow .15s;
        }
        .login-card .form-group input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(26,86,219,.12);
        }
        .login-card .btn-submit {
            width: 100%;
            margin-top: 8px;
            padding: 12px;
            font-size: .95rem;
            justify-content: center;
        }
        .login-card .hint {
            text-align: center;
            margin-top: 16px;
            font-size: .78rem;
            color: var(--text-light);
        }

        @media (max-width: 680px) {
            .login-body { grid-template-columns: 1fr; }
            .noticias { order: 2; }
            .login-card { order: 1; }
        }
    </style>
</head>
<body>
<div class="login-page">

    <header class="hero">
        <h1>✏️ Papelaria Central</h1>
        <p>Sistema Interno de Gestão de Vendas e Estoque</p>
    </header>

    <div class="login-body">

        <section class="noticias">
            <div class="card-noticia">
                <h3>📢 Comunicado Interno</h3>
                <p>O inventário geral será realizado no próximo sábado. Certifiquem-se de atualizar todas as entradas de nota fiscal até sexta-feira.</p>
            </div>
            <div class="card-noticia">
                <h3>🚀 Atualização de Segurança</h3>
                <p>A partir de agora, o sistema utiliza sessões protegidas por cargo. Caso tenha problemas no acesso, procure o administrador.</p>
            </div>
        </section>

        <section class="login-card">
            <h2>Acesso Restrito</h2>
            <form action="processa_login.php" method="POST">
                <div class="form-group">
                    <label>Usuário / Login</label>
                    <input type="text" name="login" required placeholder="Digite seu usuário">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-submit">🔓 Entrar no Sistema</button>
            </form>
            <p class="hint">Para acesso, contate o administrador do sistema</p>
        </section>

    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('erro')) {
        alert('Usuário ou senha incorretos, ou conta inativa.');
    }
</script>
</body>
</html>