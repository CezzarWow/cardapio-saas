<?php
/**
 * PARTIAL: Checkout Main - Área Principal do Modal
 * Extraído de checkout-modal.php
 */
?>

<!-- CHECKOUT PRINCIPAL -->
<div id="checkout-main" style="background: white; width: 1000px; max-width: 95%; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); display: flex; flex-direction: column; max-height: 90vh;">
    
    <!-- Header -->
    <div style="padding: 15px 25px 0 25px;">
        <h2 style="margin: 0; color: #1e293b; font-size: 1.25rem; font-weight: 800;">Pagamento</h2>
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
    <div style="padding: 15px 25px; display: grid; grid-template-columns: 1.4fr 1fr; gap: 15px; align-items: stretch; overflow-y: auto; flex: 1;">
        
        <!-- COLUNA ESQUERDA: Métodos + Inputs + Ações -->
        <div style="display: flex; flex-direction: column; gap: 15px; justify-content: flex-start;">
            
            <!-- 1. Métodos de Pagamento -->
            <div>
                <label style="display: block; font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 8px;">FORMA DE PAGAMENTO</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button onclick="setMethod('dinheiro')" id="btn-method-dinheiro" class="payment-method-btn active" style="padding: 14px 10px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                        <i data-lucide="banknote" size="22"></i>
                        <span style="font-size: 0.95rem; font-weight: 700;">Dinheiro</span>
                    </button>
                    <button onclick="setMethod('pix')" id="btn-method-pix" class="payment-method-btn" style="padding: 18px 12px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                        <i data-lucide="qr-code" size="22"></i>
                        <span style="font-size: 0.95rem; font-weight: 700;">Pix</span>
                    </button>
                    <button onclick="setMethod('credito')" id="btn-method-credito" class="payment-method-btn" style="padding: 18px 12px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                        <i data-lucide="credit-card" size="22"></i>
                        <span style="font-size: 0.95rem; font-weight: 700;">Crédito</span>
                    </button>
                    <button onclick="setMethod('debito')" id="btn-method-debito" class="payment-method-btn" style="padding: 18px 12px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                        <i data-lucide="credit-card" size="22"></i>
                        <span style="font-size: 0.95rem; font-weight: 700;">Débito</span>
                    </button>
                </div>
            </div>

            <!-- Input Desconto -->
            <div style="background: #fff1f2; padding: 10px 15px; border-radius: 10px; border: 1px solid #fda4af;">
                <label style="display: block; font-size: 0.8rem; color: #be123c; font-weight: 700; margin-bottom: 5px;">DESCONTO (R$)</label>
                <input type="text" id="discount-amount" placeholder="0,00" 
                       style="width: 100%; padding: 8px; border: 1px solid #f43f5e; border-radius: 6px; font-weight: 700; font-size: 1rem; color: #be123c; outline: none; background: white;">
            </div>

            <!-- Input Valor a Pagar -->
            <div style="background: #f1f5f9; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; font-weight: 700; margin-bottom: 8px;">VALOR A LANÇAR</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 14px; color: #64748b; font-weight: bold; font-size: 1.1rem;">R$</span>
                    <input type="text" id="pay-amount" placeholder="0,00" 
                           onkeypress="handleEnter(event)"
                           onkeyup="PDVCheckout.formatMoneyInput(this)"
                           style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 700; font-size: 1.2rem; color: #1e293b; outline: none;">
                </div>
            </div>

            <!-- Botão Adicionar Pagamento -->
            <button onclick="addPayment()" style="width: 100%; padding: 12px; background: #e2e8f0; color: #475569; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: auto; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
                <i data-lucide="plus" size="18"></i>
                ADICIONAR PAGAMENTO
            </button>
        </div>

        <!-- COLUNA DIREITA: Lista de Pagamentos + Totais -->
        <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; display: flex; flex-direction: column; overflow: hidden; height: 100%; min-height: 380px;">
            
            <h3 style="margin: 0 0 15px 0; font-size: 0.95rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="list" size="16"></i> Resumo
            </h3>

            <!-- Lista Rolável -->
            <div id="payment-list" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px; padding-right: 5px; max-height: 210px;">
                <div style="text-align: center; color: #94a3b8; font-size: 0.9rem; margin-top: 20px;">
                    Nenhum pagamento lançado
                </div>
            </div>

            <!-- Resumo Final -->
            <div style="border-top: 1px solid #cbd5e1; padding-top: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #be123c;">
                    <span>Desconto:</span>
                    <strong id="display-discount">- R$ 0,00</strong>
                </div>
                
                <div style="background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 8px;">
                    <span>PAGO:</span>
                    <span id="display-paid">R$ 0,00</span>
                </div>
                <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 12px;">
                    <span>RESTANTE:</span>
                    <span id="display-remaining">R$ 0,00</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 2px dashed #cbd5e1; font-size: 1.3rem; color: #1e293b; font-weight: 900;">
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
