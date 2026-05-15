<?php
header('Content-Type: application/json; charset=utf-8');

// Caminhos ajustados partindo da pasta controllers para a config
require_once '../config/conexao.php';

// Importação de todos os controladores de lógica
require_once 'funcionario.php';
require_once 'cliente.php';
require_once 'fornecedor.php';
require_once 'produto.php'; 
require_once 'estoque_entrada.php';
require_once 'config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Se não for GET e o input estiver vazio (via fetch JSON)
if ($metodo !== 'GET' && empty($input)) {
    $input = $_POST; // Fallback para formulários tradicionais

    echo json_encode(["status" => false, "mensagem" => "Nenhum dado recebido."]);
    exit;
}

try {
    $resposta = ["status" => false, "mensagem" => "Ação não definida."];

    switch ($metodo) {
        case 'POST':
            $tipo = $input['tipo_entidade'] ?? '';

            switch ($tipo) {
                case 'produto':
                    $resposta = cadastrarProduto($input, $conexao);
                    break;
                case 'entrada_estoque':
                    $resposta = registrarEntradaEstoque($input['id_prod'], $input['qtd'], $conexao);
                    break;
                case 'auxiliar':
                    $resposta = cadastrarAuxiliar($input['tabela'], $input['nome'], $conexao);
                    break;
                case 'cliente':
                    $resposta = cadastrarCliente($input, $conexao);
                    break;
                case 'fornecedor':
                    $resposta = cadastrarFornecedor($input, $conexao);
                    break;
                case 'funcionario':
                    $resposta = cadastrarFuncionario($input, $conexao);
                    break;
                default:
                    throw new Exception("O tipo de POST não foi encontrado.");
            }
            break;

        case 'GET':
            $tipo = $_GET['tipo'] ?? '';
            if ($tipo == 'cliente') {
                $resposta = listarClientes($conexao);
            } elseif ($tipo == 'fornecedor') {
                $resposta = listarFornecedores($conexao);
            } else {
                $resposta = listarFuncionarios($conexao);
            }
            break;

        default:
            throw new Exception("Método $metodo não suportado.");
    }

    echo json_encode($resposta);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => false, "mensagem" => "Erro interno: " . $e->getMessage()]);
}