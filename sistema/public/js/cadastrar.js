const url = "/ERP-Papelaria/sistema/controllers/api.php"; 
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

// -------------------------------------------------------
// Concatena os campos rua, bairro e número no logradouro
// -------------------------------------------------------

const LogradouroCompleto = () => {
    const elementoRua = document.getElementById('rua');
    const elementoNumero = document.getElementById('numero');
    const elementoBairro = document.getElementById('bairro');

    const elementoLogradouro = document.getElementById('logradouro');

    if (elementoRua && elementoNumero && elementoBairro && elementoLogradouro) {
        const rua = elementoRua.value.trim();
        const numero = elementoNumero.value.trim();
        const bairro = elementoBairro.value.trim();

        let texto = rua;
        if (numero !== "") texto += `, ${numero}`;
        if (bairro !== "") texto += ` - ${bairro}`;

        elementoLogradouro.value = texto;
    }
}

// Aplica o efeito de digitação para atualizar o logradouro em tempo real
['rua','numero','bairro'].forEach(id => {
    const elementoTempoReal = document.getElementById(id);
    if (elementoTempoReal) elementoTempoReal.addEventListener('input', LogradouroCompleto);
});

// --------------------------------------------------------
// Fim da concatenação
// --------------------------------------------------------

// -------------------------------------------------------
// Consumo da API viaCEP 😄👍
// -------------------------------------------------------

const cepInformado = document.getElementById('cep');

// Função auxiliar que apenas setará valores se os elementos existirem
const setValor = (seletor,valor) => {
    const elemento = document.querySelector(seletor);
    if (elemento) elemento.value = valor;
}

// Função para limpar os dados do cep caso o mesmo dê erro
const limparEndereco = () => {
    setValor('#rua','');
    setValor('#bairro','');
    setValor('#cidade','');
    setValor('#estado','');
    setValor('#cep','');
}

// Aplica o viaCEP apenas em páginas que possuam o campo CEP
if (cepInformado) {
    cepInformado.addEventListener('blur', async (elementoDaTela) => {
        // Pega o valor e remove a máscara de estilização para fazer a requisição
        let cep = elementoDaTela.target.value.replace(/\D/g,'');
        
        if(cep.length === 8) {
            // Indicativo visual da busca
            setValor('#rua','Buscando...');
            setValor('#bairro','Buscando...');
            setValor('#cidade','Buscando...');
            setValor('#estado','Buscando...');

            try {
                const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const informacoesEndereco = await resposta.json();

                if (informacoesEndereco.erro) {
                    alert('CEP não foi encontrado.');
                    limparEndereco();
                } else {
                    // Preenche todos os campos
                    setValor('#rua',informacoesEndereco.logradouro);
                    setValor('#bairro',informacoesEndereco.bairro);
                    setValor('#cidade',informacoesEndereco.localidade);
                    setValor('#estado',informacoesEndereco.uf);

                    LogradouroCompleto();

                    // Vai para o campo número automáticamente
                    const numero = document.getElementById('numero');
                    if (numero) numero.focus();
                }
            } catch (error) {
                console.error('Erro ao buscar o CEP:', error);
                alert('Erro de conexão ao buscar pelo CEP.');
                limparEndereco();
            }
        }
        else if (cep.length > 8 || cep.length < 8) {
            alert('Formato do CEP inválido.');
            limparEndereco();
        }
    });
}

// ----------------------------------------------------
// Acabou o consumo da API do cep ☕😄👍
// ----------------------------------------------------

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