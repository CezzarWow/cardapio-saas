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
    public function index()
    {
        $rid = $this->getRestaurantId();

        $rawProducts = $this->service->getProducts($rid);
        $categories = $this->service->getCategories($rid);

        // --- PREPARAÇÃO DO VIEWMODEL (Lógica de Apresentação) ---
        $stockCriticalLimit = 5;
        $totalProducts = count($rawProducts);
        $criticalStockCount = 0;

        $products = array_map(function ($prod) use ($stockCriticalLimit, &$criticalStockCount) {
            $stock = intval($prod['stock']);
            $isNegative = $stock < 0;
            $isCritical = $stock <= $stockCriticalLimit;

            if ($isCritical) {
                $criticalStockCount++;
            }

            // Definição de Classes CSS baseada em estado
            $stockClass = 'stock-product-card-stock--ok';
            if ($isNegative) {
                $stockClass = 'stock-product-card-stock--danger';
            } elseif ($isCritical) {
                $stockClass = 'stock-product-card-stock--warning';
            }

            // Lógica de Ícone (Lucide vs Emoji/Texto)
            $icon = $prod['icon'] ?? '📦';
            $isLucideIcon = (preg_match('/^[a-z-]+$/', $icon) && strlen($icon) > 4);

            return array_merge($prod, [
                'stock_int' => $stock,
                'is_critical' => $isCritical,
                'is_negative' => $isNegative,
                'stock_class' => $stockClass,
                'formatted_price' => number_format($prod['price'], 2, ',', '.'),
                'is_lucide_icon' => $isLucideIcon,
                'display_icon' => $icon
            ]);
        }, $rawProducts);

        // Dados para a View (ViewModel)
        $viewData = [
            'products' => $products,
            'categories' => $categories,
            'totalProducts' => $totalProducts,
            'criticalStockCount' => $criticalStockCount,
            'hasProducts' => !empty($products)
        ];

        // Use o renderer centralizado passando o scope local de forma segura
        View::renderFromScope('admin/products/index', get_defined_vars());
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
