<?php include("conexao.php"); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Retirada de Produtos</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            text-align: center;
        }

        .container {
            background: white;
            width: 350px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
        }

        input, select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
        }

        button {
            background: green;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
        }

        a {
            position: absolute;
            top: 10px;
            left: 10px;
        }
    </style>
</head>

<body>

<a href="index.php">⬅ Voltar</a>

<div class="container">
    <h2>Retirar Produto</h2>

    <form action="nova_compra.php" method="POST">

        <input type="text" name="nome" placeholder="Seu nome" required>

        <select name="produto">
            <?php
            $sql = "SELECT nome FROM produtos";
            $result = mysqli_query($conexao, $sql);

            while ($linha = mysqli_fetch_assoc($result)) {
                echo "<option value='".$linha['nome']."'>".$linha['nome']."</option>";
            }
            ?>
        </select>

        <input type="number" name="quantidade" placeholder="Quantidade" required>

        <button type="submit">Retirar</button>

    </form>
</div>

</body>
</html>