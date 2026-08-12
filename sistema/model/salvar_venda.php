<?php
header('Content-Type: application/json');
require_once '../config/conexao.php';

$input = json_decode(file_get_contents('php://input'), true);
$id_cli  = $input['id_cli'] ?? null;
$id_func = $input['id_func'] ?? 1; // ID padrão caso não venha da sessão
$status  = 'Pago';

try {
    mysqli_begin_transaction($conexao);

    // 1. Cria o pedido
    $sqlPed = "INSERT INTO pedido (id_cli, id_func, status) VALUES (?, ?, ?)";
    $stmtPed = $conexao->prepare($sqlPed);
    $stmtPed->bind_param("iis", $id_cli, $id_func, $status);
    $stmtPed->execute();
    $id_pedido = $stmtPed->insert_id;

    // Nota: O Trigger do banco cuidará da baixa e financeira ao inserir os itens.
    
    mysqli_commit($conexao);
    echo json_encode(["status" => true, "id_pedido" => $id_pedido]);

} catch (Exception $e) {
    mysqli_rollback($conexao);
    echo json_encode(["status" => false, "msg" => "Erro na venda: " . $e->getMessage()]);
}