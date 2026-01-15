<?php
/**
 * ============================================
 * Modal: Detalhes do Pedido Delivery
 * Com opções de impressão (Motoboy / Cozinha)
 * 
 * Refatorado: Usa classes CSS + Acessibilidade
 * ============================================
 */
?>
<div id="deliveryDetailsModal" 
     class="delivery-modal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="deliveryDetailsModalTitle"
     aria-hidden="true">
    <div class="delivery-modal__content delivery-modal__content--medium">
        
        <!-- Header -->
        <div class="delivery-modal__header delivery-modal__header--dark">
            <div>
                <h2 id="deliveryDetailsModalTitle" class="delivery-modal__title">
                    Pedido #<span id="modal-order-id">--</span>
                </h2>
                <span id="modal-order-badge" class="delivery-badge" style="margin-top: 4px; display: inline-block;"></span>
            </div>
            <button onclick="DeliveryUI.closeDetailsModal()" 
                    class="delivery-modal__close"
                    aria-label="Fechar detalhes do pedido">
                <i data-lucide="x"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="delivery-modal__body">
            
            <!-- Cliente -->
            <div class="delivery-modal__section">
                <h4 class="delivery-modal__section-title">Cliente</h4>
                <div class="delivery-modal__section-content">
                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 2px;" id="modal-client-name">--</div>
                    <div style="font-size: 0.85rem; color: #64748b;" id="modal-client-phone">--</div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="delivery-modal__section">
                <h4 class="delivery-modal__section-title">Endereço</h4>
                <div class="delivery-modal__section-content" style="display: flex; align-items: flex-start; gap: 8px;">
                    <i data-lucide="map-pin" style="width: 16px; height: 16px; color: #f59e0b; flex-shrink: 0; margin-top: 2px;"></i>
                    <span id="modal-address" style="color: #334155; font-size: 0.9rem;">--</span>
                </div>
            </div>

            <!-- Itens -->
            <div class="delivery-modal__section">
                <h4 class="delivery-modal__section-title">Itens</h4>
                <div id="modal-items-list" class="delivery-modal__section-content">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <!-- Total + Pagamento -->
            <div class="delivery-modal__info-row">
                <div class="delivery-modal__info-card delivery-modal__info-card--success">
                    <div class="delivery-modal__info-label">Total</div>
                    <div id="modal-total" class="delivery-modal__info-value">R$ --</div>
                </div>
                <div class="delivery-modal__info-card delivery-modal__info-card--neutral">
                    <div class="delivery-modal__info-label">Pagamento</div>
                    <div id="modal-payment" class="delivery-modal__info-value">--</div>
                </div>
            </div>

            <!-- Horário -->
            <div class="delivery-modal__timestamp">
                Pedido realizado em <span id="modal-time" style="font-weight: 600;">--</span>
            </div>
        </div>

        <!-- Footer: Opções de Impressão -->
        <div class="delivery-modal__print-section">
            <div class="delivery-modal__print-title">
                🖨️ Imprimir Ficha
            </div>
            <div class="delivery-modal__info-row">
                <button onclick="DeliveryPrint.openModal(DeliveryUI.currentOrder?.id, 'delivery')" 
                        class="delivery-modal__btn delivery-modal__btn--primary"
                        aria-label="Imprimir ficha do motoboy">
                    🛵 Motoboy
                </button>
                <button onclick="DeliveryPrint.openModal(DeliveryUI.currentOrder?.id, 'kitchen')" 
                        class="delivery-modal__btn delivery-modal__btn--purple"
                        aria-label="Imprimir ficha da cozinha">
                    🍳 Cozinha
                </button>
            </div>
        </div>
    </div>
</div>
