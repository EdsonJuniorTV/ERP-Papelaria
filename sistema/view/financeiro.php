<?php
require_once 'includes/auth.php';
require_once 'config/conexao.php';
verificarPermissao(['Gerente', 'Programador']);
include 'includes/header.php';

$sql = "SELECT 
            SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE 0 END) as total_entradas,
            SUM(CASE WHEN tipo = 'Saida' THEN valor ELSE 0 END) as total_saidas
        FROM movimentacao_financeira 
        WHERE MONTH(dt_mov) = MONTH(CURRENT_DATE())";

$res = mysqli_query($conexao, $sql);
$dados = mysqli_fetch_assoc($res);
$lucro = $dados['total_entradas'] - $dados['total_saidas'];
?>

<main class="container">
    <div class="header" style="background: #27ae60;">
        <h1>💰 Painel Financeiro</h1>
        <p>Resumo de entradas e saídas do mês atual</p>
    </div>

    <div class="grid-cards" style="margin-top: 20px;">
        <div class="card" style="border-left: 5px solid green;">
            <h4>Entradas (Vendas)</h4>
            <p style="font-size: 24px; color: #000000">R$ <?= number_format($dados['total_entradas'], 2, ',', '.') ?></p>
        </div>
        <div class="card" style="border-left: 5px solid red;">
            <h4>Saídas (Custos/Comissões)</h4>
            <p style="font-size: 24px; color: #000000">R$ <?= number_format($dados['total_saidas'], 2, ',', '.') ?></p>
        </div>
        <div class="card" style="background: #dad8d8;">
            <h4>Saldo Líquido</h4>
            <p style="font-size: 24px; font-weight: bold; color: #000000">R$ <?= number_format($lucro, 2, ',', '.') ?></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>