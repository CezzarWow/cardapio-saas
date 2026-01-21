<?php

namespace App\Controllers\Admin;

use App\Services\Client\ClientService;
use App\Validators\ClientValidator;

/**
 * ClientController - Super Thin (API JSON)
 */
class ClientController extends BaseController
{
    private ClientValidator $v;
    private ClientService $service;

    public function __construct(ClientService $service, ClientValidator $validator)
    {
        $this->service = $service;
        $this->v = $validator;
    }

    /**
     * Busca rápida para autocomplete (GET)
     */
    public function search()
    {
        $rid = $this->getRestaurantId();
        $term = trim($_GET['q'] ?? '');

        if (strlen($term) < 2) {
            $this->json([]);
        }

        $this->json($this->service->search($rid, $term));
    }

    /**
     * Cadastro de cliente (POST JSON)
     */
    public function store()
    {
        $rid = $this->getRestaurantId();
        // Use stashed body from middleware or read from input
        $data = $_REQUEST['JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true) ?? [];

        // Validar
        $errors = $this->v->validateStore($data);
        if ($this->v->hasErrors($errors)) {
            $this->json(['success' => false, 'message' => reset($errors)]);
            return;
        }

        // Sanitizar e executar
        $sanitized = $this->v->sanitizeStore($data);

        try {
            $client = $this->service->create($rid, $sanitized);
            $this->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso!',
                'client' => $client
            ]);
        } catch (\Exception $e) {
            error_log('ClientController::store Error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Detalhes do cliente com dívida e histórico (GET)
     */
    public function details()
    {
        $rid = $this->getRestaurantId();
        $clientId = $this->getInt('id');

        if ($clientId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do cliente obrigatório'], 400);
        }

        $result = $this->service->getDetails($rid, $clientId);

        if (!$result) {
            $this->json(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }

        $this->json([
            'success' => true,
            'client' => $result['client'],
            'history' => $result['history']
        ]);
    }
}
