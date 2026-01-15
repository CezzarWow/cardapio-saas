<?php
/**
 * ============================================
 * Modal: Impressão de Ficha (Motoboy ou Cozinha)
 *
 * Refatorado: Usa classes CSS + Acessibilidade
 * ============================================
 */
?>
<div id="deliveryPrintModal" 
     class="delivery-modal" 
     style="z-index: 1100;"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="deliveryPrintModalTitle"
     aria-hidden="true">
    <div class="delivery-modal__content delivery-modal__content--small">
        
        <!-- Header -->
        <div class="delivery-modal__header delivery-modal__header--dark">
            <span id="deliveryPrintModalTitle" style="font-weight: 700;">🖨️ Imprimir Ficha</span>
            <button onclick="DeliveryPrint.closeModal()" 
                    class="delivery-modal__close"
                    aria-label="Fechar impressão">
                <span style="color: white; font-size: 1.2rem;">✕</span>
            </button>
        </div>

        <!-- Abas de seleção -->
        <div id="print-tabs-container" class="print-tabs" role="tablist">
            <button onclick="DeliveryPrint.showDeliverySlip()" 
                    id="tab-delivery" 
                    class="print-tabs__btn print-tabs__btn--active"
                    role="tab"
                    aria-selected="true"
                    aria-controls="print-slip-content">
                🛵 Motoboy
            </button>
            <button onclick="DeliveryPrint.showKitchenSlip()" 
                    id="tab-kitchen" 
                    class="print-tabs__btn"
                    role="tab"
                    aria-selected="false"
                    aria-controls="print-slip-content">
                🍳 Cozinha
            </button>
        </div>

        <!-- Conteúdo da Ficha -->
        <div id="print-slip-content" 
             class="print-slip-container"
             role="tabpanel"
             aria-labelledby="tab-delivery">
            <!-- Prévia da ficha (preenchido via JS) -->
        </div>

        <!-- Footer -->
        <div class="delivery-modal__footer">
            <button onclick="DeliveryPrint.closeModal()" 
                    class="delivery-modal__btn delivery-modal__btn--secondary"
                    aria-label="Cancelar impressão">
                Cancelar
            </button>
            <button onclick="DeliveryPrint.print()" 
                    class="delivery-modal__btn delivery-modal__btn--primary"
                    style="flex: 2;"
                    aria-label="Imprimir ficha">
                <i data-lucide="printer" style="width: 18px; height: 18px;"></i>
                Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Área de impressão oculta -->
<div id="print-area" style="display: none;"></div>

<style>
/* Tabs de impressão */
.print-tabs {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
}

.print-tabs__btn {
    flex: 1;
    padding: 12px;
    background: white;
    border: none;
    font-weight: 600;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    color: #64748b;
    transition: all 0.15s ease;
}

.print-tabs__btn:hover {
    background: #f8fafc;
}

.print-tabs__btn:focus {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
}

.print-tabs__btn--active {
    background: #f8fafc;
    color: #1e293b;
    border-bottom-color: #3b82f6;
}

.print-tabs__btn--kitchen.print-tabs__btn--active {
    border-bottom-color: #8b5cf6;
}

.print-slip-container {
    overflow-y: auto;
    flex: 1;
    max-height: 50vh;
}

/* Estilos de impressão */
@media print {
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 80mm; 
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.4;
    }
}

.print-slip {
    padding: 20px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.5;
    background: white;
}
.print-slip-header {
    text-align: center;
    border-bottom: 2px dashed #333;
    padding-bottom: 10px;
    margin-bottom: 10px;
}
.print-slip-header h2 {
    margin: 0 0 5px 0;
    font-size: 16px;
}
.print-slip-section {
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #ccc;
}
.print-slip-section h4 {
    margin: 0 0 5px 0;
    font-size: 11px;
    text-transform: uppercase;
    color: #666;
}
.print-slip-item {
    display: flex;
    justify-content: space-between;
    margin: 3px 0;
}
.print-slip-total {
    font-size: 16px;
    font-weight: bold;
    text-align: right;
    border-top: 2px solid #333;
    padding-top: 10px;
    margin-top: 10px;
}
</style>

<script>
// Atualiza visual e ARIA das abas ao trocar
const originalShowDelivery = DeliveryPrint.showDeliverySlip;
DeliveryPrint.showDeliverySlip = function() {
    originalShowDelivery.call(this);
    const tabDelivery = document.getElementById('tab-delivery');
    const tabKitchen = document.getElementById('tab-kitchen');
    
    tabDelivery.classList.add('print-tabs__btn--active');
    tabDelivery.setAttribute('aria-selected', 'true');
    tabKitchen.classList.remove('print-tabs__btn--active');
    tabKitchen.setAttribute('aria-selected', 'false');
};

const originalShowKitchen = DeliveryPrint.showKitchenSlip;
DeliveryPrint.showKitchenSlip = function() {
    originalShowKitchen.call(this);
    const tabDelivery = document.getElementById('tab-delivery');
    const tabKitchen = document.getElementById('tab-kitchen');
    
    tabKitchen.classList.add('print-tabs__btn--active', 'print-tabs__btn--kitchen');
    tabKitchen.setAttribute('aria-selected', 'true');
    tabDelivery.classList.remove('print-tabs__btn--active');
    tabDelivery.setAttribute('aria-selected', 'false');
};
</script>
