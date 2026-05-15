
drop database if exists funcionario;
create database if not exists funcionario;
use funcionario;

create table if not exists funcionarios (
    id int auto_increment primary key,
    nome varchar(100) not null,
    cpf varchar(14) not null unique,
    dt_nascimento date not null,
    email varchar(100) not null unique,
    departamento varchar(100) not null,
    cargo varchar(100) not null,
    fone varchar(15) not null,
    salario decimal(10,2) not null
);

create table if not exists produto (
    id int auto_increment primary key,
    nome_produto varchar(100) not null,
    categoria varchar(100),
    preco_unit decimal(10,2) not null,
    estoque int not null
);

create table if not exists venda (
    id int auto_increment primary key,
    id_func int not null,
    valor_total decimal(10,2) not null,
    dt_venda datetime default current_timestamp,

    foreign key (id_func) references funcionarios(id)
);

create table if not exists venda_produto (
    venda_id int not null,
    produto_id int not null,
    quantidade int not null,
    preco_unit decimal(10,2) not null,

    primary key (venda_id, produto_id),

    foreign key (venda_id) references venda(id),
    foreign key (produto_id) references produto(id)
);

create table if not exists faixa_comissao (
    id int auto_increment primary key,
    valor_min decimal(10,2) not null,
    valor_max decimal(10,2),
    percentual decimal(5,2) not null,
    unique (valor_min, valor_max)
);

create table if not exists comissao (
    id int auto_increment primary key,
    venda_id int not null,
    funcionario_id int not null,
    valor decimal(10,2) not null,
    percentual decimal(5,2) not null,
    dt_calculo datetime default current_timestamp,

    foreign key (venda_id) references venda(id),
    foreign key (funcionario_id) references funcionarios(id)
);

create index idx_venda_func on venda(id_func);
create index idx_venda_produto_prod on venda_produto(produto_id);

INSERT INTO funcionarios (nome, cpf, dt_nascimento, email, departamento, cargo, fone, salario) VALUES 
('João Pedro de Souza Palmieri', '111.111.111-11', '2000-05-20', 'joao@gmail.com', 'atendimento', 'Caixa', '(11) 11111-1111', 2000.00),
('Ana de Castro Carvalho', '222.222.222-22', '2000-05-20', 'ana@gmail.com', 'estoque', 'Repositor', '(22) 22222-2222', 2000.00),
('Lucas Manchetti de Oliveira', '333.333.333-33', '2000-05-20', 'lucas@gmail.com', 'financeiro', 'Analista', '(33) 33333-3333', 2000.00),
('Bianca Rodrigues Monteiro', '444.444.444-44', '2000-05-20', 'bianca@gmail.com', 'servico', 'Jurídico', '(44) 44444-4444', 2000.00),
('José Bonifácio de Alcântara', '555.555.555-55', '2000-05-20', 'jose@gmail.com', 'administrativo', 'RH', '(55) 55555-5555', 2000.00),
('Marina Silva Santos', '666.666.666-66', '1995-08-12', 'marina@gmail.com', 'atendimento', 'Vendedora', '(66) 66666-6666', 2200.00),
('Rafael Souza Lima', '777.777.777-77', '1992-03-05', 'rafael@gmail.com', 'estoque', 'Conferente', '(77) 77777-7777', 2100.00),
('Camila Ferreira Dias', '888.888.888-88', '1998-11-22', 'camila@gmail.com', 'financeiro', 'Contadora', '(88) 88888-8888', 3500.00),
('Pedro Henrique Rocha', '999.999.999-99', '1990-07-30', 'pedro@gmail.com', 'administrativo', 'Gerente', '(99) 99999-9999', 5000.00),
('Juliana Costa Almeida', '000.000.000-00', '1997-01-15', 'juliana@gmail.com', 'servico', 'Advogada', '(00) 00000-0000', 4000.00);