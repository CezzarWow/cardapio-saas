<?php
/**
 * Partial: Modal Pedido Pago (Retirada)
 * Variáveis esperadas: nenhuma
 */
?>
<!-- MODAL: Opções para Pedido Pago (Retirada) -->
<div id="paidOrderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 16px; width: 350px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 60px; height: 60px; background: #dcfce7; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="shopping-bag" size="28" style="color: #16a34a;"></i>
            </div>
            <h3 style="margin: 0; color: #1e293b; font-size: 1.2rem; font-weight: 800;">Pedido para Retirada</h3>
            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">
                <strong id="paid-order-client-name">Cliente</strong>
            </p>
            <div style="margin-top: 10px; background: #f0fdf4; padding: 8px 15px; border-radius: 8px; display: inline-block;">
                <span style="font-weight: 800; color: #16a34a; font-size: 1.3rem;" id="paid-order-total">R$ 0,00</span>
                <span style="background: #22c55e; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; margin-left: 8px;">PAGO</span>
            </div>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <button onclick="deliverOrder()" style="width: 100%; padding: 14px; background: #22c55e; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(34, 197, 94, 0.3);">
                <i data-lucide="package-check" size="20"></i> ENTREGAR (Concluir)
            </button>
            
            <button onclick="editPaidOrder()" style="width: 100%; padding: 14px; background: #3b82f6; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);">
                <i data-lucide="edit-3" size="20"></i> EDITAR Pedido
            </button>
            
            <button onclick="closePaidOrderModal(); openDossier(currentPaidClientId);" style="width: 100%; padding: 14px; background: #f8fafc; border: 2px solid #e2e8f0; color: #475569; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i data-lucide="file-text" size="20"></i> Ver Dossiê
            </button>
            
            <button onclick="closePaidOrderModal()" style="width: 100%; padding: 12px; background: #f1f5f9; color: #64748b; border: none; border-radius: 10px; font-weight: 700; cursor: pointer;">
                Voltar
            </button>
        </div>
    </div>
</div>
