<?php
/**
 * PARTIAL: Checkout Footer - Botões de Ação
 * Extraído de checkout-modal.php
 */
?>

<!-- FOOTER: TOTAL, RESTANTE E BOTÕES -->
<div style="padding: 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
    
    <!-- LINHA DE TOTAIS -->
    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 15px; padding: 0 5px;">
        <div id="change-box" style="display: none; text-align: right; background: #dcfce7; padding: 8px 16px; border-radius: 8px; border: 1px solid #86efac;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #166534; margin-right: 8px;">TROCO:</span>
            <span id="checkout-change" style="font-size: 1.1rem; font-weight: 900; color: #166534;">R$ 0,00</span>
        </div>
    </div>

    <!-- BOTÕES -->
    <div style="display: flex; gap: 15px;">
        <button onclick="closeCheckout()" style="flex: 1; padding: 15px; background: white; border: 1px solid #cbd5e1; color: #475569; border-radius: 10px; font-weight: 700; cursor: pointer;">Cancelar</button>
        
        <!-- Botão SALVAR (aparece só em Retirada/Entrega) -->
        <button id="btn-save-pickup" onclick="savePickupOrder()" 
                style="display: none; flex: 1; padding: 15px; background: #f59e0b; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 0.95rem;">
            <i data-lucide="clock" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 5px;"></i>
            Pagar Depois
        </button>
        
        <button id="btn-finish-sale" onclick="submitSale()" disabled 
                style="flex: 2; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 10px; font-weight: 800; cursor: not-allowed; font-size: 1.1rem; display: flex; justify-content: center; align-items: center; gap: 10px;">
            CONCLUIR VENDA <i data-lucide="check-circle"></i>
        </button>
    </div>
</div>
