<?php
/**
 * Configurações de Conexão com o Banco de Dados
 * Sistema de Gestão - Papelaria
 */

// Credenciais do Banco
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'papelaria');

// Realiza a conexão utilizando a extensão MySQLi
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Validação da Conexão
if (!$conexao) {
    // Em ambiente de desenvolvimento, o erro detalhado é útil.
    // Em produção, o ideal é salvar em um log e exibir uma mensagem amigável.
    die("Falha crítica na conexão com o banco de dados: " . mysqli_connect_error());
}

/**
 * Ajustes de Ambiente
 */

// Define o charset para utf8mb4 (suporte completo a acentuação e emojis)
mysqli_set_charset($conexao, "utf8mb4");

// Define o fuso horário padrão para garantir que datas de vendas e registros 
// fiquem sincronizadas com o horário local.
date_default_timezone_set('America/Sao_Paulo');

// A partir daqui, a variável $conexao está pronta para uso em todo o sistema.
?>