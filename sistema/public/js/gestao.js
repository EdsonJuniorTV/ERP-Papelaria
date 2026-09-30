// ==========================================
// FUNÇÕES GERAIS DE MODAL E REQUISIÇÃO
// ==========================================
function fecharModal() {
    const overlay = document.getElementById('modal-overlay');
    const modalFunc = document.getElementById('modal-funcionario');
    const modalCli = document.getElementById('modal-cliente');
    const modalForn = document.getElementById('modal-fornecedor');

    if (overlay) overlay.style.display = 'none';
    if (modalFunc) modalFunc.style.display = 'none';
    if (modalCli) modalCli.style.display = 'none';
    if (modalForn) modalForn.style.display = 'none';
}

function requisicaoApi(dados, metodoHttp) {
    fetch('../controllers/api.php', {
        method: metodoHttp,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(r => r.json())
    .then(res => {
        alert(res.mensagem);
        if (res.status) location.reload();
    })
    .catch((e) => {
        console.error('Erro:', e);
        alert('Erro de comunicação com o servidor.');
    });
}

// ==========================================
// ENVIO DOS FORMULÁRIOS (SUBMIT)
// ==========================================
function interceptarFormulario(idFormulario) {
    const form = document.getElementById(idFormulario);
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Impede a página de recarregar

            // Extrai todos os campos (name) do formulário para um objeto JSON
            const formData = new FormData(form);
            const dados = Object.fromEntries(formData.entries());

            // Envia para o backend (O backend processa a criação e edição via método POST)
            requisicaoApi(dados, 'POST');
        });
    }
}

// Inicializa a interceptação assim que o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    interceptarFormulario('form');            // Funcionário
    interceptarFormulario('form-cliente');    // Cliente
    interceptarFormulario('form-fornecedor'); // Fornecedor
});

// ==========================================
// FUNCIONÁRIOS
// ==========================================
function abrirModal() {
    document.getElementById('modal-titulo').textContent = '👔 Novo Funcionário';
    const form = document.getElementById('form');
    if (form) form.reset();
    document.getElementById('func_id').value = '';
    document.getElementById('tipo_entidade').value = 'funcionario';

    const campoSenha = document.getElementById('func_senha');
    if (campoSenha) {
        campoSenha.required = true;
        document.getElementById('hint-senha').textContent = '(obrigatória no cadastro)';
    }

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

    // CPF Formatado
    const cpfRaw = func.cpf ?? '';
    document.getElementById('cpf').value = cpfRaw.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');

    // Preenche os campos de Endereço
    document.getElementById('cep').value        = func.cep        ?? '';
    document.getElementById('logradouro').value = func.logradouro ?? '';
    document.getElementById('rua').value        = func.rua        ?? '';
    document.getElementById('numero').value     = func.numero     ?? '';
    document.getElementById('bairro').value     = func.bairro     ?? '';
    document.getElementById('cidade').value     = func.cidade     ?? '';
    document.getElementById('estado').value     = func.estado     ?? '';

    if (document.getElementById('func_dt_nasc')) {
        document.getElementById('func_dt_nasc').value = func.dt_nasc ?? '';
    }
    if (document.getElementById('func_dt_admissao')) {
        document.getElementById('func_dt_admissao').value = func.dt_admissao ?? '';
    }

    const campoSenha = document.getElementById('func_senha');
    if (campoSenha) {
        campoSenha.value = '';
        campoSenha.required = false;
        document.getElementById('hint-senha').textContent = '(deixe em branco para não alterar)';
    }

    document.getElementById('tipo_entidade').value = 'editar_funcionario';

    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('modal-funcionario').style.display = 'block';
}

function confirmarExclusao(id, nome) {
    if (confirm(`Desativar o funcionário "${nome}"?\n\nEle não será excluído, apenas marcado como Inativo.`)) {
        requisicaoApi({ id: id }, 'DELETE');
    }
}

function reativarFuncionario(id, nome) {
    if (confirm(`Ativar o funcionário "${nome}"?\n\nEle será reativado.`)) {
        requisicaoApi({ id: id }, 'REVIVE');
    }
}

function removerFuncionario(id, nome) {
    if (confirm(`Deletar o Funcionário "${nome}"?\n\nEle será apagado definitivamente.`)) {
        requisicaoApi({ id: id }, 'REMOVE');
    }
}

// ==========================================
// CLIENTES
// ==========================================
function abrirModalCliente() {
    const titulo = document.getElementById('modal-titulo-cliente');
    if (titulo) titulo.textContent = '👥 Novo Cliente';
    
    const form = document.getElementById('form-cliente');
    if (form) form.reset();

    const cliId = document.getElementById('cli_id');
    if (cliId) cliId.value = '';

    const tipoEnt = document.querySelector('#modal-cliente #tipo_entidade');
    if (tipoEnt) tipoEnt.value = 'cliente';

    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('modal-cliente').style.display = 'block';
}

function abrirEdicaoCliente(cli) {
    const titulo = document.getElementById('modal-titulo-cliente');
    if (titulo) titulo.textContent = '✏️ Editando Cliente: ' + cli.nome;

    document.getElementById('cli_id').value    = cli.id;
    document.getElementById('cli_nome').value  = cli.nome;
    document.getElementById('cli_email').value = cli.email ?? '';

    if (document.getElementById('cli_dt_nasc')) {
        document.getElementById('cli_dt_nasc').value = cli.dt_nasc ?? '';
    }

    const cpfRaw = cli.cpf ?? '';
    const cpfInput = document.querySelector('#modal-cliente #cpf');
    if (cpfInput) cpfInput.value = cpfRaw.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');

    const foneInput = document.querySelector('#modal-cliente #fone');
    if (foneInput) foneInput.value = cli.fone ?? '';

    const cepInput = document.querySelector('#modal-cliente #cep');
    if (cepInput) cepInput.value = cli.cep ?? '';

    const logrInput = document.querySelector('#modal-cliente #logradouro');
    if (logrInput) logrInput.value = cli.logradouro ?? '';

    const ruaInput = document.querySelector('#modal-cliente #rua');
    if (ruaInput) ruaInput.value = cli.rua ?? '';

    const numInput = document.querySelector('#modal-cliente #numero');
    if (numInput) numInput.value = cli.numero ?? '';

    const bairroInput = document.querySelector('#modal-cliente #bairro');
    if (bairroInput) bairroInput.value = cli.bairro ?? '';

    const cidInput = document.querySelector('#modal-cliente #cidade');
    if (cidInput) cidInput.value = cli.cidade ?? '';

    const estInput = document.querySelector('#modal-cliente #estado');
    if (estInput) estInput.value = cli.estado ?? '';

    const tipoEnt = document.querySelector('#modal-cliente #tipo_entidade');
    if (tipoEnt) tipoEnt.value = 'editar_cliente';

    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('modal-cliente').style.display = 'block';
}

function removerCliente(id, nome) {
    if (confirm(`Remover o cliente "${nome}"?\n\nEsta ação não poderá ser desfeita.`)) {
        requisicaoApi({ id: id, tipo_entidade: 'cliente' }, 'DELETE');
    }
}

// ==========================================
// FORNECEDORES
// ==========================================
function abrirModalFornecedor() {
    const titulo = document.getElementById('modal-titulo-fornecedor');
    if (titulo) titulo.textContent = '🚚 Novo Fornecedor';

    const form = document.getElementById('form-fornecedor');
    if (form) form.reset();

    const fornId = document.getElementById('forn_id');
    if (fornId) fornId.value = '';

    const tipoEnt = document.querySelector('#modal-fornecedor #tipo_entidade');
    if (tipoEnt) tipoEnt.value = 'fornecedor';

    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('modal-fornecedor').style.display = 'block';
}

function abrirEdicaoFornecedor(forn) {
    const titulo = document.getElementById('modal-titulo-fornecedor');
    if (titulo) titulo.textContent = '✏️ Editando Fornecedor: ' + forn.nome;

    document.getElementById('forn_id').value    = forn.id;
    document.getElementById('forn_nome').value  = forn.nome;
    document.getElementById('forn_email').value = forn.email ?? '';

    const cnpjRaw = forn.cnpj ?? '';
    const cnpjInput = document.querySelector('#modal-fornecedor #cnpj');
    if (cnpjInput) cnpjInput.value = cnpjRaw.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');

    const foneInput = document.querySelector('#modal-fornecedor #fone');
    if (foneInput) foneInput.value = forn.fone ?? '';

    const cepInput = document.querySelector('#modal-fornecedor #cep');
    if (cepInput) cepInput.value = forn.cep ?? '';

    const logrInput = document.querySelector('#modal-fornecedor #logradouro');
    if (logrInput) logrInput.value = forn.logradouro ?? '';

    const ruaInput = document.querySelector('#modal-fornecedor #rua');
    if (ruaInput) ruaInput.value = forn.rua ?? '';

    const numInput = document.querySelector('#modal-fornecedor #numero');
    if (numInput) numInput.value = forn.numero ?? '';

    const bairroInput = document.querySelector('#modal-fornecedor #bairro');
    if (bairroInput) bairroInput.value = forn.bairro ?? '';

    const cidInput = document.querySelector('#modal-fornecedor #cidade');
    if (cidInput) cidInput.value = forn.cidade ?? '';

    const estInput = document.querySelector('#modal-fornecedor #estado');
    if (estInput) estInput.value = forn.estado ?? '';

    const tipoEnt = document.querySelector('#modal-fornecedor #tipo_entidade');
    if (tipoEnt) tipoEnt.value = 'editar_fornecedor';

    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('modal-fornecedor').style.display = 'block';
}

function removerFornecedor(id, nome) {
    if (confirm(`Remover o fornecedor "${nome}"?\n\nEsta ação não poderá ser desfeita.`)) {
        requisicaoApi({ id: id, tipo_entidade: 'fornecedor' }, 'DELETE');
    }
}

// Fechamento de modals via tecla ESC
document.addEventListener('keydown', (e) => { 
    if (e.key === 'Escape') fecharModal(); 
});