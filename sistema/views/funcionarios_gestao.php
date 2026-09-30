<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?erro=2");
    exit;
}

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../controllers/funcionario.php';

verificarPermissao(['Gerente', 'Programador']);
include '../includes/header.php';

$usuarioLogadoId = (int) $_SESSION['user_id'];

// Consultas via funções e modelo
$cargos = mysqli_query($conexao, "SELECT * FROM cargo ORDER BY nome ASC");
$funcionarios = listarFuncionarios($conexao, $usuarioLogadoId);
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
                                <td style="padding:13px 16px; display:flex; gap:4px; flex-wrap:wrap;">
                                    <button onclick='abrirEdicao(<?= json_encode($f) ?>)'
                                            style="background:#1a56db; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:.83rem; font-weight:600;">
                                        ✏️ Editar
                                    </button>
                                    <?php if ($f['status'] === 'Ativo'): ?>
                                        <button onclick="confirmarExclusao(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['nome'])) ?>')"
                                                style="background:#e02424; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:.83rem; font-weight:600;">
                                            🔒 Desativar
                                        </button>
                                    <?php else: ?>
                                        <button onclick="reativarFuncionario(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['nome'])) ?>')" 
                                                style="background:#228B22; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:.83rem; font-weight:600;">
                                            🔓 Reativar
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="removerFuncionario(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['nome'])) ?>')"
                                            style="background:#1C1C1C; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:.83rem; font-weight:600;">
                                        🗑 Remover
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<!-- Inclusão do Modal Isolado -->
<?php include '../includes/modal_funcionario.php'; ?>

<!-- Inclusão do JS isolado -->
<script src="../public/js/gestao.js"></script>

<?php include '../includes/footer.php'; ?>