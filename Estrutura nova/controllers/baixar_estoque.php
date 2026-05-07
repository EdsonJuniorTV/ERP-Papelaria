<?php
header('Content-Type: application/json');
require_once '../config/conexao.php';

$id_ped  = $_POST['id_pedido'] ?? null;
$id_prod = $_POST['id_prod'] ?? null;
$qtd     = $_POST['qtd'] ?? 0;
$preco   = $_POST['preco'] ?? 0;

if (!$id_ped || !$id_prod) {
    echo json_encode(["status" => "erro", "msg" => "Dados incompletos"]);
    exit;
}

// O Prepared Statement evita que erros de aspas no nome do produto quebrem a query
$sql = "INSERT INTO item_pedido (id_ped, id_prod, qtd, preco_unitario) VALUES (?, ?, ?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("iiid", $id_ped, $id_prod, $qtd, $preco);

if($stmt->execute()) {
    echo json_encode(["status" => "sucesso"]);
} else {
    // Aqui o TRIGGER de estoque insuficiente pode disparar um erro que será pego
    echo json_encode(["status" => "erro", "msg" => $stmt->error]);
}