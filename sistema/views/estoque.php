<?php
// Fazer uma lógica para falar o id do funcionário atual que está logado no sistema
require_once '../config/conexao.php';
require_once '../includes/auth.php';

// ATENÇÃO: Certifique-se de que a sessão já foi iniciada no auth.php
// Substitua 'id_funcionario' pelo nome da variável de sessão que você usa para guardar o ID do usuário logado.
$idFuncionario = isset($_SESSION['id_funcionario']) ? intval($_SESSION['id_funcionario']) : 1; 

// Injeta o ID do funcionário logado no MySQL para que a sua trigger "trg_log_preco_produto" consiga ler
mysqli_query($conexao, "SET @id_funcionario = $idFuncionario;");

include '../includes/header.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Não apagamos o produto: ele tem histórico, compras e vendas ligados a ele.
    // Apenas marcamos como Inativo, e ele deixa de aparecer no estoque e no caixa.
    mysqli_query($conexao, "UPDATE produto SET status = 'Inativo' WHERE id = $id");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['ativar'])) {
    $id = intval($_GET['ativar']);

    mysqli_query($conexao, "UPDATE produto SET status = 'Ativo' WHERE id = $id");

    // Volta para a lista de inativos, para reativar outros em sequência
    header("Location: ".$_SERVER['PHP_SELF']."?situacao=Inativo");
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
            // === MODO DE EDIÇÃO ===
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

            // Verifica o estoque atual para saber se aumentou
            $queryEst = mysqli_query($conexao, "SELECT qtd FROM estoque WHERE id_prod = $idProduto");
            $estAtual = mysqli_fetch_assoc($queryEst)['qtd'];
            $diferenca = $qtd - $estAtual;

            if ($diferenca > 0) {
                // A quantidade nova é MAIOR que a antiga. Gera uma compra para a diferença!
                $valorTotalCompra = $diferenca * $custo;
                
                // Registra a Compra
                mysqli_query($conexao,"INSERT INTO compra (id_forn, id_func, valor_total) VALUES ($fornecedor, $idFuncionario, $valorTotalCompra)");
                $idCompra = mysqli_insert_id($conexao);
                
                // Registra o Item (A sua trigger trg_item_compra_insert vai somar a diferença no estoque)
                mysqli_query($conexao,"INSERT INTO item_compra (id_compra, id_prod, qtd, preco_unit) VALUES ($idCompra, $idProduto, $diferenca, $custo)");
                
                // Atualiza apenas a qtd_minima manualmente
                mysqli_query($conexao,"UPDATE estoque SET qtd_minima = $qtd_minima WHERE id_prod = $idProduto");
            } else {
                // Se a quantidade foi reduzida ou mantida (ajuste de quebra/roubo), atualizamos direto
                mysqli_query($conexao,"
                    UPDATE estoque SET
                        qtd = $qtd, 
                        qtd_minima = $qtd_minima
                    WHERE id_prod = $idProduto
                ");
            }

        } else {
            // === MODO DE CRIAÇÃO (NOVO PRODUTO) ===
            mysqli_query($conexao,"
                INSERT INTO produto (id_forn,id_cat,id_marca,nome,preco,custo)
                VALUES ($fornecedor,$categoria,$marca,'$nome',$preco,$custo)
            ");

            $idProduto = mysqli_insert_id($conexao);

            if(!$idProduto){
                throw new Exception("Erro ao gerar ID do produto");
            }

            // Inserimos o estoque com quantidade ZERO
            mysqli_query($conexao,"
                INSERT INTO estoque (id_prod,qtd,qtd_minima)
                VALUES ($idProduto, 0, $qtd_minima)
            ");

            // Se houver quantidade inicial definida, criamos uma compra de entrada
            if ($qtd > 0) {
                $valorTotalCompra = $qtd * $custo;
                
                // 1. Gera a Compra
                mysqli_query($conexao,"INSERT INTO compra (id_forn, id_func, valor_total) VALUES ($fornecedor, $idFuncionario, $valorTotalCompra)");
                $idCompra = mysqli_insert_id($conexao);
                
                // 2. Gera o Item (Isso aciona sua trigger que vai somar o $qtd no estoque zerado que acabamos de criar)
                mysqli_query($conexao,"INSERT INTO item_compra (id_compra, id_prod, qtd, preco_unit) VALUES ($idCompra, $idProduto, $qtd, $custo)");
            }
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

    $produto_id = intval($_POST['produto']);
    $qtd_entrada = intval($_POST['qtd_entrada']);

    mysqli_begin_transaction($conexao);
    try {
        // Busca o fornecedor e custo vinculados a esse produto atualmente
        $queryProd = mysqli_query($conexao, "SELECT id_forn, custo FROM produto WHERE id = $produto_id");
        $dadosProd = mysqli_fetch_assoc($queryProd);

        if($dadosProd) {
            $idForn = $dadosProd['id_forn'];
            $custo = $dadosProd['custo'];
            $valorTotalCompra = $qtd_entrada * $custo;

            // 1. Gera o registro geral da Compra vinculando ao funcionário logado
            mysqli_query($conexao,"
                INSERT INTO compra (id_forn, id_func, valor_total)
                VALUES ($idForn, $idFuncionario, $valorTotalCompra)
            ");
            $idCompra = mysqli_insert_id($conexao);

            // 2. Gera o item da compra. 
            // Sua trigger no banco 'trg_item_compra_insert' vai automaticamente somar essa quantidade no estoque!
            mysqli_query($conexao,"
                INSERT INTO item_compra (id_compra, id_prod, qtd, preco_unit)
                VALUES ($idCompra, $produto_id, $qtd_entrada, $custo)
            ");
        }

        mysqli_commit($conexao);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conexao);
        die("ERRO: ".$e->getMessage());
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtro_fornecedor = isset($_GET['fornecedor']) ? intval($_GET['fornecedor']) : 0;
$filtro_marca = isset($_GET['marca']) ? intval($_GET['marca']) : 0;
$situacao = (isset($_GET['situacao']) && $_GET['situacao'] === 'Inativo') ? 'Inativo' : 'Ativo';

$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* LISTAS */
$marcas = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM marca ORDER BY nome ASC"), MYSQLI_ASSOC);
$fornecedores = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM fornecedor ORDER BY nome ASC"), MYSQLI_ASSOC);
$categorias = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM categoria ORDER BY nome ASC"), MYSQLI_ASSOC);
$lista_produtos = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM produto WHERE status = 'Ativo' ORDER BY nome ASC"), MYSQLI_ASSOC);

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
WHERE p.status = '$situacao'
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
WHERE p.status = '$situacao'";

$total = mysqli_fetch_assoc(mysqli_query($conexao,$sqlTotal))['total'];
$total_pages = ceil($total / $limit);

?>

<style>
    .est-filtros, .est-card {
        background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        margin-top: 20px; box-sizing: border-box;
    }
    .est-filtros { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; padding: 16px; }
    .est-card { display: none; padding: 24px; border-top: 4px solid #1a56db; }
    .est-card h3 { margin: 0 0 18px; font-size: 1.1rem; color: #111827; }

    .est-campo { display: flex; flex-direction: column; gap: 5px; flex: 1 1 180px; min-width: 0; }
    .est-campo label {
        font-size: .76rem; font-weight: 600; color: #6b7280;
        text-transform: uppercase; letter-spacing: .05em;
    }
    .est-campo input, .est-campo select {
        width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: .95rem;
        border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #111827;
        transition: border-color .15s, box-shadow .15s;
    }
    .est-campo input:focus, .est-campo select:focus {
        outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.15);
    }

    .est-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
    .est-full { grid-column: 1 / -1; }

    .est-inativa td:not(:last-child) { opacity: .6; }

    .est-acoes { display: flex; gap: 10px; flex-wrap: wrap; }
    .est-card .est-acoes { margin-top: 22px; justify-content: flex-end; }

    .est-btn {
    display: inline-block; padding: 10px 20px; border: none; border-radius: 8px;
    font-size: .9rem; font-weight: 600; cursor: pointer; line-height: 1.2;
    text-decoration: none;
    transition: background .15s, transform .15s, box-shadow .15s;
    }
    .est-btn:hover, .est-btn:focus, .est-btn:active {
        text-decoration: none;
    }
    .est-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,.15);
    }
    .est-btn:active {
        transform: translateY(0);
        box-shadow: none;
    }

    .est-btn-primario { background: #1a56db; color: #fff; }
    .est-btn-primario:hover { background: #1647b8; color: #fff; }
    .est-btn-secundario { background: #e5e7eb; color: #374151; }
    .est-btn-secundario:hover { background: #d1d5db; color: #374151; }
</style>

<main>

<div class="container" style="max-width:95%;">

<div class="header">
    <h1>📦 Controle de Estoque</h1>

    <div style="display:flex; gap:10px;">
        <button onclick="novaMercadoria()" class="btn-success">
            + Nova Mercadoria
        </button>

        <button onclick="toggleModal('modal-entrada')" class="btn-success">
            + Entrada de Mercadoria
        </button>
    </div>
</div>

<form method="GET" class="est-filtros">

    <div class="est-campo" style="flex: 2 1 240px;">
        <label for="f-search">Produto</label>
        <input type="text" id="f-search" name="search" placeholder="Buscar produto..."
               value="<?=htmlspecialchars($search)?>">
    </div>

    <div class="est-campo">
        <label for="f-fornecedor">Fornecedor</label>
        <select id="f-fornecedor" name="fornecedor">
            <option value="0">Todos fornecedores</option>
            <?php foreach($fornecedores as $f): ?>
                <option value="<?=$f['id']?>" <?=($filtro_fornecedor==$f['id'])?'selected':''?>>
                    <?=$f['nome']?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="est-campo">
        <label for="f-marca">Marca</label>
        <select id="f-marca" name="marca">
            <option value="0">Todas marcas</option>
            <?php foreach($marcas as $m): ?>
                <option value="<?=$m['id']?>" <?=($filtro_marca==$m['id'])?'selected':''?>>
                    <?=$m['nome']?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="est-campo">
        <label for="f-situacao">Situação</label>
        <select id="f-situacao" name="situacao">
            <option value="Ativo" <?=($situacao==='Ativo')?'selected':''?>>Ativos</option>
            <option value="Inativo" <?=($situacao==='Inativo')?'selected':''?>>Inativos</option>
        </select>
    </div>

    <div class="est-acoes">
        <button type="submit" class="est-btn est-btn-primario">Filtrar</button>
        <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="est-btn est-btn-secundario">Limpar</a>
    </div>

</form>

<div id="modal-cadastro" class="est-card">

<h3 id="titulo-cadastro">Nova Mercadoria</h3>

<form method="POST" id="form-produto">

    <input type="hidden" name="id_produto" id="id_produto">

    <div class="est-grid">

        <div class="est-campo est-full">
            <label for="c-nome">Nome do produto</label>
            <input type="text" id="c-nome" name="nome" placeholder="Ex: Caderno 10 matérias" required>
        </div>

        <div class="est-campo">
            <label for="c-categoria">Categoria</label>
            <select id="c-categoria" name="categoria" required>
                <?php foreach($categorias as $c): ?>
                    <option value="<?=$c['id']?>"><?=$c['nome']?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="est-campo">
            <label for="c-fornecedor">Fornecedor</label>
            <select id="c-fornecedor" name="fornecedor" required>
                <?php foreach($fornecedores as $f): ?>
                    <option value="<?=$f['id']?>"><?=$f['nome']?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="est-campo">
            <label for="c-marca">Marca</label>
            <select id="c-marca" name="marca" required>
                <?php foreach($marcas as $m): ?>
                    <option value="<?=$m['id']?>"><?=$m['nome']?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="est-campo">
            <label for="c-preco">Preço de venda (R$)</label>
            <input type="number" step="0.01" id="c-preco" name="preco" placeholder="0,00" required>
        </div>

        <div class="est-campo">
            <label for="c-custo">Custo de compra (R$)</label>
            <input type="number" step="0.01" id="c-custo" name="custo" placeholder="0,00" required>
        </div>

        <div class="est-campo">
            <label for="c-qtd">Quantidade em estoque</label>
            <input type="number" id="c-qtd" name="qtd" placeholder="0" required>
        </div>

        <div class="est-campo">
            <label for="c-qtd-minima">Quantidade mínima</label>
            <input type="number" id="c-qtd-minima" name="qtd_minima" placeholder="0" required>
        </div>

    </div>

    <div class="est-acoes">
        <button type="button" class="est-btn est-btn-secundario" onclick="toggleModal('modal-cadastro')">Cancelar</button>
        <button type="submit" name="cadastrar_produto" class="est-btn est-btn-primario">Salvar</button>
    </div>

</form>
</div>

<div id="modal-entrada" class="est-card">

<h3>Entrada de Mercadoria</h3>

<form method="POST">

    <div class="est-grid">

        <div class="est-campo">
            <label for="e-produto">Produto</label>
            <select id="e-produto" name="produto" required>
                <?php foreach($lista_produtos as $p): ?>
                    <option value="<?=$p['id']?>"><?=$p['nome']?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="est-campo">
            <label for="e-qtd">Quantidade a adicionar</label>
            <input type="number" id="e-qtd" name="qtd_entrada" min="1" placeholder="0" required>
        </div>

    </div>

    <div class="est-acoes">
        <button type="button" class="est-btn est-btn-secundario" onclick="toggleModal('modal-entrada')">Cancelar</button>
        <button type="submit" name="entrada_mercadoria" class="est-btn est-btn-primario">Registrar Entrada</button>
    </div>

</form>

</div>

<table style="width:100%; margin-top:20px; background:#fff; border-collapse:collapse;">

<thead>
<tr style="background:#EDF2F7;">
    <th>Produto</th>
    <th>Categoria</th>
    <th>Fornecedor</th>
    <th>Marca</th>
    <th>Preço</th>
    <th>Qtd Atual</th>
    <th>Qtd Mínima</th>
    <th>Status</th>
    <th>Ações</th>
</tr>
</thead>

<tbody>

<?php while($i = mysqli_fetch_assoc($res)): ?>

<tr class="<?=($situacao === 'Inativo') ? 'est-inativa' : ''?>">

    <td><?=$i['nome']?></td>
    <td><?=$i['categoria']?></td>
    <td><?=$i['fornecedor']?></td>
    <td><?=$i['marca']?></td>
    <td><?=$i['preco']?></td>

    <td style="color:<?=($i['qtd'] <= $i['qtd_minima']) ? 'red' : 'black'?>">
        <?=$i['qtd']?>
    </td>

    <td><?=$i['qtd_minima']?></td>

    <td>
        <?php if ($situacao === 'Inativo'): ?>
            <span class="badge badge-danger">Inativo</span>
        <?php else: ?>
            <?=($i['qtd'] <= $i['qtd_minima']) ? 'Reposição Necessária' : 'Estoque OK'?>
        <?php endif; ?>
    </td>

    <td>
        <?php if ($situacao === 'Inativo'): ?>
            <a href="?ativar=<?=$i['id']?>" onclick="return confirm('Reativar este produto?\n\nEle volta para o estoque e para o caixa.')" class="btn-success" style="text-decoration: none">Reativar</a>
        <?php else: ?>
            <a href="?delete=<?=$i['id']?>" onclick="return confirm('Desativar este produto?\n\nEle sai do estoque e do caixa, mas o histórico de compras e vendas é mantido.')" class="btn-danger" style="text-decoration: none">Desativar</a>
            <a href="javascript:void(0)" onclick='editarProduto(<?=json_encode($i)?>)' class="btn-submit" style="text-decoration: none">Editar</a>
        <?php endif; ?>
    </td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

<div style="margin-top:20px; display:flex; gap:5px; flex-wrap:wrap;">

<?php for($i=1; $i <= $total_pages; $i++): ?>

<a href="?situacao=<?=$situacao?>&amp;page=<?=$i?>"
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

function novaMercadoria(){
    document.getElementById('form-produto').reset();
    document.getElementById('id_produto').value = '';
    document.getElementById('titulo-cadastro').textContent = 'Nova Mercadoria';
    document.getElementById('modal-cadastro').style.display = 'block';
}

function editarProduto(p){

    const modal = document.getElementById('modal-cadastro');
    modal.style.display = 'block';
    document.getElementById('titulo-cadastro').textContent = 'Editar Mercadoria: ' + p.nome;

    document.getElementById('id_produto').value = p.id;
    modal.querySelector('[name="nome"]').value = p.nome;
    modal.querySelector('[name="preco"]').value = p.preco;
    modal.querySelector('[name="custo"]').value = p.custo;
    modal.querySelector('[name="qtd_minima"]').value = p.qtd_minima;
    modal.querySelector('[name="qtd"]').value = p.qtd;

    modal.querySelector('[name="categoria"]').value = p.id_cat;
    modal.querySelector('[name="fornecedor"]').value = p.id_forn;
    modal.querySelector('[name="marca"]').value = p.id_marca;

    modal.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php include '../includes/footer.php'; ?>