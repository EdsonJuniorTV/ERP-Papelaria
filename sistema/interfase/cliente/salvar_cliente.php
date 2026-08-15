<?php
/**
 * Endpoint para processar o cadastro de clientes
 */
header('Content-Type: application/json');
require_once '../config/conexao.php';
require_once 'cliente.php';

// Captura o input (suporta JSON do fetch e POST tradicional)
$json = file_get_contents('php://input');
$dados = json_decode($json, true) ?? $_POST;

// Validação mínima de campos obrigatórios
if (!empty($dados['nome']) && !empty($dados['cpf'])) {
    
    // Chama a função cadastrarCliente que já revisamos e protegemos
    $resultado = cadastrarCliente($dados, $conexao);
    echo json_encode($resultado);

} else {
    echo json_encode([
        "status" => false, 
        "mensagem" => "Erro: Nome e CPF são obrigatórios para o cadastro."
    ]);
}
?>