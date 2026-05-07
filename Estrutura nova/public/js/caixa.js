let carrinho = [];
const listaItens = document.getElementById('lista_itens');
const totalDisplay = document.getElementById('total_venda');

function adicionarAoCarrinho() {
    const input = document.getElementById('busca_prod');
    const qtdInput = document.getElementById('qtd_item');
    const lista = document.getElementById('lista_produtos');
    
    // Encontra a opção selecionada no datalist para pegar ID e Preço de forma segura
    const opcao = Array.from(lista.options).find(opt => opt.value === input.value);

    if (opcao) {
        const item = {
            id: opcao.dataset.id,
            nome: input.value,
            preco: parseFloat(opcao.dataset.preco),
            qtd: parseInt(qtdInput.value)
        };

        carrinho.push(item);
        atualizarInterface();
        
        // Limpa campos e devolve o foco para agilizar o trabalho do caixa
        input.value = "";
        qtdInput.value = 1;
        input.focus();
    } else {
        alert("Produto não encontrado na lista. Selecione um item válido.");
    }
}

function atualizarInterface() {
    listaItens.innerHTML = "";
    let totalGeral = 0;

    carrinho.forEach((item, index) => {
        const subtotal = item.preco * item.qtd;
        totalGeral += subtotal;

        // Visual melhorado para os itens do carrinho
        listaItens.innerHTML += `
            <div class="item-carrinho" style="display:flex; justify-content:space-between; align-items: center; padding:10px; border-bottom:1px solid #e2e8f0; background: #f7fafc; margin-bottom: 5px; border-radius: 4px;">
                <span><strong>${item.nome}</strong> (x${item.qtd})</span>
                <span>
                    R$ ${subtotal.toFixed(2)} 
                    <button type="button" onclick="removerItem(${index})" style="color:#e53e3e; border:none; background:none; cursor:pointer; font-weight:bold; margin-left: 10px;" title="Remover item">[X]</button>
                </span>
            </div>
        `;
    });

    totalDisplay.innerText = `R$ ${totalGeral.toFixed(2)}`;
}

function removerItem(index) {
    carrinho.splice(index, 1);
    atualizarInterface();
}

async function finalizarVenda() {
    if (carrinho.length === 0) return alert("O carrinho está vazio!");

    // Bloqueia duplo clique
    const btnFinalizar = document.querySelector('button[onclick="finalizarVenda()"]');
    if(btnFinalizar) btnFinalizar.disabled = true;

    // Busca o cliente (se o campo não existir, envia null)
    const idClienteInput = document.getElementById('id_cliente');
    const id_cli = idClienteInput && idClienteInput.value !== "" ? idClienteInput.value : null;

    const dadosVenda = {
        id_cli: id_cli,
        id_func: 1 // Nota: O ID do funcionário deve vir da sessão (já tratado no back-end)
    };

    try {
        // PASSO 1: Criar o Pedido Master
        const resPedido = await fetch('controllers/salvar_venda.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dadosVenda)
        });

        const resultPedido = await resPedido.json();
        
        if (!resultPedido.status || !resultPedido.id_pedido) {
            throw new Error(resultPedido.msg || "Falha ao gerar a ordem de venda.");
        }

        const id_pedido = resultPedido.id_pedido;

        // PASSO 2: Inserir cada item no pedido (disparando os Triggers do Banco)
        for (const item of carrinho) {
            // Formata os dados para o baixar_estoque.php que espera $_POST tradicional
            const formItem = new URLSearchParams();
            formItem.append('id_pedido', id_pedido);
            formItem.append('id_prod', item.id);
            formItem.append('qtd', item.qtd);
            formItem.append('preco', item.preco);

            const resItem = await fetch('controllers/baixar_estoque.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formItem.toString()
            });

            const resultItem = await resItem.json();

            // Se o Trigger do banco barrar por falta de estoque, lança um erro
            if (resultItem.status !== "sucesso") {
                throw new Error(`Erro ao lançar ${item.nome}: ${resultItem.msg}`);
            }
        }

        alert("✅ Venda finalizada com sucesso!");
        location.reload();

    } catch (error) {
        console.error("Erro na Venda:", error);
        alert("⚠️ Atenção: " + error.message);
        if(btnFinalizar) btnFinalizar.disabled = false;
    }
}