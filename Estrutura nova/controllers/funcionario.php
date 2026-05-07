<?php 
require_once 'includes/auth.php';
// Apenas estes cargos entram aqui
verificarPermissao(['Gerente', 'Dono', 'Programador']);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="public/css/cadastrar.css">
    <title>Gestão de Funcionários</title>
</head>
<body>
    <h1>Gerenciamento de Equipe</h1>
    <script src="public/scripts/cadastrar.js"></script>
</body>
</html>