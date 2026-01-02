<?php
/**
 * ============================================
 * Modal: Impressão de Ficha (Motoboy ou Cozinha)
 * ============================================
 */
?>
<div id="deliveryPrintModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1100; align-items: center; justify-content: center;">
    <div style="background: white; width: 420px; max-width: 95%; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); max-height: 90vh; display: flex; flex-direction: column;">
        
        <!-- Header -->
        <div style="padding: 15px 20px; background: #1e293b; color: white; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 700;">🖨️ Imprimir Ficha</span>
            <button onclick="DeliveryPrint.closeModal()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">✕</button>
        </div>

        <!-- Abas de seleção (escondidas quando tipo já é especificado) -->
        <div id="print-tabs-container" style="display: flex; border-bottom: 1px solid #e2e8f0;">
            <button onclick="DeliveryPrint.showDeliverySlip()" id="tab-delivery" 
                    style="flex: 1; padding: 12px; background: #f8fafc; border: none; font-weight: 600; cursor: pointer; border-bottom: 3px solid #3b82f6; color: #1e293b;">
                🛵 Motoboy
            </button>
            <button onclick="DeliveryPrint.showKitchenSlip()" id="tab-kitchen" 
                    style="flex: 1; padding: 12px; background: white; border: none; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; color: #64748b;">
                🍳 Cozinha
            </button>
        </div>

        <!-- Conteúdo da Ficha (será preenchido via JS) -->
        <div id="print-slip-content" style="overflow-y: auto; flex: 1; max-height: 50vh;">
            <!-- Prévia da ficha -->
        </div>

        <!-- Footer com botão de imprimir -->
        <div style="padding: 15px 20px; border-top: 1px solid #e2e8f0; display: flex; gap: 10px;">
            <button onclick="DeliveryPrint.closeModal()" 
                    style="flex: 1; padding: 12px; background: #f1f5f9; color: #64748b; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cancelar
            </button>
            <button onclick="DeliveryPrint.print()" 
                    style="flex: 2; padding: 12px; background: #1e293b; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i data-lucide="printer" style="width: 18px; height: 18px;"></i>
                Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Área de impressão oculta -->
<div id="print-area" style="display: none;"></div>

<style>
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
// Atualiza visual das abas ao trocar
const originalShowDelivery = DeliveryPrint.showDeliverySlip;
DeliveryPrint.showDeliverySlip = function() {
    originalShowDelivery.call(this);
    document.getElementById('tab-delivery').style.borderBottomColor = '#3b82f6';
    document.getElementById('tab-delivery').style.background = '#f8fafc';
    document.getElementById('tab-delivery').style.color = '#1e293b';
    document.getElementById('tab-kitchen').style.borderBottomColor = 'transparent';
    document.getElementById('tab-kitchen').style.background = 'white';
    document.getElementById('tab-kitchen').style.color = '#64748b';
};

const originalShowKitchen = DeliveryPrint.showKitchenSlip;
DeliveryPrint.showKitchenSlip = function() {
    originalShowKitchen.call(this);
    document.getElementById('tab-kitchen').style.borderBottomColor = '#8b5cf6';
    document.getElementById('tab-kitchen').style.background = '#f8fafc';
    document.getElementById('tab-kitchen').style.color = '#1e293b';
    document.getElementById('tab-delivery').style.borderBottomColor = 'transparent';
    document.getElementById('tab-delivery').style.background = 'white';
    document.getElementById('tab-delivery').style.color = '#64748b';
};
</script>
