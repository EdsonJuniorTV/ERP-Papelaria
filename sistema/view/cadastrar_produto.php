<?php 
require_once 'includes/auth.php';
require_once 'config/conexao.php';
include 'includes/header.php'; 

// Carrega as opções dos selects do banco de dados
$fornecedores = mysqli_query($conexao, "SELECT id, nome FROM fornecedor ORDER BY nome ASC");
$categorias   = mysqli_query($conexao, "SELECT id, nome FROM categoria ORDER BY nome ASC");
$marcas       = mysqli_query($conexao, "SELECT id, nome FROM marca ORDER BY nome ASC");
?>

<main>
    <div class="container">
        <div class="header" style="background: #e67e22;">
            <h1>📦 Novo Produto</h1>
            <p>Cadastre itens no catálogo e defina margens de lucro</p>
        </div>

        <form id="form" data-method="post">
            <input type="hidden" name="tipo_entidade" value="produto">

            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label>Nome do Produto <span>*</span></label>
                    <input type="text" name="nome" placeholder="Ex: Caderno Universitário 10 Matérias" required>
                </div>

                <div class="form-group">
                    <label>Fornecedor <span>*</span></label>
                    <select name="id_forn" required>
                        <option value="">Selecione o Fornecedor...</option>
                        <?php while($f = mysqli_fetch_assoc($fornecedores)): ?>
                            <option value="<?= $f['id'] ?>"><?= $f['nome'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Categoria <span>*</span></label>
                    <select name="id_cat" required>
                        <option value="">Selecione a Categoria...</option>
                        <?php while($c = mysqli_fetch_assoc($categorias)): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['nome'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Marca <span>*</span></label>
                    <select name="id_marca" required>
                        <option value="">Selecione a Marca...</option>
                        <?php while($m = mysqli_fetch_assoc($marcas)): ?>
                            <option value="<?= $m['id'] ?>"><?= $m['nome'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Preço de Custo (R$) <span>*</span></label>
                    <input type="number" step="0.01" name="custo" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label>Preço de Venda (R$) <span>*</span></label>
                    <input type="number" step="0.01" name="preco" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label>Estoque Mínimo (Alerta)</label>
                    <input type="number" name="qtd_minima" value="5" min="0">
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">💾 Cadastrar Produto</button>
                <button type="reset" class="btn-submit" style="background: #95a5a6; margin-left: 10px;">Limpar</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>