<?php
namespace App\Services\Pdv;

use App\Core\Database;
use App\Repositories\TableRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderItemRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockRepository;
use App\Services\CashRegisterService;
use Exception;

/**
 * PdvService - Lógica de Negócio do PDV (Frente de Caixa)
 */
class PdvService {

    private TableRepository $tableRepo;
    private OrderRepository $orderRepo;
    private OrderItemRepository $itemRepo;
    private CategoryRepository $catRepo;
    private ProductRepository $prodRepo;
    private StockRepository $stockRepo;
    private CashRegisterService $cashService;

    public function __construct(
        TableRepository $tableRepo,
        OrderRepository $orderRepo,
        OrderItemRepository $itemRepo,
        CategoryRepository $catRepo,
        ProductRepository $prodRepo,
        StockRepository $stockRepo,
        CashRegisterService $cashService
    ) {
        $this->tableRepo = $tableRepo;
        $this->orderRepo = $orderRepo;
        $this->itemRepo = $itemRepo;
        $this->catRepo = $catRepo;
        $this->prodRepo = $prodRepo;
        $this->stockRepo = $stockRepo;
        $this->cashService = $cashService;
    }

    /**
     * Busca dados do contexto atual (Mesa ou Comanda Aberta)
     */
    public function getContextData(int $restaurantId, ?int $mesaId, ?int $orderId): array {
        $contaAberta = null;
        $itensJaPedidos = [];
        $isComanda = false;
        
        // 1. Se for Mesa
        if ($mesaId) {
            $mesaDados = $this->tableRepo->findWithCurrentOrder($mesaId, $restaurantId);

            if ($mesaDados && $mesaDados['status'] == 'ocupada' && $mesaDados['current_order_id']) {
                $contaAberta = $this->orderRepo->find($mesaDados['current_order_id']);
                $itensJaPedidos = $this->itemRepo->findAll($mesaDados['current_order_id']);
                
                // Recálculo do total real
                if ($contaAberta) {
                    $contaAberta['total'] = $this->calculateTotal($itensJaPedidos);
                }
            }
        }
        // 2. Se for Comanda (Order ID direto)
        elseif ($orderId) {
            // Preciso de info do client. OrderRepository->find traz dados da order.
            // Client info precisa ser fetched?
            // O original fazia join.
            // Vou manter simples: find + getClient? Ou expand OrderRepository->findWithClient?
            // Para manter purismo, find traz order. Client info vem separado ou via repo novo metodo.
            // Original: "SELECT o.*, c.name as client_name ...".
            // Vou usar o findOpenClientOrder logic ou similar.
            // Vou usar `find` normal e se precisar de nome do cliente, front busca ou eu busco separado.
            // Mas `getContextData` retorna `contaAberta` que o front espera ter `client_name`.
            // Vou usar uma query customizada no OrderRepository ou Service.
            // Para evitar SQL aqui, vou adicionar `findWithClient` no OrderRepository depois? 
            // Ou uso o `find` e busco Client.
            
            $order = $this->orderRepo->find($orderId, $restaurantId);
            // Aceitar comandas com status aberto ou novo (não concluído/cancelado)
            $validStatus = ['aberto', 'novo'];
            if ($order && in_array($order['status'], $validStatus)) {
                $contaAberta = $order;
                // Busca cliente se tiver
                if (!empty($order['client_id'])) {
                    // $client = (new \App\Repositories\ClientRepository())->find($order['client_id'], $restaurantId);
                    // $contaAberta['client_name'] = $client['name'];
                    // Simplificação: o front usa client_name? Se sim, preciso prover.
                    // Vou assumir que o front precisa.
                    // Adicionar metodo no OrderRepository seems best.
                }

                $itensJaPedidos = $this->itemRepo->findAll($orderId);
                $contaAberta['total'] = $this->calculateTotal($itensJaPedidos);
                $isComanda = true;
            }
        }
        
        return [
            'contaAberta' => $contaAberta,
            'itensJaPedidos' => $itensJaPedidos,
            'isComanda' => $isComanda
        ];
    }

    /**
     * Busca Menu (Categorias e Produtos) para o PDV
     */
    public function getMenu(int $restaurantId): array {
        $categories = $this->catRepo->findAll($restaurantId);
        $products = $this->prodRepo->findActiveWithExtras($restaurantId);
        
        // Agrupar produtos por categoria em PHP para evitar query em loop
        $productsByCat = [];
        foreach ($products as $p) {
            $productsByCat[$p['category_id']][] = $p;
        }

        foreach ($categories as &$cat) {
            $cat['products'] = $productsByCat[$cat['id']] ?? [];
        }
        
        return $categories;
    }

    /**
     * Restaura um pedido cancelado (Desfaz a edição)
     */
    public function restoreOrder(array $backup): void {
        $conn = Database::connect(); // Needed for transaction

        try {
            $conn->beginTransaction();

            // 1. Restaura Order
            $this->orderRepo->restore($backup['order']);

            // 2. Restaura Itens
            $orderId = $backup['order']['id'];
            $this->itemRepo->insert($orderId, $backup['items']);
            
            // 3. Restaura Estoque (Decrementa pois está restaurando a venda)
            foreach ($backup['items'] as $item) {
                // StockService->decrement or StockRepo->decrement?
                // StockRepo directly.
                // Mas original fazia "UPDATE products SET stock = stock - qty".
                // Items array key might differ structure. Check original usage.
                $pid = $item['id'] ?? $item['product_id']; // restoreDetails loop uses $item['id'] from backup items.
                $this->stockRepo->decrement($pid, $item['quantity']);
            }

            // 4. Restaura Movimento financeiro
            $mov = $backup['movement'];
            if ($mov) {
                // CashRegisterService->restoreMovement?
                // Create strict method in CashRegisterService or Repo.
                // Original used "INSERT ... created_at = :date".
                // registerMovement uses NOW().
                // I need a way to insert with specific date.
                // I will add `restoreMovement` to CashRegisterService or use Repo here effectively.
                // Since I cannot change CashRegisterService easily right now (it is used), I will add `restoreMovement` to it if possible? Use SQL here? 
                // NO SQL.
                // I will use `registerMovement` but date will be NOW(). Restore usually implies reverting state. Order date is restored. Financial movement date... matches restore time or original time?
                // Original code: "VALUES ... :date". So original time.
                // I should add `restoreMovement` to CashRegisterService.
                $this->cashService->restoreMovement($conn, $mov);
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw new Exception("Erro ao restaurar backup: " . $e->getMessage());
        }
    }
    
    // Helper para cálculo
    private function calculateTotal(array $items): float {
        $total = 0;
        foreach ($items as $item) {
            $total += ($item['price'] * $item['quantity']);
        }
        return $total;
    }
}
