<?php include 'includes/header.php'; ?>

<main>
    <div class="container" style="max-width: 800px;">
        <div class="header" style="background: #34495e;">
            <h1>⚙️ Configurações de Base</h1>
            <p>Adicione novos itens às listas do sistema</p>
        </div>

        <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            
            <section>
                <h3>📂 Categorias</h3>
                <form class="form-rapido" data-tabela="categoria">
                    <input type="text" placeholder="Nova Categoria..." required>
                    <button type="submit" style="background: #3498db; color:white; border:none; padding:5px 10px; cursor:pointer;">+</button>
                </form>
            </section>

            <section>
                <h3>🏷️ Marcas</h3>
                <form class="form-rapido" data-tabela="marca">
                    <input type="text" placeholder="Nova Marca..." required>
                    <button type="submit" style="background: #3498db; color:white; border:none; padding:5px 10px; cursor:pointer;">+</button>
                </form>
            </section>

            <section>
                <h3>👔 Cargos</h3>
                <form class="form-rapido" data-tabela="cargo">
                    <input type="text" placeholder="Novo Cargo..." required>
                    <button type="submit" style="background: #3498db; color:white; border:none; padding:5px 10px; cursor:pointer;">+</button>
                </form>
            </section>
        </div>
    </div>
</main>

<script>
    // Lógica simplificada para cadastros rápidos
    document.querySelectorAll('.form-rapido').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const tabela = form.dataset.tabela;
            const nome = form.querySelector('input').value;

            const res = await fetch('controllers/api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo_entidade: 'auxiliar', tabela: tabela, nome: nome })
            });

            const dados = await res.json();
            alert(dados.mensagem);
            if(dados.status) location.reload();
        });
    });
</script>

<?php include 'includes/footer.php'; ?>