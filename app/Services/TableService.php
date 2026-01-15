<?php

namespace App\Services;

use App\Repositories\Order\OrderRepository;
use App\Repositories\TableRepository;
use Exception;

class TableService
{
    private TableRepository $tableRepo;
    private OrderRepository $orderRepo;

    public function __construct(TableRepository $tableRepo, OrderRepository $orderRepo)
    {
        $this->tableRepo = $tableRepo;
        $this->orderRepo = $orderRepo;
    }

    /**
     * Retorna todas as mesas de um restaurante, incluindo o total do pedido atual se houver.
     */
    public function getAllTables($restaurantId)
    {
        return $this->tableRepo->findAll($restaurantId);
    }

    /**
     * Retorna pedidos de clientes/delivery em aberto (sem mesa vinculada).
     */
    public function getOpenClientOrders($restaurantId)
    {
        return $this->orderRepo->findOpenClientOrders($restaurantId);
    }

    /**
     * Cria uma nova mesa.
     */
    public function createTable($restaurantId, $number)
    {
        // Check duplicidade
        $existing = $this->tableRepo->findByNumber($restaurantId, $number);

        if ($existing) {
            throw new Exception('Mesa já existe!');
        }

        $this->tableRepo->create($restaurantId, $number);

        return true;
    }

    /**
     * Deleta uma mesa.
     * Retorna array com ['success' => bool, 'occupied' => bool, 'message' => string]
     */
    public function deleteTable($restaurantId, $number, $force = false)
    {
        // Busca mesa
        $mesa = $this->tableRepo->findByNumber($restaurantId, $number);

        if (!$mesa) {
            throw new Exception('Mesa não encontrada!');
        }

        // Validação de Ocupação
        if ($mesa['status'] === 'ocupada' && !$force) {
            return [
                'success' => false,
                'occupied' => true,
                'message' => 'Mesa Ocupada!'
            ];
        }

        // Deleta
        $this->tableRepo->delete($mesa['id']);

        return ['success' => true];
    }
}
