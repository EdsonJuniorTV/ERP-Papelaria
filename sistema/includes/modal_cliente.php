<!-- ===== MODAL: Cadastro / Edição de Cliente ===== -->
<div id="modal-overlay" onclick="fecharModal()" 
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:999; backdrop-filter:blur(2px);">
</div>

<div id="modal-cliente" 
     style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
            background:#fff; border-radius:16px; padding:32px; width:min(95vw, 680px);
            max-height:90vh; overflow-y:auto; z-index:1000; box-shadow:0 20px 60px rgba(0,0,0,.2);">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 id="modal-titulo-cliente" style="margin:0;">👥 Novo Cliente</h2>
        <button onclick="fecharModal()" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:#6b7280;">✕</button>
    </div>

    <form id="form-cliente" data-method="post">
        <input type="hidden" name="tipo_entidade" id="tipo_entidade" value="cliente">
        <input type="hidden" name="id" id="cli_id" value="">

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label>Nome Completo <span>*</span></label>
                <input type="text" name="nome" id="cli_nome" placeholder="Ex: João Silva" required>
            </div>

            <div class="form-group">
                <label>CPF <span>*</span></label>
                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required>
            </div>

            <div class="form-group">
                <label>Data de Nascimento <span>*</span></label>
                <input type="date" name="dt_nasc" id="cli_dt_nasc" required>
            </div>

            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" id="cli_email" placeholder="exemplo@email.com">
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

        <div style="margin-top:24px; display:flex; gap:12px; justify-content:flex-end;">
            <button type="button" onclick="fecharModal()" 
                    style="padding:10px 20px; border:1.5px solid #e2e8f0; border-radius:8px; background:#fff; color:#374151; cursor:pointer; font-weight:600;">
                Cancelar
            </button>
            <button type="submit" class="btn-submit" style="margin:0;">
                Salvar Cliente
            </button>
        </div>
    </form>
</div>