<?php 
require __DIR__ . '/layout/header.php'; 
require __DIR__ . '/layout/sidebar.php'; 
?>

<main class="main-content">
    <section class="catalog-section">

<?php 
// Detecta modo edição de pedido PAGO (Retirada)
$isEditingPaid = isset($_GET['edit_paid']) && $_GET['edit_paid'] == '1';
$editingOrderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

// Se está editando pedido pago, busca o total original do banco
$originalPaidTotalFromDB = 0;
if ($isEditingPaid && $editingOrderId) {
    $conn = \App\Core\Database::connect();
    $stmt = $conn->prepare("SELECT total FROM orders WHERE id = :oid");
    $stmt->execute(['oid' => $editingOrderId]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
    $originalPaidTotalFromDB = floatval($orderData['total'] ?? 0);
}

// [NOVO] Carrega taxa de entrega do cardápio
$deliveryFee = 5.0; // default
$restaurantId = $_SESSION['loja_ativa_id'] ?? null;
if ($restaurantId) {
    $settingsPath = __DIR__ . '/../../../data/restaurants/' . $restaurantId . '/cardapio_settings.json';
    if (file_exists($settingsPath)) {
        $settings = json_decode(file_get_contents($settingsPath), true);
        $deliveryFee = floatval($settings['delivery_fee'] ?? 5.0);
    }
}
?>

        <?php if ($isEditingPaid && $editingOrderId): ?>
            <div id="edit-paid-banner" style="background: #dcfce7; border-bottom: 2px solid #22c55e; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="background: #16a34a; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 800;">PAGO</span>
                    <span style="font-weight: 700; color: #166534;">Pedido #<?= $editingOrderId ?> - Aguardando Retirada</span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?= BASE_URL ?>/admin/loja/mesas" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                        ← Voltar
                    </a>
                    <button onclick="cancelPaidOrder(<?= $editingOrderId ?>)" style="background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                        Cancelar Pedido
                    </button>
                </div>
            </div>
        <?php elseif (isset($isEditing) && $isEditing): ?>
            <div style="background: #fff7ed; border-bottom: 1px solid #fed7aa; padding: 10px; text-align: center; display: flex; justify-content: center; align-items: center; gap: 15px;">
                <span style="font-weight: 700; color: #9a3412;">✏️ Você está editando uma venda antiga.</span>
                <a href="pdv/cancelar-edicao" onclick="return confirm('Descartar alterações e restaurar a venda original?')" 
                   style="background: #ef4444; color: white; padding: 5px 15px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                    Cancelar Edição
                </a>
            </div>
        <?php endif; ?>
        
        <header class="top-header">
            <div class="page-title">
                <?php if ($mesa_numero): ?>
                    <h1 style="color: #b91c1c;">Mesa <?= $mesa_numero ?></h1>
                    <p>Gerenciando Pedido</p>
                <?php elseif (!empty($contaAberta) && !$mesa_id): ?>
                    <h1 style="color: #ea580c;">Comanda #<?= $contaAberta['id'] ?></h1>
                    <p style="color: #9a3412; font-weight: 600;">Cliente: <?= htmlspecialchars($contaAberta['client_name'] ?? 'Cliente') ?></p>
                <?php else: ?>
                    <h1>Balcão de Vendas</h1>
                    <p>Venda Rápida</p>
                <?php endif; ?>
            </div>
            
            <div class="search-bar">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="product-search-input" placeholder="Buscar produtos (F2)..." class="search-input" />
            </div>

            <div class="status-badge">
                <div class="status-dot"></div> Online
            </div>
        </header>

        <div class="products-container">
            <?php if (empty($categories)): ?>
                <div style="padding: 2rem; text-align: center; color: #9ca3af;">
                    <i data-lucide="package-open" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>Nenhum produto cadastrado.</p>
                </div>
            <?php else: ?>
            
                <!-- Chips de Categoria (Filtro Rápido) -->
                <div class="pdv-category-chips-container">
                    <div class="pdv-category-chips">
                        <button class="pdv-category-chip active" data-category="">
                            📂 Todos
                        </button>
                        <?php foreach ($categories as $cat): ?>
                            <?php if (!empty($cat['products'])): ?>
                                <button class="pdv-category-chip" data-category="<?= htmlspecialchars($cat['name']) ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Grid Unificado de Produtos -->
                <div class="products-grid" id="products-grid">
                    <?php foreach ($categories as $category): ?>
                        <?php if (!empty($category['products'])): ?>
                            <?php foreach ($category['products'] as $product): ?>
                                
                                <div class="product-card product-card-compact" 
                                     data-category="<?= htmlspecialchars($category['name']) ?>"
                                     onclick='PDV.clickProduct(<?= $product['id'] ?>, <?= json_encode($product['name']) ?>, <?= $product['price'] ?>, <?= $product['has_extras'] ? "true" : "false" ?>)'>
                                    
                                    <div class="product-info">
                                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                                    </div>
                                    <div class="product-price">
                                        R$ <?= number_format($product['price'], 2, ',', '.') ?>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </section>

    <input type="hidden" id="current_table_id" value="<?= $mesa_id ?? '' ?>">
    <input type="hidden" id="current_table_number" value="<?= $mesa_numero ?? '' ?>">

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
                <!-- Botão SALVAR COMANDA: Exibe se for Comanda NÃO paga -->
                <?php $showSalvar = (!empty($contaAberta) && !$mesa_id && !$isEditingPaid); ?>
                <button id="btn-save-command" onclick="saveClientOrder()" 
                        style="flex: 1; background: #ea580c; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: <?= $showSalvar ? 'flex' : 'none' ?>; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                    Salvar
                </button>

                <!-- Botão INCLUIR: Só para pedido PAGO em edição (cobra antes de incluir) -->
                <?php if ($isEditingPaid ?? false): ?>
                <button id="btn-include-paid" onclick="includePaidOrderItems()" 
                        style="flex: 1; background: #16a34a; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                    <i data-lucide="plus-circle" size="20"></i> Incluir
                </button>
                <?php endif; ?>

                <!-- Botão FINALIZAR (Venda Rápida): Só exibe se NÃO for Comanda aberta -->
                <button id="btn-finalizar" class="btn-primary" disabled onclick="finalizeSale()" 
                        style="flex: 1; display: <?= (!empty($contaAberta) && !$mesa_id) ? 'none' : 'flex' ?>; padding: 16px; font-size: 1.1rem; align-items: center; justify-content: center;">
                    Finalizar
                </button>

                <?php if (!empty($contaAberta)): ?>
                    <?php if ($mesa_id): ?>
                        <button onclick="fecharContaMesa(<?= $mesa_id ?>)" 
                                style="flex: 1; background: #2563eb; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 16px; font-size: 1.1rem;">
                            Finalizar
                        </button>
                    <?php else: ?>
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
                    <input type="hidden" id="table-initial-total" value="<?= $contaAberta['total'] ?>">
                <?php else: ?>
                    <input type="hidden" id="table-initial-total" value="0">
                <?php endif; ?>
            </div>
        </div>
    </aside>

</main>

    <!-- SUCCESS MODAL -->
    <div id="successModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 500; align-items: center; justify-content: center; pointer-events: none;">
        <div style="background: white; padding: 30px 50px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column; align-items: center; gap: 15px; animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="width: 80px; height: 80px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="check" style="width: 40px; height: 40px; color: #16a34a; stroke-width: 3;"></i>
            </div>
            <h2 style="margin: 0; color: #166534; font-size: 1.5rem; font-weight: 800;">Sucesso!</h2>
            <p style="margin: 0; color: #475569; font-weight: 500;">Operação realizada.</p>
        </div>
    </div>
    <style>
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>

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

<!-- MODAL NOVO CLIENTE -->
<div id="clientModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 350px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; color: #1e293b;">Novo Cliente</h3>
        
        <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Nome</label>
            <input type="text" id="new_client_name" autocomplete="off"
                   oninput="PDVTables.searchClientInModal(this.value)"
                   style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <div id="modal-client-results" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 6px; max-height: 150px; overflow-y: auto; z-index: 10; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Telefone (Opcional)</label>
            <input type="text" id="new_client_phone" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="document.body.removeChild(document.getElementById('clientModal'));" style="flex: 1; padding: 10px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; color: #475569; font-weight: 600;">Cancelar</button>
            <button id="btn-save-new-client" onclick="saveClient()" style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Salvar</button>
        </div>
    </div>
</div>


    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        // Injeta o carrinho recuperado do PHP para o JS
        const recoveredCart = <?= json_encode($cartRecovery ?? []) ?>;
        
        // Modo edição de pedido PAGO (para cobrar só a diferença)
        const isEditingPaidOrder = <?= ($isEditingPaid ?? false) ? 'true' : 'false' ?>;
        const originalPaidTotal = <?= $originalPaidTotalFromDB ?? 0 ?>;
        const editingPaidOrderId = <?= $editingOrderId ?? 'null' ?>;
        
        // [NOVO] Taxa de entrega configurada
        const PDV_DELIVERY_FEE = <?= $deliveryFee ?>;
    </script>

    <!-- MODAL DE ADICIONAIS -->
    <div id="extrasModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; width: 500px; max-width: 95%; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fff;">
                <h3 id="extras-modal-title" style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700;">Opções</h3>
                <button onclick="closeExtrasModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            <div id="extras-modal-content" style="padding: 20px; overflow-y: auto; flex: 1;">
                <!-- Groups will be injected here -->
                <div style="text-align: center; color: #64748b;">Carregando opções...</div>
            </div>
            <div style="padding: 15px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                <!-- Seletor de Quantidade -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 600; color: #475569; font-size: 0.9rem;">Qtd:</span>
                    <div style="display: flex; align-items: center; gap: 5px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px;">
                        <button type="button" onclick="decreaseExtrasQty()" 
                                style="width: 32px; height: 32px; border: none; background: #fee2e2; color: #991b1b; border-radius: 6px; font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">−</button>
                        <span id="extras-qty-display" style="min-width: 35px; text-align: center; font-size: 1.1rem; font-weight: 700; color: #1e293b;">1</span>
                        <button type="button" onclick="increaseExtrasQty()" 
                                style="width: 32px; height: 32px; border: none; background: #dcfce7; color: #166534; border-radius: 6px; font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
                    </div>
                </div>
                
                <!-- Botões -->
                <div style="display: flex; gap: 10px;">
                    <button onclick="closeExtrasModal()" style="padding: 10px 16px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; cursor: pointer; color: #475569;">Cancelar</button>
                    <button id="btn-add-extras" onclick="confirmExtras()" style="padding: 10px 20px; background: #16a34a; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                        Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts do PDV -->
    <script src="<?= BASE_URL ?>/js/pdv/state.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/cart.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/tables.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
