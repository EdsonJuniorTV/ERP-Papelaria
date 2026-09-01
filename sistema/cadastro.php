<?php 
require_once 'includes/auth.php'; // Garante que só usuários logados acessem
include 'includes/header.php';    // Traz o Menu e o CSS
?>

<main>
    <div class="container">
        <div class="header">
            <h1>👥 Cadastro de Clientes</h1>
            <p>Preencha todas as informações para manter a base de dados atualizada.</p>
        </div>

        <form id="form" data-method="post">
            <input type="hidden" name="tipo_entidade" value="cliente">

            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label>Nome Completo <span>*</span></label>
                    <input type="text" name="nome" placeholder="Ex: João Silva" required>
                </div>

                <div class="form-group">
                    <label>CPF <span>*</span></label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required>
                </div>

                <div class="form-group">
                    <label>Data de Nascimento <span>*</span></label>
                    <input type="date" name="dt_nasc" required>
                </div>

                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" placeholder="exemplo@email.com">
                </div>

                <div class="form-group">
                    <label>Telefone / WhatsApp</label>
                    <input type="text" id="fone" name="fone" placeholder="(00) 00000-0000" maxlength="15">
                </div>

                <div class="form-group form-group-full" style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 10px;">
                    <label><strong>Endereço de Entrega/Cobrança</strong></label>
                </div>
                
                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9">
                </div>

                <div class="form-group">
                    <label>Logradouro (Rua, Nº, Bairro)</label>
                    <input type="text" name="logradouro" id="logradouro" placeholder="Ex: Rua das Flores, 123 - Jardim Centro" readonly>
                </div>

                <div class="form-group">
                    <label>Rua</label>
                    <input type="text" name="rua" id="rua" placeholder="Ex: Rua das moedas">
                </div>
                
                <div class="form-group">
                    <label>Número</label>
                    <input type="text" name="numero" id="numero" placeholder="Ex: 2-10">
                </div>

                <div class="form-group">
                    <label>Bairro</label>
                    <input type="text" name="bairro" id="bairro" placeholder="Ex: Jardim Ferdinando">
                </div>

                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="cidade" id="cidade" placeholder="Ex: Bauru">
                </div>

                <div class="form-group">
                    <label>Estado (UF)</label>
                    <input type="text" name="estado" id="estado" placeholder="Ex: SP">
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">💾 Salvar Cadastro de Cliente</button>
                <button type="reset" class="btn-submit" style="background: #95a5a6; margin-left: 10px;">Limpar</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; // Fecha as tags body e html e traz o cadastrar.js ?>