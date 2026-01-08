<?php
namespace App\Controllers\Admin;

use App\Services\Product\ProductService;
use App\Validators\StockValidator; // Podemos reutilizar ou renomear para ProductValidator

/**
 * ProductController - Gerenciamento de Produtos (Catálogo)
 * Separado do StockController (que fica com Estoque)
 */
class ProductController extends BaseController {

    private const BASE = '/admin/loja/produtos';
    
    private StockValidator $v; // Reutilizando Validator existente por enquanto
    private ProductService $service;

    public function __construct() {
        $this->v = new StockValidator();
        $this->service = new ProductService();
    }

    // === LISTAGEM (CATÁLOGO) ===
    public function index() {
        $rid = $this->getRestaurantId();
        
        $products = $this->service->getProducts($rid);
        $categories = $this->service->getCategories($rid);
        
        require __DIR__ . '/../../../views/admin/products/index.php';
    }

    // === FORMULÁRIO CRIAR ===
    public function create() {
        $rid = $this->getRestaurantId();
        
        $categories = $this->service->getCategories($rid);
        $additionalGroups = $this->service->getAdditionalGroups($rid);

        require __DIR__ . '/../../../views/admin/products/create.php';
    }

    // === SALVAR NOVO ===
    public function store() {
        $this->handleValidatedPost(
            fn() => $this->v->validateProduct($_POST),
            fn() => $this->v->sanitizeProduct($_POST),
            fn($data, $rid) => $this->service->create($rid, $data, $this->service->handleImageUpload($_FILES['image'] ?? null)),
            self::BASE, 'criado'
        );
    }

    // === FORMULÁRIO EDITAR ===
    public function edit() {
        $rid = $this->getRestaurantId();
        $id = $this->getInt('id');

        $product = $this->service->getProduct($id, $rid);
        if (!$product) {
            $this->redirect(self::BASE);
        }

        $categories = $this->service->getCategories($rid);
        $additionalGroups = $this->service->getAdditionalGroups($rid);
        $linkedGroups = $this->service->getLinkedGroups($id); // Helper method added to Service

        require __DIR__ . '/../../../views/admin/products/edit.php';
    }

    // === ATUALIZAR ===
    public function update() {
        $this->handleValidatedPost(
            fn() => $this->v->validateProductUpdate($_POST),
            fn() => $this->v->sanitizeProduct($_POST),
            fn($data, $rid) => $this->service->update($rid, $data, $this->service->handleImageUpload($_FILES['image'] ?? null)),
            self::BASE, 'atualizado'
        );
    }

    // === DELETAR ===
    public function delete() {
        $this->handleDelete(
            fn($id, $rid) => $this->service->delete($id, $rid),
            self::BASE
        );
    }
}
