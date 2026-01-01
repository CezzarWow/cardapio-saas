<?php
/**
 * PARTIAL: Modal de Pagamento
 * Espera:
 * - $cardapioConfig (array)
 */
?>
<!-- MODAL DE PAGAMENTO -->
<div id="paymentModal" class="cardapio-modal">
    <div class="cardapio-modal-content fullscreen payment-modal">
        <div class="cardapio-suggestions-header">
            <button class="cardapio-back-btn" onclick="CardapioCheckout.backToReview()">
                <i data-lucide="arrow-left" size="20"></i>
            </button>
            <h2>💳 Pagamento</h2>
        </div>
        
        <div class="cardapio-modal-body">
            <!-- Total do Pedido -->
            <div class="payment-total-box">
                <span class="payment-total-label">Total do Pedido</span>
                <span id="paymentTotalValue" class="payment-total-value">R$ 0,00</span>
            </div>
            
            <!-- Aviso para Retirada/Local (oculto por padrão) -->
            <div id="orderTypeAlert" class="order-type-alert" style="display: none;">
                <i data-lucide="info" size="18"></i>
                <span id="orderTypeAlertText"></span>
            </div>
            
            <!-- Dados do Cliente -->
            <div class="payment-section">
                <h3 class="payment-section-title">
                    <i data-lucide="user" size="18"></i>
                    Seus Dados
                </h3>
                
                <div class="payment-form">
                    <input type="text" id="customerName" class="payment-input" placeholder="Seu nome *" maxlength="40" enterkeyhint="done" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}">
                    
                    <!-- Telefone (sempre visível, linha separada) -->
                    <input type="tel" id="customerPhone" class="payment-input" placeholder="Seu telefone *" maxlength="15" enterkeyhint="done" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}">
                    
                    <!-- Endereço (só para entrega) -->
                    <input type="text" id="customerAddress" class="payment-input delivery-only" placeholder="Endereço (rua) *" maxlength="80" enterkeyhint="done" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}">
                    
                    <div class="payment-number-row delivery-only">
                        <input type="tel" id="customerNumber" class="payment-input payment-number-input" placeholder="Nº *" maxlength="8" enterkeyhint="done" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}">
                        <button type="button" class="no-number-btn" onclick="toggleNoNumber()">
                            <span>Sem nº</span>
                        </button>
                        <input type="text" id="customerNeighborhood" class="payment-input payment-neighborhood-input" placeholder="Bairro" maxlength="30" enterkeyhint="done" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}">
                    </div>
                    
                    <textarea id="customerObs" class="payment-input payment-textarea" placeholder="Observações (opcional)" rows="2" maxlength="140" enterkeyhint="done" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}"></textarea>
                </div>
            </div>
            
            <!-- Forma de Pagamento -->
            <div class="payment-section">
                <h3 class="payment-section-title">
                    <i data-lucide="wallet" size="18"></i>
                    Forma de Pagamento
                </h3>
                
                <div class="payment-methods-list">
                    <label class="payment-method-option <?= ($cardapioConfig['accept_cash'] ?? 1) ? '' : 'disabled-option' ?>" data-method="dinheiro">
                        <input type="radio" name="paymentMethod" value="dinheiro" onchange="selectPaymentMethod('dinheiro')" <?= ($cardapioConfig['accept_cash'] ?? 1) ? '' : 'disabled' ?>>
                        <span class="payment-method-check"></span>
                        <span class="payment-method-icon">💵</span>
                        <span class="payment-method-label">Dinheiro</span>
                    </label>
                    
                    <label class="payment-method-option <?= ($cardapioConfig['accept_credit'] ?? 1) ? '' : 'disabled-option' ?>" data-method="cartao">
                        <input type="radio" name="paymentMethod" value="cartao" onchange="selectPaymentMethod('cartao')" <?= ($cardapioConfig['accept_credit'] ?? 1) ? '' : 'disabled' ?>>
                        <span class="payment-method-check"></span>
                        <span class="payment-method-icon">💳</span>
                        <span class="payment-method-label">Cartão</span>
                    </label>
                    
                    <label class="payment-method-option <?= ($cardapioConfig['accept_pix'] ?? 1) ? '' : 'disabled-option' ?>" data-method="pix">
                        <input type="radio" name="paymentMethod" value="pix" onchange="selectPaymentMethod('pix')" <?= ($cardapioConfig['accept_pix'] ?? 1) ? '' : 'disabled' ?>>
                        <span class="payment-method-check"></span>
                        <span class="payment-method-icon">💠</span>
                        <span class="payment-method-label">PIX</span>
                    </label>
                </div>
                
                <!-- Campo de Troco (só aparece se dinheiro) -->
                <div id="changeContainer" class="change-container" style="display: none;">
                    
                    <!-- Modo Edição -->
                    <div id="changeInputGroup">
                        <label class="change-label">Troco para quanto?</label>
                        <div class="change-input-row">
                            <input type="tel" id="changeAmount" class="payment-input change-amount-input" placeholder="Ex: R$ 50,00" enterkeyhint="done" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmChange()}">
                            
                            <button type="button" class="btn-confirm-change" onclick="confirmChange()">
                                <i data-lucide="check" size="18"></i>
                            </button>

                            <button type="button" class="no-change-btn" onclick="toggleNoChange()">
                                <span>Sem troco</span>
                            </button>
                        </div>
                    </div>

                    <!-- Modo Resumo (Compacto) -->
                    <div id="changeSummary" class="change-summary" style="display: none;">
                        <span id="changeSummaryText" class="change-summary-text">Troco: R$ 50,00</span>
                        <button type="button" class="btn-edit-change" onclick="editChange()">
                            <i data-lucide="pencil" size="14"></i>
                            <span>Editar</span>
                        </button>
                    </div>

                </div>
            </div>
            
            <!-- Botão Enviar Pedido (dentro do body, parte do conteúdo) -->
            <button id="sendOrderBtn" class="cardapio-floating-cart-btn send-order-btn show" onclick="sendOrder()">
                <i data-lucide="send" size="20"></i>
                <span>Enviar Pedido</span>
            </button>
        </div>
    </div>
</div>
