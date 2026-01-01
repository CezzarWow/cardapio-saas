<?php
/**
 * ============================================
 * Modal: Cancelar Pedido com Motivo
 * FASE 4: Usa ação existente (updateStatus)
 * ============================================
 */
?>
<div id="deliveryCancelModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 310; align-items: center; justify-content: center;">
    <div style="background: white; width: 420px; max-width: 95%; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
        
        <!-- Header -->
        <div style="padding: 20px 25px; background: #fef2f2; border-bottom: 1px solid #fecaca;">
            <h2 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #b91c1c; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="alert-triangle" style="width: 24px; height: 24px;"></i>
                Cancelar Pedido #<span id="cancel-order-id">--</span>
            </h2>
        </div>

        <!-- Body -->
        <div style="padding: 25px;">
            <p style="color: #475569; margin-bottom: 15px;">
                Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.
            </p>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; font-weight: 600; margin-bottom: 8px;">
                    Motivo do cancelamento (opcional)
                </label>
                <textarea id="cancel-reason" rows="3" placeholder="Ex: Cliente desistiu, endereço incorreto..."
                          style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; resize: none; box-sizing: border-box;"></textarea>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; gap: 10px;">
            <button onclick="DeliveryUI.closeCancelModal()" 
                    style="flex: 1; padding: 12px; background: white; border: 1px solid #e2e8f0; color: #64748b; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Voltar
            </button>
            <button onclick="DeliveryUI.confirmCancel()" 
                    style="flex: 1; padding: 12px; background: #dc2626; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="x-circle" style="width: 18px; height: 18px;"></i>
                Confirmar Cancelamento
            </button>
        </div>
    </div>
</div>

<input type="hidden" id="cancel-order-id-value">
