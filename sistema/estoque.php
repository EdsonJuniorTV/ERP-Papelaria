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

/*Filtro da aparência do estoque*/
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

$total = mysqli_fetch_assoc(mysqli_query($conexao,$sqlTotal))['total'];
$total_pages = ceil($total / $limit);

?>

<main>

<div class="container" style="max-width:95%;">

<div style="background:#2d3748; padding:15px; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
    <h1 style="color:white;">📦 Controle de Estoque</h1>

    <div style="display:flex; gap:10px;">
        <button onclick="toggleModal('modal-cadastro')"
                style="background:#3182ce; color:white; padding:10px 20px; border:none; border-radius:5px;">
            + Nova Mercadoria
        </button>

        <button onclick="toggleModal('modal-entrada')"
                style="background:#48bb78; color:white; padding:10px 20px; border:none; border-radius:5px;">
            + Entrada de Mercadoria
        </button>
    </div>
</div>

<form method="GET" style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">

    <input type="text" name="search" placeholder="Buscar produto..."
           value="<?=htmlspecialchars($search)?>" style="padding:8px;">

    <select name="fornecedor" style="padding:8px;">
        <option value="0">Todos fornecedores</option>
        <?php foreach($fornecedores as $f): ?>
            <option value="<?=$f['id']?>" <?=($filtro_fornecedor==$f['id'])?'selected':''?>>
                <?=$f['nome']?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="marca" style="padding:8px;">
        <option value="0">Todas marcas</option>
        <?php foreach($marcas as $m): ?>
            <option value="<?=$m['id']?>" <?=($filtro_marca==$m['id'])?'selected':''?>>
                <?=$m['nome']?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" style="padding:8px 15px; background:#2b6cb0; color:#fff; border:none; border-radius:5px;">
        Filtrar
    </button>

</form>

<div id="modal-cadastro" style="display:none; background:#fff; padding:20px; margin-top:20px; border-radius:8px;">

<h3>Novo / Editar Produto</h3>

<form method="POST">

    <input type="hidden" name="id_produto" id="id_produto">

    <input type="text" name="nome" placeholder="Nome do produto" required>

    <select name="categoria" required>
        <?php foreach($categorias as $c): ?>
            <option value="<?=$c['id']?>"><?=$c['nome']?></option>
        <?php endforeach; ?>
    </select>

    <select name="fornecedor" required>
        <?php foreach($fornecedores as $f): ?>
            <option value="<?=$f['id']?>"><?=$f['nome']?></option>
        <?php endforeach; ?>
    </select>

    <select name="marca" required>
        <?php foreach($marcas as $m): ?>
            <option value="<?=$m['id']?>"><?=$m['nome']?></option>
        <?php endforeach; ?>
    </select>

    <input type="number" step="0.01" name="preco" placeholder="Preço" required>
    <input type="number" step="0.01" name="custo" placeholder="Custo" required>
    <input type="number" name="qtd" placeholder="Quantidade inicial" required>
    <input type="number" name="qtd_minima" placeholder="Quantidade mínima" required>

    <button type="submit" name="cadastrar_produto">Salvar</button>

</form>
</div>

<div id="modal-entrada" style="display:none; background:#fff; padding:20px; margin-top:20px; border-radius:8px;">

<h3>Entrada de Mercadoria</h3>

<form method="POST">

    <select name="produto" required>
        <?php foreach($lista_produtos as $p): ?>
            <option value="<?=$p['id']?>"><?=$p['nome']?></option>
        <?php endforeach; ?>
    </select>

    <input type="number" name="qtd_entrada" placeholder="Quantidade" required>

    <button type="submit" name="entrada_mercadoria">Registrar Entrada</button>

</form>

</div>

<table style="width:100%; margin-top:20px; background:#fff; border-collapse:collapse;">

<thead>
<tr style="background:#EDF2F7;">
    <th>Produto</th>
    <th>Categoria</th>
    <th>Fornecedor</th>
    <th>Marca</th>
    <th>Qtd Atual</th>
    <th>Qtd Mínima</th>
    <th>Status</th>
    <th>Ações</th>
</tr>
</thead>

<tbody>

<?php while($i = mysqli_fetch_assoc($res)): ?>

<tr>

    <td><?=$i['nome']?></td>
    <td><?=$i['categoria']?></td>
    <td><?=$i['fornecedor']?></td>
    <td><?=$i['marca']?></td>

    <td style="color:<?=($i['qtd'] <= $i['qtd_minima']) ? 'red' : 'black'?>">
        <?=$i['qtd']?>
    </td>

    <td><?=$i['qtd_minima']?></td>

    <td>
        <?=($i['qtd'] <= $i['qtd_minima']) ? 'Reposição Necessária' : 'Estoque OK'?>
    </td>

    <td>
        <a href="?delete=<?=$i['id']?>" onclick="return confirm('Excluir?')" style="color:red;">Excluir</a>
        |
        <a href="javascript:void(0)" onclick='editarProduto(<?=json_encode($i)?>)' style="color:blue;">Editar</a>
    </td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

<div style="margin-top:20px; display:flex; gap:5px; flex-wrap:wrap;">

<?php for($i=1; $i <= $total_pages; $i++): ?>

<a href="?page=<?=$i?>"
   style="padding:5px 10px; border:1px solid #ccc;
   background:<?=($i==$page)?'#3182ce':'#fff'?>;
   color:<?=($i==$page)?'#fff':'#000'?>;
   text-decoration:none;">
   <?=$i?>
</a>

<?php endfor; ?>

</div>

</div>
</main>

<script>
function toggleModal(id){
    const modal = document.getElementById(id);
    const visible = window.getComputedStyle(modal).display !== "none";
    modal.style.display = visible ? "none" : "block";
}

function editarProduto(p){

    document.getElementById('modal-cadastro').style.display = 'block';

    document.getElementById('id_produto').value = p.id;
    document.querySelector('[name="nome"]').value = p.nome;
    document.querySelector('[name="preco"]').value = p.preco;
    document.querySelector('[name="custo"]').value = p.custo;
    document.querySelector('[name="qtd_minima"]').value = p.qtd_minima;

    document.querySelector('[name="categoria"]').value = p.id_cat;
    document.querySelector('[name="fornecedor"]').value = p.id_forn;
    document.querySelector('[name="marca"]').value = p.id_marca;
}
</script>

<?php include 'includes/footer.php'; ?>
