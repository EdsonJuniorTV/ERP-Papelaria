<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Papelaria Jardim Europa - Bem-vindo</title>
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="public/css/cadastrar.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }
        .novidades-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card-noticia {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .login-section {
            background: #f4f4f4;
            padding: 50px 20px;
            text-align: center;
        }
        .btn-login-destaque {
            background: #764ba2;
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(118, 75, 162, 0.4);
            transition: 0.3s;
        }
        .btn-login-destaque:hover {
            transform: translateY(-3px);
            background: #667eea;
        }
    </style>
</head>
<body>

    <header class="top-header">
        <div class="main-header">
            <div class="logo"><h2>Papelaria Jardim Europa</h2></div>
            <a href="#login" class="btn-submit" style="width: auto; padding: 10px 25px;">Acesso Restrito</a>
        </div>
    </header>

    <section class="hero">
        <h1>Transforme as suas ideias em papel</h1>
        <p>As melhores marcas e novidades do mundo escolar e de escritório.</p>
    </section>

    <main>
        <div class="header" style="background: none; color: #333; margin-top: 30px;">
            <h2>✨ Novidades da Semana</h2>
        </div>
        
        <div class="novidades-grid">
            <div class="card-noticia">
                <h3>Volta às Aulas 2026</h3>
                <p>Já recebemos a nova coleção de cadernos e estojos da Faber-Castell. Venha conferir!</p>
            </div>
            <div class="card-noticia">
                <h3>Novas Canetas Pastel</h3>
                <p>As queridinhas chegaram! Reposição de marcadores Stabilo em tons pastéis.</p>
            </div>
            <div class="card-noticia">
                <h3>Setor de Presentes</h3>
                <p>Agora temos uma linha exclusiva de agendas e planners personalizados.</p>
            </div>
        </div>
    </main>

    <section id="login" class="login-section">
        <div class="container" style="max-width: 400px;">
            <div class="header">
                <h1>Login do Funcionário</h1>
            </div>
            <form action="processa_login.php" method="POST">
                <div class="form-group">
                    <label>Usuário</label>
                    <input type="text" name="login" required placeholder="Digite seu login">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" required placeholder="********">
                </div>
                <button type="submit" class="btn-submit">Entrar no Sistema</button>
            </form>
        </div>
    </section>

</body>
</html>