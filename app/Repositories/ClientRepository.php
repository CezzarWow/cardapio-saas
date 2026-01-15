<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ClientRepository
{
    /**
     * Busca cliente por ID
     */
    public function find(int $id, int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT * FROM clients WHERE id = :id AND restaurant_id = :rid');
        $stmt->execute(['id' => $id, 'rid' => $restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca por nome ou telefone (Autocomplete)
     */
    public function search(int $restaurantId, string $term): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT id, name, phone, credit_limit FROM clients 
            WHERE restaurant_id = :rid 
            AND (name LIKE :term OR phone LIKE :term) 
            LIMIT 10
        ');
        $stmt->execute(['rid' => $restaurantId, 'term' => "%{$term}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca por documento (CPF/CNPJ)
     */
    public function findByDocument(int $restaurantId, string $document): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('SELECT id FROM clients WHERE restaurant_id = :rid AND document = :doc LIMIT 1');
        $stmt->execute(['rid' => $restaurantId, 'doc' => $document]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca por telefone (normaliza para comparar apenas dígitos)
     */
    public function findByPhone(int $restaurantId, string $phone): ?array
    {
        // Normaliza telefone (remove tudo que não é dígito)
        $normalizedPhone = preg_replace('/\D/', '', $phone);

        if (empty($normalizedPhone)) {
            return null;
        }

        $conn = Database::connect();

        // Busca comparando apenas dígitos (REGEXP remove não-dígitos no MySQL)
        $stmt = $conn->prepare("
            SELECT * FROM clients 
            WHERE restaurant_id = :rid 
            AND REGEXP_REPLACE(phone, '[^0-9]', '') = :phone 
            LIMIT 1
        ");
        $stmt->execute(['rid' => $restaurantId, 'phone' => $normalizedPhone]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Cria novo cliente
     */
    public function create(int $restaurantId, array $data): int
    {
        $conn = Database::connect();

        // Normaliza telefone (remove formatação)
        $phone = isset($data['phone']) ? preg_replace('/\D/', '', $data['phone']) : null;

        $stmt = $conn->prepare('
            INSERT INTO clients (restaurant_id, name, type, document, phone, zip_code, address, address_number, neighborhood, city, credit_limit, due_day) 
            VALUES (:rid, :name, :type, :doc, :phone, :zip, :addr, :num, :neigh, :city, :credit, :due)
        ');

        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'fisica',
            'doc' => $data['document'] ?? null,
            'phone' => $phone,
            'zip' => $data['zip_code'] ?? null,
            'addr' => $data['address'] ?? null,
            'num' => $data['address_number'] ?? null,
            'neigh' => $data['neighborhood'] ?? null,
            'city' => $data['city'] ?? null,
            'credit' => $data['credit_limit'] ?? 0,
            'due' => $data['due_day'] ?? null
        ]);

        return (int) $conn->lastInsertId();
    }

    /**
     * Atualiza cliente
     */
    public function update(int $id, array $data): void
    {
        $conn = Database::connect();

        // Montar query dinamicamente seria ideal, mas vou fazer estático baseado no uso atual do CreateWebOrder
        // Porém ClientService não tem update? CreateWebOrder tem findOrCreate.
        // Vou manter compatibilidade com CreateWebOrder::update (name, address, number, neighborhood)

        $sql = 'UPDATE clients SET name = :name';
        $params = ['name' => $data['name'], 'id' => $id];

        if (isset($data['address'])) {
            $sql .= ', address = :address';
            $params['address'] = $data['address'];
        }
        if (isset($data['number'])) {
            $sql .= ', address_number = :number';
            $params['number'] = $data['number'];
        }
        if (isset($data['neighborhood'])) {
            $sql .= ', neighborhood = :neighborhood';
            $params['neighborhood'] = $data['neighborhood'];
        }

        $sql .= ' WHERE id = :id';

        $conn->prepare($sql)->execute($params);
    }

    /**
     * Busca ou cria cliente (Compatible with CreateWebOrder logic)
     */
    public function findOrCreate(int $restaurantId, array $data): int
    {
        $phone = $data['phone'] ?? '';

        if (!empty($phone)) {
            $existing = $this->findByPhone($restaurantId, $phone);

            if ($existing) {
                // Atualiza dados básicos se vierem
                $this->update($existing['id'], $data);
                return $existing['id'];
            }
        }

        return $this->create($restaurantId, $data);
    }
}
