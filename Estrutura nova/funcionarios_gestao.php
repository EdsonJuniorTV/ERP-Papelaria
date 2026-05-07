<?php 
require_once 'includes/auth.php';
require_once 'config/conexao.php';
verificarPermissao(['Gerente', 'Programador']); // Apenas quem manda acessa
include 'includes/header.php';

$cargos = mysqli_query($conexao, "SELECT * FROM cargo ORDER BY nome ASC");
?>

<main>
    <div class="container">
        <div class="header" style="background: #8e44ad;">
            <h1>👔 Gestão de Funcionários</h1>
            <p>Cadastre novos colaboradores e defina permissões de acesso</p>
        </div>

        <form id="form" data-method="post">
            <input type="hidden" name="tipo_entidade" value="funcionario">
            
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label>Nome Completo <span>*</span></label>
                    <input type="text" name="nome" placeholder="Nome do colaborador" required>
                </div>

                <div class="form-group">
                    <label>CPF <span>*</span></label>
                    <input type="text" id="cpf" name="cpf" required>
                </div>

                <div class="form-group">
                    <label>Cargo / Nível de Acesso <span>*</span></label>
                    <select name="id_cargo" required>
                        <option value="">Selecione...</option>
                        <?php while($cargo = mysqli_fetch_assoc($cargos)): ?>
                            <option value="<?= $cargo['id'] ?>"><?= $cargo['nome'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Login de Acesso <span>*</span></label>
                    <input type="text" name="login" placeholder="Ex: joao.vendas" required>
                </div>

                <div class="form-group">
                    <label>Senha Provisória <span>*</span></label>
                    <input type="password" name="senha" required>
                </div>

                <div class="form-group">
                    <label>Data de Admissão</label>
                    <input type="date" name="dt_admissao">
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" id="fone" name="fone">
                </div>

                <div class="form-group form-group-full">
                    <label>Endereço Residencial</label>
                    <input type="text" name="logradouro" placeholder="Rua, Número, Bairro">
                </div>
                <input type="hidden" name="cidade" value="Bauru">
                <input type="hidden" name="estado" value="SP">
                <input type="hidden" name="cep" value="00000000">
            </div>

            <button type="submit" class="btn-submit">💾 Salvar Funcionário</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>