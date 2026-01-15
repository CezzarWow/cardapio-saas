<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class CashRegisterRepository
{
    /**
     * Busca caixa aberto de um restaurante
     */
    public function findOpen(int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca caixa por ID
     */
    public function find(int $id): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM cash_registers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Abre um novo caixa
     */
    public function open(int $restaurantId, float $openingBalance): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO cash_registers (restaurant_id, opening_balance, status, opened_at) VALUES (:rid, :val, 'aberto', NOW())");
        $stmt->execute(['rid' => $restaurantId, 'val' => $openingBalance]);
        return (int) $conn->lastInsertId();
    }

    /**
     * Fecha um caixa (atualiza status e data de fechamento)
     */
    public function close(int $restaurantId): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE cash_registers SET status = 'fechado', closed_at = NOW() WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $restaurantId]);
    }

    /**
     * Registra movimentação no caixa
     */
    public function addMovement(int $cashRegisterId, string $type, float $amount, string $description, ?int $orderId = null, ?string $date = null): void
    {
        $conn = Database::connect();
        $sql = 'INSERT INTO cash_movements (cash_register_id, type, amount, description, order_id, created_at) VALUES (:cid, :type, :amount, :desc, :oid, :date)';

        $params = [
            'cid' => $cashRegisterId,
            'type' => $type,
            'amount' => $amount,
            'desc' => $description,
            'oid' => $orderId,
            'date' => $date ?: date('Y-m-d H:i:s')
        ];

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Busca movimentações de um caixa
     */
    public function findMovements(int $cashRegisterId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM cash_movements WHERE cash_register_id = :cid ORDER BY created_at DESC');
        $stmt->execute(['cid' => $cashRegisterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca movimentação por Order ID
     */
    public function findMovementByOrder(int $orderId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM cash_movements WHERE order_id = :oid');
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca movimentação por ID
     */
    public function findMovement(int $id): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM cash_movements WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Remove movimentação por ID
     */
    public function deleteMovement(int $id): void
    {
        $conn = Database::connect();
        $conn->prepare('DELETE FROM cash_movements WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * Remove movimentação por Order ID
     */
    public function deleteMovementByOrder(int $orderId): void
    {
        $conn = Database::connect();
        $conn->prepare('DELETE FROM cash_movements WHERE order_id = :oid')->execute(['oid' => $orderId]);
    }
}
