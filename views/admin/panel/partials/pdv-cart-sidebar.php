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
            <button id="btn-undo-clear" class="btn-icon" data-action="cart-undo" title="Desfazer Limpeza" style="display: none; color: #2563eb; background: #eff6ff; border-color: #bfdbfe;">
                <i data-lucide="rotate-ccw"></i>
            </button>
            <?php if (!empty($contaAberta['id'])): ?>
            <button class="btn-icon" data-action="ficha-open" title="Ver Ficha do Cliente" style="color: #2563eb; background: #eff6ff; border-color: #bfdbfe;">
                <i data-lucide="clipboard-list"></i>
            </button>
            <?php endif; ?>
            <button class="btn-icon" data-action="cart-clear" title="Limpar Carrinho"><i data-lucide="trash-2"></i></button>
        </div>
    </div>
    
    <div id="cart-empty-state" class="cart-empty" style="flex: 1;">
        <i data-lucide="shopping-cart" size="48" color="#e5e7eb" style="margin-bottom: 1rem;"></i>
        <p>Carrinho Vazio</p>
    </div>

    <!-- Items Area com Flex 1 para empurrar rodapé (mas scrolar) -->
    <div id="cart-items-area" class="cart-items-area" style="display: none;"></div>

    <?php if (!empty($itensJaPedidos)): ?>
        <?php $savedLabel = $mesa_id ? 'Já na Mesa' : 'Já na Comanda'; ?>
        <div style="padding: 1rem; background: #fff7ed; border-bottom: 1px solid #fed7aa;">
            <h3 style="font-size: 0.85rem; font-weight: 700; color: #9a3412; margin-bottom: 0.5rem; display:flex; justify-content:space-between; align-items:center;">
                <span><?= \App\Helpers\ViewHelper::e($savedLabel) ?></span>
                <span>Total: R$ <?= number_format($contaAberta['total'], 2, ',', '.') ?></span>
            </h3>
            <div style="max-height: 150px; overflow-y: auto;">
                <?php foreach ($itensJaPedidos as $itemAntigo): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #9a3412; margin-bottom: 4px;">
                        <span><?= (int) ($itemAntigo['quantity'] ?? 0) ?>x <?= \App\Helpers\ViewHelper::e($itemAntigo['name'] ?? '') ?></span>
                        <div style="display:flex; align-items:center; gap:5px;">
                            <span>R$ <?= number_format($itemAntigo['price'], 2, ',', '.') ?></span>
                            <button data-action="saved-item-delete" 
                                    data-id="<?= (int) ($itemAntigo['id'] ?? 0) ?>" 
                                    data-order-id="<?= (int) ($contaAberta['id'] ?? 0) ?>"
                                    title="Remover item salvo"
                                    style="border:none; background:none; cursor:pointer; color:#ef4444; display:flex; align-items:center;">
                                <i data-lucide="trash" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: right; margin-top: 5px;">
                 <button data-action="table-cancel" 
                         data-table-id="<?= (int) $mesa_id ?>"
                         data-order-id="<?= (int) ($contaAberta['id'] ?? 0) ?>"
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
            
            <div id="client-search-area" style="display: flex; gap: 8px; align-items: center; height: 44px;">
                <!-- Wrapper relativo apenas para o Input e Resultados -->
                <form id="form-client-search" action="#" onsubmit="return false;" style="position: relative; flex: 1; height: 100%;">
                    <input type="text" id="client-search" name="pdv_main_search_<?= time() ?>" autocomplete="off" data-lpignore="true" placeholder="Clique para ver mesas ou digite..."
                           style="width: 100%; height: 100%; padding: 0 12px; border: 1px solid #94a3b8; border-radius: 10px; font-size: 1rem; outline: none; transition: all 0.2s; background: #f8fafc;">
                    
                    <!-- Dropdown Redesenhado -->
                    <div id="client-results" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); max-height: 200px; overflow-y: auto; z-index: 9999; margin-top: 6px;">
                    </div>
                </form>
                
                <button type="button" data-action="client-new" title="Novo Cliente"
                        style="flex-shrink: 0; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; height: 100%; width: 44px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                    <i data-lucide="user-plus" style="width: 20px;"></i>
                </button>
            </div>

            <div id="selected-client-area" style="display: none; height: 44px; background: #ecfdf5; padding: 0 12px; border-radius: 10px; border: 1px solid #a7f3d0; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="user" style="width: 18px; color: #059669;"></i>
                    <span id="selected-client-name" style="font-size: 0.95rem; font-weight: 600; color: #065f46;">Nome</span>
                </div>
                <button data-action="client-clear" style="border: none; background: #d1fae5; color: #059669; cursor: pointer; font-weight: bold; font-size: 1.2rem; padding: 4px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: center; height: 32px; width: 32px;">&times;</button>
            </div>
            
            <input type="hidden" id="current_client_id" name="client_id">
        </div>
    <?php else: ?>
        <!-- SE ESTIVER EM MESA OU COMANDA, inputs principais já estão em dashboard.php -->
         <input type="hidden" id="current_order_is_paid" value="<?= (int) ($contaAberta['is_paid'] ?? 0) ?>">
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
            
            <!-- 1. Botão SALVAR COMANDA/MESA (Sem pagar) -->
            <?php if (!empty($showSaveCommand)): ?>
            <?php
                // Botão aparece se: mesa selecionada OU comanda existente
                $showSaveBtn = !empty($mesa_id) || !empty($contaAberta['id']);
                ?>
            <button id="btn-save-command" data-action="order-save"
                    style="flex: 1; background: #ea580c; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: <?= \App\Helpers\ViewHelper::e($showSaveBtn ? 'flex' : 'none') ?>; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                Salvar
            </button>
            <?php endif; ?>

            <!-- 2. Botão INCLUIR (Edição de Pedido Pago) -->
            <?php if (!empty($showIncludePaid)): ?>
            <button id="btn-include-paid" data-action="order-include-paid"
                    style="flex: 1; background: #16a34a; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                <i data-lucide="plus-circle" size="20"></i> Incluir
            </button>
            <?php endif; ?>

            <!-- 3. Botão FINALIZAR (Balcão - Venda Rápida) -->
            <?php if (!empty($showQuickSale)): ?>
            <button id="btn-finalizar" class="btn-primary" data-action="order-finalize-quick"
                    style="flex: 1; display: flex; padding: 16px; font-size: 1.1rem; align-items: center; justify-content: center;">
                Finalizar
            </button>
            <?php endif; ?>

            <!-- 4. Botão FECHAR MESA -->
            <?php if (!empty($showCloseTable)): ?>
                <button data-action="order-close-table" data-table-id="<?= (int) $mesa_id ?>"
                        style="flex: 1; background: #2563eb; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                    Finalizar
                </button>
            <?php endif; ?>

            <!-- 5. Botão ENTREGAR/BAIXAR (Comanda) -->
            <?php if (!empty($showCloseCommand)): ?>
                <button data-action="order-close-command" data-order-id="<?= (int) ($contaAberta['id'] ?? 0) ?>"
                        style="flex: 1; background: #2563eb; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                    Finalizar
                </button>
            <?php endif; ?>
                
        </div>
    </div>
</aside>

<?php if (!empty($contaAberta['id'])): ?>
<!-- Modal de Ficha do Cliente -->
<div id="fichaModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div id="fichaContent" style="background: white; border-radius: 16px; width: 95%; max-width: 500px; max-height: 90vh; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 1.5rem; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.4rem; font-weight: 800; margin: 0;">
                    <i data-lucide="receipt" style="width: 24px; height: 24px; vertical-align: middle; margin-right: 8px;"></i>
                    <?php $fichaTitle = $mesa_id ? ('Mesa ' . $mesa_numero) : 'Cliente'; ?>
                    Ficha <?= \App\Helpers\ViewHelper::e($fichaTitle) ?>
                </h2>
                <button data-action="ficha-close" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.5rem; line-height: 1;">&times;</button>
            </div>
            <p style="font-size: 0.9rem; opacity: 0.9; margin-top: 5px;">Consumo atual • <?= \App\Helpers\ViewHelper::e(date('d/m/Y H:i')) ?></p>
        </div>
        
        <!-- Itens -->
        <div style="padding: 1.5rem; max-height: 55vh; overflow-y: auto;">
            <?php if (!empty($itensJaPedidos)): ?>
                <?php foreach ($itensJaPedidos as $item): ?>
                    <div style="padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #111827; font-size: 1rem;">
                                    <?= (int) ($item['quantity'] ?? 0) ?>x <?= \App\Helpers\ViewHelper::e($item['name'] ?? '') ?>
                                </div>
                                <?php
                                    // Mostrar adicionais se existirem
                                    if (!empty($item['extras'])):
                                        $extras = is_string($item['extras']) ? json_decode($item['extras'], true) : $item['extras'];
                                        if (!empty($extras)):
                                            ?>
                                    <div style="margin-top: 4px; padding-left: 12px; border-left: 2px solid #d1d5db;">
                                        <?php foreach ($extras as $extra): ?>
                                            <div style="font-size: 0.85rem; color: #6b7280;">
                                                + <?= \App\Helpers\ViewHelper::e($extra['name'] ?? $extra ?? '') ?> 
                                                <?php if (!empty($extra['price'])): ?>
                                                    <span style="color: #2563eb;">(+R$ <?= number_format((float) $extra['price'], 2, ',', '.') ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; endif; ?>
                                
                                <?php if (!empty($item['observation'])): ?>
                                    <div style="font-size: 0.8rem; color: #9ca3af; font-style: italic; margin-top: 4px;">
                                        📝 <?= \App\Helpers\ViewHelper::e($item['observation'] ?? '') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right; min-width: 80px;">
                                <span style="font-weight: 800; color: #2563eb; font-size: 1.1rem;">
                                    R$ <?= number_format(((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 0)), 2, ',', '.') ?>
                                </span>
                                <?php if (((int) ($item['quantity'] ?? 0)) > 1): ?>
                                    <div style="font-size: 0.75rem; color: #9ca3af;">
                                        (R$ <?= number_format((float) ($item['price'] ?? 0), 2, ',', '.') ?>/un)
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #9ca3af; padding: 2rem;">Nenhum item consumido ainda.</p>
            <?php endif; ?>
        </div>
        
        <!-- Total -->
        <div style="padding: 1.5rem; background: #eff6ff; border-top: 3px solid #2563eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.2rem; font-weight: 800; color: #374151; text-transform: uppercase;">TOTAL</span>
                <span style="font-size: 2rem; font-weight: 900; color: #2563eb;">
                    R$ <?= number_format($contaAberta['total'] ?? 0, 2, ',', '.') ?>
                </span>
            </div>
        </div>
        
        <!-- Botões -->
        <div style="padding: 1rem 1.5rem; background: #f9fafb; display: flex; gap: 10px;">
            <button data-action="ficha-print"
                    style="flex: 1; padding: 14px; background: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i data-lucide="printer" style="width: 20px; height: 20px;"></i> Imprimir
            </button>
            <button data-action="ficha-close"
                    style="flex: 1; padding: 14px; background: #6b7280; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 1rem;">
                Fechar
            </button>
        </div>
    </div>
</div>
<?php endif; ?>
