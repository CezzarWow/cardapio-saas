<?php
namespace App\Services;

use App\Repositories\RestaurantRepository;
use Exception;

// Importa Helper Global
require_once __DIR__ . '/../Helpers/ImageConverter.php';

/**
 * ConfigService - Lógica de Negócio de Configurações da Loja
 */
class ConfigService
{
    private RestaurantRepository $repo;

    public function __construct(RestaurantRepository $repo) {
        $this->repo = $repo;
    }

    /**
     * Busca dados da loja
     */
    public function getStoreData(int $restaurantId): ?array
    {
        return $this->repo->find($restaurantId);
    }

    /**
     * Atualiza configurações e logo
     */
    public function updateConfig(int $restaurantId, array $data, ?array $file = null): array
    {
        // 1. Processa Upload da Logo (se houver)
        $updateData = [
            'name' => trim($data['name']),
            'phone' => trim($data['phone']),
            'address' => trim($data['address']),
            'address_number' => trim($data['address_number'] ?? ''),
            'zip_code' => trim($data['zip_code'] ?? ''),
            'primary_color' => trim($data['primary_color'] ?? '#000000')
        ];

        if ($file && !empty($file['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            
            // Usa o Helper para converter/salvar
            $logoName = \ImageConverter::uploadAndConvert($file, $uploadDir);

            if ($logoName) {
                $updateData['logo'] = $logoName;
            } else {
                throw new Exception("Falha ao salvar ou converter a logo.");
            }
        }

        // 2. Atualiza Banco via Repo
        $this->repo->updateFull($restaurantId, $updateData);

        return [
            'logo' => $updateData['logo'] ?? null,
            'name' => $updateData['name']
        ];
    }
}
