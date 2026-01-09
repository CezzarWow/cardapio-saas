<?php
namespace App\Services\Cashier;

use App\Core\Database;
use App\Repositories\CashRegisterRepository;
use App\Repositories\Order\OrderRepository;
use PDO;
use Exception;

/**
 * CashierDashboardService - Lógica de Dashboard do Caixa
 * 
 * Responsabilidades:
 * - Buscar caixa aberto
 * - Calcular resumo de vendas
 * - Calcular movimentações
 */
class CashierDashboardService {

    private OrderRepository $orderRepo;

    private CashRegisterRepository $repo;

    public function __construct(
        CashRegisterRepository $repo,
        OrderRepository $orderRepo
    ) {
        $this->repo = $repo;
        $this->orderRepo = $orderRepo;
    }

    /**
     * Retorna caixa aberto ou null
     * (Mantido para compatibilidade se algum controller usar, mas agora delega ao Repo)
     */
    public function getOpenCashier(int $restaurantId): ?array {
        return $this->repo->findOpen($restaurantId);
    }

    /**
     * Calcula resumo de vendas por método de pagamento
     */
    public function calculateSalesSummary(int $restaurantId, string $openedAt): array {
        $vendas = $this->orderRepo->getSalesSummary($restaurantId, $openedAt);
        
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
     * Busca dados iniciais do dashboard (Método principal refatorado)
     */
    public function getDashboardData(int $restaurantId): array {
        $caixaAberto = $this->repo->findOpen($restaurantId);

        if (!$caixaAberto) {
            return [
                'status' => 'fechado',
                'movements' => [],
                'totals' => ['entrada' => 0, 'saida' => 0, 'saldo' => 0]
            ];
        }

        $movements = $this->repo->findMovements($caixaAberto['id']);
        $totals = $this->calculateTotals($movements, $caixaAberto['opening_balance']);

        return [
            'status' => 'aberto',
            'caixa' => $caixaAberto,
            'movements' => $movements,
            'totals' => $totals
        ];
    }

    /**
     * Abre um novo caixa
     */
    public function openRegister(int $restaurantId, float $amount): array {
        $caixaAberto = $this->repo->findOpen($restaurantId);

        if ($caixaAberto) {
            throw new Exception("Já existe um caixa aberto!");
        }

        $id = $this->repo->open($restaurantId, $amount);

        return ['success' => true, 'message' => 'Caixa aberto com sucesso!', 'id' => $id];
    }

    /**
     * Fecha o caixa atual
     */
    public function closeRegister(int $restaurantId): array {
        $this->repo->close($restaurantId);
        return ['success' => true, 'message' => 'Caixa fechado com sucesso!'];
    }

    /**
     * Adiciona sangria ou suprimento
     */
    public function addMovement(int $restaurantId, string $type, float $amount, string $description): array {
        $caixaAberto = $this->repo->findOpen($restaurantId);

        if (!$caixaAberto) {
            throw new Exception("Não há caixa aberto para realizar movimentação.");
        }

        $this->repo->addMovement($caixaAberto['id'], $type, $amount, $description);

        return ['success' => true, 'message' => 'Movimentação registrada!'];
    }

    /**
     * Retorna movimentos de um caixa
     */
    public function getMovements(int $cashRegisterId): array {
        return $this->repo->findMovements($cashRegisterId);
    }

    /**
     * Soma movimentos por tipo (suprimentos e sangrias)
     */
    public function sumMovements(array $movements): array {
        $suprimentos = 0;
        $sangrias = 0;
        
        foreach ($movements as $mov) {
            if ($mov['type'] === 'suprimento') {
                $suprimentos += floatval($mov['amount']);
            } elseif ($mov['type'] === 'sangria') {
                $sangrias += floatval($mov['amount']);
            }
        }
        
        return [$suprimentos, $sangrias];
    }

    /**
     * Calcula dinheiro em caixa
     */
    public function calculateCashInDrawer(float $abertura, float $vendaDinheiro, float $suprimentos, float $sangrias): float {
        return $abertura + $vendaDinheiro + $suprimentos - $sangrias;
    }

    private function calculateTotals(array $movements, float $openingBalance): array {
        $entradas = 0;
        $saidas = 0;

        foreach ($movements as $mov) {
            if ($mov['type'] == 'venda' || $mov['type'] == 'suprimento') {
                $entradas += $mov['amount'];
            } else {
                $saidas += $mov['amount'];
            }
        }

        return [
            'entrada' => $entradas,
            'saida' => $saidas,
            'saldo' => ($openingBalance + $entradas) - $saidas
        ];
    }
}
