<?php
    require_once '../config/conexao.php';
    require_once 'funcionario.php';

    header('Content-type: application/json; charset=utf-8');

    try {

        $conn = getConnection();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $data = [];

        switch($_SERVER['REQUEST_METHOD']) {

            case 'POST':
                $data = cadastrar($input, $conn);
                break;
            
            case 'GET':

                if (isset($_GET['id']) && (int)$_GET['id'] > 0) {

                    $id = (int)$_GET['id'];
                    $data = listarPorId($id, $conn);
                    
                } else {

                    $data = listar($conn);
                }

                break;

            case 'PUT':
                $data = alterar($input, $conn);
                break;

            case 'DELETE':
                $data = excluir($input, $conn);
                break;

            default:

                throw new Exception('Método não permitido', 405);
        }

    } catch ( Exception $e ) {

        http_response_code($e->getCode() ?? 400);

        echo json_encode([
            'status' => false,
            'mensagem' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    http_response_code(200);

    echo json_encode([
        'status' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>