<?php 
require_once 'config/conexao.php';
require_once 'includes/auth.php';
include 'includes/header.php';

// Consulta para listar o estoque atual
$sql = "SELECT p.id, p.nome, c.nome as categoria, e.qtd, e.qtd_minima 
        FROM produto p 
        JOIN estoque e ON p.id = e.id_prod 
        JOIN categoria c ON p.id_cat = c.id
        ORDER BY e.qtd ASC";
$res = mysqli_query($conexao, $sql);

// Lista de produtos para o Modal de Entrada
$lista_prods = mysqli_query($conexao, "SELECT id, nome FROM produto ORDER BY nome ASC");
?>

<main>
    <div class="container" style="max-width: 95%;">
        <div class="header" style="background: #2d3748; display: flex; justify-content: space-between; align-items: center;">
            <h1>📦 Controle de Estoque</h1>
            <button onclick="document.getElementById('modal-entrada').style.display='block'" style="background: #48bb78; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                + Dar Entrada de Mercadoria
            </button>
        </div>

        <div id="modal-entrada" style="display:none; background: #fff; padding: 20px; border: 2px solid #2d3748; margin: 20px 0; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3>Registrar Entrada de Itens</h3>
            <form id="form-entrada-estoque" style="display: flex; gap: 15px; align-items: flex-end;">
                <div class="form-group">
                    <label>Produto</label>
                    <select name="id_prod" required style="padding: 8px;">
                        <?php while($lp = mysqli_fetch_assoc($lista_prods)): ?>
                            <option value="<?= $lp['id'] ?>"><?= $lp['nome'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantidade</label>
                    <input type="number" name="qtd_entrada" min="1" required style="padding: 8px; width: 100px;">
                </div>
                <button type="submit" class="btn-submit" style="width: auto; padding: 10px 25px;">Confirmar Entrada</button>
                <button type="button" onclick="document.getElementById('modal-entrada').style.display='none'" style="background:#e53e3e; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">Cancelar</button>
            </form>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: white;">
            <thead>
                <tr style="background: #edf2f7; text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">Produto</th>
                    <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">Categoria</th>
                    <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">Qtd Atual</th>
                    <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">Mínimo</th>
                    <th style="padding: 15px; border-bottom: 2px solid #cbd5e0;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = mysqli_fetch_assoc($res)): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 15px;"><?= $item['nome'] ?></td>
                    <td style="padding: 15px;"><?= $item['categoria'] ?></td>
                    <td style="padding: 15px; font-weight: bold; <?= ($item['qtd'] <= $item['qtd_minima']) ? 'color:red;' : 'color:green;' ?>">
                        <?= $item['qtd'] ?>
                    </td>
                    <td style="padding: 15px; color: #718096;"><?= $item['qtd_minima'] ?></td>
                    <td style="padding: 15px;">
                        <?= ($item['qtd'] <= $item['qtd_minima']) ? '⚠️ Reposição Necessária' : '✅ Estoque Seguro' ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.getElementById('form-entrada-estoque').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const dados = Object.fromEntries(formData.entries());

    const res = await fetch('controllers/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            tipo_entidade: 'entrada_estoque',
            id_prod: dados.id_prod,
            qtd: dados.qtd_entrada
        })
    });

    const result = await res.json();
    alert(result.mensagem);
    if(result.status) location.reload();
});
</script>

<?php include 'includes/footer.php'; ?>