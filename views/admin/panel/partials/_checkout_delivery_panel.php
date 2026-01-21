<?php
/**
 * PARTIAL: Delivery Panel - Painel Lateral de Entrega
 * Extraído de checkout-modal.php
 */
?>

<!-- PAINEL LATERAL: INFORMAÇÕES DE ENTREGA -->
<div id="delivery-panel" class="delivery-panel">
    <!-- Box Centralizado -->
    <div class="delivery-box">
        <!-- Header -->
        <div class="delivery-header">
            <h3 class="delivery-title">
                <i data-lucide="map-pin" size="18" style="display: inline-block; vertical-align: middle; margin-right: 6px; color: #2563eb;"></i>
                Informações de Entrega
            </h3>
            <button type="button" onclick="CheckoutEntrega.clearData(); closeDeliveryPanel(); selectOrderType('local');" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        
        <!-- Campos -->
        <form id="form-delivery-panel" class="delivery-form" action="#" onsubmit="return false;">
            <div class="delivery-group">
                <label class="delivery-label">Nome *</label>
                <input type="text" id="delivery_name" name="pdv_delivery_name_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Nome do cliente" 
                       class="delivery-input">
            </div>
            
            <div class="delivery-group">
                <label class="delivery-label">Endereço *</label>
                <input type="text" id="delivery_address" name="pdv_delivery_address_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Rua, Av..." 
                       class="delivery-input">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                <div>
                    <label class="delivery-label">Número</label>
                    <input type="text" id="delivery_number" name="pdv_delivery_number_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Nº" 
                           class="delivery-input">
                </div>
                <div>
                    <label class="delivery-label">Bairro *</label>
                    <input type="text" id="delivery_neighborhood" name="pdv_delivery_neighborhood_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Bairro" 
                           class="delivery-input">
                </div>
            </div>
            
            <div class="delivery-group">
                <label class="delivery-label">Telefone</label>
                <input type="text" id="delivery_phone" name="pdv_delivery_phone_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="(00) 00000-0000" 
                       class="delivery-input">
            </div>
    
            <div class="delivery-group">
                <label class="delivery-label">Complemento</label>
                <input type="text" id="delivery_complement" name="pdv_delivery_complement_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Apto, Bloco..." 
                       class="delivery-input">
            </div>
    
            <div class="delivery-group">
                <label class="delivery-label">Observações</label>
                <textarea id="delivery_observation" name="pdv_delivery_observation_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Instruções especiais, ponto de referência..." 
                          class="delivery-input" style="resize: vertical; min-height: 60px;"></textarea>
            </div>
        </form>
        
        <!-- Footer com botões -->
        <div class="delivery-footer">
            <button type="button" onclick="CheckoutEntrega.clearData(); closeDeliveryPanel(); selectOrderType('local');" 
                    class="btn-delivery-cancel">
                Cancelar
            </button>
            <button type="button" onclick="confirmDeliveryData()" 
                    class="btn-delivery-confirm">
                Confirmar
            </button>
        </div>
    </div>
</div>
<!-- FIM PAINEL ENTREGA -->
