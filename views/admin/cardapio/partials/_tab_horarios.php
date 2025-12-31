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
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="calendar"></i>
        <h3 class="cardapio-admin-card-title">Horários de Funcionamento</h3>
    </div>

    <!-- [ETAPA 5] Horários Gerais (Visual apenas por enquanto) -->
    <div class="cardapio-admin-grid cardapio-admin-grid-2" style="background: #eff6ff; padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dbeafe;">
        <div class="cardapio-admin-form-group">
            <label class="cardapio-admin-label" for="opening_time">Abertura Padrão</label>
            <input type="time" 
                   class="cardapio-admin-input" 
                   id="opening_time" 
                   name="opening_time" 
                   value="<?= htmlspecialchars($config['opening_time'] ?? '08:00') ?>">
        </div>

        <div class="cardapio-admin-form-group">
            <label class="cardapio-admin-label" for="closing_time">Fechamento Padrão</label>
            <input type="time" 
                   class="cardapio-admin-input" 
                   id="closing_time" 
                   name="closing_time" 
                   value="<?= htmlspecialchars($config['closing_time'] ?? '22:00') ?>">
        </div>
        <div style="grid-column: span 2;">
            <p class="cardapio-admin-hint" style="color: #1e40af;">
                <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
                Estes horários definem o padrão geral. Utilize a tabela abaixo para ajustes específicos por dia.
            </p>
        </div>
    </div>

    <div class="cardapio-admin-hint" style="margin-bottom: 1rem;">
        <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
        Defina o horário de abertura e fechamento para cada dia da semana.
    </div>

    <?php foreach ($diasSemana as $dayNum => $dayName): 
        $hour = $businessHours[$dayNum] ?? ['is_open' => ($dayNum > 0 && $dayNum < 6), 'open_time' => '09:00', 'close_time' => '22:00'];
    ?>
    <div class="cardapio-admin-hour-row" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 8px;">
        
        <!-- Checkbox Aberto -->
        <label class="cardapio-admin-toggle" style="flex-shrink: 0;">
            <input type="checkbox" 
                   name="hours[<?= $dayNum ?>][is_open]" 
                   id="hour_day_<?= $dayNum ?>"
                   value="1"
                   <?= ($hour['is_open'] ?? 0) ? 'checked' : '' ?>
                   onchange="CardapioAdmin.toggleHourRow(<?= $dayNum ?>)">
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
        
        <!-- Nome do Dia -->
        <span style="width: 120px; font-weight: 600; color: #374151;"><?= $dayName ?></span>
        
        <!-- Horários (ou "Fechado") -->
        <div id="hour_fields_<?= $dayNum ?>" style="display: flex; align-items: center; gap: 8px; <?= ($hour['is_open'] ?? 0) ? '' : 'opacity: 0.4;' ?>">
            <input type="time" 
                   class="cardapio-admin-input" 
                   style="width: 110px; padding: 8px;"
                   name="hours[<?= $dayNum ?>][open_time]" 
                   id="hour_open_<?= $dayNum ?>"
                   value="<?= htmlspecialchars($hour['open_time'] ?? '09:00') ?>"
                   <?= ($hour['is_open'] ?? 0) ? '' : 'disabled' ?>>
            
            <span style="color: #6b7280;">até</span>
            
            <input type="time" 
                   class="cardapio-admin-input" 
                   style="width: 110px; padding: 8px;"
                   name="hours[<?= $dayNum ?>][close_time]" 
                   id="hour_close_<?= $dayNum ?>"
                   value="<?= htmlspecialchars($hour['close_time'] ?? '22:00') ?>"
                   <?= ($hour['is_open'] ?? 0) ? '' : 'disabled' ?>>
        </div>
        
        <!-- Label Fechado -->
        <span id="hour_closed_<?= $dayNum ?>" 
              style="color: #ef4444; font-weight: 500; <?= ($hour['is_open'] ?? 0) ? 'display: none;' : '' ?>">
            Fechado
        </span>
        
    </div>
    <?php endforeach; ?>

</div>
