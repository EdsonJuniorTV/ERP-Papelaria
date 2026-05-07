<?php
function registrarEntradaEstoque($id_prod, $qtd, $conn) {
    try {
        // Sanitização
        $id_prod = (int)$id_prod;
        $qtd = (int)$qtd;

        // O SQL usa o operador += (qtd = qtd + valor)
        $sql = "UPDATE estoque SET qtd = qtd + ? WHERE id_prod = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $qtd, $id_prod);
        
        if ($stmt->execute()) {
            return ["status" => true, "mensagem" => "Estoque atualizado! +$qtd unidades."];
        } else {
            throw new Exception("Erro ao atualizar banco.");
        }
    } catch (Exception $e) {
        return ["status" => false, "mensagem" => $e->getMessage()];
    }
}
?>