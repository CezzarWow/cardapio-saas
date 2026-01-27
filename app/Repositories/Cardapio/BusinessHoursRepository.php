<?php

namespace App\Repositories\Cardapio;

use App\Core\Database;
use App\Events\CardapioChangedEvent;
use App\Events\EventDispatcher;
use PDO;

/**
 * Repository para Horários de Funcionamento
 */
class BusinessHoursRepository
{
    /**
     * Busca todos os horários de um restaurante, organizados por dia
     */
    public function findAll(int $restaurantId): array
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('SELECT * FROM business_hours WHERE restaurant_id = :rid');
        $stmt->execute(['rid' => $restaurantId]);
        $hoursRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organizar por dia da semana
        $businessHours = [];
        foreach ($hoursRaw as $h) {
            $businessHours[$h['day_of_week']] = $h;
        }

        return $businessHours;
    }

    /**
     * Cria horários padrão para os 7 dias da semana
     */
    public function createDefaults(int $restaurantId): void
    {
        $conn = Database::connect();

        $defaults = [
            0 => ['is_open' => 0, 'open' => '09:00', 'close' => '22:00'], // Domingo
            1 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            2 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            3 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            4 => ['is_open' => 1, 'open' => '09:00', 'close' => '22:00'],
            5 => ['is_open' => 1, 'open' => '09:00', 'close' => '23:00'], // Sexta
            6 => ['is_open' => 1, 'open' => '09:00', 'close' => '23:00'], // Sábado
        ];

        $stmt = $conn->prepare('
            INSERT INTO business_hours (restaurant_id, day_of_week, is_open, open_time, close_time) 
            VALUES (:rid, :day, :is_open, :open, :close)
        ');

        foreach ($defaults as $day => $h) {
            $stmt->execute([
                'rid' => $restaurantId,
                'day' => $day,
                'is_open' => $h['is_open'],
                'open' => $h['open'],
                'close' => $h['close']
            ]);
        }

        EventDispatcher::dispatch(new CardapioChangedEvent($restaurantId));
    }

    /**
     * Salva horários (INSERT com UPDATE em caso de duplicata)
     */
    public function save(int $restaurantId, array $hoursData): void
    {
        $conn = Database::connect();

        $stmt = $conn->prepare('
            INSERT INTO business_hours (restaurant_id, day_of_week, is_open, open_time, close_time)
            VALUES (:rid, :day, :is_open, :open, :close)
            ON DUPLICATE KEY UPDATE 
                is_open = VALUES(is_open),
                open_time = VALUES(open_time),
                close_time = VALUES(close_time)
        ');

        for ($day = 0; $day <= 6; $day++) {
            $stmt->execute([
                'rid' => $restaurantId,
                'day' => $day,
                'is_open' => isset($hoursData[$day]['is_open']) ? 1 : 0,
                'open' => $hoursData[$day]['open_time'] ?? '09:00',
                'close' => $hoursData[$day]['close_time'] ?? '22:00'
            ]);
        }

        EventDispatcher::dispatch(new CardapioChangedEvent($restaurantId));
    }

    /**
     * Retorna horários, criando padrões se não existirem
     */
    public function findOrCreate(int $restaurantId): array
    {
        $hours = $this->findAll($restaurantId);

        if (empty($hours)) {
            $this->createDefaults($restaurantId);
            $hours = $this->findAll($restaurantId);
        }

        return $hours;
    }
}
