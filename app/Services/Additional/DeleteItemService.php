<?php

namespace App\Services\Additional;

use App\Repositories\AdditionalItemRepository;
use Exception;

/**
 * Service para deletar Item de Adicional
 * O repository já cuida de remover vínculos em cascade
 */
class DeleteItemService
{
    private AdditionalItemRepository $itemRepository;

    public function __construct()
    {
        $this->itemRepository = new AdditionalItemRepository();
    }

    /**
     * Deleta um item de adicional
     * 
     * @param int $id ID do item
     * @param int $restaurantId ID do restaurante (segurança)
     * @throws Exception Se item não existir
     */
    public function execute(int $id, int $restaurantId): void
    {
        if ($id <= 0) {
            throw new Exception('ID do item é obrigatório');
        }

        // Verifica se item existe (opcional, pode deixar o delete silencioso)
        $existingItem = $this->itemRepository->findById($id, $restaurantId);
        if (!$existingItem) {
            // Item não existe ou não pertence ao restaurante - ignora silenciosamente
            return;
        }

        // Deleta (repository remove vínculos automaticamente)
        $this->itemRepository->delete($id, $restaurantId);
    }
}
