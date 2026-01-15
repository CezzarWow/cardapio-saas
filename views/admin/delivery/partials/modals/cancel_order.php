<?php
/**
 * ============================================
 * Modal: Cancelar Pedido com Motivo
 *
 * Refatorado: Usa classes CSS + Acessibilidade
 * ============================================
 */
?>
<div id="deliveryCancelModal" 
     class="delivery-modal" 
     style="z-index: 310;"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="deliveryCancelModalTitle"
     aria-hidden="true">
    <div class="delivery-modal__content delivery-modal__content--small">
        
        <!-- Header -->
        <div class="delivery-modal__header delivery-modal__header--danger">
            <h2 id="deliveryCancelModalTitle" class="delivery-modal__title">
                <i data-lucide="alert-triangle" style="width: 24px; height: 24px;"></i>
                Cancelar Pedido #<span id="cancel-order-id">--</span>
            </h2>
        </div>

        <!-- Body -->
        <div class="delivery-modal__body" style="padding: 25px;">
            <p style="color: #475569; margin-bottom: 15px;">
                Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.
            </p>

            <div style="margin-bottom: 20px;">
                <label for="cancel-reason" style="display: block; font-size: 0.85rem; color: #64748b; font-weight: 600; margin-bottom: 8px;">
                    Motivo do cancelamento (opcional)
                </label>
                <textarea id="cancel-reason" 
                          rows="3" 
                          placeholder="Ex: Cliente desistiu, endereço incorreto..."
                          class="delivery-modal__textarea"
                          aria-label="Motivo do cancelamento"></textarea>
            </div>
        </div>

        <!-- Footer -->
        <div class="delivery-modal__footer">
            <button onclick="DeliveryUI.closeCancelModal()" 
                    class="delivery-modal__btn delivery-modal__btn--secondary"
                    aria-label="Voltar sem cancelar">
                Voltar
            </button>
            <button onclick="DeliveryUI.confirmCancel()" 
                    class="delivery-modal__btn delivery-modal__btn--danger"
                    aria-label="Confirmar cancelamento do pedido">
                <i data-lucide="x-circle" style="width: 18px; height: 18px;"></i>
                Confirmar Cancelamento
            </button>
        </div>
    </div>
</div>

<input type="hidden" id="cancel-order-id-value">
