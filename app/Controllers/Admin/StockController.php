<?php
namespace App\Controllers\Admin;

use App\Services\Stock\StockService;
use App\Validators\StockValidator;

/**
 * StockController - Super Thin (v3)
 * Usa StockService para lógica de negócio
 */
class StockController extends BaseController {

    private const BASE = '/admin/loja/produtos';
    
    private StockValidator $v;
    private StockService $service;

    public function __construct() {
        $this->v = new StockValidator();
        $this->service = new StockService();
    }

    // === LISTAGEM ===
    public function index() {
        $rid = $this->getRestaurantId();
        
        $products = $this->service->getProducts($rid);
        $categories = $this->service->getCategories($rid);
        
        require __DIR__ . '/../../../views/admin/stock/index.php';
    }

    // === FORMULÁRIO CRIAR ===
    public function create() {
        $rid = $this->getRestaurantId();
        
        $categories = $this->service->getCategories($rid);
        $additionalGroups = $this->service->getAdditionalGroups($rid);

        require __DIR__ . '/../../../views/admin/stock/create.php';
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

    // === DELETAR ===
    public function delete() {
        $this->handleDelete(
            fn($id, $rid) => $this->service->delete($id, $rid),
            self::BASE
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
        $linkedGroups = $this->service->getLinkedGroups($id);

        require __DIR__ . '/../../../views/admin/stock/edit.php';
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
}
