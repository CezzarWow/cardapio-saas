<?php
/**
 * PARTIAL: Checkout Main - Área Principal do Modal
 * Extraído de checkout-modal.php
 */
?>

<!-- CHECKOUT PRINCIPAL -->
<div id="checkout-main" style="background: white; width: 1000px; max-width: 95%; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); display: flex; flex-direction: column; max-height: 90vh;">
    
    <!-- Header -->
    <div style="padding: 15px 25px 0 25px; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0; color: #1e293b; font-size: 1.25rem; font-weight: 800;">Pagamento</h2>
        <button type="button" onclick="closeCheckout()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; line-height: 1; padding: 0;" title="Fechar">&times;</button>
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
        <div style="display: flex; flex-direction: column; gap: 10px; justify-content: flex-start;">
            
            <!-- 1. Métodos de Pagamento -->
            <div>

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

            <!-- Grid Desconto e Crediário -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                
                <!-- Desconto -->
                <div style="background: #fff1f2; padding: 10px 15px; border-radius: 10px; border: 1px solid #fda4af;">
                    <label style="display: block; font-size: 0.8rem; color: #be123c; font-weight: 700; margin-bottom: 5px;">DESCONTO (R$)</label>
                    <input type="text" id="discount-amount" placeholder="0,00" 
                           style="width: 100%; padding: 8px; border: 1px solid #f43f5e; border-radius: 6px; font-weight: 700; font-size: 1rem; color: #be123c; outline: none; background: white;">
                </div>

                <!-- Crediário (Atalho de Pagamento) -->
                <!-- Crediário (Atalho de Pagamento) -->
                <!-- Crediário (Atalho de Pagamento) -->
                <div id="container-crediario-slot" style="background: #fff7ed; padding: 10px 15px; border-radius: 10px; border: 1px solid #fdba74; transition: opacity 0.3s;">
                    <label style="display: block; font-size: 0.8rem; color: #c2410c; font-weight: 700; margin-bottom: 5px;">CREDIÁRIO</label>
                    <div style="position: relative;">
                         <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #c2410c; font-weight: bold; font-size: 1rem;">R$</span>
                         <input type="text" id="crediario-amount" placeholder="0,00" 
                                onkeypress="if(event.key === 'Enter') addCrediarioPayment()"
                                oninput="PDVCheckout.formatMoneyInput(this)"
                                style="width: 100%; padding: 8px 36px 8px 35px; border: 1px solid #fb923c; border-radius: 6px; font-weight: 700; font-size: 1rem; color: #c2410c; outline: none; background: white;">
                         <button id="btn-add-crediario" onclick="addCrediarioPayment()" style="position: absolute; right: 2px; top: 2px; bottom: 2px; border: none; background: #fb923c; color: white; border-radius: 4px; width: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="Lançar Crediário">
                            <i data-lucide="plus" size="16"></i>
                         </button>
                    </div>
                </div>

            </div>

            <!-- INFO ROW (NOVA LINHA SEPARADA) -->
            <div id="crediario-info-row" style="display: flex; justify-content: space-between; margin-top: 10px; padding: 5px 8px; background: #fff7ed; border: 1px solid #fdba74; border-radius: 6px; font-size: 0.70rem; color: #9a3412; font-weight: 700;">
                <span>LIMITE: <span id="cred-limit-total">R$ 0,00</span></span>
                <span>DISPONÍVEL: <span id="cred-limit-available">R$ 0,00</span></span>
            </div>

            <!-- Input Valor a Pagar -->
            <div style="background: #f1f5f9; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 10px;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; font-weight: 700; margin-bottom: 8px;">VALOR A LANÇAR</label>
                <div style="position: relative; display: flex; gap: 8px;">
                    <div style="position: relative; flex: 1;">
                        <span style="position: absolute; left: 12px; top: 14px; color: #64748b; font-weight: bold; font-size: 1.1rem;">R$</span>
                        <input type="text" id="pay-amount" placeholder="0,00" 
                               onkeypress="handleEnter(event)"
                               onfocus="this.setSelectionRange(this.value.length, this.value.length)"
                               onclick="this.setSelectionRange(this.value.length, this.value.length)"
                               onkeyup="PDVCheckout.formatMoneyInput(this)"
                               style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 700; font-size: 1.2rem; color: #1e293b; outline: none;">
                    </div>
                    <button onclick="CheckoutPayments.addPayment()" title="Adicionar Pagamento" style="width: 50px; background: #22c55e; color: white; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='#16a34a'" onmouseout="this.style.background='#22c55e'">
                        <i data-lucide="plus" size="24"></i>
                    </button>
                </div>
            </div>



            <!-- Área de Ajuste de Total (Estilo Clean Card Cinza) -->
            <div style="margin-top: 10px; background: #f8fafc; padding: 12px 15px; border-radius: 10px; border: 2px solid #cbd5e1; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                <label style="font-size: 0.95rem; color: #334155; font-weight: 800; margin: 0;">
                    TOTAL FINAL:
                </label>
                
                <div style="display: flex; gap: 6px; align-items: center; flex: 1;">
                     <div style="position: relative; width: 130px;">
                        <span style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: bold; font-size: 0.95rem;">R$</span>
                        <input type="text" id="display-total-edit" readonly
                               onkeypress="if(event.key === 'Enter') CheckoutAdjust.saveEdit()"
                               oninput="PDVCheckout.formatMoneyInput(this)"
                               onfocus="this.setSelectionRange(this.value.length, this.value.length)"
                               onclick="this.setSelectionRange(this.value.length, this.value.length)"
                               style="width: 100%; padding: 6px 6px 6px 30px; border: 1px solid #e2e8f0; border-radius: 6px; font-weight: 700; font-size: 1.05rem; color: #475569; outline: none; background: #f1f5f9; height: 36px;">
                    </div>
                    
                    <button id="btn-toggle-edit" onclick="CheckoutAdjust.toggleEdit()" title="Editar Valor Final"
                            style="width: 36px; height: 36px; background: white; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                        <i data-lucide="edit-2" size="16"></i>
                    </button>
                    
                    <button id="btn-save-total" onclick="CheckoutAdjust.saveEdit()" title="Confirmar Ajuste"
                            style="display: none; width: 36px; height: 36px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; align-items: center; justify-content: center; transition: all 0.2s;">
                        <i data-lucide="check" size="18"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- COLUNA DIREITA: Lista de Pagamentos + Totais -->
        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; display: flex; flex-direction: column; height: 100%; min-height: 0; overflow: hidden;">
            
            <h3 style="margin: 0 0 10px 0; font-size: 0.95rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="list" size="16"></i> Resumo
            </h3>

            <!-- Lista Rolável (ocupa espaço disponível e scrolla se exceder) -->
            <div id="payment-list" style="flex: 1 1 0; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; padding-right: 5px; margin-bottom: 10px; padding-bottom: 20px;">
                <div style="text-align: center; color: #94a3b8; font-size: 0.9rem; margin-top: 20px;">
                    Nenhum pagamento lançado
                </div>
            </div>

            <!-- Resumo Final -->
            <div style="border-top: 1px solid #cbd5e1; padding-top: 10px; margin-top: auto;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #be123c; font-weight: 600;">
                    <span>Desconto:</span>
                    <strong id="display-discount">- R$ 0,00</strong>
                </div>
                
                <!-- TROCO (sempre visível) -->
                <div id="change-display-box" style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #166534; font-weight: 600;">
                    <span>Troco:</span>
                    <strong id="display-change">R$ 0,00</strong>
                </div>
                
                <div style="background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 8px;">
                    <span>PAGO:</span>
                    <span id="display-paid">R$ 0,00</span>
                </div>
                <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 8px;">
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
