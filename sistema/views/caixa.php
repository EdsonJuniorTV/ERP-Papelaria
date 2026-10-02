<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['user_id'])) {
        header("Location: index.php?erro=2");
        exit;
    }

    require_once '../config/conexao.php';

    $idFuncionario =$_SESSION['user_id'];
    $nomeFuncionario =$_SESSION['user_nome'];

    include '../includes/header.php';

    $filtroNome = isset($_GET['nome']) ? trim($_GET['nome']) : '';$filtroFornecedor = isset($_GET['fornecedor']) ? intval($_GET['fornecedor']) : 0;
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

    if ($filtroNome !== '') {$sql .= " AND p.nome LIKE '%" . mysqli_real_escape_string($conexao,$filtroNome) . "%'";
    }
    if ($filtroFornecedor > 0) {$sql .= " AND p.id_forn = $filtroFornecedor";
    }
    if ($filtroMarca > 0) {$sql .= " AND p.id_marca = $filtroMarca";
    }

    $produtos = mysqli_query($conexao,$sql);

    $custo_total = mysqli_fetch_assoc(
        mysqli_query($conexao,"SELECT SUM(p.custo * e.qtd) as total FROM produto p JOIN estoque e ON p.id = e.id_prod WHERE e.qtd > 0")
    )['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Caixa</title>
<link rel="stylesheet" href="/ERP-papelaria/sistema/public/css/caixa.css">
<link rel="stylesheet" href="/ERP-papelaria/sistema/public/css/css.css">
</head>
<body>
    <div class="wrap" style="max-width: 1300px;">
        <div style="background-color: #1a56db; color: #ffffff; padding: 22px 28px; border-radius: 10px; 
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="color: #ffffff; margin: 0; font-size: 1.3rem;">Caixa</h1>
        </div>

        <!-- TELA 1: Seleção de Produtos -->
        <div id="tela1" class="grid">

            <div class="card">
                <h2>Produtos</h2>

                <form method="GET">
                    <div style="padding: 10px">
                        <label>Buscar Pelo Nome do Produto</label>
                        <input type="text" name="nome" placeholder="Ex: Nome do produto." value="<?= htmlspecialchars($filtroNome)?>">
                    </div>
                    <div style="padding: 10px">
                        <select name="fornecedor">
                            <option value="0">Todas os Fornecedores</option>
                            <?php foreach($fornecedores as$f): ?>
                                <option value="<?= $f['id']?>" <?= ($filtroFornecedor ==$f['id']) ? 'selected' : '' ?>>
                                    <?= $f['nome']?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="padding: 10px">
                        <select name="marca">
                            <option value="0">Todas as Marcas</option>
                            <?php foreach($marcas as$m): ?>
                                <option value="<?= $m['id']?>" <?= ($filtroMarca ==$m['id']) ? 'selected' : '' ?>>
                                    <?= $m['nome']?>
                                </option>
                            <?php endforeach;?>
                        </select>
                    </div>

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
                        <div>Ações</div>
                    </div>

                    <div class="tbody" style="max-height: 350px; overflow-y: auto;">
                        <?php while($p = mysqli_fetch_assoc($produtos)) { ?>
                            <div class="tr" style="display: grid; grid-template-columns: 60px 2.5fr 1.2fr 2fr 100px 80px 50px; 
                            align-items: center; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; gap: 15px;">
                                <div><?php echo $p['id']; ?></div>
                                <div><?php echo $p['nome']; ?></div>
                                <div><?php echo $p['marca']; ?></div>
                                <div><?php echo $p['fornecedor']; ?></div>
                                <div><?php echo number_format($p['preco'],2,',','.'); ?></div>
                                <div id="est_<?php echo $p['id']; ?>">
                                    <?php echo $p['qtd']; ?>
                                </div>
                                <div>
                                    <div style="padding: 2px">
                                        <button class="botaoAdd" 
                                        onclick="addCarrinho(
                                        '<?php echo $p['id']; ?>',
                                        '<?php echo addslashes($p['nome']); ?>',
                                        <?php echo $p['preco']; ?>,
                                        <?php echo $p['custo']; ?>)">
                                            +
                                        </button>
                                    </div>
                                    <div style="padding: 2px">
                                        <button class="botaoAdd"
                                        onclick="removerCarrinho('<?php echo $p['id']; ?>')">
                                            -
                                        </button>
                                    </div>
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
                <button class="btn-success" onclick="irTela2()">Finalizar Pedido</button>
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
                        <option value="<?= $cli['id']; ?>"><?= $cli['nome']; ?> (CPF: <?=$cli['cpf']; ?>)</option>
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
                
                <button id="btn-finalizar" class="btn-success" onclick="finalizar()">Finalizar Venda</button>
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
        let totalVenda = 0;
        let custoVendasSession = 0; // Nova variável para trackear o custo apenas das vendas atuais

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
            if(carrinho.length === 0) {
                alert("O carrinho está vazio!");
                return;
            }

            totalVenda = carrinho.reduce((acc, item) => acc + (item.preco * item.qtd), 0);

            let resumo = "";
            carrinho.forEach(i => {
                let sub = i.preco * i.qtd;
                resumo += i.nome + " x" + i.qtd + " - " + dinheiro(sub) + "<br>";
            });

            document.getElementById('resumo-comanda').innerHTML = resumo;
            document.getElementById('nota').innerHTML = "";
            document.getElementById('total2').innerText = dinheiro(totalVenda);

            document.getElementById('cliente_id').value = '';
            document.getElementById('forma_pagamento').value = '';
            document.getElementById('parcelas-area').style.display = 'none';
            document.getElementById('valor_pago').value = '';
            document.getElementById('valor_pago').readOnly = false;
            document.getElementById('troco').innerText = 'R$ 0,00';
            document.getElementById('msg').innerText = '';
            document.getElementById('btn-finalizar').disabled = false;

            document.getElementById('tela1').style.display = 'none';
            document.getElementById('tela2').style.display = 'grid';
        }

        function voltarTela1() {
            document.getElementById('tela2').style.display = 'none';
            document.getElementById('tela1').style.display = 'grid';
            document.getElementById('msg').innerText = '';
        }

        document.getElementById('forma_pagamento').addEventListener('change', function(){
            let f = this.value;
            let area = document.getElementById('parcelas-area');
            let campo = document.getElementById('valor_pago');

            if(f === "Cartão"){
                area.style.display = 'block';
                calcularParcelas();
            } else {
                area.style.display = 'none';
                campo.readOnly = false;
                campo.value = '';
                document.getElementById('valor-parcela').innerText = '';
                document.getElementById('troco').innerText = 'R$ 0,00';
            }
        });

        document.getElementById('parcelas').addEventListener('change', calcularParcelas);

        function calcularParcelas(){
            let p = parseInt(document.getElementById('parcelas').value) || 1;
            let valor = totalVenda / p;

            document.getElementById('valor-parcela').innerText = p + "x de " + dinheiro(valor);

            let campo = document.getElementById('valor_pago');
            campo.value = valor.toFixed(2);
            campo.readOnly = true;
            document.getElementById('troco').innerText = 'R$ 0,00';
        }

        document.getElementById('valor_pago').addEventListener('input', function(){
            let f = document.getElementById('forma_pagamento').value;
            if(f === "Cartão") return;

            let pago = parseFloat(this.value) || 0;
            let troco = pago - totalVenda;

            document.getElementById('troco').innerText = troco > 0 ? dinheiro(troco) : 'R$ 0,00';
        });

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
                // Modificado para apontar para o novo arquivo da API 
                let response = await fetch('../controllers/venda.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                
                let res = await response.json();

                if(res.sucesso) {
                    let total = 0, custo = 0, notaHtml = "";

                    carrinho.forEach(i => {
                        let sub = i.preco * i.qtd;
                        total += sub;
                        custo += i.custo * i.qtd;
                        notaHtml += i.nome + " x" + i.qtd + " - " + dinheiro(sub) + "<br>";
                    });

                    caixa += total;
                    custoVendasSession += custo; // Soma os custos da venda no histórico da sessão
                    
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
            
            // Correção lógica: o Lucro da sessão do caixa é o dinheiro arrecadado MENOS os custos dos itens vendidos (e não do estoque total da loja)
            let resultado = caixa - custoVendasSession;

            document.getElementById('tela2').style.display = 'none';
            document.getElementById('tela3').style.display = 'grid';

            document.getElementById('f_investido').innerText = dinheiro(totalInvestido);
            document.getElementById('f_meta').innerText = dinheiro(meta);
            document.getElementById('f_total').innerText = dinheiro(caixa);

            if(resultado >= 0){
                document.getElementById('f_resultado').innerText = "Lucro: " + dinheiro(resultado);
                document.getElementById('f_resultado').style.color = "green";
            } else {
                document.getElementById('f_resultado').innerText = "Prejuízo: " + dinheiro(resultado);
                document.getElementById('f_resultado').style.color = "red";
            }
        }

        function resetar(){
            carrinho = [];
            totalVenda = 0;
            document.getElementById('btn-finalizar').disabled = false;
            document.getElementById('msg').innerText = '';
            document.getElementById('cliente_id').value = '';
            document.getElementById('forma_pagamento').value = '';
            document.getElementById('valor_pago').value = '';
            document.getElementById('troco').innerText = 'R$ 0,00';
            document.getElementById('resumo-comanda').innerHTML = '';
            document.getElementById('nota').innerHTML = '';

            document.getElementById('tela3').style.display = 'none';
            document.getElementById('tela2').style.display = 'none';
            document.getElementById('tela1').style.display = 'grid';

            document.getElementById('lista-comanda').innerHTML = '';
            document.getElementById('total1').innerText = 'R$ 0,00';

            comanda = Math.floor(Math.random()*1000);
            document.getElementById('num-comanda').innerText = '#' + comanda;
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>