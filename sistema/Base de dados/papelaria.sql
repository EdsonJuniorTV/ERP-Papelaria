-- 1. Criação do Banco
create database if not exists papelaria;
use papelaria;

-- 2. Tabelas de Configuração (Base)
create table cargo(
    id int primary key auto_increment,
    nome varchar(100) not null unique
);

create table categoria(
    id int primary key auto_increment,
    nome varchar(100) not null unique
);

create table marca(
    id int primary key auto_increment,
    nome varchar(100) not null unique
);

create table endereco(
    id int primary key auto_increment,
    logradouro varchar(200) not null,
    cidade varchar(100) not null,
    estado char(2) not null,
    cep char(9) not null
);

-- 3. Tabelas de Entidades (Pessoas e Empresas)
create table funcionario(
    id int primary key auto_increment,
    id_cargo int not null,
    id_end int not null,
    cpf char(14) not null unique,
    nome varchar(80) not null,
    login varchar(50) not null unique, -- Adicionado para o sistema
    senha varchar(255) not null,       -- Adicionado para o sistema
    dt_nasc date not null,
    dt_admissao date not null,
    dt_saida date,
    status enum('Ativo','Inativo','Demitido') default 'Ativo',
    fone varchar(20) not null,
    email varchar(120) not null,
    foreign key (id_cargo) references cargo(id),
    foreign key (id_end) references endereco(id)
);

-- Histórico de status dos funcionários
create table historico_funcionario(
    id int primary key auto_increment,
    id_func int not null,
    evento varchar(100) not null,
    dt_evento datetime default current_timestamp,
    foreign key (id_func) references funcionario(id)
);

create table folha_pagamento (
    id int primary key auto_increment,
    id_func int not null,
    mes_referencia int not null, -- Ex: 5 (Maio)
    ano_referencia int not null, -- Ex: 2026
    valor_pago decimal(10,2) not null,
    dt_pagamento date not null,
    foreign key (id_func) references funcionario(id)
);

create table cliente(
    id int primary key auto_increment,
    cpf char(14) not null unique,
    id_end int not null,
    nome varchar(80) not null,
    dt_nasc date not null,
    fone varchar(20) not null,
    email varchar(120) not null,
    foreign key (id_end) references endereco(id)
);

create table fornecedor(
    id int primary key auto_increment,
    nome varchar(80) not null,
    cnpj char(18) not null unique,
    fone varchar(20) not null,
    email varchar(120) not null,
    id_end int,
    foreign key (id_end) references endereco(id)
);

-- 4. Tabelas de Produtos e Estoque
create table produto(
    id int primary key auto_increment,
    id_forn int not null,
    id_cat int not null,
    id_marca int not null, -- Adicionado para normalização
    nome varchar(150) not null,
    preco decimal(10,2) not null,
    custo decimal(10,2) not null,
    status enum('Ativo','Inativo') default 'Ativo',
    foreign key (id_forn) references fornecedor(id),
    foreign key (id_cat) references categoria(id),
    foreign key (id_marca) references marca(id),
    index idx_status (status)
);

-- Histórico de alterações nos produtos
create table historico_produto(
    id int primary key auto_increment,
    id_prod int not null,
    id_func int not null,
    campo_alterado varchar(50) not null,
    valor_antigo varchar(200),
    valor_novo varchar(200),
    dt_alteracao datetime default current_timestamp,
    foreign key (id_prod) references produto(id),
    foreign key (id_func) references funcionario(id)
);

create table estoque(
    id int primary key auto_increment,
    id_prod int not null unique, -- Um registro por produto
    qtd int not null default 0,
    qtd_minima int default 5,
    foreign key (id_prod) references produto(id)
);

-- 5. Tabelas de Operações
create table pedido(
    id int primary key auto_increment,
    id_cli int not null,
    id_func int not null,
    dt_pedido datetime default current_timestamp,
    status enum('Aberto','Pago','Cancelado') default 'Aberto',
    foreign key (id_cli) references cliente(id),
    foreign key (id_func) references funcionario(id)
);

create table item_pedido(
    id int primary key auto_increment,
    id_ped int not null,
    id_prod int not null,
    qtd int not null,
    preco_unitario decimal(10,2) not null,
    foreign key (id_ped) references pedido(id),
    foreign key (id_prod) references produto(id)
);

create table compra(
    id int primary key auto_increment,
    id_forn int not null,
    id_func int not null,
    dt_compra datetime default current_timestamp,
    valor_total decimal(10,2),
    foreign key (id_forn) references fornecedor(id),
    foreign key (id_func) references funcionario(id)
);

create table item_compra(
    id int primary key auto_increment,
    id_compra int not null,
    id_prod int not null,
    qtd int not null,
    preco_unit decimal(10,2) not null,
    foreign key (id_compra) references compra(id),
    foreign key (id_prod) references produto(id)
);

create table movimentacao_financeira(
    id int primary key auto_increment,
    tipo enum('Entrada','Saida') not null,
    descricao varchar(200) not null,
    valor decimal(10,2) not null,
    dt_mov date not null,
    id_ped int,
    id_compra int,
    foreign key (id_ped) references pedido(id),
    foreign key (id_compra) references compra(id)
);

create table comissao(
    id int primary key auto_increment,
    id_func int not null,
    id_ped int not null,
    id_item_ped int not null, -- Vinculado ao item específico
    valor_comissao decimal(10,2) not null,
    dt_gerada datetime default current_timestamp,
    foreign key (id_func) references funcionario(id),
    foreign key (id_ped) references pedido(id)
);

-- 6. TRIGGERS (Automação)

delimiter //

-- Trigger: Atualiza estoque na venda
create trigger trg_item_pedido_insert
after insert on item_pedido
for each row
begin
    update estoque set qtd = qtd - NEW.qtd where id_prod = NEW.id_prod;
end; //

-- Trigger: Atualiza estoque na compra
create trigger trg_item_compra_insert
after insert on item_compra
for each row
begin
    update estoque set qtd = qtd + NEW.qtd where id_prod = NEW.id_prod;
end; //

-- Trigger: Financeiro na venda paga
create trigger trg_pedido_pago
after update on pedido
for each row
begin
    if NEW.status = 'Pago' and OLD.status <> 'Pago' then
        insert into movimentacao_financeira(tipo, descricao, valor, dt_mov, id_ped)
        values ('Entrada', concat('Pagamento pedido #', NEW.id), 
               (select sum(qtd * preco_unitario) from item_pedido where id_ped = NEW.id), 
               curdate(), NEW.id);
    end if;
end; //

CREATE TRIGGER trg_compra_financeiro
AFTER INSERT ON compra
FOR EACH ROW
BEGIN
    -- Se a compra tiver um valor gerado, registra automaticamente como despesa (Saída)
    IF NEW.valor_total IS NOT NULL AND NEW.valor_total > 0 THEN
        INSERT INTO movimentacao_financeira (tipo, descricao, valor, dt_mov, id_compra)
        VALUES (
            'Saida', 
            CONCAT('Compra de mercadoria - Pedido #', NEW.id), 
            NEW.valor_total, 
            DATE(NEW.dt_compra), 
            NEW.id
        );
    END IF;
END; //

-- Trigger: Histórico de alteração de produto (com captura do Funcionário)
create trigger trg_log_preco_produto
after update on produto
for each row
begin
    -- Declara variável para armazenar o ID do funcionário logado
    -- Se a aplicação não setar @id_funcionario, assume o ID 1 (Programador/Admin) como fallback
    DECLARE v_id_func INT;
    SET v_id_func = IFNULL(@id_funcionario, 1);

    if OLD.preco <> NEW.preco then
        insert into historico_produto (id_prod, id_func, campo_alterado, valor_antigo, valor_novo)
        values (OLD.id, v_id_func, 'preco', OLD.preco, NEW.preco);
    end if;
    
    if OLD.nome <> NEW.nome then
        insert into historico_produto (id_prod, id_func, campo_alterado, valor_antigo, valor_novo)
        values (OLD.id, v_id_func, 'nome', OLD.nome, NEW.nome);
    end if;

    if OLD.status <> NEW.status then
        insert into historico_produto (id_prod, id_func, campo_alterado, valor_antigo, valor_novo)
        values (OLD.id, v_id_func, 'status', OLD.status, NEW.status);
    end if;
end; //

create trigger trg_log_status_funcionario
after update on funcionario
for each row
begin
    if OLD.status <> NEW.status then
        insert into historico_funcionario (id_func, evento)
        values (OLD.id, concat('Alteração de status: de ', OLD.status, ' para ', NEW.status));
    end if;
    
    if OLD.id_cargo <> NEW.id_cargo then
        insert into historico_funcionario (id_func, evento)
        values (OLD.id, 'Mudança de cargo/promoção');
    end if;
end; //

create trigger trg_gera_comissao_item
after insert on item_pedido
for each row
begin
    -- Busca quem é o funcionário que fez o pedido
    set @vendedor_id = (select id_func from pedido where id = NEW.id_ped);
    
    -- Insere a comissão (5% do preço unitário * quantidade)
    insert into comissao (id_func, id_ped, id_item_ped, valor_comissao)
    values (@vendedor_id, NEW.id_ped, NEW.id, (NEW.preco_unitario * NEW.qtd) * 0.05);
end; //

CREATE TRIGGER trg_item_pedido_check_estoque
BEFORE INSERT ON item_pedido
FOR EACH ROW
BEGIN
    DECLARE estoque_atual INT;
    DECLARE status_prod VARCHAR(10);

    -- Produto desativado não pode ser vendido
    SELECT status INTO status_prod FROM produto WHERE id = NEW.id_prod;
    IF status_prod = 'Inativo' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERRO: Produto inativo não pode ser vendido!';
    END IF;

    -- Busca a quantidade atual no estoque
    SELECT qtd INTO estoque_atual FROM estoque WHERE id_prod = NEW.id_prod;

    -- Sem linha de estoque (NULL) ou quantidade insuficiente: cancela a operação
    IF estoque_atual IS NULL OR estoque_atual < NEW.qtd THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'ERRO: Estoque insuficiente para este produto!';
    END IF;
END; //
delimiter ;

-- 7. Dados Iniciais para Teste
insert into endereco(logradouro, cidade, estado, cep) 
values ('Parque Jardim Europa, 6-80, lado par','Bauru','SP','17017-050'),
('Alameda Gabriel Monteiro da Silva, 6-20, lado ímpar','Bauru','SP','01441-002'),
('Avenida Angélica, 9-20, lado par','Bauru','SP','01228-000');
insert into cargo(nome) values ('Gerente'), ('Vendedor'), ('Repositor');

insert into categoria(nome) 
values ('Escrita'),
('Papéis'),
('Cadernos'),
('Organização'),
('Informática'),
('Escolar'),
('Artes'),
('Pintura'),
('Colagem'),
('Arquivo'),
('Acessórios'),
('Calculadoras'),
('Mochilas'),
('Encadernação'),
('Etiquetas');

insert into marca(nome) 
values  ('Bic'),
        ('Faber-Castell'),
        ('Pilot'),
        ('Stabilo'),
        ('Compactor'),
        ('Cis'),
        ('Tilibra'),
        ('Jandaia'),
        ('Credeal'),
        ('Acrilex'),
        ('Tris'),
        ('Maped'),
        ('Mercur'),
        ('Sharpie'),
        ('Pentel'),
        ('Maxprint'),
        ('Dell'),
        ('HP'),
        ('Multilaser'),
        ('Elgin');

insert into fornecedor(nome, cnpj, fone, email, id_end) 
values  ('Distribuidora Central', '12.345.678/0001-99', '(14) 99999-9999', 'contato@dist.com', 1),
        ('Comercial Horizonte', '11.111.111/0001-11', '(14) 91111-1111', 'alpha@gmail.com', 2),
        ('Atlas Distribuição', '22.222.222/0001-22', '(14) 92222-2222', 'beta@gmail.com', 1),
        ('Imperial Distribuidora', '33.333.333/0001-33', '(14) 93333-3333', 'gama@gmail.com', 3),
        ('Integra Distribuidora', '44.444.444/0001-44', '(14) 94444-4444', 'delta@gmail.com', 3),
        ('Capital Distribuidora', '55.555.555/0001-55', '(14) 95555-5555', 'omega@gmail.com', 1),
        ('Delta Fornecimentos', '66.666.666/0001-66', '(14) 96666-6666', 'prime@gmail.com', 2);

-- Ao inserir este produto, o estoque será criado automaticamente pelo trigger
insert into produto
(id_forn, id_cat, id_marca, nome, preco, custo) 
values 

(1, 1, 1, 'Caneta Cristal Azul', 2.50, 1.00),
(1, 1, 3, 'Caneta Cristal Preta', 2.50, 1.00),
(1, 1, 4, 'Caneta Cristal Vermelha', 2.70, 1.10),
(1, 2, 5, 'Lápis HB Nº2', 1.50, 0.70),
(1, 2, 6, 'Lápis 6B Profissional', 3.00, 1.50),
(2, 1, 2, 'Marca Texto Amarelo', 4.50, 2.00),
(2, 1, 4, 'Marca Texto Verde', 4.50, 2.00),
(2, 3, 7, 'Caderno Universitário 200 Folhas', 18.90, 10.00),
(2, 3, 8, 'Caderno Brochura 96 Folhas', 9.90, 5.50),
(3, 4, 9, 'Borracha Branca Escolar', 2.00, 0.80),
(3, 4, 6, 'Apontador com Depósito', 5.50, 2.50),
(3, 5, 12, 'Régua 30cm Transparente', 3.50, 1.50),
(4, 5, 11, 'Tesoura Escolar sem Ponta', 8.90, 4.20),
(4, 6, 10, 'Cola Branca 90g', 6.00, 2.80),
(4, 6, 10, 'Cola Bastão 20g', 7.50, 3.50),
(5, 7, 7, 'Papel Sulfite A4 500 Folhas', 32.90, 22.00),
(5, 7, 8, 'Bloco de Notas Adesivas', 12.90, 6.50),
(5, 8, 15, 'Grampeador Pequeno', 15.90, 8.00),
(5, 8, 15, 'Caixa de Grampos 26/6', 4.90, 2.20),
(6, 9, 9, 'Pasta Plástica com Elástico', 6.90, 3.10),
(6, 9, 8, 'Envelope Kraft A4', 1.20, 0.50),
(6, 10, 16, 'Calculadora Científica', 49.90, 30.00),
(7, 10, 18, 'Mouse USB Preto', 35.00, 20.00),
(7, 11, 17, 'Teclado ABNT2', 69.90, 40.00),
(7, 11, 19, 'Pen Drive 32GB', 39.90, 22.00);

-- Inserção inicial do estoque
insert into estoque (id_prod, qtd, qtd_minima) values
(1, 100, 5),
(2, 120, 5),
(3, 80, 5),
(4, 150, 5),
(5, 60, 5),
(6, 90, 5),
(7, 70, 5),
(8, 40, 5),
(9, 55, 5),
(10, 130, 5),
(11, 75, 5),
(12, 95, 5),
(13, 45, 5),
(14, 110, 5),
(15, 85, 5),
(16, 50, 5),
(17, 65, 5),
(18, 35, 5),
(19, 140, 5),
(20, 100, 5),
(21, 200, 5),
(22, 25, 5),
(23, 30, 5),
(24, 20, 5),
(25, 60, 5);

insert into funcionario(id_cargo,id_end,cpf,nome,login,senha,dt_nasc,dt_admissao,fone,email)
values(1,1,"000.000.000-00","Programador","admin","$2y$10$G9poHdud5XJxgiQq1p0syOMEgE.wNxBwZWoA8ux.KxsnXPf4tDKni","1995-10-10","2010-10-10","(00) 00000-0000","programador@gmail.com"), -- 123
(2,2,"111.111.111-11","Marcos Almeida","MarcosAlmeida","$2y$10$Idk7meRz4qORBjEpppftc.TnUwJa5d4fODe3yqySRhAFXsLgivOTK","1992-05-04","2017-08-04","(14) 85756-9484","marcos@gmail.com"), -- aranha
(3,3,"222.222.222-22","Maria Madeira","mary","$2y$10$ZkhyPvE3ynT1Gh3DFnaHMe/8kpLHTuo.RWpUvix8jiqfY5DTmnNa.","1998-07-14","2019-06-27","(14) 82634-7363","mary@gmail.com"); -- bolo

INSERT INTO cliente(cpf,id_end,nome,dt_nasc,fone,email)
VALUES("945.827.536-55",1,"Armando Fernandes","1997-05-12","(14) 99734-0987","ArmandoFernandes@gmail.com");