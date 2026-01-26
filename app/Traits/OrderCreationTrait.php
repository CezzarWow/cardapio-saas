<?php

namespace App\Traits;

use App\Core\Logger;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\StockRepository;

/**
 * OrderCreationTrait
 * 
 * Trait com métodos comuns para criação de pedidos.
 * Elimina duplicação entre Actions (Mesa, Comanda, Delivery, Balcão).
 */
trait OrderCreationTrait
{
    /**
     * Insere itens no pedido e baixa estoque
     * 
     * @param int $orderId ID do pedido
     * @param array $cart Carrinho de itens
     * @param OrderItemRepository $itemRepo Repository de itens
     * @param StockRepository $stockRepo Repository de estoque
     * @return void
     */
    protected function insertItemsAndDecrementStock(
        int $orderId,
        array $cart,
        OrderItemRepository $itemRepo,
        StockRepository $stockRepo
    ): void {
        // Inserir itens
        $itemRepo->insert($orderId, $cart);

        // Baixar estoque
        foreach ($cart as $item) {
            $stockRepo->decrement($item['id'], $item['quantity']);
        }
    }

    /**
     * Loga criação de pedido de forma padronizada
     * 
     * @param string $type Tipo do pedido (MESA, COMANDA, DELIVERY, BALCAO)
     * @param int $orderId ID do pedido
     * @param array $context Contexto adicional (total, table_number, client_name, etc)
     * @return void
     */
    protected function logOrderCreated(string $type, int $orderId, array $context = []): void
    {
        $message = "[{$type}] Pedido #{$orderId} criado";
        
        if (isset($context['total'])) {
            $message .= ". Total: R$ " . number_format($context['total'], 2, ',', '.');
        }

        Logger::info($message, array_merge([
            'order_id' => $orderId,
            'type' => strtolower($type)
        ], $context));
    }

    /**
     * Loga erro de forma padronizada
     * 
     * @param string $type Tipo do pedido (MESA, COMANDA, DELIVERY, BALCAO)
     * @param string $action Ação que falhou (abrir, fechar, adicionar, etc)
     * @param \Throwable $e Exceção capturada
     * @param array $context Contexto adicional
     * @return void
     */
    protected function logOrderError(string $type, string $action, \Throwable $e, array $context = []): void
    {
        Logger::error("[{$type}] ERRO ao {$action}", array_merge([
            'type' => strtolower($type),
            'action' => $action,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], $context));
    }
}
