<?php

namespace App\Controllers\Admin;

use App\Core\View;
use App\Services\Product\ProductService; // Podemos reutilizar ou renomear para ProductValidator
use App\Validators\StockValidator;

/**
 * ProductController - Gerenciamento de Produtos (Catálogo)
 * Separado do StockController (que fica com Estoque)
 */
class ProductController extends BaseController
{
    private const BASE = '/admin/loja/produtos';

    private StockValidator $v;
    private ProductService $service;

    public function __construct(ProductService $service, StockValidator $validator)
    {
        $this->service = $service;
        $this->v = $validator;
    }

    // === LISTAGEM (CATÁLOGO) ===
    // DEPRECATED: Redireciona para o SPA Dashboard
    public function index()
    {
        $this->redirect('/admin/loja/catalogo#produtos');
    }

    // === FORMULÁRIO CRIAR ===
    public function create()
    {
        $rid = $this->getRestaurantId();

        $categories = $this->service->getCategories($rid);
        $additionalGroups = $this->service->getAdditionalGroups($rid);

        View::renderFromScope('admin/products/create', get_defined_vars());
    }

    // === SALVAR NOVO ===
    public function store()
    {
        $this->handleValidatedPost(
            fn () => $this->v->validateProduct($_POST),
            fn () => $this->v->sanitizeProduct($_POST),
            fn ($data, $rid) => $this->service->create($rid, $data, $this->service->handleImageUpload($_FILES['image'] ?? null)),
            self::BASE,
            'criado'
        );
    }

    // === FORMULÁRIO EDITAR ===
    public function edit()
    {
        $rid = $this->getRestaurantId();
        $id = $this->getInt('id');

        $product = $this->service->getProduct($id, $rid);
        if (!$product) {
            $this->redirect(self::BASE);
        }

        $categories = $this->service->getCategories($rid);
        $additionalGroups = $this->service->getAdditionalGroups($rid);
        $linkedGroups = $this->service->getLinkedGroups($id); // Helper method added to Service

        View::renderFromScope('admin/products/edit', get_defined_vars());
    }

    // === ATUALIZAR ===
    public function update()
    {
        $this->handleValidatedPost(
            fn () => $this->v->validateProductUpdate($_POST),
            fn () => $this->v->sanitizeProduct($_POST),
            fn ($data, $rid) => $this->service->update($rid, $data, $this->service->handleImageUpload($_FILES['image'] ?? null)),
            self::BASE,
            'atualizado'
        );
    }

    // === DELETAR ===
    public function delete()
    {
        $this->handleDelete(
            fn ($id, $rid) => $this->service->delete($id, $rid),
            self::BASE
        );
    }
}
