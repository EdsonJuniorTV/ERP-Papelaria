<?php
require_once 'config/conexao.php';
require_once 'includes/auth.php';

$id_ped  = $_POST['id_pedido'];
$id_prod = $_POST['id_prod'];
$qtd     = $_POST['qtd'];
$preco   = $_POST['preco'];

// 1. Insere o item. 
// O Trigger 'trg_item_pedido_insert' baixará o estoque automaticamente.
// O Trigger 'trg_gera_comissao_item' calculará a comissão automaticamente.
$sql = "INSERT INTO item_pedido (id_ped, id_prod, qtd, preco_unitario) 
        VALUES ('$id_ped', '$id_prod', '$qtd', '$preco')";

if(mysqli_query($conexao, $sql)) {
    echo json_encode(["status" => "sucesso"]);
} else {
    echo json_encode(["status" => "erro", "msg" => mysqli_error($conexao)]);
}
?>