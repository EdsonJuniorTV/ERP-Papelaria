<?php 
require_once 'includes/auth.php';
require_once 'config/conexao.php';
include 'includes/header.php'; 
?>

<main>
    <div class="container">
        <div class="header" style="background: #2c3e50;">
            <h1>🚚 Gestão de Fornecedores</h1>
            <p>Cadastre as empresas que fornecem seus produtos</p>
        </div>

        <form id="form" data-method="post">
            <input type="hidden" name="tipo_entidade" value="fornecedor">

            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label>Razão Social / Nome Fantasia <span>*</span></label>
                    <input type="text" name="nome" placeholder="Ex: Distribuidora de Papéis Ltda" required>
                </div>
                
                <div class="form-group">
                    <label>CNPJ <span>*</span></label>
                    <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0001-00" required>
                </div>

                <div class="form-group">
                    <label>Telefone Comercial</label>
                    <input type="text" id="fone" name="fone">
                </div>

                <div class="form-group">
                    <label>E-mail de Contato</label>
                    <input type="email" name="email" placeholder="vendas@fornecedor.com">
                </div>

                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" id="cep" name="cep">
                </div>

                <div class="form-group form-group-full">
                    <label>Endereço Completo</label>
                    <input type="text" name="logradouro" placeholder="Rua, Número, Bairro">
                </div>
                
                <input type="hidden" name="cidade" value="Bauru">
                <input type="hidden" name="estado" value="SP">
            </div>

            <button type="submit" class="btn-submit">💾 Cadastrar Fornecedor</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>