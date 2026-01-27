<?php
/**
 * ============================================
 * Modal: Impressão de Ficha (Motoboy ou Cozinha)
 *
 * Refatorado: Usa inert + hidden (W3C standard)
 * ============================================
 */
?>
<!-- Modal de Impressão Removido (Usa Impressão Direta) -->
<!-- CSS e Container necessários para funcionamento do print-bundle -->


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
