<!-- ===== MODAL: Cadastro / Edição de Funcionário ===== -->
<div id="modal-overlay" onclick="fecharModal()" 
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:999; backdrop-filter:blur(2px);">
</div>

<div id="modal-funcionario" 
     style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
            background:#fff; border-radius:16px; padding:32px; width:min(95vw, 680px);
            max-height:90vh; overflow-y:auto; z-index:1000; box-shadow:0 20px 60px rgba(0,0,0,.2);">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 id="modal-titulo" style="margin:0;">👔 Novo Funcionário</h2>
        <button onclick="fecharModal()" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:#6b7280;">✕</button>
    </div>

    <form id="form" data-method="post">
        <input type="hidden" name="tipo_entidade" id="tipo_entidade" value="funcionario">
        <input type="hidden" name="id" id="func_id" value="">

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label>Nome Completo <span style="color:red;">*</span></label>
                <input type="text" name="nome" id="func_nome" placeholder="Nome do colaborador" required>
            </div>

            <div class="form-group">
                <label>CPF <span style="color:red;">*</span></label>
                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required>
            </div>

            <div class="form-group">
                <label>Cargo / Nível de Acesso <span style="color:red;">*</span></label>
                <select name="id_cargo" id="func_cargo" required>
                    <option value="">Selecione...</option>
                    <?php 
                    mysqli_data_seek($cargos, 0);
                    while ($cargo = mysqli_fetch_assoc($cargos)): ?>
                        <option value="<?= $cargo['id'] ?>"><?= htmlspecialchars($cargo['nome']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Login de Acesso <span style="color:red;">*</span></label>
                <input type="text" name="login" id="func_login" placeholder="Ex: joao.vendas" required>
            </div>

            <div class="form-group" id="campo-senha">
                <label>Senha <span style="color:red;">*</span> <small id="hint-senha" style="color:#9ca3af;">(obrigatória no cadastro)</small></label>
                <input type="password" name="senha" id="func_senha">
            </div>

            <div class="form-group">
                <label>Data de Nascimento</label>
                <input type="date" name="dt_nasc" id="func_dt_nasc">
            </div>

            <div class="form-group">
                <label>Data de Admissão</label>
                <input type="date" name="dt_admissao" id="func_dt_admissao">
            </div>

            <div class="form-group">
                <label>Telefone</label>
                <input type="text" id="fone" name="fone" placeholder="(00) 00000-0000" maxlength="15">
            </div>

            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" id="func_email" placeholder="email@exemplo.com">
            </div>

            <div class="form-group form-group-full">
                <label>CEP</label>
                <input type="text" name="cep" id="cep" placeholder="Ex: 00000-000" maxlength="9">
            </div>
            <div class="form-group">
                <label>Endereço Residencial</label>
                <input type="text" name="logradouro" id="logradouro" placeholder="Ex: Rua Salvador Dali, 1-40" readonly>
            </div>
            <div class="form-group">
                <label>Rua</label>
                <input type="text" name="rua" id="rua" placeholder="Ex: Rua dos Barcos">
            </div>
            <div class="form-group">
                <label>Número</label>
                <input type="text" name="numero" id="numero" placeholder="Ex: 2-30">
            </div>
            <div class="form-group">
                <label>Bairro</label>
                <input type="text" name="bairro" id="bairro" placeholder="Ex: Sertão do Norte">
            </div>
            <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="cidade" id="cidade" placeholder="Ex: São José do Rio Preto">
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
            <button type="submit" class="btn-submit" id="btn-salvar" style="margin:0;">
                💾 Salvar Funcionário
            </button>
        </div>
    </form>
</div>