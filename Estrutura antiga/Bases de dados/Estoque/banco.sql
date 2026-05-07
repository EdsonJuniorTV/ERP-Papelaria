-- Reset do banco para garantir a nova estrutura
DROP DATABASE IF EXISTS erp_papelaria1;
CREATE DATABASE IF NOT EXISTS erp_papelaria1;
USE erp_papelaria1;

-- 1. Tabela de Produtos (Atualizada com preco_custo)
CREATE TABLE produtos (
    id                  INT                 AUTO_INCREMENT PRIMARY KEY,
    nome                VARCHAR(100)        NOT NULL,
    categoria           VARCHAR(50)         NOT NULL,
    preco_custo         DECIMAL(10,2)       NOT NULL, -- Alteração feita por você
    estoque_atual       INT                 NOT NULL
);

-- 2. Tabela de Histórico (Estrutura do grupo de estoque)
CREATE TABLE historico_produtos (
    id                  INT                 AUTO_INCREMENT PRIMARY KEY,
    produto_id          INT,
    nome_produto        VARCHAR(100),
    quantidade          INT,
    data                DATETIME            DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- 3. Tabela de Compras/Vendas
CREATE TABLE compras (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    produto_id      INT NOT NULL,
    quantidade      INT NOT NULL,
    data_compra     DATE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- 4. Tabela de Estoque (Sincronizada)
CREATE TABLE estoque(
    produto_id          INT         PRIMARY KEY,
    qtd_produto         INT         NOT NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 5. Tabelas de Movimentação (Para os Triggers funcionarem)
CREATE TABLE entrada_produto(
    id                  INT         AUTO_INCREMENT PRIMARY KEY,
    produto_id          INT         NOT NULL,
    quantidade          INT         NOT NULL,
    data_entrada        DATE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

CREATE TABLE saida_produto(
    id                  INT         AUTO_INCREMENT PRIMARY KEY,
    produto_id          INT         NOT NULL,
    quantidade          INT         NOT NULL,
    data_saida          DATE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- --------------------------------------------------------
-- 20 produtos listados
-- --------------------------------------------------------

INSERT INTO produtos (nome, categoria, preco_custo, estoque_atual) VALUES
('Caneta Esferográfica Azul', 'Escrita', 1.20, 100),
('Caneta Esferográfica Preta', 'Escrita', 1.20, 80),
('Lápis Grafite HB', 'Escrita', 0.80, 150),
('Borracha Escolar Branca', 'Escrita', 0.50, 60),
('Apontador com Depósito', 'Escrita', 2.50, 40),
('Caderno Inteligente A4', 'Cadernos', 45.90, 15),
('Caderno Espiral 10 Matérias', 'Cadernos', 18.50, 30),
('Bloco de Notas Adesivas', 'Papéis', 4.20, 55),
('Resma de Papel A4 500fls', 'Papéis', 28.90, 20),
('Régua de Alumínio 30cm', 'Medição', 7.50, 25),
('Tesoura Escolar Inox', 'Corte', 5.30, 35),
('Cola Bastão 40g', 'Adesivos', 3.80, 45),
('Fita Adesiva Transparente', 'Adesivos', 2.10, 50),
('Estojo Escolar Duplo', 'Organização', 12.90, 12),
('Pasta Sanfonada 12 Divisórias', 'Organização', 22.00, 10),
('Marcador de Texto Amarelo', 'Escrita', 3.50, 70),
('Corretivo em Fita', 'Escrita', 6.80, 25),
('Grampeador de Mesa', 'Escritório', 19.90, 8),
('Caixa de Clips 28mm', 'Escritório', 4.50, 40),
('Calculadora Científica', 'Eletrônicos', 55.00, 5);

-- Alimenta a tabela de estoque inicial 
INSERT INTO estoque (produto_id, qtd_produto)
SELECT id, estoque_atual FROM produtos;

-- --------------------------------------------------------
-- Triggers
-- --------------------------------------------------------

DELIMITER $$

-- Gatilho para entrada de estoque
CREATE TRIGGER entrada_estoque
AFTER INSERT ON entrada_produto
FOR EACH ROW
BEGIN
    UPDATE estoque
    SET qtd_produto = qtd_produto + NEW.quantidade
    WHERE produto_id = NEW.produto_id;
    
    UPDATE produtos
    SET estoque_atual = estoque_atual + NEW.quantidade
    WHERE id = NEW.produto_id;
END $$

-- Gatilho para saída de estoque (com verificação)
CREATE TRIGGER verificar_estoque
BEFORE INSERT ON saida_produto
FOR EACH ROW
BEGIN
    DECLARE qtd_atual INT;
    SELECT qtd_produto INTO qtd_atual FROM estoque WHERE produto_id = NEW.produto_id;
    IF qtd_atual < NEW.quantidade THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estoque insuficiente';
    END IF;
END $$

CREATE TRIGGER saida_estoque
AFTER INSERT ON saida_produto
FOR EACH ROW
BEGIN
    UPDATE estoque
    SET qtd_produto = qtd_produto - NEW.quantidade
    WHERE produto_id = NEW.produto_id;
    
    UPDATE produtos
    SET estoque_atual = estoque_atual - NEW.quantidade
    WHERE id = NEW.produto_id;
END $$

DELIMITER ;