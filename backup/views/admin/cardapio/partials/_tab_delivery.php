<?php
/**
 * ============================================
 * PARTIAL: Aba Delivery
 * ============================================
 */
?>

<!-- Card Delivery (compacto) -->
<div class="cardapio-admin-card cardapio-admin-delivery-card">
    <div class="cardapio-admin-card-header delivery-card-header">
        <div class="delivery-header-title-group">
            <i data-lucide="truck"></i>
            <h3 class="cardapio-admin-card-title">Delivery</h3>
        </div>
        <div class="delivery-header-actions">
            <button type="button" class="cardapio-admin-btn delivery-btn-edit" id="btn_edit_delivery"
                    onclick="CardapioAdmin.toggleCardEdit('delivery')">
                <i data-lucide="pencil" size="14"></i> Editar
            </button>
            <button type="button" class="cardapio-admin-btn delivery-btn-cancel" id="btn_cancel_delivery"
                    onclick="CardapioAdmin.cancelCardEdit('delivery')">
                <i data-lucide="x" size="14"></i> Cancelar
            </button>
        </div>
    </div>

    <!-- Toggle Habilitar -->
    <div class="cardapio-admin-toggle-row delivery-field delivery-toggle-row is-disabled">
        <span class="cardapio-admin-toggle-label">Habilitar Delivery</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="delivery_enabled" id="delivery_enabled" value="1" disabled
                   <?= ($config['delivery_enabled'] ?? 1) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <!-- Campos Delivery -->
    <div class="cardapio-admin-grid cardapio-admin-grid-2 delivery-grid-mt">
        <div class="cardapio-admin-form-group delivery-field delivery-field-container is-disabled">
            <label class="cardapio-admin-label" for="delivery_fee">Taxa (R$)</label>
            <input type="text" 
                   class="cardapio-admin-input delivery-input-config" 
                   id="delivery_fee" 
                   name="delivery_fee" 
                   placeholder="5,00"
                   disabled
                   value="<?= number_format($config['delivery_fee'] ?? 5, 2, ',', '.') ?>">
        </div>

        <div class="cardapio-admin-form-group delivery-field delivery-field-container is-disabled">
            <label class="cardapio-admin-label" for="min_order_value">Pedido Mín. (R$)</label>
            <input type="text" 
                   class="cardapio-admin-input delivery-input-config" 
                   id="min_order_value" 
                   name="min_order_value" 
                   placeholder="20,00"
                   disabled
                   value="<?= number_format($config['min_order_value'] ?? 20, 2, ',', '.') ?>">
        </div>
    </div>

    <div class="cardapio-admin-grid cardapio-admin-grid-2 delivery-grid-mt">
        <div class="cardapio-admin-form-group delivery-field delivery-field-container is-disabled">
            <label class="cardapio-admin-label" for="delivery_time_min">Tempo Mín. (min)</label>
            <input type="number" 
                   class="cardapio-admin-input delivery-input-config" 
                   id="delivery_time_min" 
                   name="delivery_time_min" 
                   min="1"
                   disabled
                   value="<?= $config['delivery_time_min'] ?? 30 ?>">
        </div>

        <div class="cardapio-admin-form-group delivery-field delivery-field-container is-disabled">
            <label class="cardapio-admin-label" for="delivery_time_max">Tempo Máx. (min)</label>
            <input type="number" 
                   class="cardapio-admin-input delivery-input-config" 
                   id="delivery_time_max" 
                   name="delivery_time_max" 
                   min="1"
                   disabled
                   value="<?= $config['delivery_time_max'] ?? 45 ?>">
        </div>
    </div>
</div>

<!-- Cards Retirada e Consumo (lado a lado, compactos) -->
<div class="cardapio-admin-grid cardapio-admin-grid-2">
    
    <!-- Card Retirada -->
    <div class="cardapio-admin-card cardapio-admin-delivery-card">
        <div class="cardapio-admin-card-header delivery-card-header-sm">
            <i data-lucide="package"></i>
            <h3 class="cardapio-admin-card-title">Retirada no Local</h3>
        </div>
        <div class="cardapio-admin-toggle-row delivery-toggle-row">
            <span class="cardapio-admin-toggle-label" style="font-size: 0.9rem;">Habilitar Retirada</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="pickup_enabled" id="pickup_enabled" value="1"
                       <?= ($config['pickup_enabled'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- Card Consumo Local -->
    <div class="cardapio-admin-card cardapio-admin-delivery-card">
        <div class="cardapio-admin-card-header delivery-card-header-sm">
            <i data-lucide="utensils"></i>
            <h3 class="cardapio-admin-card-title">Consumo no Local</h3>
        </div>
        <div class="cardapio-admin-toggle-row delivery-toggle-row">
            <span class="cardapio-admin-toggle-label" style="font-size: 0.9rem;">Habilitar Consumo</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="dine_in_enabled" id="dine_in_enabled" value="1"
                       <?= ($config['dine_in_enabled'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
    </div>

</div>
