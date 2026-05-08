const url = "controllers/api.php"; 
const form = document.getElementById("form");

// Máscaras de CPF, Telefone e CEP
const mascaras = {
    cpf: (v) => v.replace(/\D/g, '').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'),
    fone: (v) => v.replace(/\D/g, '').replace(/^(\d{2})(\d)/g, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2'),
    cep: (v) => v.replace(/\D/g, '').replace(/(\d{5})(\d)/, '$1-$2')
};

// Aplica as máscaras apenas se os elementos existirem na página atual
['cpf', 'fone', 'cep'].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.addEventListener('input', (e) => e.target.value = mascaras[id](e.target.value));
});

// Impede que o script quebre em páginas que não possuem a tag <form id="form">
if (form) {
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const dados = Object.fromEntries(formData.entries());
        
        // Removemos as máscaras antes de enviar para o banco (valida se o dado existe)
        if (dados.cpf) dados.cpf = dados.cpf.replace(/\D/g, '');
        if (dados.fone) dados.fone = dados.fone.replace(/\D/g, '');
        if (dados.cep) dados.cep = dados.cep.replace(/\D/g, '');

        // Define o método HTTP (padrão POST se não for especificado no HTML)
        const method = form.dataset.method ? form.dataset.method.toUpperCase() : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
            
            const result = await response.json();
            
            // Exibe a mensagem retornada pelo PHP
            alert(result.mensagem || (result.status ? "Operação realizada com sucesso!" : "Erro na operação."));
            
            // Recarrega a página apenas se o cadastro deu certo
            if (result.status) location.reload();
            
        } catch (error) {
            console.error("Erro crítico de comunicação:", error);
            alert("Erro ao comunicar com o servidor. Verifique sua conexão.");
        }
    });
}