<?php
$msg = "";
$tipo = "";

if (isset($_GET["msg"])) {
    $msg = htmlspecialchars($_GET["msg"]);
    $tipo = htmlspecialchars($_GET["tipo"]);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Clientes</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<div class="container">
    <h2>Cadastrar Cliente</h2>

    <?php if ($msg): ?>
        <div class="msg <?php echo $tipo; ?>">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form action="salvar.php" method="POST">
        
        <label>Nome:</label>
        <input type="text" name="nome" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Telefone:</label>
        <input type="text" name="telefone">

        <button type="submit">Cadastrar</button>

    </form>
</div>

</body>
</html>