<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AdditionalGroupRepository
{
    /**
     * Persiste um novo Grupo de Adicionais
     * Contrato Explícito: Recebe primitivos tipados, não array genérico.
     */
    public function save(int $restaurantId, string $name, int $required = 0): int
    {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("INSERT INTO additional_groups (restaurant_id, name, required) VALUES (:rid, :name, :req)");
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $name,
            'req' => $required
        ]);
        
        return (int) $conn->lastInsertId();
    }

    /**
     * Busca um grupo por ID e RestaurantID
     * Retorna array ou null (CQRS Lite: Leitura simples pode devolver array puro)
     */
    public function findById(int $id, int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM additional_groups WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Verifica se existe um grupo com este nome para este restaurante
     * (Usado para validação de unicidade no Domain)
     */
    public function nameExists(int $restaurantId, string $name, ?int $excludeId = null): bool
    {
        $conn = Database::connect();
        $sql = "SELECT id FROM additional_groups WHERE restaurant_id = :rid AND name = :name";
        $params = ['rid' => $restaurantId, 'name' => $name];

        if ($excludeId) {
            $sql .= " AND id != :exc";
            $params['exc'] = $excludeId;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return (bool) $stmt->fetch();
    }
}
