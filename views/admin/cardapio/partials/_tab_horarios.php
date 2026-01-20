<?php
/**
 * ============================================
 * PARTIAL: Aba Horários
 * Horários de funcionamento por dia da semana
 * ============================================
 */

?>

<!-- Card Horários -->

<!-- Card Horários -->
<div class="cardapio-admin-card" style="padding: 16px;">
    <div class="cardapio-admin-card-header" style="margin-bottom: 12px; justify-content: flex-start;">
        <i data-lucide="calendar"></i>
        <h3 class="cardapio-admin-card-title">Horários de Funcionamento</h3>
    </div>


    <?php foreach ($businessHoursList as $dayNum => $day): ?>
    <div class="cardapio-admin-hour-row" style="display: flex; align-items: center; gap: 10px; padding: 6px 10px; background: #f8fafc; border-radius: 6px; margin-bottom: 6px; border: 1px solid #f1f5f9;">
        
        <!-- Checkbox Aberto -->
        <label class="cardapio-admin-toggle" style="flex-shrink: 0; transform: scale(0.8);">
            <input type="checkbox" 
                   name="hours[<?= $dayNum ?>][is_open]" 
                   id="hour_day_<?= $dayNum ?>"
                   value="1"
                   <?= $day['is_open'] ? 'checked' : '' ?>
                   onchange="CardapioAdmin.toggleHourRow(<?= $dayNum ?>)">
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
        
        <!-- Nome do Dia -->
        <span style="width: 100px; font-weight: 500; font-size: 0.85rem; color: #374151;"><?= htmlspecialchars($day['name']) ?></span>
        
        <!-- Horários (ou "Fechado") -->
        <div id="hour_fields_<?= $dayNum ?>" style="display: flex; align-items: center; gap: 6px; <?= $day['is_open'] ? '' : 'opacity: 0.4;' ?>">
            <input type="time" 
                   class="cardapio-admin-input" 
                   style="width: 90px; padding: 4px 6px; font-size: 0.85rem; height: 30px;"
                   name="hours[<?= $dayNum ?>][open_time]" 
                   id="hour_open_<?= $dayNum ?>"
                   value="<?= htmlspecialchars($day['open_time']) ?>"
                   <?= $day['is_open'] ? '' : 'disabled' ?>>
            
            <span style="color: #6b7280; font-size: 0.8rem;">até</span>
            
            <input type="time" 
                   class="cardapio-admin-input" 
                   style="width: 90px; padding: 4px 6px; font-size: 0.85rem; height: 30px;"
                   name="hours[<?= $dayNum ?>][close_time]" 
                   id="hour_close_<?= $dayNum ?>"
                   value="<?= htmlspecialchars($day['close_time']) ?>"
                   <?= $day['is_open'] ? '' : 'disabled' ?>>
        </div>
        
        <!-- Label Fechado -->
        <span id="hour_closed_<?= $dayNum ?>" 
              style="color: #ef4444; font-weight: 500; font-size: 0.8rem; margin-left: auto; <?= $day['is_open'] ? 'display: none;' : '' ?>">
            Fechado
        </span>
        
    </div>
    <?php endforeach; ?>

</div>
