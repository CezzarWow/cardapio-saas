<?php

namespace App\Repositories\Order;

use App\Core\Database;
use PDO;

/**
 * Repository para Clientes (usado pela API de pedidos)
 */
class ClientRepository
{
    /**
     * Busca cliente por telefone
     */
    public function findByPhone(int $restaurantId, string $phone): ?array
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT id FROM clients 
            WHERE restaurant_id = :rid 
            AND phone = :phone 
            LIMIT 1
        ");
        $stmt->execute(['rid' => $restaurantId, 'phone' => $phone]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Atualiza dados de um cliente existente
     */
    public function update(int $id, array $data): void
    {
        $conn = Database::connect();
        
        $conn->prepare("
            UPDATE clients SET 
                name = :name,
                address = :address,
                address_number = :number,
                neighborhood = :neighborhood
            WHERE id = :id
        ")->execute([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'number' => $data['number'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'id' => $id
        ]);
    }

    /**
     * Cria novo cliente
     * @return int ID do cliente criado
     */
    public function create(int $restaurantId, array $data): int
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            INSERT INTO clients (
                restaurant_id, 
                name, 
                phone, 
                address,
                address_number,
                neighborhood
            ) VALUES (
                :rid, 
                :name, 
                :phone, 
                :address,
                :number,
                :neighborhood
            )
        ");
        
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'number' => $data['number'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null
        ]);
        
        return (int) $conn->lastInsertId();
    }

    /**
     * Busca ou cria cliente (helper)
     * @return int ID do cliente
     */
    public function findOrCreate(int $restaurantId, array $data): int
    {
        $phone = $data['phone'] ?? '';
        
        // Se tem telefone, tenta buscar existente
        if (!empty($phone)) {
            $existing = $this->findByPhone($restaurantId, $phone);
            
            if ($existing) {
                // Atualiza dados
                $this->update($existing['id'], $data);
                return $existing['id'];
            }
        }
        
        // Cria novo
        return $this->create($restaurantId, $data);
    }
}
