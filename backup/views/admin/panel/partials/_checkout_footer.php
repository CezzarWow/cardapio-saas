<?php
/**
 * PARTIAL: Checkout Footer - Botões de Ação
 * Extraído de checkout-modal.php
 */
?>

<!-- FOOTER: TOTAL, RESTANTE E BOTÕES -->
<div class="checkout-footer">
    
    <!-- LINHA DE TOTAIS -->
    <div class="footer-totals">
        <div id="change-box" style="display: none; text-align: right; background: #dcfce7; padding: 8px 16px; border-radius: 8px; border: 1px solid #86efac;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #166534; margin-right: 8px;">TROCO:</span>
            <span id="checkout-change" style="font-size: 1.1rem; font-weight: 900; color: #166534;">R$ 0,00</span>
        </div>
    </div>

    <!-- BOTÕES -->
    <div class="footer-buttons">
        <button onclick="closeCheckout()" class="btn-cancel">Cancelar</button>
        
        <!-- Botão SALVAR (aparece só em Retirada/Entrega) -->
        <button id="btn-save-pickup" onclick="savePickupOrder()" 
                class="btn-pay-later" style="display: none;">
            <i data-lucide="clock" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 5px;"></i>
            Pagar Depois
        </button>
        
        <button id="btn-finish-sale" onclick="submitSale()" disabled 
                class="btn-finish">
            CONCLUIR VENDA <i data-lucide="check-circle"></i>
        </button>
    </div>
</div>
