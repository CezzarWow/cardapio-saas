<?php

namespace App\Presenters;

/**
 * CardapioPresenter
 * Responsável por formatar dados para exibição na View
 * Retira a lógica de apresentação do Service de Query
 */
class CardapioPresenter
{
    /**
     * Prepara a lista de horários (Merge de defaults com DB)
     */
    public function formatBusinessHours(array $dbHours): array
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado'
        ];

        $list = [];
        foreach ($days as $dayNum => $dayName) {
            $defaultOpen = ($dayNum > 0 && $dayNum < 6);
            $current = $dbHours[$dayNum] ?? [];
            
            $list[$dayNum] = [
                'name' => $dayName,
                'is_open' => $current['is_open'] ?? $defaultOpen,
                'open_time' => $current['open_time'] ?? '09:00',
                'close_time' => $current['close_time'] ?? '22:00'
            ];
        }

        return $list;
    }

    /**
     * Processa e prepara as mensagens do WhatsApp
     */
    public function formatWhatsAppMessages(string $json): array
    {
        $data = json_decode($json, true);
        $beforeList = [];
        $afterList = [];

        if (isset($data['before']) || isset($data['after'])) {
             $beforeList = $data['before'] ?? [];
             $afterList = $data['after'] ?? [];
        } else if (is_array($data)) {
             if (count($data) >= 1) $beforeList[] = $data[0];
             if (count($data) >= 2) $afterList[] = $data[1];
        }

        if (empty($beforeList)) $beforeList[] = 'Olá! Gostaria de fazer um pedido:';
        if (empty($afterList)) $afterList[] = 'Aguardo a confirmação.';

        return [
            'before' => $beforeList,
            'after' => $afterList
        ];
    }

    /**
     * Formata combos com descrições amigáveis e cálcula descontos
     */
    public function formatCombos(array $combos): array
    {
        foreach ($combos as &$combo) {
            $items = $combo['items'] ?? [];
            
            // Se precisar remover o array bruto da view, descomente:
            // unset($combo['items']); 

            $counts = [];
            $originalPrice = 0;

            foreach ($items as $it) {
                $name = $it['name'];
                $counts[$name] = ($counts[$name] ?? 0) + 1;
                $originalPrice += floatval($it['price']);
            }

            // Descrição: "2 X-Burger + 1 Coca"
            $descParts = [];
            foreach ($counts as $name => $qty) {
                $descParts[] = ($qty > 1 ? "{$qty} " : "") . $name;
            }
            
            $combo['items_description'] = implode(" + ", $descParts);
            $combo['original_price'] = $originalPrice;
            
            // Cálculo de desconto visual
            if ($originalPrice > 0) {
                $discount = (($originalPrice - $combo['price']) / $originalPrice) * 100;
                $combo['discount_percent'] = round($discount);
            } else {
                $combo['discount_percent'] = 0;
            }
        }
        
        return $combos;
    }
}
