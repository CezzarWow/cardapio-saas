<?php

namespace App\Repositories\Order;

use App\Core\Database;
use PDO;

/**
 * Repository para Pedidos (API)
 *
 * Responsável exclusivamente pela tabela `orders`.
 * Para itens de pedido, use OrderItemRepository.
 * Para pagamentos de pedido, use OrderPaymentRepository.
 */
class OrderRepository
{
    /**
     * Restaura pedido (Cria com ID específico)
     */
    public function restore(array $order): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            INSERT INTO orders (id, restaurant_id, total, status, payment_method, created_at) 
            VALUES (:id, :rid, :total, :status, :pay, :date)
        ');
        $stmt->execute([
            'id' => $order['id'],
            'rid' => $order['restaurant_id'],
            'total' => $order['total'],
            'status' => $order['status'],
            'pay' => $order['payment_method'],
            'date' => $order['created_at']
        ]);
    }

    /**
     * Cria um novo pedido.
     *
     * @param array $data Dados do pedido
     * @param string $status Status inicial (padrão: 'novo', use 'aberto' para mesa/comanda)
     * @return int ID do pedido criado
     */
    public function create(array $data, string $status = 'novo'): int
    {
        $conn = Database::connect();

        $stmt = $conn->prepare("
            INSERT INTO orders (
                restaurant_id, 
                client_id,
                table_id,
                total, 
                status, 
                order_type, 
                payment_method,
                observation,
                change_for,
                source,
                created_at
            ) VALUES (
                :rid, 
                :cid,
                :tid,
                :total, 
                :status, 
                :otype, 
                :payment,
                :obs,
                :change,
                :source,
                NOW()
            )
        ");


        $stmt->execute([
            'rid' => $data['restaurant_id'],
            'cid' => $data['client_id'],
            'tid' => $data['table_id'] ?? null,
            'total' => $data['total'],
            'status' => $status,
            'otype' => $data['order_type'],
            'payment' => $data['payment_method'],
            'obs' => $data['observation'] ?? null,
            'change' => $data['change_for'] ?? null,
            'source' => $data['source'] ?? 'pdv'
        ]);

        return (int) $conn->lastInsertId();
    }

    /**
     * Busca pedido por ID
     */
    public function find(int $id, int $restaurantId = null): ?array
    {
        $conn = Database::connect();
        $sql = 'SELECT * FROM orders WHERE id = :id';
        $params = ['id' => $id];

        if ($restaurantId) {
            $sql .= ' AND restaurant_id = :rid';
            $params['rid'] = $restaurantId;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca todos os pedidos com detalhes (para listagem)
     */
    public function findAllWithDetails(int $restaurantId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare('
            SELECT o.*, 
                   COALESCE(SUM(oi.quantity * oi.price), 0) as calculated_total
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.restaurant_id = :rid
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ');
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Transições de status válidas por tipo de pedido/conta.
     *
     * - PEDIDOS (operacionais): novo → aguardando → em_preparo → pronto → entregue
     * - CONTAS (financeiras): aberto → concluido
     * - Estados finais: concluido, cancelado
     *
     * @see implementation_plan.md Seção 2.4 e 2.5
     */
    private const VALID_TRANSITIONS = [
        // PEDIDOS (operacionais) - novo NUNCA vira aberto
        'novo' => ['aguardando', 'concluido', 'cancelado'],
        'aguardando' => ['em_preparo', 'cancelado'],
        'em_preparo' => ['pronto', 'cancelado'],
        'pronto' => ['em_entrega', 'entregue', 'concluido'],
        'em_entrega' => ['entregue', 'cancelado'],
        'entregue' => ['concluido'],

        // CONTAS (financeiras)
        'aberto' => ['concluido', 'cancelado'],

        // Estados finais
        'concluido' => [],
        'cancelado' => [],
    ];

    /**
     * Atualiza status do pedido com validação de transição.
     *
     * @param int $id ID do pedido
     * @param string $newStatus Novo status desejado
     * @return int Número de linhas afetadas (deve ser 1)
     * @throws \RuntimeException Se pedido não encontrado
     * @throws \InvalidArgumentException Se transição inválida
     */
    public function updateStatus(int $id, string $newStatus): int
    {
        $conn = Database::connect();

        // Buscar status atual
        $stmt = $conn->prepare('SELECT status FROM orders WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new \RuntimeException("Pedido #{$id} não encontrado");
        }

        $currentStatus = $order['status'];
        $allowed = self::VALID_TRANSITIONS[$currentStatus] ?? [];

        if (!in_array($newStatus, $allowed)) {
            // Log para auditoria antes de lançar exceção
            error_log("[ORDER_STATUS] Transição inválida bloqueada: #{$id} {$currentStatus} → {$newStatus}");
            throw new \InvalidArgumentException(
                "Transição de status inválida: {$currentStatus} → {$newStatus} (Pedido #{$id})"
            );
        }

        // Executar UPDATE
        $updateStmt = $conn->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $updateStmt->execute(['status' => $newStatus, 'id' => $id]);

        $rowCount = $updateStmt->rowCount();

        // Log de sucesso
        error_log("[ORDER_STATUS] Transição OK: #{$id} {$currentStatus} → {$newStatus} (rows: {$rowCount})");

        return $rowCount;
    }

    /**
     * Atualiza dados de pagamento
     */
    public function updatePayment(int $id, bool $isPaid, string $method): void
    {
        $conn = Database::connect();
        $conn->prepare('UPDATE orders SET is_paid = :paid, payment_method = :method WHERE id = :id')
             ->execute(['paid' => $isPaid ? 1 : 0, 'method' => $method, 'id' => $id]);
    }

    /**
     * Atualiza cliente do pedido
     */
    public function updateClient(int $id, int $clientId): void
    {
        $conn = Database::connect();
        $conn->prepare('UPDATE orders SET client_id = :cid WHERE id = :oid')
             ->execute(['cid' => $clientId, 'oid' => $id]);
    }

    /**
     * Atualiza total do pedido
     */
    public function updateTotal(int $id, float $total): void
    {
        $conn = Database::connect();
        $conn->prepare('UPDATE orders SET total = GREATEST(0, :total) WHERE id = :id')
             ->execute(['total' => $total, 'id' => $id]);
    }

    /**
     * Atualiza o tipo do pedido
     */
    public function updateOrderType(int $id, string $orderType): void
    {
        $conn = Database::connect();
        $conn->prepare('UPDATE orders SET order_type = :ot WHERE id = :id')
             ->execute(['ot' => $orderType, 'id' => $id]);
    }

    /**
     * Deleta pedido
     */
    public function delete(int $id): void
    {
        $conn = Database::connect();
        $conn->prepare('DELETE FROM orders WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * Busca pedidos de clientes em aberto (não vinculados a mesas)
     */
    public function findOpenClientOrders(int $restaurantId): array
    {
        $conn = Database::connect();

        $sql = "
            SELECT o.*, c.name as client_name, c.phone as client_phone 
            FROM orders o
            LEFT JOIN clients c ON o.client_id = c.id
            WHERE o.restaurant_id = :rid 
            AND o.status NOT IN ('concluido', 'cancelado')
            AND o.order_type IN ('balcao', 'comanda', 'local', 'delivery', 'pickup', 'entrega', 'retirada')
            AND (o.is_paid = 0 OR o.is_paid IS NULL)
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
            AND (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) > 0
            ORDER BY o.created_at DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca comanda aberta de um cliente específico (exceto delivery)
     */
    public function findOpenByClient(int $clientId, int $restaurantId): ?array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare("
            SELECT id, total, status, order_type 
            FROM orders 
            WHERE client_id = :cid 
            AND restaurant_id = :rid 
            AND status = 'aberto'
            AND order_type IN ('comanda', 'balcao', 'local', 'delivery', 'pickup', 'entrega', 'retirada')
            ORDER BY created_at DESC 
            LIMIT 1
        ");

        $stmt->execute(['cid' => $clientId, 'rid' => $restaurantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Calcula a dívida total de um cliente (soma de pagamentos via crediário)
     *
     * @param int $clientId ID do cliente
     * @return float Total da dívida em crediário
     */
    public function getDebtByClient(int $clientId): float
    {
        $conn = Database::connect();

        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(op.amount), 0) as debt
            FROM order_payments op
            INNER JOIN orders o ON o.id = op.order_id
            WHERE o.client_id = :cid
            AND o.status != 'cancelado'
            AND op.method = 'crediario'
        ");

        $stmt->execute(['cid' => $clientId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (float) ($result['debt'] ?? 0);
    }

    /**
     * Busca histórico de pedidos de um cliente
     *
     * @param int $clientId ID do cliente
     * @param int $restaurantId ID do restaurante
     * @param int $limit Limite de registros
     * @return array Lista de pedidos
     */
    public function findByClient(int $clientId, int $restaurantId, int $limit = 20): array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare("
            SELECT 
                o.id,
                o.total,
                o.status,
                o.order_type as type,
                o.is_paid,
                o.created_at,
                CONCAT('Pedido #', o.id) as description
            FROM orders o
            WHERE o.client_id = :cid
            AND o.restaurant_id = :rid
            ORDER BY o.created_at DESC
            LIMIT :lim
        ");

        $stmt->bindValue(':cid', $clientId, PDO::PARAM_INT);
        $stmt->bindValue(':rid', $restaurantId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
