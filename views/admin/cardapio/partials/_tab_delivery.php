<?php
/**
 * ============================================
 * PARTIAL: Aba Delivery
 * ============================================
 */
?>

<!-- Card Delivery (compacto) -->
<div class="cardapio-admin-card" style="padding: 16px;">
    <div class="cardapio-admin-card-header" style="margin-bottom: 12px; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="truck"></i>
            <h3 class="cardapio-admin-card-title">Delivery</h3>
        </div>
        <div style="display: flex; gap: 6px;">
            <button type="button" class="cardapio-admin-btn" id="btn_edit_delivery"
                    style="background: #e2e8f0; color: #475569; padding: 6px 12px; font-size: 0.8rem;"
                    onclick="CardapioAdmin.toggleCardEdit('delivery')">
                <i data-lucide="pencil" size="14"></i> Editar
            </button>
            <button type="button" class="cardapio-admin-btn" id="btn_cancel_delivery"
                    style="background: #fee2e2; color: #ef4444; padding: 6px 12px; font-size: 0.8rem; display: none;"
                    onclick="CardapioAdmin.cancelCardEdit('delivery')">
                <i data-lucide="x" size="14"></i> Cancelar
            </button>
        </div>
    </div>

    <!-- Toggle Habilitar -->
    <div class="cardapio-admin-toggle-row delivery-field" style="padding: 6px 0; border: none; opacity: 0.7; pointer-events: none;">
        <span class="cardapio-admin-toggle-label">Habilitar Delivery</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="delivery_enabled" id="delivery_enabled" value="1" disabled
                   <?= ($config['delivery_enabled'] ?? 1) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <!-- Campos Delivery -->
    <div class="cardapio-admin-grid cardapio-admin-grid-2" style="margin-top: 10px; gap: 12px;">
        <div class="cardapio-admin-form-group delivery-field" style="opacity: 0.7; pointer-events: none;">
            <label class="cardapio-admin-label" for="delivery_fee" style="font-size: 0.8rem; margin-bottom: 4px;">Taxa (R$)</label>
            <input type="text" 
                   class="cardapio-admin-input" 
                   id="delivery_fee" 
                   name="delivery_fee" 
                   placeholder="5,00"
                   disabled
                   style="padding: 6px 10px; font-size: 0.9rem; background-color: #f8fafc;"
                   value="<?= number_format($config['delivery_fee'] ?? 5, 2, ',', '.') ?>">
        </div>

        <div class="cardapio-admin-form-group delivery-field" style="opacity: 0.7; pointer-events: none;">
            <label class="cardapio-admin-label" for="min_order_value" style="font-size: 0.8rem; margin-bottom: 4px;">Pedido Mín. (R$)</label>
            <input type="text" 
                   class="cardapio-admin-input" 
                   id="min_order_value" 
                   name="min_order_value" 
                   placeholder="20,00"
                   disabled
                   style="padding: 6px 10px; font-size: 0.9rem; background-color: #f8fafc;"
                   value="<?= number_format($config['min_order_value'] ?? 20, 2, ',', '.') ?>">
        </div>
    </div>

    <div class="cardapio-admin-grid cardapio-admin-grid-2" style="margin-top: 8px; gap: 12px;">
        <div class="cardapio-admin-form-group delivery-field" style="opacity: 0.7; pointer-events: none;">
            <label class="cardapio-admin-label" for="delivery_time_min" style="font-size: 0.8rem; margin-bottom: 4px;">Tempo Mín. (min)</label>
            <input type="number" 
                   class="cardapio-admin-input" 
                   id="delivery_time_min" 
                   name="delivery_time_min" 
                   min="1"
                   disabled
                   style="padding: 6px 10px; font-size: 0.9rem; background-color: #f8fafc;"
                   value="<?= $config['delivery_time_min'] ?? 30 ?>">
        </div>

        <div class="cardapio-admin-form-group delivery-field" style="opacity: 0.7; pointer-events: none;">
            <label class="cardapio-admin-label" for="delivery_time_max" style="font-size: 0.8rem; margin-bottom: 4px;">Tempo Máx. (min)</label>
            <input type="number" 
                   class="cardapio-admin-input" 
                   id="delivery_time_max" 
                   name="delivery_time_max" 
                   min="1"
                   disabled
                   style="padding: 6px 10px; font-size: 0.9rem; background-color: #f8fafc;"
                   value="<?= $config['delivery_time_max'] ?? 45 ?>">
        </div>
    </div>
</div>

<!-- Cards Retirada e Consumo (lado a lado, compactos) -->
<div class="cardapio-admin-grid cardapio-admin-grid-2">
    
    <!-- Card Retirada -->
    <div class="cardapio-admin-card" style="padding: 16px;">
        <div class="cardapio-admin-card-header" style="margin-bottom: 10px;">
            <i data-lucide="package"></i>
            <h3 class="cardapio-admin-card-title">Retirada no Local</h3>
        </div>
        <div class="cardapio-admin-toggle-row" style="padding: 6px 0; border: none;">
            <span class="cardapio-admin-toggle-label" style="font-size: 0.9rem;">Habilitar Retirada</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="pickup_enabled" id="pickup_enabled" value="1"
                       <?= ($config['pickup_enabled'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- Card Consumo Local -->
    <div class="cardapio-admin-card" style="padding: 16px;">
        <div class="cardapio-admin-card-header" style="margin-bottom: 10px;">
            <i data-lucide="utensils"></i>
            <h3 class="cardapio-admin-card-title">Consumo no Local</h3>
        </div>
        <div class="cardapio-admin-toggle-row" style="padding: 6px 0; border: none;">
            <span class="cardapio-admin-toggle-label" style="font-size: 0.9rem;">Habilitar Consumo</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="dine_in_enabled" id="dine_in_enabled" value="1"
                       <?= ($config['dine_in_enabled'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
    </div>

</div>
