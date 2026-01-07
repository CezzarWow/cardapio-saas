<?php

namespace App\Services;

use PDO;
use Exception;

class CashRegisterService
{
    /**
     * Valida se o caixa está aberto para o restaurante
     * 
     * @param PDO $conn Conexão ativa
     * @param int $restaurantId ID do restaurante
     * @return array Dados do caixa aberto
     * @throws Exception Se o caixa estiver fechado
     */
    public function assertOpen(PDO $conn, int $restaurantId): array
    {
        $stmt = $conn->prepare("SELECT id FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $restaurantId]);
        $caixa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$caixa) {
            throw new Exception('O Caixa está FECHADO! Abra o caixa para vender. 🔒');
        }

        return $caixa;
    }

    /**
     * Registra uma movimentação no caixa (apenas VENDA)
     * ⚠️ NÃO gerencia transaction (deve ser chamado dentro de uma)
     * 
     * @param PDO $conn Conexão ativa com transaction iniciada
     * @param int $cashRegisterId ID do caixa
     * @param float $amount Valor da movimentação
     * @param string $description Descrição da venda
     * @param int $orderId ID do pedido vinculado
     */
    public function registerMovement(PDO $conn, int $cashRegisterId, float $amount, string $description, int $orderId): void
    {
        $stmt = $conn->prepare("INSERT INTO cash_movements 
            (cash_register_id, type, amount, description, order_id, created_at) 
            VALUES (:cid, 'venda', :val, :desc, :oid, NOW())");
            
        $stmt->execute([
            'cid' => $cashRegisterId,
            'val' => $amount,
            'desc' => $description,
            'oid' => $orderId
        ]);
    }
}
