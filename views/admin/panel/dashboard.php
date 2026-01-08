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

<?php require __DIR__ . '/partials/success-modal.php'; ?>

<?php require __DIR__ . '/partials/checkout-modal.php'; ?>

<?php require __DIR__ . '/partials/client-modal.php'; ?>


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

<?php require __DIR__ . '/partials/extras-modal.php'; ?>

    <!-- Scripts do PDV -->
    <script src="<?= BASE_URL ?>/js/pdv/state.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/cart.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/cart-core.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/cart-ui.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/cart-extras-modal.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/tables.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/tables-mesa.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/tables-cliente.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/tables-client-modal.js?v=<?= time() ?>"></script>
    
    <!-- Módulos de Checkout (ordem de dependência obrigatória) -->
    <script src="<?= BASE_URL ?>/js/pdv/checkout/helpers.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/state.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/totals.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/ui.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/payments.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/submit.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/orderType.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/retirada.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/entrega.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/pickup.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout/index.js?v=<?= time() ?>"></script>
    
    <script src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
