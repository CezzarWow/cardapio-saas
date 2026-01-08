<?php
namespace App\Services\Cashier;

use App\Core\Database;
use PDO;

/**
 * CashierDashboardService - Lógica de Dashboard do Caixa
 * 
 * Responsabilidades:
 * - Buscar caixa aberto
 * - Calcular resumo de vendas
 * - Calcular movimentações
 */
class CashierDashboardService {

    /**
     * Retorna caixa aberto ou null
     */
    public function getOpenCashier(int $restaurantId): ?array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Calcula resumo de vendas por método de pagamento
     */
    public function calculateSalesSummary(int $restaurantId, string $openedAt): array {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("
            SELECT op.method, SUM(op.amount) as total 
            FROM order_payments op
            INNER JOIN orders o ON o.id = op.order_id
            WHERE o.restaurant_id = :rid 
            AND o.created_at >= :opened_at 
            AND o.status = 'concluido'
            GROUP BY op.method
        ");
        $stmt->execute(['rid' => $restaurantId, 'opened_at' => $openedAt]);
        $vendas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $resumo = [
            'total_bruto' => 0,
            'dinheiro' => $vendas['dinheiro'] ?? 0,
            'credito' => $vendas['credito'] ?? 0,
            'debito' => $vendas['debito'] ?? 0,
            'pix' => $vendas['pix'] ?? 0,
        ];
        $resumo['total_bruto'] = array_sum($resumo);
        
        return $resumo;
    }

    /**
     * Retorna movimentações do caixa
     */
    public function getMovements(int $cashierId): array {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM cash_movements WHERE cash_register_id = :cid ORDER BY created_at DESC");
        $stmt->execute(['cid' => $cashierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Soma suprimentos e sangrias
     */
    public function sumMovements(array $movimentos): array {
        $suprimentos = 0;
        $sangrias = 0;
        
        foreach ($movimentos as $mov) {
            if ($mov['type'] == 'suprimento') $suprimentos += $mov['amount'];
            if ($mov['type'] == 'sangria') $sangrias += $mov['amount'];
        }
        
        return [$suprimentos, $sangrias];
    }

    /**
     * Calcula dinheiro físico em gaveta
     */
    public function calculateCashInDrawer(float $openingBalance, float $salesCash, float $suprimentos, float $sangrias): float {
        return $openingBalance + $salesCash + $suprimentos - $sangrias;
    }

    /**
     * Abre novo caixa
     */
    public function openCashier(int $restaurantId, float $openingBalance): void {
        $conn = Database::connect();
        $conn->prepare("INSERT INTO cash_registers (restaurant_id, opening_balance, status, opened_at) VALUES (:rid, :val, 'aberto', NOW())")
             ->execute(['rid' => $restaurantId, 'val' => $openingBalance]);
    }

    /**
     * Fecha caixa aberto
     */
    public function closeCashier(int $restaurantId): void {
        $conn = Database::connect();
        $conn->prepare("UPDATE cash_registers SET status = 'fechado', closed_at = NOW() WHERE restaurant_id = :rid AND status = 'aberto'")
             ->execute(['rid' => $restaurantId]);
    }

    /**
     * Adiciona movimento (sangria/suprimento)
     */
    public function addMovement(int $cashierId, string $type, float $amount, string $description): void {
        $conn = Database::connect();
        $conn->prepare("INSERT INTO cash_movements (cash_register_id, type, amount, description) VALUES (:cid, :type, :amount, :desc)")
             ->execute(['cid' => $cashierId, 'type' => $type, 'amount' => $amount, 'desc' => $description]);
    }
}
