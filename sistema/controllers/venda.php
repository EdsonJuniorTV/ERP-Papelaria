<?php
// Endpoint de Processamento de Vendas (Refatorado)
    header('Content-Type: application/json');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once '../config/conexao.php';

    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['acao']) && $input['acao'] === 'finalizar_venda') {
        try {
            mysqli_begin_transaction($conexao);
            
            $id_func = $_SESSION['user_id'] ?? 1; // Utiliza a sessão ou um fallback
            $id_cli = intval($input['id_cliente']);
            
            if ($id_cli <= 0) {
                throw new Exception("Por favor, selecione um cliente válido.");
            }
            
            // 1. Cria o Pedido com status 'Aberto' para habilitar a trigger de UPDATE depois
            $stmt = mysqli_prepare($conexao, "INSERT INTO pedido (id_cli, id_func, status) VALUES (?, ?, 'Aberto')");
            mysqli_stmt_bind_param($stmt, "ii", $id_cli, $id_func);
            mysqli_stmt_execute($stmt);
            $id_ped = mysqli_insert_id($conexao);
            
            // 2. Insere os Itens do Pedido (Isso aciona as Triggers de Estoque e Comissão)
            $stmt_item = mysqli_prepare($conexao, "INSERT INTO item_pedido (id_ped, id_prod, qtd, preco_unitario) VALUES (?, ?, ?, ?)");
            foreach ($input['carrinho'] as $item) {
                $id_prod = intval($item['cod']);
                $qtd = intval($item['qtd']);
                $preco = floatval($item['preco']);
                
                mysqli_stmt_bind_param($stmt_item, "iiid", $id_ped, $id_prod, $qtd, $preco);
                mysqli_stmt_execute($stmt_item);
            }
            
            // 3. Atualiza para 'Pago' (Isso aciona a Trigger trg_pedido_pago criando a entrada financeira)
            $stmt_pago = mysqli_prepare($conexao, "UPDATE pedido SET status = 'Pago' WHERE id = ?");
            mysqli_stmt_bind_param($stmt_pago, "i", $id_ped);
            mysqli_stmt_execute($stmt_pago);
            
            mysqli_commit($conexao);
            echo json_encode(['sucesso' => true, 'id_pedido' => $id_ped]);
            
        } catch (Exception $e) {
            mysqli_rollback($conexao);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Ação não definida ou inválida.']);
    }
?>