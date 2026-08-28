<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['user_id'])) {
        header("Location: index.php?erro=2");
        exit;
    }

    require_once 'config/conexao.php';

    // -------------------------------------------------------------------------
    // PROCESSAMENTO DA VENDA VIA AJAX (Recebe os dados do JavaScript)
    // -------------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['acao']) && $input['acao'] === 'finalizar_venda') {
            header('Content-Type: application/json');
            
            try {
                mysqli_begin_transaction($conexao);
                
                $id_func = $_SESSION['user_id'];
                $id_cli = intval($input['id_cliente']);
                
                if ($id_cli <= 0) {
                    throw new Exception("Por favor, selecione um cliente válido.");
                }
                
                // 1. Cria o Pedido com status 'Aberto'
                $stmt = mysqli_prepare($conexao, "INSERT INTO pedido (id_cli, id_func, status) VALUES (?, ?, 'Aberto')");
                mysqli_stmt_bind_param($stmt, "ii", $id_cli, $id_func);
                mysqli_stmt_execute($stmt);
                $id_ped = mysqli_insert_id($conexao);
                
                // 2. Insere os Itens do Pedido (Isso aciona as Triggers de Estoque e Comissão)
                $stmt_item = mysqli_prepare($conexao, "INSERT INTO item_pedido (id_ped, id_prod, qtd, preco_unitario) VALUES (?, ?, ?, ?)");
                foreach ($input['carrinho'] as $item) {
                    $id_prod = intval($item['cod']);
                    $qtd = intval($item['qtd']);
                    $preco = floatval($item['preco']);
                    
                    mysqli_stmt_bind_param($stmt_item, "iiid", $id_ped, $id_prod, $qtd, $preco);
                    mysqli_stmt_execute($stmt_item);
                }
                
                // 3. Atualiza para 'Pago' (Isso aciona a Trigger que cria a movimentação financeira)
                $stmt_pago = mysqli_prepare($conexao, "UPDATE pedido SET status = 'Pago' WHERE id = ?");
                mysqli_stmt_bind_param($stmt_pago, "i", $id_ped);
                mysqli_stmt_execute($stmt_pago);
                
                mysqli_commit($conexao);
                echo json_encode(['sucesso' => true, 'id_pedido' => $id_ped]);
                
            } catch (Exception $e) {
                mysqli_rollback($conexao);
                echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
            }
            exit; // Interrompe o script para não renderizar o HTML no retorno do AJAX
        }
    }
    // -------------------------------------------------------------------------

    $idFuncionario = $_SESSION['user_id'];
    $nomeFuncionario = $_SESSION['user_nome'];

    include 'includes/header.php';

    $filtroNome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
    $filtroFornecedor = isset($_GET['fornecedor']) ? intval($_GET['fornecedor']) : 0;
    $filtroMarca = isset($_GET['marca']) ? intval($_GET['marca']) : 0;

    $clientes = mysqli_query($conexao, "SELECT id, nome, cpf FROM cliente");

    $fornecedores = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM fornecedor ORDER BY nome ASC"), MYSQLI_ASSOC);
    $marcas = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM marca ORDER BY nome ASC"), MYSQLI_ASSOC);
    $categorias = mysqli_fetch_all(mysqli_query($conexao, "SELECT id, nome FROM categoria ORDER BY nome ASC"), MYSQLI_ASSOC);

    $sql = "SELECT 
        p.id, p.nome, p.preco, p.id_cat, 
        p.custo, p.id_forn, p.id_marca,
        c.nome AS categoria,
        f.nome AS fornecedor,
        m.nome AS marca,
        e.qtd FROM produto p 
        JOIN estoque e ON p.id = e.id_prod
        JOIN categoria c ON p.id_cat = c.id
        JOIN fornecedor f ON p.id_forn = f.id
        JOIN marca m ON p.id_marca = m.id
        WHERE 1 = 1 AND e.qtd > 0";

    if ($filtroNome !== '') {
        $sql .= " AND p.nome LIKE '%" . mysqli_real_escape_string($conexao, $filtroNome) . "%'";
    }
    if ($filtroFornecedor > 0) {
        $sql .= " AND p.id_forn = $filtroFornecedor";
    }
    if ($filtroMarca > 0) {
        $sql .= " AND p.id_marca = $filtroMarca";
    }

    $produtos = mysqli_query($conexao, $sql);

    $custo_total = mysqli_fetch_assoc(
        mysqli_query($conexao,"SELECT SUM(p.custo * e.qtd) as total FROM produto p JOIN estoque e ON p.id = e.id_prod WHERE e.qtd > 0")
    )['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Caixa</title>
<link rel="stylesheet" href="public/css/caixa.css">
<link rel="stylesheet" href="public/css/css.css">
</head>
<body>
    <div class="wrap">
        <h1>Caixa</h1>

        <!-- TELA 1: Seleção de Produtos -->
        <div id="tela1" class="grid">

            <div class="card">
                <h2>Produtos</h2>

                <form method="GET">
                    <label>Buscar Pelo Nome do Produto</label>
                    <input type="text" name="nome" placeholder="Ex: Nome do produto."
                    value="<?= htmlspecialchars($filtroNome)?>">

                    <select name="fornecedor">
                        <option value="0">Todas os Fornecedores</option>
                        <?php foreach($fornecedores as $f): ?>
                            <option value="<?= $f['id']?>" <?= ($filtroFornecedor == $f['id']) ? 'selected' : '' ?>>
                                <?= $f['nome']?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="marca">
                        <option value="0">Todas as Marcas</option>
                        <?php foreach($marcas as $m): ?>
                            <option value="<?= $m['id']?>" <?= ($filtroMarca == $m['id']) ? 'selected' : '' ?>>
                                <?= $m['nome']?>
                            </option>
                        <?php endforeach;?>
                    </select>

                    <button type="submit">Filtrar</button>
                </form>

                <div class="table">
                    <div class="thead">
                        <div>Código</div>
                        <div>Produto</div>
                        <div>Marca</div>
                        <div>Fornecedor</div>
                        <div>Preço</div>
                        <div>Estoque</div>
                        <div></div>
                    </div>

                    <div class="tbody">
                        <?php while($p = mysqli_fetch_assoc($produtos)) { ?>
                            <div class="tr">
                                <div><?php echo $p['id']; ?></div>
                                <div><?php echo $p['nome']; ?></div>
                                <div><?php echo $p['marca']; ?></div>
                                <div><?php echo $p['fornecedor']; ?></div>
                                <div><?php echo number_format($p['preco'],2,',','.'); ?></div>
                                <div id="est_<?php echo $p['id']; ?>">
                                    <?php echo $p['qtd']; ?>
                                </div>
                                <div>
                                    <button class="botaoAdd" 
                                    onclick="addCarrinho(
                                    '<?php echo $p['id']; ?>',
                                    '<?php echo addslashes($p['nome']); ?>',
                                    <?php echo $p['preco']; ?>,
                                    <?php echo $p['custo']; ?>)">
                                        +
                                    </button>
                                    <button class="botaoAdd"
                                    onclick="removerCarrinho('<?php echo $p['id']; ?>')">
                                        -
                                    </button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Comanda <span id="num-comanda"></span></h2>
                <div id="lista-comanda"></div>
                <div>Total: <b id="total1">R$ 0,00</b></div>
                <button class="btn ok" onclick="irTela2()">Finalizar Pedido</button>
            </div>

        </div>

        <!-- TELA 2: Pagamento e Finalização -->
        <div id="tela2" class="grid" style="display:none;">

            <div class="card">
                <h2>Comanda</h2>
                <div id="resumo-comanda"></div>

                <h3 style="margin-top:15px;">Nota Fiscal</h3>
                <div id="nota"></div>
            </div>

            <div class="card">
                <h2>Pagamento</h2>

                <div>Total: <b id="total2">R$ 0,00</b></div>

                <!-- Adicionado a seleção de cliente obrigatoria para a tabela pedido -->
                <select id="cliente_id" style="margin-top: 10px;">
                    <option value="">Selecione o Cliente</option>
                    <?php while($cli = mysqli_fetch_assoc($clientes)) { ?>
                        <option value="<?= $cli['id']; ?>"><?= $cli['nome']; ?> (CPF: <?= $cli['cpf']; ?>)</option>
                    <?php } ?>
                </select>

                <select id="forma_pagamento" style="margin-top: 10px;">
                    <option value="">Forma de Pagamento</option>
                    <option>Dinheiro</option>
                    <option>Cartão</option>
                    <option>Pix</option>
                    <option>Cheque</option>
                </select>

                <div id="parcelas-area" style="display:none;">
                    <select id="parcelas">
                        <option value="1">1x</option>
                        <option value="2">2x</option>
                        <option value="3">3x</option>
                        <option value="4">4x</option>
                        <option value="5">5x</option>
                        <option value="6">6x</option>
                    </select>
                    <div id="valor-parcela"></div>
                </div>

                <input type="number" id="valor_pago" placeholder="Valor pago" style="margin-top: 10px;">
                <div>Troco: <b id="troco">R$ 0,00</b></div>

                <div id="msg" style="color: red; font-weight: bold; margin-top: 10px;"></div>
                
                <button id="btn-finalizar" class="btn ok" onclick="finalizar()">Finalizar Venda</button>
                <button class="btn" style="background:#ccc; color:#333;" onclick="voltarTela1()">Voltar</button>
            </div>
        </div>

        <!-- TELA 3: Financeiro -->
        <div id="tela3" class="grid" style="display:none;">
            <div class="card">
                <h2>Financeiro</h2>
                <div>Investimento atual:</div><b id="f_investido"></b><br><br>
                <div>Meta (investimento + 2000):</div><b id="f_meta"></b><br><br>
                <div>Total em caixa:</div><b id="f_total"></b><br><br>
                <div>Resultado:</div><b id="f_resultado"></b><br><br>
                <button class="btn ok" onclick="resetar()">Novo Atendimento</button>
            </div>
        </div>
    </div>
    
    <script>
        let carrinho = [];
        let totalInvestido = <?php echo $custo_total; ?>;
        let caixa = 0;

        let comanda = Math.floor(Math.random()*1000);
        document.getElementById('num-comanda').innerText = '#' + comanda;

        function dinheiro(v) { return 'R$ '+ v.toFixed(2).replace('.',','); }   

        function atualizar(){
            let html = '', total = 0;
            carrinho.forEach(i => {
                let sub = i.preco * i.qtd;
                total += sub;
                html += i.nome + " x" + i.qtd + " - " + dinheiro(sub) + "<br>";
            });
            document.getElementById('lista-comanda').innerHTML = html;
            document.getElementById('total1').innerText = dinheiro(total);
        }

        function addCarrinho(c,n,p,ct){
            let est = document.getElementById('est_'+c);
            if(parseInt(est.innerText) <= 0) return alert('Sem estoque para este produto.');

            est.innerText = parseInt(est.innerText) - 1;
            let i = carrinho.find(x => x.cod == c);
            if(i) i.qtd++; else carrinho.push({cod:c, nome:n, preco:p, custo:ct, qtd:1});
            atualizar();
        }

        function removerCarrinho(c){
            let i = carrinho.find(x => x.cod == c);
            if(!i || i.qtd <= 0){
                alert('Este produto não está no carrinho');
                return;
            }

            let est = document.getElementById('est_'+c);
            est.innerText = parseInt(est.innerText) + 1;
            i.qtd--;
            
            if(i.qtd === 0){
                carrinho = carrinho.filter(x => x.cod != c);
            }
            atualizar();
        }

        function irTela2(){
            if(carrinho.length == 0) {
                alert("O carrinho está vazio!");
                return;
            }

            document.getElementById('tela1').style.display='none';
            document.getElementById('tela2').style.display='grid';

            let total = 0, resumo = "";
            carrinho.forEach(i => {
                let sub = i.preco * i.qtd;
                total += sub;
                resumo += i.nome+" x"+i.qtd+" - "+dinheiro(sub)+"<br>";
            });

            document.getElementById('resumo-comanda').innerHTML = resumo;
            document.getElementById('nota').innerHTML = "";
            document.getElementById('total2').innerText = dinheiro(total);
        }

        function voltarTela1() {
            document.getElementById('tela2').style.display = 'none';
            document.getElementById('tela1').style.display = 'grid';
            document.getElementById('msg').innerText = '';
        }

        document.getElementById('forma_pagamento').addEventListener('change',function(){
            let f = this.value;
            let area = document.getElementById('parcelas-area');
            let campo = document.getElementById('valor_pago');

            if(f === "Cartão"){
                area.style.display='block';
                calcularParcelas();
            } else{
                area.style.display='none';
                campo.readOnly=false;
                campo.value='';
                document.getElementById('valor-parcela').innerText='';
            }
        });

        document.getElementById('parcelas').addEventListener('change', calcularParcelas);

        function calcularParcelas(){
            let total = parseFloat(document.getElementById('total2').innerText.replace('R$ ','').replace(',','.'))||0;
            let p = parseInt(document.getElementById('parcelas').value);
            let valor = total / p;

            document.getElementById('valor-parcela').innerText = p+"x de "+dinheiro(valor);

            let campo = document.getElementById('valor_pago');
            campo.value = valor.toFixed(2);
            campo.readOnly = true;
            document.getElementById('troco').innerText = 'R$ 0,00';
        }

        document.getElementById('valor_pago').addEventListener('input',function(){
            let f = document.getElementById('forma_pagamento').value;
            if(f === "Cartão") return;

            let total = parseFloat(document.getElementById('total2').innerText.replace('R$ ','').replace(',','.'))||0;
            let pago = parseFloat(this.value)||0;
            let troco = pago - total;

            document.getElementById('troco').innerText = troco > 0 ? dinheiro(troco) : 'R$ 0,00';
        });

        // FUNÇÃO ATUALIZADA COM FETCH API PARA COMUNICAR COM O BANCO
        async function finalizar(){
            let forma = document.getElementById('forma_pagamento').value;

            let clienteSelect = document.getElementById('cliente_id');
            let cliente_id = clienteSelect.value;

            let btn = document.getElementById('btn-finalizar');
            let msgBox = document.getElementById('msg');

            if(!cliente_id){
                msgBox.innerText = 'Selecione o Cliente antes de finalizar.';
                return;
            }
            if(!forma){
                msgBox.innerText = 'Escolha a forma de pagamento.';
                return;
            }

            let nomeCliente = clienteSelect.options[clienteSelect.selectedIndex].text;

            msgBox.style.color = 'blue';
            msgBox.innerText = 'Processando venda...';
            btn.disabled = true;

            let payload = {
                acao: 'finalizar_venda',
                id_cliente: cliente_id,
                forma_pagamento: forma,
                carrinho: carrinho
            };

            try {
                // Envia para o bloco PHP no topo deste arquivo
                let response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                
                let res = await response.json();

                if(res.sucesso) {
                    let total=0, custo=0, notaHtml="";

                    carrinho.forEach(i => {
                        let sub = i.preco * i.qtd;
                        total += sub;
                        custo += i.custo * i.qtd;
                        notaHtml += i.nome + " x" + i.qtd + " - " + dinheiro(sub) + "<br>";
                    });

                    caixa += total;
                    totalInvestido -= custo;
                    
                    let nomeVendedor = "<?php echo addslashes($nomeFuncionario); ?>";

                    document.getElementById('nota').innerHTML = `
                    <b>Nº do Pedido (Banco):</b> #${res.id_pedido}<br>
                    <b>Vendedor:</b> ${nomeVendedor} <br>
                    <b>Cliente:</b> ${nomeCliente} <br><br>
                    ${notaHtml}
                    <br><b>Total:</b> ${dinheiro(total)}
                    `;

                    msgBox.style.color = 'green';
                    msgBox.innerText = 'Venda gravada no sistema com sucesso!';

                    setTimeout(resetar, 4000);
                } else {
                    msgBox.style.color = 'red';
                    msgBox.innerText = 'Erro do banco: ' + res.erro;
                    btn.disabled = false;
                }
            } catch (error) {
                msgBox.style.color = 'red';
                msgBox.innerText = 'Erro na comunicação com o servidor.';
                btn.disabled = false;
            }
        }

        function irFinanceiro(){
            let meta = totalInvestido + 2000;
            let resultado = caixa - totalInvestido;

            document.getElementById('tela2').style.display='none';
            document.getElementById('tela3').style.display='grid';

            document.getElementById('f_investido').innerText = dinheiro(totalInvestido);
            document.getElementById('f_meta').innerText = dinheiro(meta);
            document.getElementById('f_total').innerText = dinheiro(caixa);

            if(resultado >= 0){
                document.getElementById('f_resultado').innerText="Lucro: "+dinheiro(resultado);
                document.getElementById('f_resultado').style.color="green";
            } else{
                document.getElementById('f_resultado').innerText="Prejuízo: "+dinheiro(resultado);
                document.getElementById('f_resultado').style.color="red";
            }
        }

        function resetar(){
            carrinho = [];
            document.getElementById('btn-finalizar').disabled = false;
            document.getElementById('msg').innerText = '';
            document.getElementById('cliente_id').value = '';
            document.getElementById('forma_pagamento').value = '';
            document.getElementById('valor_pago').value = '';
            document.getElementById('troco').innerText = 'R$ 0,00';
            document.getElementById('resumo-comanda').innerHTML = '';
            document.getElementById('nota').innerHTML = '';

            document.getElementById('tela3').style.display='none';
            document.getElementById('tela1').style.display='grid';

            document.getElementById('lista-comanda').innerHTML='';
            document.getElementById('total1').innerText='R$ 0,00';

            comanda = Math.floor(Math.random()*1000);
            document.getElementById('num-comanda').innerText='#'+comanda;
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>