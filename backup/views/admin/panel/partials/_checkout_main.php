<?php
/**
 * PARTIAL: Checkout Main - Área Principal do Modal
 * Extraído de checkout-modal.php
 */
?>

<!-- CHECKOUT PRINCIPAL -->
<div id="checkout-main" class="checkout-container">
    
    <!-- Header -->
    <div class="checkout-header">
        <h2 class="checkout-title">Pagamento</h2>
        <button type="button" onclick="closeCheckout()" class="checkout-header-btn" title="Fechar">&times;</button>
    </div>
    
    <?php if ($isEditingPaid ?? false): ?>
    <!-- AVISO: Cobrando apenas a diferença -->
    <div id="differential-payment-banner" style="margin: 15px 25px 0; padding: 12px; background: #dbeafe; border: 1px solid #3b82f6; border-radius: 8px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
            <i data-lucide="info" size="18" style="color: #2563eb;"></i>
            <span style="font-weight: 700; color: #1e40af; font-size: 0.9rem;">Cobrando apenas os novos itens</span>
        </div>
        <div style="font-size: 0.85rem; color: #1e40af;">
            Valor já pago: <strong>R$ <?= number_format($contaAberta['total'] ?? 0, 2, ',', '.') ?></strong> — 
            Você está cobrando apenas a <strong>diferença</strong>.
        </div>
    </div>
    <?php endif; ?>

    <!-- GRID LAYOUT -->
    <div class="checkout-content">
        
        <!-- COLUNA ESQUERDA: Métodos + Inputs + Ações -->
        <div style="display: flex; flex-direction: column; gap: 10px; justify-content: flex-start;">
            
            <!-- 1. Métodos de Pagamento -->
            <div>
                <div class="payment-methods-grid">
                    <button onclick="setMethod('dinheiro')" id="btn-method-dinheiro" class="payment-method-btn active">
                        <i data-lucide="banknote" size="22"></i>
                        <span>Dinheiro</span>
                    </button>
                    <button onclick="setMethod('pix')" id="btn-method-pix" class="payment-method-btn large">
                        <i data-lucide="qr-code" size="22"></i>
                        <span>Pix</span>
                    </button>
                    <button onclick="setMethod('credito')" id="btn-method-credito" class="payment-method-btn large">
                        <i data-lucide="credit-card" size="22"></i>
                        <span>Crédito</span>
                    </button>
                    <button onclick="setMethod('debito')" id="btn-method-debito" class="payment-method-btn large">
                        <i data-lucide="credit-card" size="22"></i>
                        <span>Débito</span>
                    </button>
                </div>
            </div>

            <!-- Grid Desconto e Crediário -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                
                <!-- Desconto -->
                <div class="discount-box">
                    <label class="discount-label">DESCONTO (R$)</label>
                    <input type="text" id="discount-amount" placeholder="0,00" class="discount-input">
                </div>

                <!-- Crediário (Atalho de Pagamento) -->
                <div id="container-crediario-slot" class="crediario-box">
                    <label class="crediario-label">CREDIÁRIO</label>
                    <div class="crediario-wrapper">
                         <span class="crediario-currency">R$</span>
                         <input type="text" id="crediario-amount" placeholder="0,00" 
                                onkeypress="if(event.key === 'Enter') addCrediarioPayment()"
                                oninput="PDVCheckout.formatMoneyInput(this)"
                                class="crediario-input">
                         <button id="btn-add-crediario" onclick="addCrediarioPayment()" class="btn-add-crediario" title="Lançar Crediário">
                            <i data-lucide="plus" size="16"></i>
                         </button>
                    </div>
                </div>

            </div>

            <!-- INFO ROW (NOVA LINHA SEPARADA) -->
            <div id="crediario-info-row" class="crediario-info">
                <span>LIMITE: <span id="cred-limit-total">R$ 0,00</span></span>
                <span>DISPONÍVEL: <span id="cred-limit-available">R$ 0,00</span></span>
            </div>

            <!-- Input Valor a Pagar -->
            <div class="pay-input-box">
                <label class="pay-label">VALOR A LANÇAR</label>
                <div class="pay-controls">
                    <div style="position: relative; flex: 1;">
                        <span class="pay-currency">R$</span>
                        <input type="text" id="pay-amount" placeholder="0,00" 
                               onkeypress="handleEnter(event)"
                               onfocus="this.setSelectionRange(this.value.length, this.value.length)"
                               onclick="this.setSelectionRange(this.value.length, this.value.length)"
                               onkeyup="PDVCheckout.formatMoneyInput(this)"
                               class="pay-input">
                    </div>
                    <button onclick="CheckoutPayments.addPayment()" title="Adicionar Pagamento" class="btn-add-payment" onmouseover="this.style.background='#16a34a'" onmouseout="this.style.background='#22c55e'">
                        <i data-lucide="plus" size="24"></i>
                    </button>
                </div>
            </div>

            <!-- Área de Ajuste de Total (Estilo Clean Card Cinza) -->
            <div class="adjust-total-box">
                <label class="adjust-label">
                    TOTAL FINAL:
                </label>
                
                <div class="adjust-controls">
                     <div class="adjust-input-wrapper">
                        <span class="adjust-currency">R$</span>
                        <input type="text" id="display-total-edit" readonly
                               onkeypress="if(event.key === 'Enter') CheckoutAdjust.saveEdit()"
                               oninput="PDVCheckout.formatMoneyInput(this)"
                               onfocus="this.setSelectionRange(this.value.length, this.value.length)"
                               onclick="this.setSelectionRange(this.value.length, this.value.length)"
                               class="adjust-input">
                    </div>
                    
                    <button id="btn-toggle-edit" onclick="CheckoutAdjust.toggleEdit()" title="Editar Valor Final"
                            class="btn-adjust-action btn-adjust-edit">
                        <i data-lucide="edit-2" size="16"></i>
                    </button>
                    
                    <button id="btn-save-total" onclick="CheckoutAdjust.saveEdit()" title="Confirmar Ajuste"
                            class="btn-adjust-action btn-adjust-confirm" style="display: none;">
                        <i data-lucide="check" size="18"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- COLUNA DIREITA: Lista de Pagamentos + Totais -->
        <div class="checkout-summary-col">
            
            <h3 class="summary-title">
                <i data-lucide="list" size="16"></i> Resumo
            </h3>

            <!-- Lista Rolável (ocupa espaço disponível e scrolla se exceder) -->
            <div id="payment-list" class="payment-list">
                <div style="text-align: center; color: #94a3b8; font-size: 0.9rem; margin-top: 20px;">
                    Nenhum pagamento lançado
                </div>
            </div>

            <!-- Resumo Final -->
            <div class="summary-footer">
                <div class="summary-row discount">
                    <span>Desconto:</span>
                    <strong id="display-discount">- R$ 0,00</strong>
                </div>
                
                <!-- TROCO (sempre visível) -->
                <div id="change-display-box" class="summary-row change">
                    <span>Troco:</span>
                    <strong id="display-change">R$ 0,00</strong>
                </div>
                
                <div class="summary-box paid">
                    <span>PAGO:</span>
                    <span id="display-paid">R$ 0,00</span>
                </div>
                <div class="summary-box remaining">
                    <span>RESTANTE:</span>
                    <span id="display-remaining">R$ 0,00</span>
                </div>

                <div class="summary-total">
                    <span>TOTAL:</span>
                    <strong id="checkout-total-display">R$ 0,00</strong>
                </div>
            </div>
        </div>
    </div>

    <?php // Tipo de Pedido ?>
    <?php require __DIR__ . '/_checkout_order_type.php'; ?>

    <?php // Footer com Botões ?>
    <?php require __DIR__ . '/_checkout_footer.php'; ?>
    
</div>
<!-- FIM CHECKOUT PRINCIPAL -->
