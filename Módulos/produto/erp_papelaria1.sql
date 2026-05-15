-- ERP Papelaria - Script Completo com 20 Produtos
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Criar o Banco de Dados se não existir
CREATE DATABASE IF NOT EXISTS `erp_papelaria1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `erp_papelaria1`;

-- Limpeza de tabelas existentes para evitar erros de duplicidade na carga
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `estoque`, `compras`, `historico`, `produtos`;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Estrutura para tabela `produtos`
CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `preco_custo` decimal(10,2) NOT NULL,
  `estoque_atual` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Estrutura para tabela `estoque`
CREATE TABLE `estoque` (
  `produto_id` int(11) NOT NULL,
  `qtd_produto` int(11) NOT NULL,
  PRIMARY KEY (`produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Estrutura para tabela `historico`
CREATE TABLE `historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_pessoa` varchar(100) DEFAULT NULL,
  `produto` varchar(100) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `data` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Estrutura para tabela `compras`
CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `nome_cliente` varchar(100) DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `data_compra` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- INSERÇÃO DE 20 PRODUTOS (Inventário Completo)
-- --------------------------------------------------------

INSERT INTO `produtos` (`nome`, `categoria`, `preco_custo`, `estoque_atual`) VALUES
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

-- Sincronizando a tabela de estoque automaticamente com base nos produtos acima
INSERT INTO `estoque` (produto_id, qtd_produto)
SELECT id, estoque_atual FROM produtos;

-- --------------------------------------------------------
-- RESTRIÇÕES (CHAVES ESTRANGEIRAS)
-- --------------------------------------------------------

ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

COMMIT;