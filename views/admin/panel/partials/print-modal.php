<?php
/**
 * Modal de Impressão de Comanda - PDV/Balcão
 * Com abas para Cozinha e Cupom/Recibo
 */
?>
<div id="pdvPrintModal" 
     class="delivery-modal" 
     style="z-index: 1100; display: none;"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="pdvPrintModalTitle">
    <div class="delivery-modal__content delivery-modal__content--small">
        
        <!-- Header -->
        <div class="delivery-modal__header delivery-modal__header--dark">
            <span id="pdvPrintModalTitle" style="font-weight: 700;">🖨️ Imprimir</span>
            <button onclick="PDVPrint.close()" 
                    class="delivery-modal__close"
                    aria-label="Fechar impressão">
                <span style="color: white; font-size: 1.2rem;">✕</span>
            </button>
        </div>

        <!-- Abas de seleção -->
        <div id="pdv-print-tabs" class="print-tabs" role="tablist">
            <button onclick="PDVPrint.showKitchenSlip()" 
                    id="pdv-tab-kitchen" 
                    class="print-tabs__btn print-tabs__btn--active"
                    role="tab"
                    aria-selected="true"
                    aria-controls="pdv-print-slip-content">
                🍳 Cozinha
            </button>
            <button onclick="PDVPrint.showReceiptSlip()" 
                    id="pdv-tab-receipt" 
                    class="print-tabs__btn"
                    role="tab"
                    aria-selected="false"
                    aria-controls="pdv-print-slip-content">
                🧾 Cupom
            </button>
        </div>

        <!-- Conteúdo da Ficha -->
        <div id="pdv-print-slip-content" 
             class="print-slip-container"
             role="tabpanel"
             aria-labelledby="pdv-tab-kitchen">
            <!-- Prévia da ficha (preenchido via JS) -->
        </div>

        <!-- Footer -->
        <div class="delivery-modal__footer">
            <button onclick="PDVPrint.close()" 
                    class="delivery-modal__btn delivery-modal__btn--secondary"
                    aria-label="Fechar sem imprimir">
                Fechar
            </button>
            <button onclick="PDVPrint.print()" 
                    class="delivery-modal__btn delivery-modal__btn--primary"
                    style="flex: 2;"
                    aria-label="Imprimir">
                <i data-lucide="printer" style="width: 18px; height: 18px;"></i>
                Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Área de impressão oculta (para PDV) -->
<div id="pdv-print-area" style="display: none;"></div>

<style>
/* Estilos das abas */
#pdvPrintModal .print-tabs {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
}

#pdvPrintModal .print-tabs__btn {
    flex: 1;
    padding: 12px;
    background: white;
    border: none;
    font-weight: 600;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    color: #64748b;
    transition: all 0.15s ease;
    font-size: 0.95rem;
}

#pdvPrintModal .print-tabs__btn:hover {
    background: #f8fafc;
}

#pdvPrintModal .print-tabs__btn:focus {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
}

#pdvPrintModal .print-tabs__btn--active {
    background: #f8fafc;
    color: #1e293b;
    border-bottom-color: #3b82f6;
}

/* Aba Cupom com cor diferente quando ativa */
#pdvPrintModal .print-tabs__btn--receipt.print-tabs__btn--active {
    border-bottom-color: #10b981;
    color: #059669;
}

/* Container do conteúdo */
#pdvPrintModal .print-slip-container {
    overflow-y: auto;
    flex: 1;
    max-height: 50vh;
}

#pdvPrintModal .print-slip {
    padding: 20px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.5;
    background: white;
}

#pdvPrintModal .print-slip-header {
    text-align: center;
    border-bottom: 2px dashed #333;
    padding-bottom: 10px;
    margin-bottom: 10px;
}

#pdvPrintModal .print-slip-header h2 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

#pdvPrintModal .print-slip-section {
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #ccc;
}

#pdvPrintModal .print-slip-section h4 {
    margin: 0 0 5px 0;
    font-size: 11px;
    text-transform: uppercase;
    color: #666;
}

#pdvPrintModal .print-slip-item {
    display: flex;
    justify-content: space-between;
    margin: 3px 0;
}

#pdvPrintModal .print-slip-total {
    font-size: 16px;
    font-weight: bold;
    text-align: right;
    border-top: 2px solid #333;
    padding-top: 10px;
    margin-top: 10px;
}

/* Media query para impressão */
@media print {
    body * { visibility: hidden; }
    #pdv-print-area, #pdv-print-area * { visibility: visible; }
    #pdv-print-area { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 80mm; 
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.4;
    }
}

/* printing overlay animation */
.printing-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.printer-anim {
    position: relative;
    width: 60px;
    height: 60px;
    margin-bottom: 20px;
}

.printer-base {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 25px;
    background: #e2e8f0;
    border-radius: 8px 8px 4px 4px;
    border: 2px solid #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.printer-paper {
    position: absolute;
    top: 5px; left: 10px; right: 10px;
    height: 35px;
    background: #fff;
    border-radius: 2px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    z-index: 5;
    animation: printSlip 1.5s infinite ease-in-out;
}

@keyframes printSlip {
    0% { transform: translateY(0); opacity: 0; }
    20% { opacity: 1; }
    80% { transform: translateY(-25px); opacity: 1; }
    100% { transform: translateY(-30px); opacity: 0; }
}

.printing-text {
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<!-- Overlay de Animação -->
<div id="printing-overlay" class="printing-overlay" style="display: none;">
    <div class="printer-anim">
        <div class="printer-paper"></div>
        <div class="printer-base"></div>
    </div>
    <div class="printing-text">Imprimindo...</div>
</div>
