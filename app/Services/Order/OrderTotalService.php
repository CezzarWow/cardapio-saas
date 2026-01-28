<?php

namespace App\Services\Order;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\Order\OrderItemRepository;
use Exception;

/**
 * OrderTotalService
 *
 * ÚNICA fonte de verdade para cálculo e atualização de totais de pedidos.
 * Responsável por:
 * 1. Somar itens por canal (Delivery vs Mesa)
 * 2. Centralizar regras de Taxa de Entrega
 * 3. Persistir total, total_delivery e total_table com consistência
 */
class OrderTotalService
{
    private OrderItemRepository $itemRepo;

    // Tipos considerados "Delivery"
    private const DELIVERY_SOURCES = ['delivery', 'pickup', 'retirada', 'entrega'];
    
    // Tipos considerados "Mesa"
    private const TABLE_SOURCES = ['comanda', 'table', 'mesa', 'balcao', 'local'];

    public function __construct()
    {
        $this->itemRepo = new OrderItemRepository();
    }

    /**
     * Recalcula e salva os totais do pedido
     *
     * @param int $orderId
     * @return array ['total' => float, 'total_delivery' => float, 'total_table' => float]
     */
    public function recalculate(int $orderId): array
    {
        $items = $this->itemRepo->findAll($orderId);

        $totalDelivery = 0.0;
        $totalTable = 0.0;
        $globalTotal = 0.0; // Será a soma dos dois

        foreach ($items as $item) {
            $price = (float) $item['price'];
            $qty = (float) ($item['quantity'] ?? 1);
            $subtotal = $price * $qty;
            
            $source = strtolower($item['source_type'] ?? '');
            $name = $item['name'] ?? '';

            // Regra 1: Taxa de Entrega sempre vai para Delivery
            if ($name === 'Taxa de Entrega' || $name === 'Entrega' || $name === 'Frete') {
                $totalDelivery += $subtotal;
                continue;
            }

            // Regra 2: Segregação por Source Type
            if (in_array($source, self::DELIVERY_SOURCES)) {
                $totalDelivery += $subtotal;
            } elseif (in_array($source, self::TABLE_SOURCES)) {
                $totalTable += $subtotal;
            } else {
                // Caso desconhecido/legado (vazio ou NULL)
                // Se não tiver source definido, vamos tentar inferir ou jogar no "Global" sem atribuir a canal?
                // O escopo diz: "definir padrão (ex: se order_type=delivery, soma no delivery)".
                // Por segurança e simplicidade da Fase 2 (onde já checamos que não há NULLs):
                // Vamos assumir que se não é entrega/mesa explicitamente, entra no canal DEFAULT que poderia ser delivery se fosse delivery.
                // Mas, como vimos que SÓ TEM delivery e comanda hoje, qualquer outra coisa é estranha.
                
                // Vamos logar warning e assumir "Outros" => Delivery (Fallback seguro para Delivery Orders antigas)
                // Mas espere, se for uma comanda antiga sem source?
                // O diagnóstico mostrou ZERO NULLS. Então esse bloco 'else' teoricamente nunca roda.
                // Vamos manter simples: se não bate com nada, não soma em canal específico, mas soma no global? 
                // Não. global = delivery + table. 
                // Se não somar em canal, o global fica errado em relação aos itens.
                
                // Decisão: Assumir Delivery como fallback SE não for mesa explicitamente.
                // Mas vou logar para garantir.
                if (!empty($source)) {
                     Logger::warning("OrderTotalService: Item com source desconhecido", [
                        'order_id' => $orderId,
                        'item_id' => $item['id'],
                        'source' => $source
                     ]);
                }
                
                // Fallback: Se for 'legado', verificar contexto é caro. 
                // Como não temos NULLs hoje, vou somar no Delivery por padrão (maioria dos casos críticos).
                $totalDelivery += $subtotal;
            }
        }

        $globalTotal = $totalDelivery + $totalTable;

        // Persistir
        $this->updateDatabase($orderId, $globalTotal, $totalDelivery, $totalTable);

        return [
            'total' => $globalTotal,
            'total_delivery' => $totalDelivery,
            'total_table' => $totalTable
        ];
    }

    private function updateDatabase(int $orderId, float $total, float $delivery, float $table): void
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            UPDATE orders 
            SET total = :total, 
                total_delivery = :delivery, 
                total_table = :table 
            WHERE id = :id
        ");
        
        $stmt->execute([
            'total' => $total,
            'delivery' => $delivery,
            'table' => $table,
            'id' => $orderId
        ]);
    }
}
