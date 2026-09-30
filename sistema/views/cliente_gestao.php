<?php
    require_once '../config/conexao.php';
    require_once '../controllers/cliente.php';
    require_once '../includes/auth.php';
    include '../includes/header.php';

    $todosClientes = listarClientes($conexao);
?>

<main>
    <div class="container">
        <div class="header">
            <div>
                <h1>👥Gestão de Clientes</h1>
                <p>Veja, adicione, altere ou remova clientes</p>
            </div>
            <button class="btn-success" onclick="abrirModalCliente()">
                ADICIONAR
            </button>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>NOME</th>
                        <th>CPF</th>
                        <th>E-MAIL</th>
                        <th>TELEFONE</th>
                        <th>ENDEREÇO</th>
                        <th>CIDADE</th>
                        <th>ESTADO (UF)</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($todosClientes)): ?>
                        <tr>
                            <td colspan="8">Nenhum cliente encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($todosClientes as $tc): ?>
                            <tr>
                                <td><?= htmlspecialchars($tc['nome']) ?></td>
                                <td><?= htmlspecialchars($tc['cpf']) ?></td>
                                <td><?= htmlspecialchars($tc['email']) ?></td>
                                <td><?= htmlspecialchars($tc['fone']) ?></td>
                                <td><?= htmlspecialchars($tc['logradouro'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tc['cidade'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($tc['estado'] ?? '-') ?></td>
                                <td>
                                    <button class="btn-submit" onclick='abrirEdicaoCliente(<?= json_encode($tc) ?>)'>EDITAR</button>
                                    <button class="btn-danger" onclick="removerCliente(<?= $tc['id'] ?>, '<?= htmlspecialchars(addslashes($tc['nome'])) ?>')">REMOVER</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/modal_cliente.php'; ?>
<script src="../public/js/gestao.js"></script>

<?php include '../includes/footer.php'; ?>