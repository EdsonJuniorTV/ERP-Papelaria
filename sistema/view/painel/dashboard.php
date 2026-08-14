<?php
require_once 'includes/auth.php';
include 'includes/header.php';
$cargo = $_SESSION['user_cargo'];
?>

<main class="dashboard-container">
    <div class="header">
        <h1>👋 Olá, <?= $_SESSION['user_nome'] ?></h1>
        <p>Painel de Controle - Papelaria Interna</p>
    </div>

    <div class="grid-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
        
        <div class="card" onclick="location.href='caixa.php'" style="cursor:pointer; background: #3498db; color: white; padding: 30px; border-radius: 10px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">🛒</div>
            <h3>Frente de Caixa (PDV)</h3>
            <p>Realizar vendas e emitir pedidos</p>
        </div>

        <div class="card" onclick="location.href='cadastro.php'" style="cursor:pointer; background: #1abc9c; color: white; padding: 30px; border-radius: 10px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">👥</div>
            <h3>Novo Cliente</h3>
            <p>Cadastrar clientes no sistema</p>
        </div>

        <div class="card" onclick="location.href='estoque.php'" style="cursor:pointer; background: #2c3e50; color: white; padding: 30px; border-radius: 10px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">📦</div>
            <h3>Gestão de Estoque</h3>
            <p>Reposição e inventário</p>
        </div>

        <div class="card" onclick="location.href='configuracoes.php'" style="cursor:pointer; background: #7f8c8d; color: white; padding: 30px; border-radius: 10px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">⚙️</div>
            <h3>Configurações</h3>
            <p>Categorias, Marcas e Cargos</p>
        </div>

        <div class="card" onclick="location.href='fornecedores_gestao.php'" style="cursor:pointer; background: #15929b; color: white; padding: 30px; border-radius: 10px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">🚚</div>
            <h3>Gestão de fornecedores</h3>
            <p>Cadastragem de fornecedores</p>
        </div>

        <div class="card" onclick="location.href='cadastrar_produto.php'" style="cursor:pointer; background: #b8670a; color: white; padding: 30px; border-radius: 10px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">✏️</div>
            <h3>Cadastro de Produtos</h3>
            <p>Adicionar novos produtos ao estoque</p>
        </div>


        <?php if ($cargo == 'Gerente' || $cargo == 'Programador'): ?>
            
            <div class="card" onclick="location.href='funcionarios_gestao.php'" style="cursor:pointer; background: #8e44ad; color: white; padding: 30px; border-radius: 10px; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 10px;">👔</div>
                <h3>Gestão de Equipe</h3>
                <p>Funcionários e Comissões</p>
            </div>

            <div class="card" onclick="location.href='financeiro.php'" style="cursor:pointer; background: #38b619; color: white; padding: 30px; border-radius: 10px; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 10px;">💵</div>
                <h3>Faturamento da Empresa</h3>
                <p>Vendas, Compras e saldo líquido</p>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php include 'includes/footer.php'; ?>