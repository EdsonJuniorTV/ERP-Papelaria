<?php 
require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../controllers/fornecedor.php';
include '../includes/header.php'; 

$todosFornecedores = listarFornecedores($conexao);
?>

<main>
    <div class="container">
        <div class="header" style="background: #2c3e50;">
            <div>
                <h1>🚚 Gestão de Fornecedores</h1>
                <p>Veja, adicione, altere ou remova fornecedores</p>
            </div>
            <button class="btn-success" onclick="abrirModalFornecedor()">
                ADICIONAR
            </button>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>RAZÃO SOCIAL / NOME</th>
                        <th>CNPJ</th>
                        <th>E-MAIL</th>
                        <th>TELEFONE</th>
                        <th>LOGRADOURO</th>
                        <th>CIDADE</th>
                        <th>ESTADO (UF)</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($todosFornecedores)): ?>
                        <tr>
                            <td colspan="8">Nenhum fornecedor encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($todosFornecedores as $tf): ?>
                            <tr>
                                <td><?= htmlspecialchars($tf['nome']) ?></td>
                                <td><?= htmlspecialchars($tf['cnpj'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tf['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tf['fone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tf['logradouro'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tf['cidade'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tf['estado'] ?? '-') ?></td>
                                <td>
                                    <button class="btn-submit" onclick='abrirEdicaoFornecedor(<?= json_encode($tf) ?>)'>EDITAR</button>
                                    <button class="btn-danger" onclick="removerFornecedor(<?= $tf['id'] ?>, '<?= htmlspecialchars(addslashes($tf['nome'])) ?>')">REMOVER</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/modal_fornecedor.php'; ?>
<script src="../public/js/gestao.js"></script>

<?php include '../includes/footer.php'; ?>