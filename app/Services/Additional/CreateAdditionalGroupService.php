<?php

namespace App\Services\Additional;

use App\Core\Database;
use App\Domain\Additional\AdditionalGroupManager;
use App\Repositories\AdditionalPivotRepository;
use Exception;

class CreateAdditionalGroupService
{
    private $manager;
    private $pivotRepository;

    public function __construct()
    {
        $this->manager = new AdditionalGroupManager();
        $this->pivotRepository = new AdditionalPivotRepository();
    }

    /**
     * Executa o caso de uso: Criar Grupo + Vínculos Iniciais
     */
    public function execute(int $restaurantId, array $data): int
    {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $name = $data['name'] ?? '';
            // "required" não vinha do front antes, mas o repository suporta. Default 0.
            $required = isset($data['required']) ? (bool) $data['required'] : false;
            
            // 1. Delegar ao Domínio para criar o grupo
            $groupId = $this->manager->createGroup($restaurantId, $name, $required);

            // 2. Processar Vínculos (Lógica de Aplicação)
            $itemIds = $data['item_ids'] ?? [];
            if (!empty($itemIds) && is_array($itemIds)) {
                foreach ($itemIds as $itemId) {
                    // Nota: Idealmente validaríamos se o item pertence ao restaurante aqui
                    // ou no domínio, mas para este piloto vamos focar no fluxo principal.
                    $this->pivotRepository->link($groupId, (int) $itemId);
                }
            }

            $conn->commit();
            return $groupId;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
