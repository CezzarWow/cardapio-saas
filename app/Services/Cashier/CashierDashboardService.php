<?php

namespace App\Services\Cashier;

use App\Repositories\CashRegisterRepository;
use App\Repositories\Order\OrderPaymentRepository;
use App\Repositories\Order\OrderRepository;
use Exception;

/**
 * CashierDashboardService - Lógica de Dashboard do Caixa
 *
 * Responsabilidades:
 * - Buscar caixa aberto
 * - Calcular resumo de vendas
 * - Calcular movimentações
 */
class CashierDashboardService
{
    private OrderRepository $orderRepo;
    private OrderPaymentRepository $paymentRepo;

    private CashRegisterRepository $repo;

    public function __construct(
        CashRegisterRepository $repo,
        OrderRepository $orderRepo,
        OrderPaymentRepository $paymentRepo
    ) {
        $this->repo = $repo;
        $this->orderRepo = $orderRepo;
        $this->paymentRepo = $paymentRepo;
    }

    /**
     * Retorna caixa aberto ou null
     * (Mantido para compatibilidade se algum controller usar, mas agora delega ao Repo)
     */
    public function getOpenCashier(int $restaurantId): ?array
    {
        return $this->repo->findOpen($restaurantId);
    }

    /**
     * Calcula resumo de vendas por método de pagamento
     */
    public function calculateSalesSummary(int $restaurantId, string $openedAt): array
    {
        $vendas = $this->paymentRepo->getSalesSummary($restaurantId, $openedAt);

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
    public function getDashboardData(int $restaurantId): array
    {
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
    public function openRegister(int $restaurantId, float $amount): array
    {
        $caixaAberto = $this->repo->findOpen($restaurantId);

        if ($caixaAberto) {
            throw new Exception('Já existe um caixa aberto!');
        }

        $id = $this->repo->open($restaurantId, $amount);

        return ['success' => true, 'message' => 'Caixa aberto com sucesso!', 'id' => $id];
    }

    /**
     * Verifica se há pendências que impedem o fechamento do caixa
     * @return array Lista de pendências encontradas
     */
    public function checkPendingItems(int $restaurantId): array
    {
        $pendencias = [];
        $conn = \App\Core\Database::connect();

        // Buscar data de abertura do caixa atual
        $caixaAberto = $this->repo->findOpen($restaurantId);
        $openedAt = $caixaAberto ? $caixaAberto['opened_at'] : date('Y-m-d 00:00:00');

        // 1. Verificar mesas ocupadas
        $stmtMesas = $conn->prepare("
            SELECT COUNT(*) as total FROM tables 
            WHERE restaurant_id = :rid AND current_order_id IS NOT NULL
        ");
        $stmtMesas->execute(['rid' => $restaurantId]);
        $mesasOcupadas = (int) $stmtMesas->fetch(\PDO::FETCH_ASSOC)['total'];
        
        if ($mesasOcupadas > 0) {
            $pendencias[] = [
                'tipo' => 'mesas',
                'quantidade' => $mesasOcupadas,
                'mensagem' => "Existem {$mesasOcupadas} mesa(s) ocupada(s) que precisam ser fechadas"
            ];
        }

        // 2. Verificar pedidos delivery/pickup não finalizados (apenas do turno atual)
        $stmtDelivery = $conn->prepare("
            SELECT COUNT(*) as total FROM orders 
            WHERE restaurant_id = :rid 
              AND order_type IN ('delivery', 'pickup')
              AND status NOT IN ('entregue', 'cancelado')
              AND created_at >= :opened_at
        ");
        $stmtDelivery->execute(['rid' => $restaurantId, 'opened_at' => $openedAt]);
        $deliveryPendentes = (int) $stmtDelivery->fetch(\PDO::FETCH_ASSOC)['total'];
        
        if ($deliveryPendentes > 0) {
            $pendencias[] = [
                'tipo' => 'delivery',
                'quantidade' => $deliveryPendentes,
                'mensagem' => "Existem {$deliveryPendentes} pedido(s) de entrega/retirada pendente(s)"
            ];
        }

        // 3. Verificar comandas de clientes abertas (sem mesa, status = 'aberto')
        // Nota: Comandas abertas de QUALQUER data bloqueiam fechamento (cliente não pagou)
        $stmtClientes = $conn->prepare("
            SELECT COUNT(*) as total FROM orders 
            WHERE restaurant_id = :rid 
              AND table_id IS NULL 
              AND order_type NOT IN ('delivery', 'pickup')
              AND status = 'aberto'
        ");
        $stmtClientes->execute(['rid' => $restaurantId]);
        $clientesAbertos = (int) $stmtClientes->fetch(\PDO::FETCH_ASSOC)['total'];
        
        if ($clientesAbertos > 0) {
            $pendencias[] = [
                'tipo' => 'clientes',
                'quantidade' => $clientesAbertos,
                'mensagem' => "Existem {$clientesAbertos} comanda(s) de cliente(s) em aberto"
            ];
        }

        return $pendencias;
    }

    /**
     * Fecha o caixa atual (com validação de pendências)
     */
    public function closeRegister(int $restaurantId): array
    {
        // Verificar pendências antes de fechar
        $pendencias = $this->checkPendingItems($restaurantId);
        
        if (!empty($pendencias)) {
            return [
                'success' => false,
                'error' => 'pending_items',
                'pendencias' => $pendencias,
                'message' => 'Não é possível fechar o caixa. Existem itens pendentes.'
            ];
        }

        $this->repo->close($restaurantId);
        return ['success' => true, 'message' => 'Caixa fechado com sucesso!'];
    }

    /**
     * Adiciona sangria ou suprimento
     */
    public function addMovement(int $restaurantId, string $type, float $amount, string $description): array
    {
        $caixaAberto = $this->repo->findOpen($restaurantId);

        if (!$caixaAberto) {
            throw new Exception('Não há caixa aberto para realizar movimentação.');
        }

        $this->repo->addMovement($caixaAberto['id'], $type, $amount, $description);

        return ['success' => true, 'message' => 'Movimentação registrada!'];
    }

    /**
     * Retorna movimentos de um caixa
     */
    public function getMovements(int $cashRegisterId): array
    {
        return $this->repo->findMovements($cashRegisterId);
    }

    /**
     * Soma movimentos por tipo (suprimentos e sangrias)
     */
    public function sumMovements(array $movements): array
    {
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
    public function calculateCashInDrawer(float $abertura, float $vendaDinheiro, float $suprimentos, float $sangrias): float
    {
        return $abertura + $vendaDinheiro + $suprimentos - $sangrias;
    }

    private function calculateTotals(array $movements, float $openingBalance): array
    {
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
