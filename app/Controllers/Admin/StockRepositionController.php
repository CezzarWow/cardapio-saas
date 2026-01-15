<?php

namespace App\Controllers\Admin;

use App\Services\Stock\StockService;
use App\Validators\StockValidator;
use App\Core\View;
use Exception;

/**
 * StockRepositionController - Super Thin
 * Responsável pelo ajuste operacional de estoque usando Service e Validator
 */
class StockRepositionController extends BaseController
{
    private StockService $service;
    private StockValidator $validator;

    public function __construct(
        StockService $service,
        StockValidator $validator
    ) {
        $this->service = $service;
        $this->validator = $validator;
    }

    // 1. LISTAR PRODUTOS PARA REPOSIÇÃO
    public function index(): void
    {
        $rid = $this->getRestaurantId();

        $products = $this->service->getProducts($rid);
        $categories = $this->service->getCategories($rid);

        View::renderFromScope('admin/reposition/index', get_defined_vars());
    }

    // 2. AJUSTAR ESTOQUE (INCREMENTAL)
    public function adjust(): void
    {
        $rid = $this->getRestaurantId(); // Valida sessão

        $data = $this->getJsonBody(); // Helper do BaseController

        // Validação
        $errors = $this->validator->validateReposition($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $productId = (int)$data['product_id'];
            $amount = (int)$data['amount'];

            $result = $this->service->adjustStock($rid, $productId, $amount);

            // Retorna sucesso com mensagem do service
            $this->json([
                'success' => true,
                'message' => 'Estoque ajustado com sucesso',
                'new_stock' => $result['new_stock'],
                'product_name' => $result['product_name']
            ]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao ajustar estoque: ' . $e->getMessage()], 400);
        }
    }
}
