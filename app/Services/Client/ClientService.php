<?php
namespace App\Services\Client;

use App\Repositories\ClientRepository;
use App\Repositories\Order\OrderRepository;
use Exception;

/**
 * ClientService - Lógica de Negócio de Clientes
 */
class ClientService {

    private ClientRepository $clientRepo;
    private OrderRepository $orderRepo;

    public function __construct(ClientRepository $clientRepo, OrderRepository $orderRepo) {
        $this->clientRepo = $clientRepo;
        $this->orderRepo = $orderRepo;
    }

    /**
     * Busca clientes por nome ou telefone
     * Inclui informação se o cliente tem comanda aberta
     */
    public function search(int $restaurantId, string $term): array {
        $clients = $this->clientRepo->search($restaurantId, $term);
        
        // Verificar quais clientes têm comanda aberta
        foreach ($clients as &$client) {
            $openOrder = $this->orderRepo->findOpenByClient($client['id'], $restaurantId);
            $client['has_open_order'] = $openOrder !== null;
            $client['open_order_id'] = $openOrder['id'] ?? null;
            $client['open_order_total'] = $openOrder['total'] ?? null;
        }
        
        return $clients;
    }

    /**
     * Cadastra novo cliente
     * 
     * @throws Exception Se documento já existir
     */
    public function create(int $restaurantId, array $data): array {
        // Verifica duplicidade de documento
        if (!empty($data['document'])) {
            $exists = $this->clientRepo->findByDocument($restaurantId, $data['document']);
            if ($exists) {
                throw new Exception('CPF/CNPJ já cadastrado neste restaurante');
            }
        }

        $id = $this->clientRepo->create($restaurantId, $data);
        
        return [
            'id' => $id,
            'name' => $data['name'],
            'phone' => $data['phone']
        ];
    }

    /**
     * Retorna detalhes do cliente com dívida e histórico
     */
    public function getDetails(int $restaurantId, int $clientId): ?array {
        $client = $this->clientRepo->find($clientId, $restaurantId);
        
        if (!$client) {
            return null;
        }
        
        // Calcula dívida atual
        $client['current_debt'] = $this->orderRepo->getDebtByClient($clientId);
        
        // Busca histórico
        $historyRaw = $this->orderRepo->findByClient($clientId, $restaurantId, 20);
        
        $history = array_map(function($item) {
            return [
                'type' => $item['type'],
                'description' => $item['description'] . ($item['is_paid'] ? ' (Pago)' : ' (Aberto)'),
                'amount' => floatval($item['total']),
                'created_at' => $item['created_at']
            ];
        }, $historyRaw);
        
        return [
            'client' => $client,
            'history' => $history
        ];
    }
}
