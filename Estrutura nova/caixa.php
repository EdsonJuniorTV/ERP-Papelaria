<?php 
require_once 'config/conexao.php';
include 'includes/header.php'; // Carrega CSS e Menu

// Consultas para o funcionamento do PDV
$produtos = mysqli_query($conexao, "SELECT p.id, p.nome, p.preco, e.qtd FROM produto p JOIN estoque e ON p.id = e.id_prod WHERE e.qtd > 0");
$clientes = mysqli_query($conexao, "SELECT id, nome FROM cliente");
?>

<div class="wrap">
    <h1>🛒 Caixa Livre</h1>
    <div class="grid">
        <div class="card">
            <h2>Lançar Item</h2>
            <div class="row">
                <input type="text" id="busca_prod" list="lista_produtos" placeholder="Produto...">
                <datalist id="lista_produtos">
                    <?php while($p = mysqli_fetch_assoc($produtos)): ?>
                        <option value="<?= $p['nome'] ?>" data-id="<?= $p['id'] ?>" data-preco="<?= $p['preco'] ?>">
                            R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                        </option>
                    <?php endwhile; ?>
                </datalist>
                <input type="number" id="qtd_item" value="1" min="1" style="width: 60px;">
                <button onclick="adicionarAoCarrinho()" class="btn-add">Add</button>
            </div>
            <div class="row" style="margin-top: 15px;">
                <select id="id_cliente">
                    <option value="">Consumidor Final</option>
                    <?php while($c = mysqli_fetch_assoc($clientes)): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['nome'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="card">
            <h2>Itens</h2>
            <div id="lista_itens" class="comanda" style="min-height: 150px;"></div>
            <div class="resumo">
                <p>Total: <span id="total_venda">R$ 0,00</span></p>
                <button onclick="finalizarVenda()" class="btn-finalizar">Finalizar (F8)</button>
            </div>
        </div>
    </div>
</div>

<script src="public/scripts/caixa.js"></script>
<?php include 'includes/footer.php'; ?>