<!-- ===== MODAL: Cadastro / Edição de Fornecedor ===== -->
<div id="modal-overlay" onclick="fecharModal()" 
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:999; backdrop-filter:blur(2px);">
</div>

<div id="modal-fornecedor" 
     style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
            background:#fff; border-radius:16px; padding:32px; width:min(95vw, 680px);
            max-height:90vh; overflow-y:auto; z-index:1000; box-shadow:0 20px 60px rgba(0,0,0,.2);">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 id="modal-titulo-fornecedor" style="margin:0;">🚚 Novo Fornecedor</h2>
        <button onclick="fecharModal()" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:#6b7280;">✕</button>
    </div>

    <form id="form-fornecedor" data-method="post">
        <input type="hidden" name="tipo_entidade" id="tipo_entidade" value="fornecedor">
        <input type="hidden" name="id" id="forn_id" value="">

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label>Razão Social / Nome Fantasia <span>*</span></label>
                <input type="text" name="nome" id="forn_nome" placeholder="Ex: Distribuidora de Papéis Ltda" required>
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
                <input type="email" name="email" id="forn_email" placeholder="vendas@fornecedor.com">
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
        
        <div style="margin-top:24px; display:flex; gap:12px; justify-content:flex-end;">
            <button type="button" onclick="fecharModal()" 
                    style="padding:10px 20px; border:1.5px solid #e2e8f0; border-radius:8px; background:#fff; color:#374151; cursor:pointer; font-weight:600;">
                Cancelar
            </button>
            <button type="submit" class="btn-submit" style="margin:0;">
                💾 Salvar Fornecedor
            </button>
        </div>
    </form>
</div>