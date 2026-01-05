<?php
/**
 * ============================================
 * ORDER API CONTROLLER
 * Recebe pedidos do cardápio público (web)
 * e salva no banco de dados
 * ============================================
 */
namespace App\Controllers\Api;

use App\Core\Database;
use PDO;

class OrderApiController {
    
    /**
     * Cria um novo pedido via API (cardápio web)
     * POST /api/order/create
     */
    public function create() {
        header('Content-Type: application/json');
        
        // Recebe dados JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }
        
        // Validações
        $restaurantId = $input['restaurant_id'] ?? null;
        $customerName = trim($input['customer_name'] ?? '');
        $customerPhone = trim($input['customer_phone'] ?? '');
        $orderTypeRaw = trim($input['order_type'] ?? 'delivery');
        
        // Se vier vazio, assume delivery
        if (empty($orderTypeRaw)) {
            $orderTypeRaw = 'delivery';
        }
        
        $paymentMethod = $input['payment_method'] ?? 'dinheiro';
        $items = $input['items'] ?? [];
        
        // Mapeia tipo de pedido do frontend (português) para banco (inglês)
        $orderTypeMap = [
            'entrega' => 'delivery',
            'retirada' => 'pickup', // Retirada = pickup (balcão)
            'local' => 'local',
            'delivery' => 'delivery',
            'pickup' => 'pickup'
        ];
        
        $orderType = $orderTypeMap[$orderTypeRaw] ?? 'delivery';
        
        // DEBUG: Log para ver o que está chegando (remover depois)
        error_log("OrderAPI: orderTypeRaw = '$orderTypeRaw' | orderType final = '$orderType'");
        
        if (!$restaurantId || !$customerName || empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios faltando']);
            exit;
        }
        
        try {
            $conn = Database::connect();
            $conn->beginTransaction();
            
            // 1. Criar ou buscar cliente
            $clientId = $this->getOrCreateClient($conn, $restaurantId, $customerName, $customerPhone, $input);
            
            // 2. Calcular total
            $subtotal = 0;
            foreach ($items as $item) {
                $itemTotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
                // Adiciona adicionais
                if (!empty($item['additionals'])) {
                    foreach ($item['additionals'] as $add) {
                        $itemTotal += ($add['price'] ?? 0) * ($item['quantity'] ?? 1);
                    }
                }
                $subtotal += $itemTotal;
            }
            
            $deliveryFee = floatval($input['delivery_fee'] ?? 0);
            $total = $subtotal + $deliveryFee;
            
            // Formata troco se houver (converte "R$ 50,00" para decimal)
            $changeAmount = $input['change_amount'] ?? null;
            if ($changeAmount) {
                $changeAmount = str_replace(['R$', ' ', '.'], '', $changeAmount); // Remove R$, espaço e ponto
                $changeAmount = str_replace(',', '.', $changeAmount); // Vírgula vira ponto
                $changeAmount = floatval($changeAmount);
            }

            // 3. Criar pedido
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    restaurant_id, 
                    client_id, 
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
                    :total, 
                    'novo', 
                    :otype, 
                    :payment,
                    :obs,
                    :change,
                    'web',
                    NOW()
                )
            ");
            
            // DEBUG: Log dos dados que serão salvos
            error_log("OrderAPI INSERT: otype = '$orderType'");
            
            $stmt->execute([
                'rid' => $restaurantId,
                'cid' => $clientId,
                'total' => $total,
                'otype' => $orderType,
                'payment' => $paymentMethod,
                'obs' => $input['observation'] ?? null,
                'change' => $changeAmount
            ]);
            
            // Força update do order_type caso tenha falhado
            $orderId = $conn->lastInsertId();
            $conn->prepare("UPDATE orders SET order_type = :ot WHERE id = :oid AND (order_type IS NULL OR order_type = '')")
                 ->execute(['ot' => $orderType, 'oid' => $orderId]);
            
            // 4. Inserir itens do pedido
            $stmtItem = $conn->prepare("
                INSERT INTO order_items (
                    order_id, 
                    product_id, 
                    name, 
                    quantity, 
                    price
                ) VALUES (
                    :oid, 
                    :pid, 
                    :name, 
                    :qty, 
                    :price
                )
            ");
            
            foreach ($items as $item) {
                $itemPrice = $item['unit_price'] ?? 0;
                
                // Se tem adicionais, soma no preço unitário
                if (!empty($item['additionals'])) {
                    foreach ($item['additionals'] as $add) {
                        $itemPrice += ($add['price'] ?? 0);
                    }
                }
                
                $stmtItem->execute([
                    'oid' => $orderId,
                    'pid' => $item['product_id'] ?? null,
                    'name' => $item['name'] ?? 'Produto',
                    'qty' => $item['quantity'] ?? 1,
                    'price' => $itemPrice
                ]);
            }
            
            $conn->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Pedido criado com sucesso',
                'order_id' => $orderId
            ]);
            
        } catch (\Exception $e) {
            if (isset($conn)) $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao criar pedido: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Busca ou cria um cliente
     * @return int ID do cliente
     */
    private function getOrCreateClient($conn, $restaurantId, $name, $phone, $input) {
        // Tenta buscar por telefone (se fornecido)
        if (!empty($phone)) {
            $stmt = $conn->prepare("
                SELECT id FROM clients 
                WHERE restaurant_id = :rid 
                AND phone = :phone 
                LIMIT 1
            ");
            $stmt->execute(['rid' => $restaurantId, 'phone' => $phone]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Atualiza dados se mudaram
                $conn->prepare("
                    UPDATE clients SET 
                        name = :name,
                        address = :address,
                        address_number = :number,
                        neighborhood = :neighborhood
                    WHERE id = :id
                ")->execute([
                    'name' => $name,
                    'address' => $input['customer_address'] ?? null,
                    'number' => $input['customer_number'] ?? null,
                    'neighborhood' => $input['customer_neighborhood'] ?? null,
                    'id' => $existing['id']
                ]);
                
                return $existing['id'];
            }
        }
        
        // Cria novo cliente
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
            'name' => $name,
            'phone' => $phone,
            'address' => $input['customer_address'] ?? null,
            'number' => $input['customer_number'] ?? null,
            'neighborhood' => $input['customer_neighborhood'] ?? null
        ]);
        
        return $conn->lastInsertId();
    }
}
