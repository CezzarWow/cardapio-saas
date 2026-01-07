<?php

namespace App\Services;

use PDO;

class StockService
{
    /**
     * Decrementa o estoque de um produto
     * ⚠️ NÃO gerencia transaction (deve ser chamado dentro de uma)
     * 
     * @param PDO $conn Conexão ativa
     * @param int $productId ID do produto
     * @param float $quantity Quantidade a reduzir
     */
    public function decrement(PDO $conn, int $productId, float $quantity): void
    {
        $stmt = $conn->prepare("UPDATE products SET stock = stock - :qtd WHERE id = :pid");
        $stmt->execute([
            'qtd' => $quantity,
            'pid' => $productId
        ]);
    }

    /**
     * Incrementa o estoque de um produto (Devolução)
     * ⚠️ NÃO gerencia transaction (deve ser chamado dentro de uma)
     * 
     * @param PDO $conn Conexão ativa
     * @param int $productId ID do produto
     * @param float $quantity Quantidade a devolver
     */
    public function increment(PDO $conn, int $productId, float $quantity): void
    {
        $stmt = $conn->prepare("UPDATE products SET stock = stock + :qtd WHERE id = :pid");
        $stmt->execute([
            'qtd' => $quantity,
            'pid' => $productId
        ]);
    }
}
