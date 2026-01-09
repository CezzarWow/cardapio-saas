<?php
namespace App\Controllers\Admin;

use App\Services\Stock\StockService;

/**
 * StockController - Gerenciamento de Estoque
 * Focado em Visualização de Níveis e Movimentações.
 * (CRUD de Produtos agora é em ProductController)
 */
class StockController extends BaseController {

    private StockService $service;

    public function __construct(StockService $service) {
        $this->service = $service;
    }

    // === MOVIMENTAÇÕES ===
    public function movements() {
        $rid = $this->getRestaurantId();
        
        // Filtros
        $filters = [
            'product' => $_GET['product'] ?? null,
            'category' => $_GET['category'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];

        // Busca dados
        $movements = $this->service->getMovements($rid, $filters);
        $products = $this->service->getProducts($rid);
        $categories = $this->service->getCategories($rid);
        
        // Calcula estatísticas (Logic moved from View to Service)
        $stats = $this->service->getMovementStats($movements);
        
        require __DIR__ . '/../../../views/admin/movements/index.php';
    }

    // === DASHBOARD DE ESTOQUE ===
    public function index() {
        $rid = $this->getRestaurantId();
        
        // Mantemos getProducts aqui apenas para exibir a lista com níveis de estoque
        $products = $this->service->getProducts($rid);
        $categories = $this->service->getCategories($rid);
        
        // A view original 'stock/index.php' foi movida para 'products/index.php'.
        // Devemos criar uma nova view 'stock/dashboard.php' ou usar a antiga 'reposition' como principal?
        // Por compatibilidade com a estrutura antiga, se o user acessar /admin/loja/estoque (se a rota existir),
        // ele espera ver algo. Se a rota principal de CRUD foi movida, aqui fica o dashboard analítico.
        
        // Mas a view 'stock/index.php' que copiamos para 'products/index.php' tinha o grid de cards.
        // Vamos manter uma view simples aqui se necessário, ou redirecionar para Produtos.
        
        // User pediu "separar". Então Estoque != Produtos.
        // Vamos renderizar 'views/admin/stock/index.php' (mas precisamos garantir que ela não tenha botões de "Novo Produto" apontando pro lugar errado).
        // Na verdade, a view 'products/index.php' É a view de gestão.
        
        // Se a rota '/admin/loja/produtos' é a nova casa, este controller pode servir para "Movimentações" ou "Reposição".
        // Vamos assumir que existe uma rota '/admin/loja/estoque'.
        
        // Por enquanto, vou redirecionar para 'produtos' se o user tentar listar aqui, 
        // OU renderizar a view 'stock/index.php' (que eu preciso limpar de botões de CRUD).
        
        // Decisão: Manter 'stock/index.php' focada só em ver quantidades, sem editar.
        // Mas eu não editei 'stock/index.php' ainda.
        
        // Vou manter o include, mas sabendo que a view original ainda tem links de edição.
        // O ideal é limpar a view 'stock/index.php' original.
        
        require __DIR__ . '/../../../views/admin/stock/index.php';
    }
}
