<?php
/**
 * MODAL CHECKOUT - Partial do PDV
 * 
 * VARIÁVEIS REQUERIDAS NO ESCOPO:
 * - $isEditingPaid (bool) - Modo edição de pedido pago
 * - $contaAberta (array|null) - Dados da conta aberta
 * - $deliveryFee (float) - Taxa de entrega
 */
?>
    <div id="checkoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center;">
    
    <!-- Container Flex: Checkout + Painel Entrega -->
    <div style="display: flex; gap: 0; align-items: stretch;">
    
    <!-- CHECKOUT PRINCIPAL (inalterado) -->
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

        <!-- GRID LAYOUT (NEW) -->
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

                <!-- 2. Desconto and 3. Valor (Agora em linhas separadas) -->
                
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
                    <!-- Items serão inseridos aqui via JS -->
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

        <!-- FOOTER: TOTAL, RESTANTE E BOTÕES -->
        <div style="padding: 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            
            <!-- LINHA DE TOTAIS -->
            <!-- LINHA DE TOTAIS -->
            <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 15px; padding: 0 5px;">
                
                <div id="change-box" style="display: none; text-align: right; background: #dcfce7; padding: 10px 20px; border-radius: 8px; border: 1px solid #86efac; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <span style="font-size: 0.85rem; font-weight: 800; color: #166534; display: block; letter-spacing: 0.5px;">TROCO</span>
                    <span id="checkout-change" style="font-size: 1.6rem; font-weight: 900; color: #166534;">R$ 0,00</span>
                </div>
            </div>


            <!-- BOTÕES -->
            <div style="display: flex; gap: 15px;">
                <button onclick="closeCheckout()" style="flex: 1; padding: 15px; background: white; border: 1px solid #cbd5e1; color: #475569; border-radius: 10px; font-weight: 700; cursor: pointer;">Cancelar</button>
                
                <!-- Botão SALVAR (aparece só em Retirada/Entrega) -->
                <button id="btn-save-pickup" onclick="savePickupOrder()" 
                        style="display: none; flex: 1; padding: 15px; background: #f59e0b; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 0.95rem;">
                    <i data-lucide="clock" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 5px;"></i>
                    Pagar Depois
                </button>
                
                <button id="btn-finish-sale" onclick="submitSale()" disabled 
                        style="flex: 2; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 10px; font-weight: 800; cursor: not-allowed; font-size: 1.1rem; display: flex; justify-content: center; align-items: center; gap: 10px;">
                    CONCLUIR VENDA <i data-lucide="check-circle"></i>
                </button>
            </div>
        </div>
        
    </div>
    <!-- FIM CHECKOUT PRINCIPAL -->
    
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
    
    </div>
    <!-- FIM Container Flex -->
    
</div>
