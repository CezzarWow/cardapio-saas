<?php

namespace App\Repositories\Order;

use App\Core\Database;
use PDO;

/**
 * Repository para Itens de Pedido
 * 
 * Responsável exclusivamente pela tabela `order_items`
 */
class OrderItemRepository
{
    /**
     * Busca todos os itens de um pedido
     */
    public function findAll(int $orderId): array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT product_id, quantity, price, id, name, extras, observation 
            FROM order_items 
            WHERE order_id = :oid
        ");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um item específico
     */
    public function find(int $itemId, int $orderId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT product_id, quantity, price 
            FROM order_items 
            WHERE id = :id AND order_id = :oid
        ");
        $stmt->execute(['id' => $itemId, 'oid' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Insere múltiplos itens em um pedido (agrupa itens duplicados)
     */
    public function insert(int $orderId, array $items): void
    {
        $conn = Database::connect();
        
        // Preparar statements
        $stmtFind = $conn->prepare("
            SELECT id, quantity FROM order_items 
            WHERE order_id = :oid AND product_id = :pid
        ");
        
        $stmtInsert = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, name, quantity, price, extras, observation) 
            VALUES (:oid, :pid, :name, :qty, :price, :extras, :obs)
        ");
        
        $stmtUpdate = $conn->prepare("
            UPDATE order_items SET quantity = :qty WHERE id = :id
        ");
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? ($item['id'] ?? null);
            $quantity = $item['quantity'] ?? 1;
            $obs = $item['observation'] ?? null;
            
            // Tratamento de Extras
            $rawExtras = $item['extras'] ?? [];
            if (empty($rawExtras)) {
                $rawExtras = null; // Normaliza vazio para null
            }
            $extrasJson = $rawExtras ? json_encode($rawExtras) : null;
            
            // Busca item IDENTICO no banco (mesmo produto, mesmos extras, mesma obs)
            // SQL não compara JSON facilmente, então buscamos todos candidatos do produto
            $stmtFind->execute(['oid' => $orderId, 'pid' => $productId]);
            $candidates = $stmtFind->fetchAll(PDO::FETCH_ASSOC);
            
            $matchId = null;
            
            // Procura match exato nos candidatos
            foreach ($candidates as $cand) {
                // Busca dados completos do item para comparar observação e extras
                // A query do stmtFind trazia só ID e Qty. Preciso de mais dados?
                // Sim. Vou ajustar a query stmtFind ali em cima ou fazer query limpa aqui.
                // Ajuste rápido: fazer query específica de check.
                
                // Melhor: Alterar a query stmtFind (linha 53) para trazer extras e observation
                // Mas como estou editando bloco, vou fazer query ad-hoc aqui para garantir.
                $checkStmt = $conn->prepare("SELECT id, quantity, extras, observation FROM order_items WHERE id = :id");
                $checkStmt->execute(['id' => $cand['id']]);
                $fullCand = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$fullCand) continue;
                
                // Comparação
                $candExtras = $fullCand['extras']; // Vem como string JSON ou null
                $candObs = $fullCand['observation'];
                
                // Normaliza extras do banco (null ou "[]" ou string)
                $candExtrasNorm = ($candExtras === '[]' || empty($candExtras)) ? null : $candExtras;
                
                // Compara Extras (String JSON exata ou ambos null)
                $extrasMatch = ($extrasJson === $candExtrasNorm);
                
                // Compara Observação
                $obsMatch = ((string)$obs === (string)$candObs);
                
                if ($extrasMatch && $obsMatch) {
                    $matchId = $fullCand['id'];
                    $currentQty = $fullCand['quantity'];
                    break;
                }
            }
            
            if ($matchId) {
                // Item identico existe: Incrementar
                $newQty = $currentQty + $quantity;
                $stmtUpdate->execute(['qty' => $newQty, 'id' => $matchId]);
            } else {
                // Novo Item
                $stmtInsert->execute([
                    'oid' => $orderId,
                    'pid' => $productId,
                    'name' => $item['name'] ?? 'Produto',
                    'qty' => $quantity,
                    'price' => $item['price'],
                    'extras' => $extrasJson,
                    'obs' => $obs
                ]);
            }
        }
    }

    /**
     * Adiciona um único item ao pedido
     */
    public function add(int $orderId, array $item): void
    {
        $this->insert($orderId, [$item]);
    }

    /**
     * Atualiza quantidade de um item
     */
    public function updateQuantity(int $itemId, int $quantity): void
    {
        $conn = Database::connect();
        $conn->prepare("UPDATE order_items SET quantity = :qty WHERE id = :id")
             ->execute(['qty' => $quantity, 'id' => $itemId]);
    }

    /**
     * Deleta todos os itens de um pedido
     */
    public function deleteAll(int $orderId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_items WHERE order_id = :oid")
             ->execute(['oid' => $orderId]);
    }

    /**
     * Deleta um item específico
     */
    public function delete(int $itemId): void
    {
        $conn = Database::connect();
        $conn->prepare("DELETE FROM order_items WHERE id = :id")
             ->execute(['id' => $itemId]);
    }
}
