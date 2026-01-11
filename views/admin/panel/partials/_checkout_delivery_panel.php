<?php
/**
 * PARTIAL: Delivery Panel - Painel Lateral de Entrega
 * Extraído de checkout-modal.php
 */
?>

<!-- PAINEL LATERAL: INFORMAÇÕES DE ENTREGA -->
<div id="delivery-panel" style="display: none; background: white; width: 320px; border-radius: 0 16px 16px 0; margin-left: -16px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); display: none; flex-direction: column; max-height: 90vh; overflow: hidden;">
    <!-- Header -->
    <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700;">
            <i data-lucide="map-pin" size="18" style="display: inline-block; vertical-align: middle; margin-right: 6px; color: #2563eb;"></i>
            Informações de Entrega
        </h3>
        <button type="button" onclick="closeDeliveryPanel()" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #64748b;">&times;</button>
    </div>
    
    <!-- Campos -->
    <form id="form-delivery-panel" action="#" onsubmit="return false;" style="padding: 20px; flex: 1; overflow-y: auto;">
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Nome *</label>
            <input type="text" id="delivery_name" name="pdv_delivery_name_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Nome do cliente" 
                   style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Endereço *</label>
            <input type="text" id="delivery_address" name="pdv_delivery_address_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Rua, Av..." 
                   style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Número</label>
                <input type="text" id="delivery_number" name="pdv_delivery_number_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Nº" 
                       style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Bairro *</label>
                <input type="text" id="delivery_neighborhood" name="pdv_delivery_neighborhood_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Bairro" 
                       style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Telefone</label>
            <input type="text" id="delivery_phone" name="pdv_delivery_phone_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="(00) 00000-0000" 
                   style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Complemento</label>
            <input type="text" id="delivery_complement" name="pdv_delivery_complement_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Apto, Bloco..." 
                   style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Observações</label>
            <textarea id="delivery_observation" name="pdv_delivery_observation_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Instruções especiais, ponto de referência..." 
                      style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box; resize: vertical; min-height: 60px;"></textarea>
        </div>
    </form>
    
    <!-- Footer com botões -->
    <div style="padding: 15px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; gap: 10px;">
        <button type="button" onclick="closeDeliveryPanel()" 
                style="flex: 1; padding: 12px; background: white; border: 1px solid #cbd5e1; color: #475569; border-radius: 8px; font-weight: 600; cursor: pointer;">
            Cancelar
        </button>
        <button type="button" onclick="confirmDeliveryData()" 
                style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
            Confirmar
        </button>
    </div>
</div>
<!-- FIM PAINEL ENTREGA -->
