<?php

namespace App\Controllers\Admin;

use App\Services\TableService;
use App\Validators\TableValidator;
use App\Core\View;
use Exception;

class TableController extends BaseController
{
    private TableService $service;
    private TableValidator $validator;

    public function __construct(TableService $service, TableValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    /**
     * Renderiza a View Principal de Mesas
     */
    public function index()
    {
        $this->checkSession();
        $rid = $this->getRestaurantId();

        try {
            // Busca dados via Service
            $tables = $this->service->getAllTables($rid);
            $clientOrders = $this->service->getOpenClientOrders($rid);

            // Renderiza View
            View::renderFromScope('admin/tables/index', get_defined_vars());

        } catch (Exception $e) {
            die('Erro ao carregar mesas: ' . $e->getMessage());
        }
    }

    /**
     * API: Salva (Cria) nova Mesa
     */
    public function store()
    {
        $this->checkSession();
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        // Validação
        $errors = $this->validator->validateStore($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => reset($errors)], 400);
            return;
        }

        try {
            $this->service->createTable($rid, $data['number']);
            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Deleta Mesa
     */
    public function deleteByNumber()
    {
        $this->checkSession();
        $rid = $this->getRestaurantId();
        $data = $this->getJsonBody();

        // Validação
        $errors = $this->validator->validateDelete($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => reset($errors)], 400);
            return;
        }

        try {
            $force = $data['force'] ?? false;
            $result = $this->service->deleteTable($rid, $data['number'], $force);

            if ($result['success']) {
                $this->json(['success' => true]);
            } else {
                // Retorna flag 'occupied' para frontend pedir confirmação
                $this->json($result);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Busca Simples (usado pelo PDV)
     */
    public function search()
    {
        $this->checkSession();
        $rid = $this->getRestaurantId();

        try {
            // Reutiliza o método getAllTables mas filtra campos se necessário no frontend
            // Ou cria um metodo especifico 'getSimpleList' no service se performance for critica
            $tables = $this->service->getAllTables($rid);
            $this->json($tables);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
