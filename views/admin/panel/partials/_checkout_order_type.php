<?php
/**
 * PARTIAL: Checkout Order Type - Tipo de Pedido
 * Extraído de checkout-modal.php
 */
?>

<!-- 4. Tipo de Pedido (Cards) -->
<div style="background: white; margin-bottom: 5px; padding: 0 25px;">
    <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 10px; font-weight: 700;">TIPO DE PEDIDO</label>
    <input type="hidden" id="keep_open_value" value="false">
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
        <!-- OPÇÃO 1: LOCAL -->
        <div onclick="selectOrderType('local', this)" class="order-type-card active" title="Finaliza o pedido imediatamente"
             style="border: 2px solid #2563eb; background: #eff6ff; border-radius: 8px; padding: 10px 5px; cursor: pointer; text-align: center; transition: all 0.2s;">
            <i data-lucide="utensils" size="18" style="color: #2563eb; margin-bottom: 4px;"></i>
            <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Local</div>
        </div>
        <!-- OPÇÃO 2: RETIRADA -->
        <div onclick="selectOrderType('retirada', this)" class="order-type-card" title="Mantém aberto como PAGO"
             style="border: 1px solid #cbd5e1; background: white; border-radius: 8px; padding: 10px 5px; cursor: pointer; text-align: center; transition: all 0.2s;">
            <i data-lucide="shopping-bag" size="18" style="color: #64748b; margin-bottom: 4px;"></i>
            <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Retirada</div>
        </div>
        <!-- OPÇÃO 3: ENTREGA -->
        <div onclick="selectOrderType('entrega', this)" class="order-type-card" title="Pedido para Entrega"
             style="border: 1px solid #cbd5e1; background: white; border-radius: 8px; padding: 10px 5px; cursor: pointer; text-align: center; transition: all 0.2s;">
            <i data-lucide="truck" size="18" style="color: #64748b; margin-bottom: 4px;"></i>
            <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Entrega</div>
        </div>
    </div>
    
    <!-- AVISO: Retirada - Mostra cliente ou aviso -->
    <div id="retirada-client-alert" style="display: none; margin-top: 12px;">
        <!-- Se TEM cliente selecionado -->
        <div id="retirada-client-selected" style="display: none; padding: 10px 12px; background: #d1fae5; border: 1px solid #10b981; border-radius: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="user-check" size="18" style="color: #059669;"></i>
                    <span style="font-weight: 700; color: #065f46; font-size: 0.9rem;" id="retirada-client-name">Cliente Selecionado</span>
                </div>
                <button type="button" onclick="clearRetiradaClient()" style="background: none; border: none; color: #059669; cursor: pointer; font-size: 0.8rem; text-decoration: underline;">Alterar</button>
            </div>
        </div>
        
        <!-- Se NÃO tem cliente -->
        <div id="retirada-no-client" style="display: none; padding: 10px 12px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="alert-triangle" size="18" style="color: #d97706;"></i>
                    <span style="font-weight: 600; color: #92400e; font-size: 0.85rem;">Vincule um cliente na barra lateral</span>
                </div>
            </div>
        </div>
    </div>

    <!-- AVISO: Entrega - Mostra status dos dados -->
    <div id="entrega-alert" style="display: none; margin-top: 12px;">
        <!-- Dados de entrega preenchidos -->
        <div id="entrega-dados-ok" style="display: none; padding: 10px 12px; background: #d1fae5; border: 1px solid #10b981; border-radius: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check-circle" size="18" style="color: #059669;"></i>
                    <span style="font-weight: 700; color: #065f46; font-size: 0.9rem;">Dados de entrega cadastrados</span>
                    <span style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">+ R$ <?= number_format($deliveryFee, 2, ',', '.') ?></span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="clearDeliveryData()" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 0.8rem; text-decoration: underline;">Excluir</button>
                    <button type="button" onclick="openDeliveryPanel()" style="background: none; border: none; color: #059669; cursor: pointer; font-size: 0.8rem; text-decoration: underline;">Editar</button>
                </div>
            </div>
        </div>
        
        <!-- Dados de entrega NÃO preenchidos -->
        <div id="entrega-dados-pendente" style="display: none; padding: 10px 12px; background: #dbeafe; border: 1px solid #3b82f6; border-radius: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="map-pin" size="18" style="color: #2563eb;"></i>
                    <span style="font-weight: 600; color: #1e40af; font-size: 0.85rem;">Preencha os dados da entrega</span>
                </div>
                <button type="button" onclick="openDeliveryPanel()" style="background: #2563eb; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600;">Preencher</button>
            </div>
        </div>
    </div>
</div>
