<?php

namespace App\DTO;

/**
 * DTO para Item de Pedido.
 * ETAPA 4: type safety em vez de arrays.
 *
 * @see app/Repositories/Order/OrderItemRepository
 */
final class OrderItemDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $productId,
        public readonly string $name,
        public readonly int $quantity,
        public readonly float $price,
        /** @var array<int, mixed>|null extras decodificados */
        public readonly ?array $extras,
        public readonly ?string $observation,
    ) {
    }

    public static function fromArray(array $row): self
    {
        $extras = $row['extras'] ?? null;
        if (is_string($extras)) {
            $decoded = json_decode($extras, true);
            $extras = is_array($decoded) ? $decoded : null;
        }

        return new self(
            id: isset($row['id']) ? (int) $row['id'] : null,
            productId: (int) ($row['product_id'] ?? $row['id'] ?? 0),
            name: (string) ($row['name'] ?? ''),
            quantity: (int) ($row['quantity'] ?? 1),
            price: (float) ($row['price'] ?? 0),
            extras: $extras,
            observation: isset($row['observation']) ? (string) $row['observation'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $a = [
            'product_id' => $this->productId,
            'name' => $this->name,
            'quantity' => $this.quantity,
            'price' => $this->price,
            'extras' => $this->extras,
            'observation' => $this->observation,
        ];
        if ($this->id !== null) {
            $a['id'] = $this->id;
        }
        return $a;
    }
}
