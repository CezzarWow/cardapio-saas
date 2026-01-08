<?php
/**
 * PDV-CART-SIDEBAR.PHP - Sidebar do Carrinho
 * 
 * Contém: Header do carrinho, Estado vazio, Itens já pedidos, Footer com totais e botões
 * Variáveis esperadas: $mesa_id, $mesa_numero, $contaAberta, $itensJaPedidos, $isEditingPaid
 */
?>

<aside class="cart-sidebar" style="padding-bottom: 85px;">
    <div class="cart-header">
        <h2 class="cart-title">
            <i data-lucide="shopping-cart" color="#2563eb"></i> Carrinho
        </h2>
        <div style="display: flex; gap: 5px;">
            <button id="btn-undo-clear" class="btn-icon" onclick="PDVCart.undoClear()" title="Desfazer Limpeza" style="display: none; color: #2563eb; background: #eff6ff; border-color: #bfdbfe;">
                <i data-lucide="rotate-ccw"></i>
            </button>
            <button class="btn-icon" onclick="clearCart()" title="Limpar Carrinho"><i data-lucide="trash-2"></i></button>
        </div>
    </div>
    
    <div id="cart-empty-state" class="cart-empty" style="flex: 1;">
        <i data-lucide="shopping-cart" size="48" color="#e5e7eb" style="margin-bottom: 1rem;"></i>
        <p>Carrinho Vazio</p>
    </div>

    <!-- Items Area com Flex 1 para empurrar rodapé (mas scrolar) -->
    <div id="cart-items-area" style="flex: 1; overflow-y: auto; padding: 0 1.5rem; display: none;"></div>

    <?php if (!empty($itensJaPedidos)): ?>
        <div style="padding: 1rem; background: #fff7ed; border-bottom: 1px solid #fed7aa;">
            <h3 style="font-size: 0.85rem; font-weight: 700; color: #9a3412; margin-bottom: 0.5rem; display:flex; justify-content:space-between; align-items:center;">
                <span><?= $mesa_id ? 'Já na Mesa' : 'Já na Comanda' ?></span>
                <span>Total: R$ <?= number_format($contaAberta['total'], 2, ',', '.') ?></span>
            </h3>
            <div style="max-height: 250px; overflow-y: auto;">
                <?php foreach ($itensJaPedidos as $itemAntigo): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #9a3412; margin-bottom: 4px;">
                        <span><?= $itemAntigo['quantity'] ?>x <?= $itemAntigo['name'] ?></span>
                        <div style="display:flex; align-items:center; gap:5px;">
                            <span>R$ <?= number_format($itemAntigo['price'], 2, ',', '.') ?></span>
                            <button onclick="deleteSavedItem(<?= $itemAntigo['id'] ?>, <?= $contaAberta['id'] ?>)" title="Remover item salvo"
                                    style="border:none; background:none; cursor:pointer; color:#ef4444; display:flex; align-items:center;">
                                <i data-lucide="trash" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: right; margin-top: 5px;">
                 <button onclick="cancelTableOrder(<?= $mesa_id ?>, <?= $contaAberta['id'] ?>)" 
                         style="background: none; border: none; color: #dc2626; font-size: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: underline;">
                     Cancelar Pedido da Mesa
                 </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="cart-footer" style="box-shadow: 0 -4px 12px rgba(0,0,0,0.05); padding-top: 10px; padding-bottom: 15px;">
        
    <?php if (!$mesa_id && empty($contaAberta)): ?>
        <div style="margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px dashed #e5e7eb;">
            <label style="font-size: 1.1rem; font-weight: 800; color: #1f2937; margin-bottom: 10px; display: block;">Identificar Mesa / Cliente</label>
            
                <div id="client-search-area" style="display: flex; gap: 12px; align-items: flex-start;">
                <!-- Wrapper relativo apenas para o Input e Resultados -->
                <form id="form-client-search" action="#" onsubmit="return false;" style="position: relative; flex: 1;">
                    <input type="text" id="client-search" name="pdv_main_search_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Clique para ver mesas ou digite..."
                           style="width: 100%; padding: 10px 12px; border: 1px solid #94a3b8; border-radius: 10px; font-size: 1.1rem; outline: none; transition: all 0.2s; background: #f8fafc;">
                    
                    <!-- Dropdown Redesenhado -->
                    <div id="client-results" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); max-height: 200px; overflow-y: auto; z-index: 9999; margin-top: 6px;">
                    </div>
                </form>
                
                <button type="button" onclick="const m=document.getElementById('clientModal'); if(m) { document.body.appendChild(m); m.style.display='flex'; m.style.zIndex='9999'; document.getElementById('new_client_name').focus(); }" title="Novo Cliente"
                        style="flex-shrink: 0; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; height: 38px; width: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                    <i data-lucide="user-plus" style="width: 20px;"></i>
                </button>
            </div>

            <div id="selected-client-area" style="display: none; background: #ecfdf5; padding: 8px; border-radius: 6px; border: 1px solid #a7f3d0; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="user" style="width: 16px; color: #059669;"></i>
                    <span id="selected-client-name" style="font-size: 0.9rem; font-weight: 600; color: #065f46;">Nome</span>
                </div>
                <button onclick="clearClient()" style="border: none; background: none; color: #059669; cursor: pointer; font-weight: bold;">&times;</button>
            </div>
            
            <input type="hidden" id="current_client_id" name="client_id">
        </div>
    <?php else: ?>
        <!-- SE ESTIVER EM MESA OU COMANDA, NÃO MOSTRA BUSCA -->
         <input type="hidden" id="current_client_id" name="client_id" value="<?= $contaAberta['client_id'] ?? '' ?>">
         <input type="hidden" id="current_order_id" value="<?= $contaAberta['id'] ?? '' ?>">
         <input type="hidden" id="current_order_is_paid" value="<?= ($contaAberta['is_paid'] ?? 0) ?>">
         <input type="hidden" id="client-search"> 
    <?php endif; ?>
        
        <!-- TOTAL GERAL (Mesa + Carrinho) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <span style="font-size: 1.5rem; font-weight: 900; color: #111827; text-transform: uppercase;">TOTAL</span>
            <span id="grand-total" style="font-size: 1.8rem; font-weight: 900; color: #2563eb;">R$ 0,00</span>
        </div>

        <!-- Adicionar (Carrinho) - Só mostra se estiver em Mesa ou Comanda Aberta -->
        <?php if ($mesa_id || !empty($contaAberta)): ?>
        <div class="total-row" style="margin-bottom: 1.5rem;">
            <span class="total-label" style="font-size: 1rem; color: #111827; font-weight: 700;">Adicionar</span>
            <span id="cart-total" class="total-value" style="font-size: 1.1rem; color: #16a34a;">R$ 0,00</span>
        </div>
        <?php else: ?>
            <!-- Escondido no Balcão, mas mantendo ID pro JS funcionar -->
             <span id="cart-total" style="display: none;">R$ 0,00</span>
        <?php endif; ?>

        <!-- Botões de Ação -->
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            
            <!-- 1. Botão SALVAR COMANDA (Sem pagar) -->
            <?php if (!empty($showSaveCommand)): ?>
            <button id="btn-save-command" onclick="saveClientOrder()" 
                    style="flex: 1; background: #ea580c; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                Salvar
            </button>
            <?php endif; ?>

            <!-- 2. Botão INCLUIR (Edição de Pedido Pago) -->
            <?php if (!empty($showIncludePaid)): ?>
            <button id="btn-include-paid" onclick="includePaidOrderItems()" 
                    style="flex: 1; background: #16a34a; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                <i data-lucide="plus-circle" size="20"></i> Incluir
            </button>
            <?php endif; ?>

            <!-- 3. Botão FINALIZAR (Balcão - Venda Rápida) -->
            <?php if (!empty($showQuickSale)): ?>
            <button id="btn-finalizar" class="btn-primary" onclick="finalizeSale()" 
                    style="flex: 1; display: flex; padding: 16px; font-size: 1.1rem; align-items: center; justify-content: center;">
                Finalizar
            </button>
            <?php endif; ?>

            <!-- 4. Botão FECHAR MESA -->
            <?php if (!empty($showCloseTable)): ?>
                <button onclick="fecharContaMesa(<?= $mesa_id ?>)" 
                        style="flex: 1; background: #2563eb; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                    Finalizar Mesa
                </button>
            <?php endif; ?>

            <!-- 5. Botão ENTREGAR/BAIXAR (Comanda) -->
            <?php if (!empty($showCloseCommand)): ?>
                <?php 
                    $isPaid = !empty($contaAberta['is_paid']) && $contaAberta['is_paid'] == 1;
                    $btnText = $isPaid ? 'Entregar (Concluir)' : 'Finalizar';
                    $btnColor = $isPaid ? '#059669' : '#2563eb';
                ?>
                <button onclick="fecharComanda(<?= $contaAberta['id'] ?>)" 
                        style="flex: 1; background: <?= $btnColor ?>; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                    <?= $btnText ?>
                </button>
            <?php endif; ?>
                
            <!-- Hidden input para o JS ler o valor inicial -->
            <input type="hidden" id="table-initial-total" value="<?= $contaAberta['total'] ?? 0 ?>">
        </div>
    </div>
</aside>
