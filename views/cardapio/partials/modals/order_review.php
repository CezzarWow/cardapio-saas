<?php
/**
 * PARTIAL: Modal de Revisão do Pedido
 * Espera:
 * - $cardapioConfig (array)
 */
?>
<!-- MODAL DE RESUMO DO PEDIDO (CONFIRA SEU PEDIDO) -->
<div id="orderReviewModal" class="cardapio-modal">
    <div class="cardapio-modal-content fullscreen order-review-modal">
        <div class="cardapio-suggestions-header">
            <button class="cardapio-back-btn" onclick="closeOrderReviewModal()">
                <i data-lucide="arrow-left" size="20"></i>
            </button>
            <h2>📋 Confira seu Pedido</h2>
        </div>
        
        <div class="cardapio-modal-body">
            <!-- Tipo de Pedido -->
            <div class="order-type-section">
                <?php
                    $dineEnabled = ((int) ($cardapioConfig['dine_in_enabled'] ?? 1)) ? true : false;
                    $pickupEnabled = ((int) ($cardapioConfig['pickup_enabled'] ?? 1)) ? true : false;
                    $deliveryEnabled = ((int) ($cardapioConfig['delivery_enabled'] ?? 1)) ? true : false;
                ?>
                <label class="order-type-option <?= \App\Helpers\ViewHelper::e($dineEnabled ? '' : 'disabled-option') ?>" data-method="local">
                    <input type="radio" name="orderType" value="local" onchange="selectOrderType('local')" <?= \App\Helpers\ViewHelper::e($dineEnabled ? '' : 'disabled') ?>>
                    <span class="order-type-check"></span>
                    <span class="order-type-icon">🍽️</span>
                    <span class="order-type-label">Local</span>
                </label>
                
                <label class="order-type-option <?= \App\Helpers\ViewHelper::e($pickupEnabled ? '' : 'disabled-option') ?>" data-method="retirada">
                    <input type="radio" name="orderType" value="retirada" onchange="selectOrderType('retirada')" <?= \App\Helpers\ViewHelper::e($pickupEnabled ? '' : 'disabled') ?>>
                    <span class="order-type-check"></span>
                    <span class="order-type-icon">🛍️</span>
                    <span class="order-type-label">Retirada</span>
                </label>
                
                <label class="order-type-option <?= \App\Helpers\ViewHelper::e($deliveryEnabled ? '' : 'disabled-option') ?>" data-method="entrega">
                    <input type="radio" name="orderType" value="entrega" onchange="selectOrderType('entrega')" <?= \App\Helpers\ViewHelper::e($deliveryEnabled ? 'checked' : 'disabled') ?>>
                    <span class="order-type-check"></span>
                    <span class="order-type-icon">🚗</span>
                    <span class="order-type-label">Entrega</span>
                </label>
            </div>

            <!-- Taxa de Entrega (Dinâmica) -->
            <div id="deliveryFeeRow" style="display: none; background: #fffbeb; color: #b45309; padding: 10px; margin: 10px 0; border-radius: 8px; font-size: 0.9rem; align-items: center; justify-content: space-between; border: 1px solid #fcd34d;">
                <span style="display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="bike" size="16"></i> Taxa de Entrega
                </span>
                <span id="deliveryFeeValue" style="font-weight: 600;">R$ 0,00</span>
            </div>
            
            <div id="orderReviewItems" class="order-review-items">
                <!-- Itens serão inseridos via JavaScript -->
            </div>
            
            <!-- Spacer para Samsung Internet (bug de scroll com footer fixo) -->
            <div class="modal-scroll-spacer"></div>
        </div>
        
    </div>
    <!-- Botão fora do conteiner de conteúdo -->
    <button id="finalizeOrderBtn" class="cardapio-floating-cart-btn order-finalize-btn show" onclick="goToPayment()">
        <i data-lucide="credit-card" size="20"></i>
        <span id="orderReviewTotal">R$ 0,00</span>
        <span>Finalizar</span>
    </button>
</div>
