<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class RestaurantRepository
{
    public function create(array $data): int
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('INSERT INTO restaurants (user_id, name, slug) VALUES (:uid, :name, :slug)');
        $stmt->execute([
            'uid' => $data['user_id'],
            'name' => trim($data['name']),
            'slug' => trim($data['slug'])
        ]);
        return (int) $conn->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM restaurants WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByUser(int $userId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM restaurants WHERE user_id = :uid ORDER BY id DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('UPDATE restaurants SET name = :name, slug = :slug WHERE id = :id');
        $stmt->execute([
            'name' => trim($data['name']),
            'slug' => trim($data['slug']),
            'id' => $id
        ]);
    }

    /**
     * Atualiza dados completos do restaurante (Configurações)
     */
    public function updateFull(int $id, array $data): void
    {
        $conn = Database::connect();
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params['name'] = $data['name'];
        }
        if (isset($data['slug'])) {
            $fields[] = 'slug = :slug';
            $params['slug'] = $data['slug'];
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = :phone';
            $params['phone'] = $data['phone'];
        }
        if (isset($data['address'])) {
            $fields[] = 'address = :address';
            $params['address'] = $data['address'];
        }
        if (isset($data['address_number'])) {
            $fields[] = 'address_number = :address_number';
            $params['address_number'] = $data['address_number'];
        }
        if (isset($data['zip_code'])) {
            $fields[] = 'zip_code = :zip_code';
            $params['zip_code'] = $data['zip_code'];
        }
        if (isset($data['primary_color'])) {
            $fields[] = 'primary_color = :primary_color';
            $params['primary_color'] = $data['primary_color'];
        }
        if (isset($data['logo'])) {
            $fields[] = 'logo = :logo';
            $params['logo'] = $data['logo'];
        }

        if (empty($fields)) {
            return;
        }

        $sql = 'UPDATE restaurants SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $conn->prepare($sql)->execute($params);
    }

    public function delete(int $id): void
    {
        $conn = Database::connect();
        $conn->prepare('DELETE FROM restaurants WHERE id = :id')->execute(['id' => $id]);
    }

    public function toggleStatus(int $id): void
    {
        $conn = Database::connect();
        $conn->prepare('UPDATE restaurants SET is_active = NOT is_active WHERE id = :id')->execute(['id' => $id]);
    }
}
