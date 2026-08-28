<?php
require_once 'includes/auth.php';
require_once 'config/conexao.php';
verificarPermissao(['Gerente', 'Programador']);
include 'includes/header.php';

// Captura o mês e o ano selecionados nos filtros (ou usa o atual por padrão)
$mes_selecionado = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$ano_selecionado = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');

// 1. Entradas, Saídas e Saldo do Período Selecionado
$sql_resumo = "SELECT 
            SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE 0 END) as total_entradas,
            SUM(CASE WHEN tipo = 'Saida' THEN valor ELSE 0 END) as total_saidas
        FROM movimentacao_financeira 
        WHERE MONTH(dt_mov) = $mes_selecionado 
          AND YEAR(dt_mov) = $ano_selecionado";

$res_resumo = mysqli_query($conexao, $sql_resumo);
$dados = mysqli_fetch_assoc($res_resumo);

$total_entradas = $dados['total_entradas'] ?? 0;
$total_saidas   = $dados['total_saidas'] ?? 0;
$lucro          = $total_entradas - $total_saidas;

// 2. Custo Total Investido em Estoque Atual
$sql_investido = "SELECT SUM(p.custo * e.qtd) as investido FROM produto p JOIN estoque e ON p.id = e.id_prod WHERE e.qtd > 0";
$res_inv = mysqli_query($conexao, $sql_investido);
$total_investido = mysqli_fetch_assoc($res_inv)['investido'] ?? 0;

// 3. Produtos Mais Vendidos no Período
$sql_produtos = "SELECT 
            p.nome AS produto,
            SUM(ip.qtd) AS qtd_vendida,
            SUM(ip.qtd * ip.preco_unitario) AS total_faturado
        FROM item_pedido ip
        JOIN pedido ped ON ip.id_ped = ped.id
        JOIN produto p ON ip.id_prod = p.id
        WHERE ped.status = 'Pago'
          AND MONTH(ped.dt_pedido) = $mes_selecionado
          AND YEAR(ped.dt_pedido) = $ano_selecionado
        GROUP BY p.id
        ORDER BY qtd_vendida DESC
        LIMIT 5";

$res_produtos = mysqli_query($conexao, $sql_produtos);

// 4. Dados para o Gráfico (Movimentação dos últimos 7 dias ou do mês)
$sql_grafico = "SELECT 
            DATE(dt_mov) as data_mov,
            SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE 0 END) as entradas,
            SUM(CASE WHEN tipo = 'Saida' THEN valor ELSE 0 END) as saidas
        FROM movimentacao_financeira
        WHERE MONTH(dt_mov) = $mes_selecionado AND YEAR(dt_mov) = $ano_selecionado
        GROUP BY DATE(dt_mov)
        ORDER BY DATE(dt_mov) ASC";

$res_grafico = mysqli_query($conexao, $sql_grafico);

$datas = [];
$entradas_grafico = [];
$saidas_grafico = [];

while ($row = mysqli_fetch_assoc($res_grafico)) {
    $datas[] = date('d/m', strtotime($row['data_mov']));
    $entradas_grafico[] = (float)$row['entradas'];
    $saidas_grafico[]   = (float)$row['saidas'];
}
?>

<!-- Biblioteca Chart.js para o gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main class="container">
    <div class="header" style="background: #27ae60; color: #fff; padding: 20px; border-radius: 8px;">
        <h1>💰 Painel Financeiro</h1>
        <p>Acompanhe o desempenho do caixa e o fluxo de vendas</p>
    </div>

    <!-- Filtro por Mês e Ano -->
    <form method="GET" style="margin-top: 20px; background: #fff; padding: 15px; border-radius: 8px; display: flex; gap: 10px; align-items: center;">
        <label><b>Filtrar Período:</b></label>
        <select name="mes" style="padding: 5px;">
            <?php // gerador simples de meses ?>
            <?php for($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($mes_selecionado == $m) ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                </option>
            <?php endfor; ?>
        </select>
        <select name="ano" style="padding: 5px;">
            <?php for($a = date('Y'); $a >= date('Y') - 3; $a--): ?>
                <option value="<?= $a ?>" <?= ($ano_selecionado == $a) ? 'selected' : '' ?>><?= $a ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn" style="padding: 6px 15px; background: #2980b9; color: #fff; border: none; border-radius: 4px;">Filtrar</button>
    </form>

    <!-- Indicadores Principais -->
    <div class="grid-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <div class="card" style="border-left: 5px solid green; padding: 15px; background: #fff; border-radius: 6px;">
            <h4>Entradas (Vendas)</h4>
            <p style="font-size: 22px; font-weight: bold; color: green;">R$ <?= number_format($total_entradas, 2, ',', '.') ?></p>
        </div>
        <div class="card" style="border-left: 5px solid red; padding: 15px; background: #fff; border-radius: 6px;">
            <h4>Saídas (Custos/Comissões)</h4>
            <p style="font-size: 22px; font-weight: bold; color: red;">R$ <?= number_format($total_saidas, 2, ',', '.') ?></p>
        </div>
        <div class="card" style="border-left: 5px solid #2980b9; padding: 15px; background: #fff; border-radius: 6px;">
            <h4>Saldo Líquido</h4>
            <p style="font-size: 22px; font-weight: bold; color: <?= $lucro >= 0 ? '#27ae60' : '#c0392b' ?>;">
                R$ <?= number_format($lucro, 2, ',', '.') ?>
            </p>
        </div>
        <div class="card" style="border-left: 5px solid #8e44ad; padding: 15px; background: #fff; border-radius: 6px;">
            <h4>Estoque Investido</h4>
            <p style="font-size: 22px; font-weight: bold; color: #8e44ad;">R$ <?= number_format($total_investido, 2, ',', '.') ?></p>
        </div>
    </div>

    <!-- Grid com Gráfico e Produtos Mais Vendidos -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 25px;">
        <!-- Gráfico -->
        <div class="card" style="padding: 20px; background: #fff; border-radius: 8px;">
            <h3>📊 Fluxo Diário do Mês</h3>
            <canvas id="graficoFinanceiro" style="max-height: 320px;"></canvas>
        </div>

        <!-- Produtos Mais Vendidos -->
        <div class="card" style="padding: 20px; background: #fff; border-radius: 8px;">
            <h3>🔥 Top 5 Mais Vendidos</h3>
            <ul style="list-style: none; padding: 0; margin-top: 15px;">
                <?php if (mysqli_num_rows($res_produtos) > 0): ?>
                    <?php while($prod = mysqli_fetch_assoc($res_produtos)): ?>
                        <li style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                            <span><b><?= htmlspecialchars($prod['produto']) ?></b> (<?= $prod['qtd_vendida'] ?>x)</span>
                            <span style="color: #27ae60;">R$ <?= number_format($prod['total_faturado'], 2, ',', '.') ?></span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #7f8c8d; font-size: 14px;">Nenhuma venda registrada neste período.</p>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</main>

<script>
    // Inicialização do Gráfico Chart.js
    const ctx = document.getElementById('graficoFinanceiro').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($datas) ?>,
            datasets: [
                {
                    label: 'Entradas (R$)',
                    data: <?= json_encode($entradas_grafico) ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.7)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Saídas (R$)',
                    data: <?= json_encode($saidas_grafico) ?>,
                    backgroundColor: 'rgba(231, 76, 60, 0.7)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>