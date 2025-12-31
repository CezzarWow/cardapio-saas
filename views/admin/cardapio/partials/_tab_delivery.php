<?php
/**
 * ============================================
 * PARTIAL: Aba Delivery
 * ============================================
 */
?>

<!-- Card Toggle Delivery -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="truck"></i>
        <h3 class="cardapio-admin-card-title">Delivery</h3>
    </div>

    <div class="cardapio-admin-toggle-row">
        <span class="cardapio-admin-toggle-label">Habilitar Delivery</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="delivery_enabled" id="delivery_enabled" value="1"
                   <?= ($config['delivery_enabled'] ?? 1) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <!-- Campos Delivery (condicionais) -->
    <div id="delivery-fields" style="margin-top: 1.5rem; <?= ($config['delivery_enabled'] ?? 1) ? '' : 'display: none;' ?>">
        
        <div class="cardapio-admin-grid cardapio-admin-grid-2">
            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label" for="delivery_fee">Taxa de Entrega (R$)</label>
                <input type="text" 
                       class="cardapio-admin-input delivery-field" 
                       id="delivery_fee" 
                       name="delivery_fee" 
                       placeholder="5,00"
                       disabled
                       value="<?= number_format($config['delivery_fee'] ?? 5, 2, ',', '.') ?>">
            </div>

            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label" for="min_order_value">Pedido Mínimo (R$)</label>
                <input type="text" 
                       class="cardapio-admin-input delivery-field" 
                       id="min_order_value" 
                       name="min_order_value" 
                       placeholder="20,00"
                       disabled
                       value="<?= number_format($config['min_order_value'] ?? 20, 2, ',', '.') ?>">
            </div>
        </div>

        <div class="cardapio-admin-grid cardapio-admin-grid-2">
            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label" for="delivery_time_min">Tempo Mínimo (min)</label>
                <input type="number" 
                       class="cardapio-admin-input delivery-field" 
                       id="delivery_time_min" 
                       name="delivery_time_min" 
                       min="1"
                       disabled
                       value="<?= $config['delivery_time_min'] ?? 30 ?>">
            </div>

            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label" for="delivery_time_max">Tempo Máximo (min)</label>
                <input type="number" 
                       class="cardapio-admin-input delivery-field" 
                       id="delivery_time_max" 
                       name="delivery_time_max" 
                       min="1"
                       disabled
                       value="<?= $config['delivery_time_max'] ?? 45 ?>">
            </div>
        </div>

        <!-- Botões Editar/Aplicar (EMBAIXO) -->
        <div class="cardapio-admin-edit-actions" style="margin-top: 1rem; display: flex; gap: 10px;">
            <button type="button" 
                    class="cardapio-admin-btn" 
                    id="btn_edit_delivery"
                    style="background: #e2e8f0; color: #475569;"
                    onclick="CardapioAdmin.startDeliveryEdit()">
                <i data-lucide="pencil" size="16"></i>
                Editar
            </button>
            
            <button type="button" 
                    class="cardapio-admin-btn" 
                    id="btn_apply_delivery"
                    style="background: #22c55e; color: white; display: none;"
                    onclick="CardapioAdmin.applyDeliveryEdit()">
                <i data-lucide="check" size="16"></i>
                Aplicar
            </button>
        </div>

    </div>
</div>

<!-- Cards Retirada e Consumo (lado a lado) -->
<div class="cardapio-admin-grid cardapio-admin-grid-2">
    
    <!-- Card Retirada -->
    <div class="cardapio-admin-card">
        <div class="cardapio-admin-card-header">
            <i data-lucide="package"></i>
            <h3 class="cardapio-admin-card-title">Retirada no Local</h3>
        </div>

        <div class="cardapio-admin-toggle-row">
            <span class="cardapio-admin-toggle-label">Habilitar Retirada</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="pickup_enabled" id="pickup_enabled" value="1"
                       <?= ($config['pickup_enabled'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- Card Consumo Local -->
    <div class="cardapio-admin-card">
        <div class="cardapio-admin-card-header">
            <i data-lucide="utensils"></i>
            <h3 class="cardapio-admin-card-title">Consumo no Local</h3>
        </div>

        <div class="cardapio-admin-toggle-row">
            <span class="cardapio-admin-toggle-label">Habilitar Consumo no Local</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="dine_in_enabled" id="dine_in_enabled" value="1"
                       <?= ($config['dine_in_enabled'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
    </div>

</div>
