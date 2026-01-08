<?php
namespace App\Services\Client;

use App\Core\Database;
use PDO;
use Exception;

/**
 * ClientService - Lógica de Negócio de Clientes
 */
class ClientService {

    /**
     * Busca clientes por nome ou telefone
     */
    public function search(int $restaurantId, string $term): array {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT id, name, phone FROM clients 
            WHERE restaurant_id = :rid 
            AND (name LIKE :term OR phone LIKE :term) 
            LIMIT 10
        ");
        $stmt->execute(['rid' => $restaurantId, 'term' => "%{$term}%"]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cadastra novo cliente
     * 
     * @throws Exception Se documento já existir
     */
    public function create(int $restaurantId, array $data): array {
        $conn = Database::connect();
        
        // Verifica duplicidade de documento
        if (!empty($data['document'])) {
            $check = $conn->prepare("SELECT id FROM clients WHERE restaurant_id = :rid AND document = :doc");
            $check->execute(['rid' => $restaurantId, 'doc' => $data['document']]);
            if ($check->fetch()) {
                throw new Exception('CPF/CNPJ já cadastrado neste restaurante');
            }
        }

        $stmt = $conn->prepare("
            INSERT INTO clients (restaurant_id, name, type, document, phone, zip_code, address, address_number, neighborhood, city, credit_limit, due_day) 
            VALUES (:rid, :name, :type, :doc, :phone, :zip, :addr, :num, :neigh, :city, :credit, :due)
        ");
        
        $stmt->execute([
            'rid' => $restaurantId,
            'name' => $data['name'],
            'type' => $data['type'],
            'doc' => $data['document'],
            'phone' => $data['phone'],
            'zip' => $data['zip_code'],
            'addr' => $data['address'],
            'num' => $data['address_number'],
            'neigh' => $data['neighborhood'],
            'city' => $data['city'],
            'credit' => $data['credit_limit'],
            'due' => $data['due_day']
        ]);
        
        return [
            'id' => $conn->lastInsertId(),
            'name' => $data['name'],
            'phone' => $data['phone']
        ];
    }

    /**
     * Retorna detalhes do cliente com dívida e histórico
     */
    public function getDetails(int $restaurantId, int $clientId): ?array {
        $conn = Database::connect();
        
        // Busca cliente
        $stmt = $conn->prepare("SELECT * FROM clients WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $clientId, 'rid' => $restaurantId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$client) {
            return null;
        }
        
        // Calcula dívida atual
        $client['current_debt'] = $this->calculateDebt($conn, $clientId);
        
        // Busca histórico
        $history = $this->getOrderHistory($conn, $restaurantId, $clientId);
        
        return [
            'client' => $client,
            'history' => $history
        ];
    }

    private function calculateDebt($conn, int $clientId): float {
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as debt FROM orders WHERE client_id = :cid AND is_paid = 0 AND status = 'aberto'");
        $stmt->execute(['cid' => $clientId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['debt'] ?? 0);
    }

    private function getOrderHistory($conn, int $restaurantId, int $clientId): array {
        $stmt = $conn->prepare("
            SELECT id, total, is_paid, status, created_at,
                   CASE WHEN is_paid = 1 THEN 'pagamento' ELSE 'pedido' END as type,
                   CONCAT('Pedido #', id) as description
            FROM orders 
            WHERE client_id = :cid AND restaurant_id = :rid
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute(['cid' => $clientId, 'rid' => $restaurantId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function($item) {
            return [
                'type' => $item['type'],
                'description' => $item['description'] . ($item['is_paid'] ? ' (Pago)' : ' (Aberto)'),
                'amount' => floatval($item['total']),
                'created_at' => $item['created_at']
            ];
        }, $history);
    }
}
