<?php
/**
 * ============================================
 * PARTIAL: Aba Horários
 * Horários de funcionamento por dia da semana
 * ============================================
 */

$diasSemana = [
    0 => 'Domingo',
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado'
];
?>

<!-- Card Horários -->
<div class="cardapio-admin-card" style="padding: 16px;">
    <div class="cardapio-admin-card-header" style="margin-bottom: 12px;">
        <i data-lucide="calendar"></i>
        <h3 class="cardapio-admin-card-title">Horários de Funcionamento</h3>
    </div>

    <!-- Horários Gerais (Compacto) -->
    <div style="background: #eff6ff; padding: 10px; border-radius: 6px; margin-bottom: 12px; border: 1px solid #dbeafe;">
        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
                <label class="cardapio-admin-label" for="opening_time" style="font-size: 0.8rem; margin-bottom: 4px;">Abertura Padrão</label>
                <input type="time" 
                       class="cardapio-admin-input" 
                       id="opening_time" 
                       name="opening_time" 
                       style="padding: 6px; font-size: 0.9rem;"
                       value="<?= htmlspecialchars($config['opening_time'] ?? '08:00') ?>">
            </div>
            <div style="flex: 1;">
                <label class="cardapio-admin-label" for="closing_time" style="font-size: 0.8rem; margin-bottom: 4px;">Fechamento Padrão</label>
                <input type="time" 
                       class="cardapio-admin-input" 
                       id="closing_time" 
                       name="closing_time" 
                       style="padding: 6px; font-size: 0.9rem;"
                       value="<?= htmlspecialchars($config['closing_time'] ?? '22:00') ?>">
            </div>
        </div>
        <p class="cardapio-admin-hint" style="color: #1e40af; font-size: 0.75rem; margin-top: 6px; margin-bottom: 0;">
            * Define o padrão. Ajuste abaixo por dia.
        </p>
    </div>

    <?php foreach ($diasSemana as $dayNum => $dayName): 
        $hour = $businessHours[$dayNum] ?? ['is_open' => ($dayNum > 0 && $dayNum < 6), 'open_time' => '09:00', 'close_time' => '22:00'];
    ?>
    <div class="cardapio-admin-hour-row" style="display: flex; align-items: center; gap: 10px; padding: 6px 10px; background: #f8fafc; border-radius: 6px; margin-bottom: 6px; border: 1px solid #f1f5f9;">
        
        <!-- Checkbox Aberto -->
        <label class="cardapio-admin-toggle" style="flex-shrink: 0; transform: scale(0.8);">
            <input type="checkbox" 
                   name="hours[<?= $dayNum ?>][is_open]" 
                   id="hour_day_<?= $dayNum ?>"
                   value="1"
                   <?= ($hour['is_open'] ?? 0) ? 'checked' : '' ?>
                   onchange="CardapioAdmin.toggleHourRow(<?= $dayNum ?>)">
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
        
        <!-- Nome do Dia -->
        <span style="width: 100px; font-weight: 500; font-size: 0.85rem; color: #374151;"><?= $dayName ?></span>
        
        <!-- Horários (ou "Fechado") -->
        <div id="hour_fields_<?= $dayNum ?>" style="display: flex; align-items: center; gap: 6px; <?= ($hour['is_open'] ?? 0) ? '' : 'opacity: 0.4;' ?>">
            <input type="time" 
                   class="cardapio-admin-input" 
                   style="width: 90px; padding: 4px 6px; font-size: 0.85rem; height: 30px;"
                   name="hours[<?= $dayNum ?>][open_time]" 
                   id="hour_open_<?= $dayNum ?>"
                   value="<?= htmlspecialchars($hour['open_time'] ?? '09:00') ?>"
                   <?= ($hour['is_open'] ?? 0) ? '' : 'disabled' ?>>
            
            <span style="color: #6b7280; font-size: 0.8rem;">até</span>
            
            <input type="time" 
                   class="cardapio-admin-input" 
                   style="width: 90px; padding: 4px 6px; font-size: 0.85rem; height: 30px;"
                   name="hours[<?= $dayNum ?>][close_time]" 
                   id="hour_close_<?= $dayNum ?>"
                   value="<?= htmlspecialchars($hour['close_time'] ?? '22:00') ?>"
                   <?= ($hour['is_open'] ?? 0) ? '' : 'disabled' ?>>
        </div>
        
        <!-- Label Fechado -->
        <span id="hour_closed_<?= $dayNum ?>" 
              style="color: #ef4444; font-weight: 500; font-size: 0.8rem; margin-left: auto; <?= ($hour['is_open'] ?? 0) ? 'display: none;' : '' ?>">
            Fechado
        </span>
        
    </div>
    <?php endforeach; ?>

</div>
