const url = "/app/controllers/api.php";
const form = document.getElementById("form");
const lista = document.getElementById("lista");

document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

document.getElementById('fone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
    }
});

function resetForm() {

    form.reset();

    const div = document.querySelector(".form-grid");

    if (div.lastElementChild) {
        div.lastElementChild.remove();
    }

    const headerForm = document.querySelector(".header");
    headerForm.children[0].textContent = "Cadastro de Funcionários";
    headerForm.children[1].style.display = "block";

    form.querySelector("[type='submit']").textContent = "Cadastrar Funcionário";
    form.dataset.method = "post";

    return;
}

function listar(data) {

    const fragment = document.createDocumentFragment();

    data.forEach(e => {

        let p = document.createElement("p");
        p.textContent = e.nome;

        let imgExcluir = document.createElement("img");
        imgExcluir.src = "/public/assets/delete.png";
        imgExcluir.alt = "Lixeira";     

        let buttonExcluir = document.createElement("button");
        buttonExcluir.dataset.action = "excluir";
        buttonExcluir.title = "Excluir";
        buttonExcluir.appendChild(imgExcluir);

        let imgEditar = document.createElement("img");
        imgEditar.src = "/public/assets/edit.png";
        imgEditar.alt = "Editar";     

        let buttonEditar = document.createElement("button");
        buttonEditar.dataset.action = "editar";
        buttonEditar.title = "Editar";
        buttonEditar.appendChild(imgEditar);

        let imgMenu = document.createElement("img");
        imgMenu.src = "/public/assets/verMais.png";
        imgMenu.alt = "Ver Mais";

        let buttonMenu = document.createElement("button");
        buttonMenu.dataset.action = "menu";
        buttonMenu.title = "Ver Mais";
        buttonMenu.appendChild(imgMenu);

        const divHead = document.createElement("div");
        divHead.append(p, buttonExcluir, buttonEditar, buttonMenu);

        const divDetalhes = document.createElement("div");
        divDetalhes.style.display = "none";

        const div  = document.createElement("div");
        div.dataset.id = e.id;
        div.append(divHead, divDetalhes);

        fragment.appendChild(div);
    });

    lista.appendChild(fragment);

    if (lista.innerHTML.trim() !== '') {
        lista.style.display = "flex";
    }

    return;
}

function listarDetalhes(data, element) {

    const fragment = document.createDocumentFragment();

    const colunas = ['CPF', 'Data de Nascimento', 'E-mail', 'Departamento', 'Cargo', 'Telefone', 'Salário R$'];

    const valores = Object.entries(data)
        .filter(([chave]) => chave !== 'id' && chave !== 'nome')
        .map(([, valor]) => valor);

    for (let i = 0; i < colunas.length; i++) {
        const titulo = document.createElement("span");
        titulo.textContent = colunas[i];

        const valor = document.createElement("span");
        valor.textContent = valores[i];

        const div = document.createElement("div");
        div.append(titulo, valor);
        fragment.appendChild(div);
    }

    element.appendChild(fragment);
    element.style.display = "flex";

    return;
}

async function getFuncionarios() {
    try {

        const request = await fetch(url, {
            method: "GET",
            headers: {
                'Content-Type': 'application/json'
            }  
        });

        const response = await request.json();

        if (!response.status && response.data.entidade.length <= 0) {

            lista.innerHTML = '';
            lista.style.display = 'none';
            return;
        }

        return listar(response.data.entidade);

    } catch ( error ) {

        console.error("Erro: ", error);
    }
}

async function buscarPorId(id) {
    try {

        let _url = `${url}?id=${id}`;

        const request = await fetch(_url, {
            method: "GET",
            headers: {
                'Content-Type': 'Application/json'
            }
        });

        const response = await request.json();

        if (!response.status && response.data.entidade.length <= 0) {
            return;
        }

        return response.data.entidade;

    } catch ( error ) {

        console.log(error);
    }
}

async function excluir (id) {
    try {

        const request = await fetch(url, {
            method: "DELETE",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(id)
        });

        const response = await request.json();

        if (response.data && response.data.length === 0) {

            return !response.status;
        }

        return response.status;
        
    } catch ( error ) {

        console.error("Erro: ", error);
    }
}

async function atualizarLista(id, nome) {
    const elemento = document.querySelector(`[data-id="${id}"]`);
    elemento.querySelector("div p").textContent = nome;
    elemento.children[1].innerHTML = "";
    try {
        const response = await buscarPorId(id);
        listarDetalhes(response, elemento.children[1]);

    } catch (error) {

        console.log(error);
    }
    return;
}

form.addEventListener("submit", async (e) => {

    e.preventDefault();

    const data = Object.fromEntries(new FormData(form));
    const method = form.dataset.method.toUpperCase();

    try {
        const request = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const response = await request.json();
        
        alert(response.data.mensagem);

        if (method === "PUT") {
            atualizarLista(Number(data.id), data['nome']);
            return resetForm();
        }
            
        listar([response.data.entidade]);
        return form.reset();

    } catch ( error ) {

        form.reset();
        console.error("Erro: ", error);
    }
});

lista.addEventListener("click", async (e) => {

    const registro = e.target.closest("[data-id]");
    const id = Number(registro?.dataset.id);
    const principal = registro.children[0];
    const dados = registro.children[1];
    const action = e.target.closest("[data-action]")?.dataset.action;

    switch (action) {

        case "excluir":

            if (!confirm("Deseja excluir este registro?")) {
                break;
            }

            try {
                const response = await excluir(id);

                if (response) {

                    registro.remove();

                    if (lista.children.length === 0) {

                        lista.style.display = 'none';
                        break;
                    }

                } else {

                    alert("Erro ao excluir funcionário!"); 
                }
            } catch (error) {

                console.log(error);
            }
             
            break;
        
        case "editar":

            if (!confirm("Deseja editar este registro?")) {
                break;
            }

            try {
                const response = await buscarPorId(id);
                const campos = form.elements;

                const headerForm = document.querySelector(".header");
                headerForm.children[0].textContent = "Edição de Funcionários";
                headerForm.children[1].style.display = "none";

                form.querySelector("[type='submit']").textContent = "Salvar Alterações";

                for (const campo of campos) {
                    campo.value = response[campo.name];
                }

                const input = document.createElement("input");
                input.name = "id";
                input.id = "id";
                input.value = id;

                const div = document.createElement("div");
                div.className = "form-group";
                div.style.display = "none";
                div.appendChild(input);

                document.querySelector(".form-grid").appendChild(div);

                form.dataset.method = "put";

            } catch (error) {

                console.log(error);
            }

            break;

        case "menu":

            if (dados.style.display === 'none') {

                if (dados.innerHTML.trim() === '') {

                    try {
                        const response = await buscarPorId(id);
                        listarDetalhes(response, dados);

                    } catch (error) {

                        console.log(error);
                    }

                } else {
                    dados.style.display = "flex";
                }

            } else {

                dados.style.display = "none";
            }
            break;
        
        default:
            return;
    }
});

window.addEventListener("load", () => {
    getFuncionarios();
});