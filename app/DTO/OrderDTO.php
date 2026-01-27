<?php

namespace App\DTO;

/**
 * DTO (Data Transfer Object) para Pedido.
 * ETAPA 4: substituir arrays por objetos tipados para type safety e documentação.
 *
 * @see app/Repositories/Order/OrderRepository
 */
final class OrderDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $restaurantId,
        public readonly ?int $clientId,
        public readonly ?int $tableId,
        public readonly float $total,
        public readonly string $status,
        public readonly string $orderType,
        public readonly string $paymentMethod,
        public readonly ?string $observation,
        public readonly ?string $changeFor,
        public readonly string $source,
        public readonly ?string $createdAt,
        public readonly ?int $isPaid = null,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : null,
            restaurantId: (int) ($row['restaurant_id'] ?? 0),
            clientId: isset($row['client_id']) ? (int) $row['client_id'] : null,
            tableId: isset($row['table_id']) ? (int) $row['table_id'] : null,
            total: (float) ($row['total'] ?? 0),
            status: (string) ($row['status'] ?? 'novo'),
            orderType: (string) ($row['order_type'] ?? 'balcao'),
            paymentMethod: (string) ($row['payment_method'] ?? 'dinheiro'),
            observation: isset($row['observation']) ? (string) $row['observation'] : null,
            changeFor: isset($row['change_for']) ? (string) $row['change_for'] : null,
            source: (string) ($row['source'] ?? 'pdv'),
            createdAt: isset($row['created_at']) ? (string) $row['created_at'] : null,
            isPaid: isset($row['is_paid']) ? (int) $row['is_paid'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $a = [
            'restaurant_id' => $this->restaurantId,
            'client_id' => $this->clientId,
            'table_id' => $this->tableId,
            'total' => $this->total,
            'status' => $this->status,
            'order_type' => $this->orderType,
            'payment_method' => $this->paymentMethod,
            'observation' => $this->observation,
            'change_for' => $this->changeFor,
            'source' => $this->source,
            'created_at' => $this->createdAt,
        ];
        if ($this->id !== null) {
            $a['id'] = $this->id;
        }
        if ($this->isPaid !== null) {
            $a['is_paid'] = $this->isPaid;
        }
        return $a;
    }
}
