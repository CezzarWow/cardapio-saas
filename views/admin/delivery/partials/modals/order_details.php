<?php
/**
 * ============================================
 * Modal: Detalhes do Pedido Delivery
 * Com opções de impressão (Motoboy / Cozinha)
 * ============================================
 */
?>
<div id="deliveryDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center;">
    <div style="background: white; width: 500px; max-width: 95%; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); max-height: 90vh; display: flex; flex-direction: column;">
        
        <!-- Header com X destacado -->
        <div style="padding: 16px 20px; background: #1e293b; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: white;">
                    Pedido #<span id="modal-order-id">--</span>
                </h2>
                <span id="modal-order-badge" class="delivery-badge" style="margin-top: 4px; display: inline-block;"></span>
            </div>
            <button onclick="DeliveryUI.closeDetailsModal()" style="background: rgba(255,255,255,0.2); border: none; cursor: pointer; padding: 8px; border-radius: 8px; transition: background 0.2s;">
                <i data-lucide="x" style="width: 24px; height: 24px; color: white;"></i>
            </button>
        </div>

        <!-- Body (scrollable) -->
        <div style="padding: 20px; overflow-y: auto; flex: 1;">
            
            <!-- Cliente -->
            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;">Cliente</h4>
                <div style="background: #f8fafc; padding: 12px; border-radius: 8px;">
                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 2px;" id="modal-client-name">--</div>
                    <div style="font-size: 0.85rem; color: #64748b;" id="modal-client-phone">--</div>
                </div>
            </div>

            <!-- Endereço -->
            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;">Endereço</h4>
                <div style="background: #f8fafc; padding: 12px; border-radius: 8px; display: flex; align-items: flex-start; gap: 8px;">
                    <i data-lucide="map-pin" style="width: 16px; height: 16px; color: #f59e0b; flex-shrink: 0; margin-top: 2px;"></i>
                    <span id="modal-address" style="color: #334155; font-size: 0.9rem;">--</span>
                </div>
            </div>

            <!-- Itens -->
            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;">Itens</h4>
                <div id="modal-items-list" style="background: #f8fafc; padding: 12px; border-radius: 8px;">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <!-- Total + Pagamento -->
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1; background: #dcfce7; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 0.75rem; color: #166534; text-transform: uppercase;">Total</div>
                    <div id="modal-total" style="font-size: 1.2rem; font-weight: 800; color: #166534;">R$ --</div>
                </div>
                <div style="flex: 1; background: #f1f5f9; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Pagamento</div>
                    <div id="modal-payment" style="font-size: 1rem; font-weight: 700; color: #334155;">--</div>
                </div>
            </div>

            <!-- Horário -->
            <div style="margin-top: 12px; text-align: center; font-size: 0.8rem; color: #94a3b8;">
                Pedido realizado em <span id="modal-time" style="font-weight: 600;">--</span>
            </div>
        </div>

        <!-- Footer: Opções de Impressão -->
        <div style="padding: 16px 20px; border-top: 2px solid #e2e8f0; background: #f8fafc;">
            <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 10px; text-align: center;">
                🖨️ Imprimir Ficha
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="DeliveryPrint.openModal(DeliveryUI.currentOrder?.id, 'delivery')" 
                        style="flex: 1; padding: 14px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem; box-shadow: 0 2px 8px rgba(59,130,246,0.3);">
                    🛵 Motoboy
                </button>
                <button onclick="DeliveryPrint.openModal(DeliveryUI.currentOrder?.id, 'kitchen')" 
                        style="flex: 1; padding: 14px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem; box-shadow: 0 2px 8px rgba(139,92,246,0.3);">
                    🍳 Cozinha
                </button>
            </div>
        </div>
    </div>
</div>
