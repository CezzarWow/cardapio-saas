<?php
/**
 * PARTIAL: Checkout Order Type - Tipo de Pedido
 * Extraído de checkout-modal.php
 */
?>

<!-- 4. Tipo de Pedido (Cards) -->
<div class="order-types-container">
    <input type="hidden" id="keep_open_value" value="false">
    <div class="order-types-grid">
        <!-- OPÇÃO 1: LOCAL -->
        <div onclick="selectOrderType('local', this)" class="order-type-card active" title="Finaliza o pedido imediatamente">
            <i data-lucide="utensils" size="16" style="color: #2563eb; margin-bottom: 2px;"></i>
            <div class="order-type-label">Local</div>
        </div>
        <!-- OPÇÃO 2: RETIRADA -->
        <div onclick="selectOrderType('retirada', this)" class="order-type-card" title="Mantém aberto como PAGO">
            <i data-lucide="shopping-bag" size="16" style="color: #64748b; margin-bottom: 2px;"></i>
            <div class="order-type-label">Retirada</div>
        </div>
        <!-- OPÇÃO 3: ENTREGA -->
        <div onclick="selectOrderType('entrega', this)" class="order-type-card" title="Pedido para Entrega">
            <i data-lucide="truck" size="16" style="color: #64748b; margin-bottom: 2px;"></i>
            <div class="order-type-label">Entrega</div>
        </div>
    </div>
    
    <!-- Container com altura fixa para evitar tremor ao alternar -->
    <div id="order-type-alerts-container" class="order-type-alerts">
        <!-- AVISO: Retirada - Mostra cliente ou aviso -->
        <div id="retirada-client-alert" style="display: none;">
            <!-- Se TEM cliente selecionado -->
        <div id="retirada-client-selected" style="display: none;" class="alert-box success">
            <div class="alert-content" style="justify-content: space-between; width: 100%;">
                <div class="alert-content">
                    <i data-lucide="user-check" size="18" style="color: #059669;"></i>
                    <span id="retirada-client-name" class="alert-text">Cliente Selecionado</span>
                </div>
                <button type="button" onclick="clearRetiradaClient()" class="alert-btn" style="color: #059669;">Alterar</button>
            </div>
        </div>
        
        <!-- Se NÃO tem cliente -->
        <div id="retirada-no-client" style="display: none;" class="alert-box warning">
            <div class="alert-content" style="justify-content: space-between; width: 100%;">
                <div class="alert-content">
                    <i data-lucide="alert-triangle" size="18" style="color: #d97706;"></i>
                    <span class="alert-text">Vincule um cliente na barra lateral</span>
                </div>
            </div>
        </div>
    </div>

        <!-- AVISO: Entrega - Mostra status dos dados -->
        <div id="entrega-alert" style="display: none;">
            <!-- Dados de entrega preenchidos -->
            <div id="entrega-dados-ok" style="display: none;" class="alert-box success">
                <div class="alert-content" style="justify-content: space-between; width: 100%;">
                    <div class="alert-content">
                        <i data-lucide="check-circle" size="18" style="color: #059669;"></i>
                        <span class="alert-text">Dados de entrega</span>
                        <span style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">+ R$ <?= number_format($deliveryFee, 2, ',', '.') ?></span>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="clearDeliveryData()" class="alert-btn" style="color: #dc2626;">Excluir</button>
                        <button type="button" onclick="openDeliveryPanel()" class="alert-btn" style="color: #059669;">Editar</button>
                    </div>
                </div>
            </div>
            
            <!-- Dados de entrega NÃO preenchidos -->
            <div id="entrega-dados-pendente" style="display: none;" class="alert-box warning">
                <div class="alert-content" style="justify-content: space-between; width: 100%;">
                    <div class="alert-content">
                        <i data-lucide="alert-triangle" size="18" style="color: #d97706;"></i>
                        <span class="alert-text">Preencha os dados da entrega</span>
                    </div>
                    <button type="button" onclick="openDeliveryPanel()" class="alert-btn action">Preencher</button>
                </div>
            </div>
        </div>
    </div><!-- fecha order-type-alerts-container -->
</div>
