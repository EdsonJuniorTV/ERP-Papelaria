<?php

require_once 'config/conexao.php';
require_once 'includes/auth.php';
include 'includes/header.php';

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    mysqli_query($conexao, "DELETE FROM estoque WHERE id_prod = $id");
    mysqli_query($conexao, "DELETE FROM produto WHERE id = $id");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if(isset($_POST['cadastrar_produto'])){

    $idProduto = isset($_POST['id_produto']) ? intval($_POST['id_produto']) : 0;

    $nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
    $categoria = intval($_POST['categoria']);
    $fornecedor = intval($_POST['fornecedor']);
    $marca = intval($_POST['marca']);
    $preco = floatval($_POST['preco']);
    $custo = floatval($_POST['custo']);
    $qtd = intval($_POST['qtd']);
    $qtd_minima = intval($_POST['qtd_minima']);

    mysqli_begin_transaction($conexao);

    try {

        if ($idProduto > 0) {

            mysqli_query($conexao,"
                UPDATE produto SET
                    id_forn = $fornecedor,
                    id_cat = $categoria,
                    id_marca = $marca,
                    nome = '$nome',
                    preco = $preco,
                    custo = $custo
                WHERE id = $idProduto
            ");

            mysqli_query($conexao,"
                UPDATE estoque SET
                    qtd = $qtd,
                    qtd_minima = $qtd_minima
                WHERE id_prod = $idProduto
            ");

        } else {

            mysqli_query($conexao,"
                INSERT INTO produto (id_forn,id_cat,id_marca,nome,preco,custo)
                VALUES ($fornecedor,$categoria,$marca,'$nome',$preco,$custo)
            ");

            $idProduto = mysqli_insert_id($conexao);

            if(!$idProduto){
                throw new Exception("Erro ao gerar ID do produto");
            }

            mysqli_query($conexao,"
                UPDATE estoque SET
                    qtd = $qtd,
                    qtd_minima = $qtd_minima
                WHERE id_prod = $idProduto
            ");
        }

        mysqli_commit($conexao);

        header("Location: ".$_SERVER['PHP_SELF']);
        exit;

    } catch(Exception $e){
        mysqli_rollback($conexao);
        die("ERRO: ".$e->getMessage());
    }
}

if(isset($_POST['entrada_mercadoria'])){

    $produto = intval($_POST['produto']);
    $qtd = intval($_POST['qtd_entrada']);

    mysqli_query($conexao,"
        UPDATE estoque
        SET qtd = qtd + $qtd
        WHERE id_prod = $produto
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtro_fornecedor = isset($_GET['fornecedor']) ? intval($_GET['fornecedor']) : 0;
$filtro_marca = isset($_GET['marca']) ? intval($_GET['marca']) : 0;

$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* LISTAS */
$marcas = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM marca ORDER BY nome ASC"), MYSQLI_ASSOC);
$fornecedores = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM fornecedor ORDER BY nome ASC"), MYSQLI_ASSOC);
$categorias = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM categoria ORDER BY nome ASC"), MYSQLI_ASSOC);
$lista_produtos = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM produto ORDER BY nome ASC"), MYSQLI_ASSOC);

$sql = "SELECT 
    p.id, p.nome, p.preco, p.custo,
    p.id_cat, p.id_forn, p.id_marca,
    c.nome AS categoria,
    f.nome AS fornecedor,
    m.nome AS marca,
    e.qtd, e.qtd_minima
FROM produto p
JOIN estoque e ON p.id = e.id_prod
JOIN categoria c ON p.id_cat = c.id
JOIN fornecedor f ON p.id_forn = f.id
JOIN marca m ON p.id_marca = m.id
WHERE 1=1
";

if ($search !== '') {
    $sql .= " AND p.nome LIKE '%".mysqli_real_escape_string($conexao,$search)."%'";
}

if ($filtro_fornecedor > 0) {
    $sql .= " AND p.id_forn = $filtro_fornecedor";
}

if ($filtro_marca > 0) {
    $sql .= " AND p.id_marca = $filtro_marca";
}

$sql .= " ORDER BY (e.qtd <= e.qtd_minima) DESC, e.qtd ASC
LIMIT $limit OFFSET $offset";

$res = mysqli_query($conexao, $sql);

$sqlTotal = "SELECT COUNT(*) as total
FROM produto p
JOIN estoque e ON p.id = e.id_prod
WHERE 1=1";

if ($search !== '') {
    $sqlTotal .= " AND p.nome LIKE '%".mysqli_real_escape_string($conexao, $search)."%'";
}

if ($filtro_fornecedor > 0) {
    $sqlTotal .= " AND p.id_forn = $filtro_fornecedor";
}

if ($filtro_marca > 0) {
    $sqlTotal .= " AND p.id_marca = $filtro_marca";
}

$resultTotal = mysqli_query($conexao, $sqlTotal);
$total = mysqli_fetch_assoc($resultTotal)['total'];

$total_pages = ceil($total / $limit);


?>

<style>
.estoque-acoes {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.estoque-acoes .btn-submit {
    white-space: nowrap;
}

.filtros-estoque {
    display: grid;
    grid-template-columns: minmax(220px, 2fr) minmax(180px, 1fr) minmax(180px, 1fr) auto;
    gap: 14px;
    align-items: end;
    padding: 22px 28px !important;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.filtros-estoque .form-group {
    padding: 0;
}

.filtro-botao {
    display: flex;
    align-items: flex-end;
}

.filtro-botao .btn-submit {
    height: 40px;
}

.estoque-modal {
    display: none;
    margin: 20px 28px;
    padding: 24px !important;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
}

.estoque-modal h3 {
    margin-bottom: 4px;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
}

.modal-header h3 {
    font-size: 1.05rem;
}

.modal-header p {
    margin-top: 4px;
    font-size: .82rem;
}

.modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    padding: 0;
    background: var(--surface-alt);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-muted);
    font-size: 1.4rem;
    line-height: 1;
    cursor: pointer;
    box-shadow: none;
}

.modal-close:hover {
    background: var(--red-light);
    border-color: var(--red);
    color: var(--red);
}

.produto-form {
    padding: 0;
}

.form-acoes {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

#modal-entrada {
    margin: 20px 28px;
    border: 1px solid var(--border);
    background: var(--surface);
}

#modal-entrada .modal-header {
    margin-bottom: 18px;
}

#form-entrada-estoque {
    display: grid;
    grid-template-columns: minmax(250px, 1fr) 160px auto;
    gap: 14px;
    align-items: end;
}

#form-entrada-estoque .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

#form-entrada-estoque select,
#form-entrada-estoque input[type="number"] {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-sans);
    font-size: .9rem;
    color: var(--text);
    background: var(--surface);
}

#form-entrada-estoque select:focus,
#form-entrada-estoque input[type="number"]:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(26, 86, 219, .12);
}

.tabela-estoque {
    margin-top: 20px;
}

.tabela-topo {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 28px;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}

.tabela-topo h2 {
    font-size: 1rem;
}

.tabela-topo p {
    margin-top: 3px;
    font-size: .82rem;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.tabela-estoque table {
    min-width: 950px;
}

.tabela-estoque tbody td {
    padding: 14px 16px;
}

.tabela-estoque tbody td strong {
    font-weight: 600;
}

.acoes-tabela {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-acao {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 9px;
    border-radius: 7px;
    border: 1px solid transparent;
    font-family: var(--font-sans);
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s, color .15s, border-color .15s;
}

.btn-editar {
    background: var(--brand-light);
    color: var(--brand);
    border-color: #c7d8fb;
}

.btn-editar:hover {
    background: var(--brand);
    color: #fff;
    text-decoration: none;
}

.btn-excluir {
    background: var(--red-light);
    color: var(--red);
    border-color: #f5c2c2;
}

.btn-excluir:hover {
    background: var(--red);
    color: #fff;
    text-decoration: none;
}


.paginacao {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 24px 28px 28px;
}

.paginacao a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text-muted);
    font-size: .82rem;
    font-weight: 600;
    transition: background .15s, color .15s, border-color .15s;
}

.paginacao a:hover {
    background: var(--brand-light);
    color: var(--brand);
    border-color: #bfd2fa;
    text-decoration: none;
}

.paginacao a.active {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
}


/* Destaque de estoque baixo */

.tabela-estoque tbody tr:has(.badge-danger) {
    background: rgba(224, 36, 36, .025);
}

.tabela-estoque tbody tr:has(.badge-danger):hover {
    background: var(--red-light);
}

</style>
<main>

<div class="container" style="max-width: 1400px;">

    <div class="header">
        <div class="flex items-center justify-between" style="gap:20px; flex-wrap:wrap;">
            <div>
                <h1>📦 Controle de Estoque</h1>
                <p>Gerencie produtos, entradas e níveis de estoque.</p>
            </div>

            <div class="estoque-acoes">
                <button type="button"
                        class="btn-submit"
                        onclick="toggleModal('modal-cadastro')">
                    + Nova Mercadoria
                </button>

                <button type="button"
                        class="btn-submit btn-success"
                        onclick="toggleModal('modal-entrada')">
                    + Entrada de Mercadoria
                </button>
            </div>
        </div>
    </div>

    <form method="GET" class="filtros-estoque">

        <div class="form-group filtro-busca">
            <label for="search">Produto</label>
            <input
                type="text"
                id="search"
                name="search"
                placeholder="Buscar produto..."
                value="<?=htmlspecialchars($search)?>"
            >
        </div>

        <div class="form-group">
            <label for="fornecedor">Fornecedor</label>

            <select name="fornecedor" id="fornecedor">
                <option value="0">Todos fornecedores</option>

                <?php foreach($fornecedores as $f): ?>
                    <option
                        value="<?=$f['id']?>"
                        <?=($filtro_fornecedor == $f['id']) ? 'selected' : ''?>
                    >
                        <?=htmlspecialchars($f['nome'])?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="marca">Marca</label>

            <select name="marca" id="marca">
                <option value="0">Todas marcas</option>

                <?php foreach($marcas as $m): ?>
                    <option
                        value="<?=$m['id']?>"
                        <?=($filtro_marca == $m['id']) ? 'selected' : ''?>
                    >
                        <?=htmlspecialchars($m['nome'])?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filtro-botao">
            <button type="submit" class="btn-submit">
                🔎 Filtrar
            </button>
        </div>

    </form>


    <div id="modal-cadastro" class="estoque-modal">

        <div class="modal-header">
            <div>
                <h3>Nova / Editar Produto</h3>
                <p>Cadastre ou altere as informações da mercadoria.</p>
            </div>

            <button
                type="button"
                class="modal-close"
                onclick="toggleModal('modal-cadastro')">
                ×
            </button>
        </div>

        <form method="POST" class="produto-form">

            <input
                type="hidden"
                name="id_produto"
                id="id_produto"
            >

            <div class="form-grid">

                <div class="form-group form-group-full">
                    <label for="nome">
                        Nome do produto <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="nome"
                        id="nome"
                        placeholder="Nome do produto"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="categoria">
                        Categoria <span>*</span>
                    </label>

                    <select name="categoria" id="categoria" required>
                        <?php foreach($categorias as $c): ?>
                            <option value="<?=$c['id']?>">
                                <?=htmlspecialchars($c['nome'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fornecedor-produto">
                        Fornecedor <span>*</span>
                    </label>

                    <select
                        name="fornecedor"
                        id="fornecedor-produto"
                        required
                    >
                        <?php foreach($fornecedores as $f): ?>
                            <option value="<?=$f['id']?>">
                                <?=htmlspecialchars($f['nome'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="marca-produto">
                        Marca <span>*</span>
                    </label>

                    <select
                        name="marca"
                        id="marca-produto"
                        required
                    >
                        <?php foreach($marcas as $m): ?>
                            <option value="<?=$m['id']?>">
                                <?=htmlspecialchars($m['nome'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="preco">
                        Preço <span>*</span>
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="preco"
                        id="preco"
                        placeholder="0,00"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="custo">
                        Custo <span>*</span>
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="custo"
                        id="custo"
                        placeholder="0,00"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="qtd">
                        Quantidade inicial <span>*</span>
                    </label>

                    <input
                        type="number"
                        name="qtd"
                        id="qtd"
                        placeholder="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="qtd_minima">
                        Quantidade mínima <span>*</span>
                    </label>

                    <input
                        type="number"
                        name="qtd_minima"
                        id="qtd_minima"
                        placeholder="0"
                        required
                    >
                </div>
            </div>

            <div class="form-acoes">
                <button
                    type="button"
                    class="btn-submit btn-ghost"
                    onclick="toggleModal('modal-cadastro')">
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="cadastrar_produto"
                    class="btn-submit">
                    💾 Salvar Produto
                </button>
            </div>

        </form>
    </div>

    <div id="modal-entrada">

        <div class="modal-header">
            <div>
                <h3>📥 Entrada de Mercadoria</h3>
                <p>Registre a entrada de produtos no estoque.</p>
            </div>

            <button
                type="button"
                class="modal-close"
                onclick="toggleModal('modal-entrada')">
                ×
            </button>
        </div>

        <form
            method="POST"
            id="form-entrada-estoque"
        >

            <div class="form-group">
                <label for="produto-entrada">
                    Produto <span>*</span>
                </label>

                <select
                    name="produto"
                    id="produto-entrada"
                    required
                >
                    <?php foreach($lista_produtos as $p): ?>
                        <option value="<?=$p['id']?>">
                            <?=htmlspecialchars($p['nome'])?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="qtd-entrada">
                    Quantidade <span>*</span>
                </label>

                <input
                    type="number"
                    name="qtd_entrada"
                    id="qtd-entrada"
                    placeholder="Quantidade"
                    min="1"
                    required
                >
            </div>

            <button
                type="submit"
                name="entrada_mercadoria"
                class="btn-submit btn-success">
                📥 Registrar Entrada
            </button>

        </form>

    </div>

    <div class="tabela-estoque">

        <div class="tabela-topo">
            <div>
                <h2>Produtos em Estoque</h2>
                <p>Visualize a situação atual das mercadorias.</p>
            </div>
        </div>

        <div class="table-responsive">

            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Fornecedor</th>
                        <th>Marca</th>
                        <th>Qtd. Atual</th>
                        <th>Qtd. Mínima</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($i = mysqli_fetch_assoc($res)): ?>

                    <?php
                        $estoque_baixo = ($i['qtd'] <= $i['qtd_minima']);
                    ?>

                    <tr>

                        <td>
                            <strong><?=htmlspecialchars($i['nome'])?></strong>
                        </td>

                        <td>
                            <?=htmlspecialchars($i['categoria'])?>
                        </td>

                        <td>
                            <?=htmlspecialchars($i['fornecedor'])?>
                        </td>

                        <td>
                            <?=htmlspecialchars($i['marca'])?>
                        </td>

                        <td>
                            <span class="<?= $estoque_baixo ? 'text-red' : 'text-green' ?>">
                                <?=$i['qtd']?>
                            </span>
                        </td>

                        <td>
                            <?=$i['qtd_minima']?>
                        </td>

                        <td>

                            <?php if($estoque_baixo): ?>

                                <span class="badge badge-danger">
                                    ⚠ Reposição Necessária
                                </span>

                            <?php else: ?>

                                <span class="badge badge-ok">
                                    ✓ Estoque OK
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <div class="acoes-tabela">

                                <button
                                    type="button"
                                    class="btn-acao btn-editar"
                                    onclick='editarProduto(<?=json_encode($i, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)?>)'
                                    title="Editar produto">
                                    ✏ Editar
                                </button>

                                <a
                                    href="?delete=<?=$i['id']?>"
                                    class="btn-acao btn-excluir"
                                    onclick="return confirm('Deseja realmente excluir este produto?')"
                                    title="Excluir produto">
                                    🗑 Excluir
                                </a>
                            </div>
                        </td>
                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>
        </div>
    </div>

    
    <?php if($total_pages > 1): ?>
        <div class="paginacao">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
    <a
        href="?<?=http_build_query([
            'page' => $i,
            'search' => $search,
            'fornecedor' => $filtro_fornecedor,
            'marca' => $filtro_marca
        ])?>"
        class="<?=($i == $page) ? 'active' : ''?>">
        <?=$i?>
    </a>

<?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
</main>
<script>

function toggleModal(id) {

    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    const visible = window.getComputedStyle(modal).display !== 'none';

    modal.style.display = visible ? 'none' : 'block';
}


function editarProduto(p) {

    const modal = document.getElementById('modal-cadastro');
    modal.style.display = 'block';
    document.getElementById('id_produto').value = p.id;
    document.getElementById('nome').value = p.nome || '';
    document.getElementById('preco').value = p.preco || '';
    document.getElementById('custo').value = p.custo || '';
    document.getElementById('qtd').value = p.qtd || '';
    document.getElementById('qtd_minima').value = p.qtd_minima || '';
    document.getElementById('categoria').value = p.id_cat || '';
    document.getElementById('fornecedor-produto').value = p.id_forn || '';
    document.getElementById('marca-produto').value = p.id_marca || '';

    modal.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

</script>

<?php include 'includes/footer.php'; ?>
