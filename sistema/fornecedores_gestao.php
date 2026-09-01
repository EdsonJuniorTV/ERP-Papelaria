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
                    <input type="text" id="cnpj" name="cnpj" placeholder="Ex: 00.000.000/0000-00" maxlength="18" required>
                </div>

                <div class="form-group">
                    <label>Telefone Comercial</label>
                    <input type="text" id="fone" name="fone" placeholder="Ex: (00)00000-0000" maxlength="15">
                </div>

                <div class="form-group">
                    <label>E-mail de Contato</label>
                    <input type="email" name="email" placeholder="vendas@fornecedor.com">
                </div>

                <div class="form-group form-group-full" style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 10px;">
                    <label>CEP</label>
                    <input type="text" id="cep" name="cep" placeholder="Ex: 00000-000" maxlength="9">
                </div>

                <div class="form-group">
                    <label>Endereço Completo</label>
                    <input type="text" name="logradouro" id="logradouro" placeholder="Ex: Rua Fernandes, 8-90 - Monte tupiniquín" readonly>
                </div>

                <div class="form-group">
                    <label>Rua</label>
                    <input type="text" name="rua" id="rua" placeholder="Ex: Rua da quinta da bela holinda">
                </div>

                <div class="form-group">
                    <label>Número</label>
                    <input type="text" name="numero" id="numero" placeholder="Ex: 3-40">
                </div>

                <div class="form-group">
                    <label>Bairro</label>
                    <input type="text" name="bairro" id="bairro" placeholder="Ex: Jardim Elementar">
                </div>
                
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="cidade" id="cidade" placeholder="Ex: Piratininga">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <input type="text" name="estado" id="estado" placeholder="Ex: SP">
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">💾 Cadastrar Fornecedor</button>
                <button type="reset" class="btn-submit" style="background: #95a5a6; margin-left: 10px;">Limpar</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>