<?php 
require_once 'config/conexao.php';
include 'includes/header.php';
$produtos = mysqli_query($conexao, "SELECT p.id, p.nome, p.preco, e.qtd FROM produto p JOIN estoque e ON p.id = e.id_prod WHERE e.qtd > 0");
$clientes = mysqli_query($conexao, "SELECT id, nome FROM cliente");
?>

<main>
    <div class="wrap">

        <div class="page-header">
            <h1>🛒 Frente de Caixa</h1>
            <p>Registre vendas e finalize pedidos</p>
        </div>

        <div class="grid">

            <!-- Lançar item -->
            <div class="card">
                <h2>Lançar Item</h2>

                <div class="row">
                    <input type="text" id="busca_prod" list="lista_produtos" placeholder="Digite o produto...">
                    <datalist id="lista_produtos">
                        <?php while($p = mysqli_fetch_assoc($produtos)): ?>
                            <option value="<?= htmlspecialchars($p['nome']) ?>"
                                    data-id="<?= $p['id'] ?>"
                                    data-preco="<?= $p['preco'] ?>">
                                R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                            </option>
                        <?php endwhile; ?>
                    </datalist>

                    <input type="number" id="qtd_item" value="1" min="1" style="width: 75px;">
                    <button onclick="adicionarAoCarrinho()" class="btn-add">+ Add</button>
                </div>

                <div class="row" style="margin-top: 14px;">
                    <select id="id_cliente" style="flex:1;">
                        <option value="">👤 Consumidor Final</option>
                        <?php while($c = mysqli_fetch_assoc($clientes)): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- Carrinho -->
            <div class="card">
                <h2>Itens do Pedido</h2>

                <div id="lista_itens" class="comanda"></div>

                <div class="resumo">
                    <p>Total: <span id="total_venda">R$ 0,00</span></p>
                    <button onclick="finalizarVenda()" class="btn-finalizar">✅ Finalizar (F8)</button>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="/public/js/caixa.js"></script>
<?php include 'includes/footer.php'; ?>