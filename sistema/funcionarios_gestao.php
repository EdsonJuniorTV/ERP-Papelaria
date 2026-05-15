<?php 
require_once 'includes/auth.php';
require_once 'config/conexao.php';
verificarPermissao(['Gerente', 'Programador']);
include 'includes/header.php';

$cargos = mysqli_query($conexao, "SELECT * FROM cargo ORDER BY nome ASC");

$funcionarios = mysqli_fetch_all(
    mysqli_query($conexao, "
        SELECT f.id, f.nome, f.login, f.cpf, f.fone, f.email, f.status,
               c.nome AS cargo, f.id_cargo
        FROM funcionario f
        JOIN cargo c ON f.id_cargo = c.id
        ORDER BY f.nome ASC
    "),
    MYSQLI_ASSOC
);
?>

<main>
    <div class="container">

        <div class="header" style="background: #8e44ad; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h1>👔 Gestão de Funcionários</h1>
                <p style="color:rgba(255,255,255,.75); margin:0;">Cadastre, edite e gerencie os colaboradores</p>
            </div>
            <button onclick="abrirModal()" 
                    style="background:white; color:#8e44ad; border:none; padding:10px 20px; border-radius:8px; font-weight:700; cursor:pointer; font-size:.9rem;">
                + Novo Funcionário
            </button>
        </div>

        <div style="overflow-x:auto; margin-top:24px;">
            <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08);">
                <thead>
                    <tr style="background:#f3f4f6; text-align:left;">
                        <th style="padding:12px 16px; font-size:.82rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Nome</th>
                        <th style="padding:12px 16px; font-size:.82rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Login</th>
                        <th style="padding:12px 16px; font-size:.82rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Cargo</th>
                        <th style="padding:12px 16px; font-size:.82rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Telefone</th>
                        <th style="padding:12px 16px; font-size:.82rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Status</th>
                        <th style="padding:12px 16px; font-size:.82rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($funcionarios)): ?>
                        <tr>
                            <td colspan="6" style="padding:30px; text-align:center; color:#9ca3af;">
                                Nenhum funcionário cadastrado ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($funcionarios as $f): ?>
                            <tr style="border-top:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                                <td style="padding:13px 16px; font-weight:600; color:#111827;"><?= htmlspecialchars($f['nome']) ?></td>
                                <td style="padding:13px 16px; color:#6b7280; font-size:.88rem;"><?= htmlspecialchars($f['login']) ?></td>
                                <td style="padding:13px 16px;">
                                    <span style="background:#ede9fe; color:#7e3af2; padding:3px 10px; border-radius:20px; font-size:.8rem; font-weight:600;">
                                        <?= htmlspecialchars($f['cargo']) ?>
                                    </span>
                                </td>
                                <td style="padding:13px 16px; color:#6b7280; font-size:.88rem;"><?= htmlspecialchars($f['fone']) ?></td>
                                <td style="padding:13px 16px;">
                                    <?php if ($f['status'] === 'Ativo'): ?>
                                        <span style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-size:.8rem; font-weight:600;">✔ Ativo</span>
                                    <?php else: ?>
                                        <span style="background:#fde8e8; color:#9b1c1c; padding:3px 10px; border-radius:20px; font-size:.8rem; font-weight:600;">✖ Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:13px 16px; display:flex; gap:8px; flex-wrap:wrap;">
                                    <button onclick='abrirEdicao(<?= json_encode($f) ?>)'
                                            style="background:#1a56db; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:.83rem; font-weight:600;">
                                        ✏️ Editar
                                    </button>
                                    <?php if ($f['status'] === 'Ativo'): ?>
                                        <button onclick="confirmarExclusao(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['nome'])) ?>')"
                                                style="background:#e02424; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:.83rem; font-weight:600;">
                                            🗑 Desativar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ===== MODAL: Cadastro / Edição ===== -->
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
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
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
                        <input type="text" id="fone" name="fone" placeholder="(00) 00000-0000">
                    </div>

                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" id="func_email" placeholder="email@exemplo.com">
                    </div>

                    <div class="form-group form-group-full">
                        <label>Endereço Residencial</label>
                        <input type="text" name="logradouro" id="func_logradouro" placeholder="Rua, Número, Bairro">
                    </div>

                    <input type="hidden" name="cidade" value="Bauru">
                    <input type="hidden" name="estado" value="SP">
                    <input type="hidden" name="cep" value="00000000">
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

    </div>
</main>

<script>
function abrirModal() {
    document.getElementById('modal-titulo').textContent = '👔 Novo Funcionário';
    document.getElementById('form').reset();
    document.getElementById('func_id').value = '';
    document.getElementById('tipo_entidade').value = 'funcionario';

    document.getElementById('func_senha').required = true;
    document.getElementById('hint-senha').textContent = '(obrigatória no cadastro)';

    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('modal-funcionario').style.display = 'block';
}

function abrirEdicao(func) {
    document.getElementById('modal-titulo').textContent = '✏️ Editando: ' + func.nome;

    document.getElementById('func_id').value    = func.id;
    document.getElementById('func_nome').value  = func.nome;
    document.getElementById('func_cargo').value = func.id_cargo;
    document.getElementById('func_login').value = func.login;
    document.getElementById('fone').value       = func.fone  ?? '';
    document.getElementById('func_email').value = func.email ?? '';

    const cpfRaw = func.cpf ?? '';
    document.getElementById('cpf').value = cpfRaw.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');

    document.getElementById('func_senha').value    = '';
    document.getElementById('func_senha').required = false;
    document.getElementById('hint-senha').textContent = '(deixe em branco para não alterar)';

    document.getElementById('tipo_entidade').value = 'editar_funcionario';

    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('modal-funcionario').style.display = 'block';
}

function fecharModal() {
    document.getElementById('modal-overlay').style.display    = 'none';
    document.getElementById('modal-funcionario').style.display = 'none';
}

function confirmarExclusao(id, nome) {
    if (!confirm(`Desativar o funcionário "${nome}"?\n\nEle não será excluído, apenas marcado como Inativo.`)) return;

    fetch('/ERP-Papelaria/sistema/controllers/api.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.mensagem);
        if (res.status) location.reload();
    })
    .catch(() => alert('Erro de comunicação com o servidor.'));
}

document.addEventListener('keydown', (e) => { if (e.key === 'Escape') fecharModal(); });
</script>

<?php include 'includes/footer.php'; ?>