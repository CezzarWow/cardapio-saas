<?php

namespace App\Repositories\Cardapio;

use App\Core\Database;
use PDO;

/**
 * Repository para Configurações do Cardápio Web
 */
class CardapioConfigRepository
{
    /**
     * Busca configuração do cardápio por restaurante
     */
    public function findByRestaurant(int $restaurantId): ?array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('SELECT * FROM cardapio_config WHERE restaurant_id = :rid');
        $stmt->execute(['rid' => $restaurantId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Cria configuração padrão para o restaurante
     */
    public function createDefault(int $restaurantId): void
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('INSERT INTO cardapio_config (restaurant_id) VALUES (:rid)');
        $stmt->execute(['rid' => $restaurantId]);
    }

    /**
     * Atualiza configurações do cardápio
     */
    public function update(int $restaurantId, array $data): void
    {
        $conn = Database::connect();

        $sql = 'UPDATE cardapio_config SET 
                    whatsapp_enabled = :whatsapp_enabled,
                    whatsapp_number = :whatsapp_number,
                    whatsapp_message = :whatsapp_message,
                    is_open = :is_open,
                    opening_time = :opening_time,
                    closing_time = :closing_time,
                    closed_message = :closed_message,
                    delivery_enabled = :delivery_enabled,
                    delivery_fee = :delivery_fee,
                    min_order_value = :min_order_value,
                    delivery_time_min = :delivery_time_min,
                    delivery_time_max = :delivery_time_max,
                    pickup_enabled = :pickup_enabled,
                    dine_in_enabled = :dine_in_enabled,
                    accept_cash = :accept_cash,
                    accept_credit = :accept_credit,
                    accept_debit = :accept_debit,
                    accept_pix = :accept_pix,
                    pix_key = :pix_key,
                    pix_key_type = :pix_key_type
                WHERE restaurant_id = :rid';

        $data['rid'] = $restaurantId;
        $conn->prepare($sql)->execute($data);
    }

    /**
     * Retorna configuração, criando padrão se não existir
     */
    public function findOrCreate(int $restaurantId): array
    {
        $config = $this->findByRestaurant($restaurantId);

        if (!$config) {
            $this->createDefault($restaurantId);
            $config = $this->findByRestaurant($restaurantId);
        }

        return $config;
    }
}
