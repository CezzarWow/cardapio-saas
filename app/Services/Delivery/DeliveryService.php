<?php

namespace App\Services\Delivery;

use App\Repositories\Delivery\DeliveryOrderRepository;

/**
 * DeliveryService - Lógica de Negócio de Delivery/Pedidos
 * Unifica consultas, atualização de status e envio para mesas
 */
class DeliveryService
{
    private DeliveryOrderRepository $repository;

    /**
     * Transições permitidas de status (Regra de Negócio)
     */
    private const TRANSITIONS = [
        'novo'    => ['preparo', 'cancelado'],
        'preparo' => ['rota', 'cancelado'],
        'rota'    => ['entregue', 'cancelado'],
        // Adicionei suporte reverso opcional ou fluxo simplificado se necessário depois
    ];

    public function __construct(DeliveryOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    // ====================================================
    // CONSULTAS (QUERIES)
    // ====================================================

    /**
     * Busca pedidos para o Kanban
     */
    public function getOrders(int $restaurantId, ?string $statusFilter = null): array
    {
        return $this->repository->fetchByRestaurant($restaurantId, $statusFilter);
    }

    /**
     * Busca histórico por dia operacional
     */
    public function getOrdersByOperationalDay(int $restaurantId, string $date): array
    {
        return $this->repository->fetchByOperationalDay($restaurantId, $date);
    }

    /**
     * Detalhes do pedido para modal
     */
    public function getOrderDetails(int $orderId, int $restaurantId): ?array
    {
        $order = $this->repository->findWithDetails($orderId, $restaurantId);

        if (!$order) {
            return null;
        }

        // Separa items
        $allItems = $order['items'] ?? [];
        
        // Filtra itens baseados no tipo do pedido (Se for Delivery/Retirada, mostra apenas itens dessa origem)
        // Isso resolve o problema de mostrar itens de Mesa junto com Delivery no modal de detalhes
        $orderType = strtolower($order['order_type'] ?? '');
        $isDeliveryOrPickup = in_array($orderType, ['delivery', 'entrega', 'pickup', 'retirada']);

        $filteredItems = [];
        $calculatedTotal = 0;

        foreach ($allItems as $item) {
            $source = strtolower(trim($item['source_type'] ?? ''));
            
            // Lógica de inclusão:
            // 1. Se item tem source_type 'delivery'/'pickup', inclui
            // 2. Se item não tem source_type (antigo) e o pedido é delivery, inclui (fallback comanda antiga)
            // 3. Taxa de entrega sempre inclui se for delivery
            
            $isStartItem = empty($source) || $source === 'legado'; // Item sem source
            $isTargetSource = in_array($source, ['delivery', 'entrega', 'pickup', 'retirada']);
            $isFee = ($item['name'] ?? '') === 'Taxa de Entrega';

            if ($isDeliveryOrPickup) {
                if ($isTargetSource || $isStartItem || $isFee) {
                    $filteredItems[] = $item;
                    $calculatedTotal += ($item['price'] * ($item['quantity'] ?? 1));
                }
            } else {
                // Se não for delivery (ex: mesa), mostra tudo (comportamento padrão)
                $filteredItems[] = $item;
                $calculatedTotal += ($item['price'] * ($item['quantity'] ?? 1));
            }
        }

        // Se filtrou algo (quantidade diferente), usa o total recalculado para exibição
        if ($isDeliveryOrPickup) {
             if (!empty($order['total_delivery']) && $order['total_delivery'] > 0) {
                 $order['total'] = $order['total_delivery'];
             } elseif (count($filteredItems) < count($allItems)) {
                 $order['total'] = $calculatedTotal;
             }
        }

        unset($order['items']); // Remove para não duplicar no retorno

        return [
            'order' => $order,
            'items' => $filteredItems
        ];
    }

    // ====================================================
    // AÇÕES (COMMANDS)
    // ====================================================

    /**
     * Atualiza status com validação de transição
     */
    public function updateStatus(int $orderId, string $newStatus, int $restaurantId): array
    {
        $order = $this->repository->findById($orderId, $restaurantId);

        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        if (!in_array($order['order_type'], ['delivery', 'pickup'])) {
            return ['success' => false, 'message' => 'Este pedido não é delivery/retirada'];
        }

        $currentStatus = $order['status'];

        // Permite "forçar" update se for admin? Por enquanto regra estrita.
        // Se status igual, retorna ok
        if ($currentStatus === $newStatus) {
            return ['success' => true, 'message' => 'Status já atualizado', 'new_status' => $newStatus];
        }

        $allowed = self::TRANSITIONS[$currentStatus] ?? [];

        // Permite atualizações livres se o status atual não estiver mapeado (ex: entregue)
        // Mas se estiver, exige seguir o fluxo.
        // E 'cancelado' é terminal?
        if (isset(self::TRANSITIONS[$currentStatus]) && !in_array($newStatus, $allowed)) {
            return [
                'success' => false,
                'message' => "Transição inválida: {$currentStatus} → {$newStatus}"
            ];
        }

        $this->repository->updateStatus($orderId, $newStatus);

        return [
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'new_status' => $newStatus
        ];
    }

    /**
     * Envia pedido Local para Mesas
     */
    public function sendToTable(int $orderId, int $restaurantId): array
    {
        $order = $this->repository->findById($orderId, $restaurantId);

        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        if ($order['order_type'] !== 'local') {
            return ['success' => false, 'message' => 'Este pedido não é do tipo Local'];
        }

        if ($order['status'] !== 'novo') {
            return ['success' => false, 'message' => 'Pedido já foi enviado ou processado'];
        }

        // 'aberto' faz aparecer na aba Mesas
        $this->repository->updateStatus($orderId, 'aberto');

        return [
            'success' => true,
            'message' => 'Pedido enviado para Mesas com sucesso'
        ];
    }
    public function checkOrdersState(int $restaurantId): string
    {
        return $this->repository->getLastUpdateHash($restaurantId);
    }

    /**
     * Hub Unificado: Retorna dados do cliente e todos seus pedidos ativos
     */
    public function getClientHubData(int $orderId, int $restaurantId): array
    {
        // 1. Acha o pedido original para descobrir o cliente
        $order = $this->repository->findById($orderId, $restaurantId);
        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        // Tenta achar cliente via tabela orders (client_id)
        // Se for nulo, talvez precise buscar via tabela de mesas? Por enquanto assume orders.client_id
        // Para mesas anônimas, o hub talvez não funcione bem (só mostraria a mesa).
        // Mas o foco aqui é Client Hub.
        
        // *Preciso do client_id, mas o findById simples não retorna. Vou usar o findWithDetails ou melhorar o findById.
        // O repositorio->findById retorna array associativo básico. Se client_id não vier, busco full.
        $fullOrder = $this->repository->findWithDetails($orderId, $restaurantId);
        if (!$fullOrder || empty($fullOrder['client_id'])) {
            // Se não tem cliente vinculado, retorna só esse pedido como "hub de 1 item"
            // Ou retorna erro? O usuário quer um Hub de Cliente.
            // Para mesa sem cliente cadastrado, é um "Cliente Anônimo".
            return [
                'success' => true,
                'client' => [
                    'name' => $fullOrder['client_name'] ?? ('Mesa ' . ($fullOrder['table_number'] ?? '?')),
                    'phone' => '--',
                    'is_anonymous' => true
                ],
                'orders' => [$fullOrder] 
            ];
        }

        $clientId = $fullOrder['client_id'];

        // 2. Busca tudo do cliente
        $data = $this->repository->fetchClientHubData($clientId, $restaurantId);

        return [
            'success' => true,
            'client' => $data['client'],
            'orders' => $data['orders']
        ];
    }

    /**
     * Retorna apenas os dados de um pedido específico (sem agregar outros pedidos do cliente)
     */
    public function getSingleOrderData(int $orderId, int $restaurantId): array
    {
        $order = $this->repository->findWithDetails($orderId, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        return [
            'success' => true,
            'client' => [
                'name' => $order['client_name'] ?? ('Mesa ' . ($order['table_number'] ?? '?')),
                'phone' => $order['client_phone'] ?? '--',
            ],
            'orders' => [$order] // Apenas este pedido
        ];
    }
}
