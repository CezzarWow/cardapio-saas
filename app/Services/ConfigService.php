<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

// Importa Helper Global
require_once __DIR__ . '/../Helpers/ImageConverter.php';

/**
 * ConfigService - Lógica de Negócio de Configurações da Loja
 */
class ConfigService
{
    /**
     * Busca dados da loja
     */
    public function getStoreData(int $restaurantId): ?array
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Atualiza configurações e logo
     */
    public function updateConfig(int $restaurantId, array $data, ?array $file = null): array
    {
        $conn = Database::connect();
        
        // 1. Processa Upload da Logo (se houver)
        $logoSql = "";
        $params = [
            'name' => trim($data['name']),
            'phone' => trim($data['phone']),
            'address' => trim($data['address']),
            'address_number' => trim($data['address_number'] ?? ''),
            'zip_code' => trim($data['zip_code'] ?? ''),
            'color' => trim($data['primary_color'] ?? '#000000'),
            'id' => $restaurantId
        ];

        if ($file && !empty($file['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            
            // Usa o Helper para converter/salvar
            $logoName = \ImageConverter::uploadAndConvert($file, $uploadDir);

            if ($logoName) {
                $logoSql = ", logo = :logo";
                $params['logo'] = $logoName;
                
                // Atualiza sessão se necessário (Controller cuida disso?)
                // Melhor retornar o nome da logo para o Controller atualizar a sessão
            } else {
                throw new Exception("Falha ao salvar ou converter a logo.");
            }
        }

        // 2. Atualiza Banco
        $sql = "UPDATE restaurants SET 
                name = :name, 
                phone = :phone, 
                address = :address, 
                address_number = :address_number,
                zip_code = :zip_code,
                primary_color = :color
                $logoSql 
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return [
            'logo' => $params['logo'] ?? null,
            'name' => $params['name']
        ];
    }
}
