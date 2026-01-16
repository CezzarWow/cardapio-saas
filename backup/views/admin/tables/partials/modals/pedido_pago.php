<?php
/**
 * Partial: Modal Opções de Pedido Pago
 * Permite entregar ou editar pedidos já pagos
 */
?>
<div id="paidOrderModal" 
     class="table-modal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="paidOrderModalTitle"
     aria-hidden="true">
    <div class="table-modal__content table-modal__content--medium">
        
        <!-- Header -->
        <div class="table-modal__header table-modal__header--success" style="padding: 20px;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; opacity: 0.9; font-weight: 600;">Pedido Pago</div>
                <div id="paidOrderModalTitle" style="font-size: 1.2rem; font-weight: 800;">
                    <span id="paid-order-client-name">Cliente</span>
                </div>
            </div>
            <button onclick="closePaidOrderModal()" 
                    class="table-modal__close" 
                    style="background: rgba(255,255,255,0.2); width: 32px; height: 32px; border-radius: 8px; color: white;"
                    aria-label="Fechar modal">
                ✕
            </button>
        </div>

        <!-- Content -->
        <div class="table-modal__body">
            <!-- Total -->
            <div style="background: #f0fdf4; padding: 16px; border-radius: 10px; text-align: center; margin-bottom: 20px; border: 2px solid #bbf7d0;">
                <div style="font-size: 0.8rem; color: #166534; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Valor Total</div>
                <div id="paid-order-total" style="font-size: 1.8rem; font-weight: 800; color: #15803d;">R$ 0,00</div>
                <div style="font-size: 0.75rem; color: #22c55e; font-weight: 600; margin-top: 4px;">✓ PAGO</div>
            </div>

            <!-- Ações -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button onclick="deliverOrder()" 
                        class="btn-action btn-action--primary" 
                        style="width: 100%; padding: 14px; justify-content: center; font-size: 1rem;">
                    <i data-lucide="package-check" style="width: 20px; height: 20px;"></i>
                    Marcar como Entregue
                </button>
                
                <button onclick="editPaidOrder()" 
                        class="btn-action btn-action--danger-outline" 
                        style="width: 100%; padding: 14px; justify-content: center; font-size: 1rem; border-color: #e2e8f0; color: #475569;">
                    <i data-lucide="pencil" style="width: 18px; height: 18px;"></i>
                    Editar Pedido
                </button>
            </div>
        </div>
    </div>
</div>
