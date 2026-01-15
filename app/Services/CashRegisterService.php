<?php

namespace App\Services;

use App\Repositories\CashRegisterRepository;
use Exception;
use PDO;

class CashRegisterService
{
    private CashRegisterRepository $repo;

    public function __construct(CashRegisterRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Valida se o caixa está aberto para o restaurante
     *
     * @param PDO $conn (Unused but kept for compatibility/signature)
     */
    public function assertOpen(PDO $conn, int $restaurantId): array
    {
        $caixa = $this->repo->findOpen($restaurantId);

        if (!$caixa) {
            throw new Exception('O Caixa está FECHADO! Abra o caixa para vender. 🔒');
        }

        return $caixa;
    }

    /**
     * Registra uma movimentação no caixa (apenas VENDA)
     *
     * @param PDO $conn (Unused)
     */
    public function registerMovement(PDO $conn, int $cashRegisterId, float $amount, string $description, int $orderId): void
    {
        $this->repo->addMovement($cashRegisterId, 'venda', $amount, $description, $orderId);
    }

    /**
     * Busca movimentação por Order ID
     */
    public function findByOrderId(PDO $conn, int $orderId): ?array
    {
        return $this->repo->findMovementByOrder($orderId);
    }

    /**
     * Remove movimentação por ID do pedido
     */
    public function deleteByOrderId(PDO $conn, int $orderId): void
    {
        $this->repo->deleteMovementByOrder($orderId);
    }

    /**
     * Restaura movimentação (com data específica)
     */
    public function restoreMovement(PDO $conn, array $mov): void
    {
        $this->repo->addMovement(
            $mov['cash_register_id'],
            $mov['type'],
            $mov['amount'],
            $mov['description'],
            $mov['order_id'] ?? $mov['oid'] ?? null,
            $mov['created_at']
        );
    }
}
