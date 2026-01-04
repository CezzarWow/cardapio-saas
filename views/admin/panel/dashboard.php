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
                <input type="text" placeholder="Buscar produtos (F2)..." class="search-input" />
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
            
                <?php foreach ($categories as $category): ?>
                    <?php if (!empty($category['products'])): ?>
                        
                        <h3 style="font-weight: 800; color: #111827; font-size: 1.25rem; margin: 2rem 0 1rem 0; padding-left: 5px; border-left: 4px solid #f59e0b;">
                            <?= htmlspecialchars($category['name']) ?>
                        </h3>

                        <div class="products-grid">
                            <?php foreach ($category['products'] as $product): ?>
                                
                                <div class="product-card" 
                                     onclick="addToCart(<?= $product['id'] ?>, '<?= addslashes($product['name']) ?>', <?= $product['price'] ?>)">
                                    
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" loading="lazy">
                                    <?php else: ?>
                                        <div class="product-icon icon-orange">
                                            <?= strtoupper(substr($product['name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="product-info">
                                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                                    </div>
                                    <div class="product-price">
                                        R$ <?= number_format($product['price'], 2, ',', '.') ?>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </section>

    <input type="hidden" id="current_table_id" value="<?= $mesa_id ?? '' ?>">
    <input type="hidden" id="current_table_number" value="<?= $mesa_numero ?? '' ?>">

    <aside class="cart-sidebar">
        <div class="cart-header">
            <h2 class="cart-title">
                <i data-lucide="shopping-cart" color="#2563eb"></i> Carrinho
            </h2>
            <button class="btn-icon" onclick="clearCart()" title="Limpar Carrinho"><i data-lucide="trash-2"></i></button>
        </div>
        
        <div id="cart-empty-state" class="cart-empty">
            <i data-lucide="shopping-cart" size="48" color="#e5e7eb" style="margin-bottom: 1rem;"></i>
            <p>Carrinho Vazio</p>
        </div>

        <div id="cart-items-area" style="flex: 1; overflow-y: auto; padding: 1rem; display: none;">
        </div>

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

        <div class="cart-footer" style="box-shadow: 0 -4px 12px rgba(0,0,0,0.05); padding-top: 20px; padding-bottom: 30px;">
            
        <?php if (!$mesa_id && empty($contaAberta)): ?>
            <div style="margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px dashed #e5e7eb;">
                <label style="font-size: 1.1rem; font-weight: 800; color: #1f2937; margin-bottom: 10px; display: block;">Identificar Mesa / Cliente</label>
                
                    <div id="client-search-area" style="display: flex; gap: 12px; align-items: flex-start;">
                    <!-- Wrapper relativo apenas para o Input e Resultados -->
                    <div style="position: relative; flex: 1;">
                        <input type="text" id="client-search" placeholder="Clique para ver mesas ou digite..." autocomplete="off"
                               style="width: 100%; padding: 15px 12px; border: 1px solid #94a3b8; border-radius: 10px; font-size: 1.1rem; outline: none; transition: all 0.2s; background: #f8fafc;">
                        
                        <!-- Dropdown Redesenhado -->
                        <div id="client-results" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); max-height: 200px; overflow-y: auto; z-index: 9999; margin-top: 6px;">
                        </div>
                    </div>
                    
                    <button type="button" onclick="const m=document.getElementById('clientModal'); if(m) { m.style.display='flex'; document.getElementById('new_client_name').focus(); }" title="Novo Cliente"
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

            <!-- Adicionar (Carrinho) -->
            <div class="total-row" style="margin-bottom: 1.5rem;">
                <span class="total-label" style="font-size: 1rem; color: #111827; font-weight: 700;">Adicionar</span>
                <span id="cart-total" class="total-value" style="font-size: 1.1rem; color: #16a34a;">R$ 0,00</span>
            </div>

            <!-- Botões de Ação -->
            <div style="display: flex; gap: 10px;">
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
                        style="flex: 1; display: <?= (!empty($contaAberta) && !$mesa_id) ? 'none' : 'block' ?>; padding: 16px; font-size: 1.1rem;">
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
    <!-- ... (Rest of Checkout Modal) ... -->
    
    <div style="background: white; width: 620px; max-width: 95%; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); display: flex; flex-direction: column; max-height: 90vh;">
        
        <!-- Header minimalista / Apenas título ou nada? O usuário pediu pra tirar tudo -->
        <div style="padding: 20px 25px 0 25px;">
            <h2 style="margin: 0; color: #1e293b; font-size: 1.4rem; font-weight: 800;">Pagamento</h2>
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

        <div style="padding: 25px;">
            
            <!-- NOVO LAYOUT DO BODY DO MODAL -->
            <div style="flex: 1; min-height: 0; padding: 0 25px 20px; overflow-y: auto;">
                
                <!-- 1. Lista de Pagamentos (Topo e maior) -->
                <div id="payment-list" style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; padding: 15px; overflow-y: auto; background: #f8fafc; min-height: 80px; display: none;"></div>

                <!-- 2. Seleção de Método -->
                <!-- REMOVIDO TEXTO "FORMA DE PAGAMENTO" -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                    <button onclick="setMethod('dinheiro')" id="btn-method-dinheiro" class="payment-method-btn active" style="padding: 12px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: all 0.2s;">
                        <i data-lucide="banknote" size="24"></i>
                        <span style="font-size: 0.85rem; font-weight: 700;">Dinheiro</span>
                    </button>
                    <button onclick="setMethod('pix')" id="btn-method-pix" class="payment-method-btn" style="padding: 12px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: all 0.2s;">
                        <i data-lucide="qr-code" size="24"></i>
                        <span style="font-size: 0.85rem; font-weight: 700;">Pix</span>
                    </button>
                    <button onclick="setMethod('credito')" id="btn-method-credito" class="payment-method-btn" style="padding: 12px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: all 0.2s;">
                        <i data-lucide="credit-card" size="24"></i>
                        <span style="font-size: 0.85rem; font-weight: 700;">Crédito</span>
                    </button>
                    <button onclick="setMethod('debito')" id="btn-method-debito" class="payment-method-btn" style="padding: 12px; border: 2px solid #cbd5e1; border-radius: 10px; background: white; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: all 0.2s;">
                        <i data-lucide="credit-card" size="24"></i>
                        <span style="font-size: 0.85rem; font-weight: 700;">Débito</span>
                    </button>
                </div>

                <!-- 3. Valor em Destaque -->
                <!-- REMOVIDO TEXTO "VALOR RECEBIDO" -->
                <div style="background: #f1f5f9; padding: 15px; border-radius: 12px; display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px;">
                    <div style="flex: 1; position: relative;">
                        <span style="position: absolute; left: 12px; top: 16px; color: #64748b; font-weight: bold; font-size: 1.1rem;">R$</span>
                        <input type="text" id="pay-amount" placeholder="0,00" 
                               style="width: 100%; padding: 12px 12px 12px 40px; border: 2px solid #94a3b8; border-radius: 10px; font-weight: 800; font-size: 1.5rem; color: #1e293b; outline: none;">
                    </div>
                    <button onclick="addPayment()" style="width: 60px; height: 55px; background: #0f172a; color: white; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                        <i data-lucide="arrow-down" size="28"></i>
                    </button>
                </div>

                <!-- 4. Tipo de Pedido (Cards) -->
                <div style="background: white; margin-bottom: 5px;">
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
                        <div class="order-type-card disabled" style="border: 1px solid #e2e8f0; background: #f1f5f9; border-radius: 8px; padding: 10px 5px; text-align: center; opacity: 0.5; cursor: not-allowed;">
                            <i data-lucide="truck" size="18" style="color: #94a3b8; margin-bottom: 4px;"></i>
                            <div style="font-weight: 700; font-size: 0.85rem; color: #94a3b8;">Entrega</div>
                        </div>
                    </div>
                    
                    <!-- AVISO: Cliente obrigatório para Retirada -->
                    <div id="retirada-client-alert" style="display: none; margin-top: 12px; padding: 12px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <i data-lucide="alert-triangle" size="18" style="color: #d97706;"></i>
                            <span style="font-weight: 700; color: #92400e; font-size: 0.9rem;">Cliente obrigatório para Retirada</span>
                        </div>
                        
                        <!-- Campo de busca de cliente -->
                        <div style="position: relative; margin-bottom: 10px;">
                            <input type="text" id="retirada-client-search" placeholder="Buscar cliente por nome ou telefone..."
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d97706; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;"
                                   oninput="searchClientForRetirada(this.value)">
                            <div id="retirada-client-results" style="display: none; position: absolute; left: 0; right: 0; top: 100%; background: white; border: 1px solid #e5e7eb; border-radius: 6px; max-height: 150px; overflow-y: auto; z-index: 100; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                        </div>
                        
                        <!-- Botões -->
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="document.getElementById('clientModal').style.display='flex'" 
                                    style="flex: 1; padding: 10px; background: white; color: #d97706; border: 1px solid #d97706; border-radius: 6px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i data-lucide="user-plus" size="16"></i> Cadastrar Novo
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        <!-- FOOTER: TOTAL, RESTANTE E BOTÕES -->
        <div style="padding: 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            
            <!-- LINHA DE TOTAIS -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 0 5px;">
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b;">
                        TOTAL A PAGAR: <span id="checkout-total-display" style="color: #2563eb;">R$ 0,00</span>
                    </div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b;">
                        FALTAM: <span id="checkout-remaining" style="color: #dc2626;">R$ 0,00</span>
                    </div>
                </div>
                
                <div id="change-box" style="display: none; text-align: right; background: #dcfce7; padding: 10px 20px; border-radius: 8px; border: 1px solid #86efac; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <span style="font-size: 0.85rem; font-weight: 800; color: #166534; display: block; letter-spacing: 0.5px;">TROCO</span>
                    <span id="checkout-change" style="font-size: 1.6rem; font-weight: 900; color: #166534;">R$ 0,00</span>
                </div>
            </div>

            <!-- BOTÕES -->
            <div style="display: flex; gap: 15px;">
                <button onclick="closeCheckout()" style="flex: 1; padding: 15px; background: white; border: 1px solid #cbd5e1; color: #475569; border-radius: 10px; font-weight: 700; cursor: pointer;">Cancelar</button>
                
                <button id="btn-finish-sale" onclick="submitSale()" disabled 
                        style="flex: 2; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 10px; font-weight: 800; cursor: not-allowed; font-size: 1.1rem; display: flex; justify-content: center; align-items: center; gap: 10px;">
                    CONCLUIR VENDA <i data-lucide="check-circle"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NOVO CLIENTE -->
<div id="clientModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 200;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 350px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; color: #1e293b;">Novo Cliente</h3>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Nome</label>
            <input type="text" id="new_client_name" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Telefone (Opcional)</label>
            <input type="text" id="new_client_phone" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="document.getElementById('clientModal').style.display='none'" style="flex: 1; padding: 10px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; color: #475569; font-weight: 600;">Cancelar</button>
            <button onclick="saveClient()" style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Salvar</button>
        </div>
    </div>
</div>

<!-- MODAL SUCESSO -->
<div id="successModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); justify-content: center; align-items: center; z-index: 300;">
    <div style="background: white; padding: 30px; border-radius: 16px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: scale(1.1);">
        <div style="width: 60px; height: 60px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
            <i data-lucide="check-circle-2" style="width: 32px; color: #16a34a;"></i>
        </div>
        <h2 style="margin: 0; color: #166534; font-size: 1.5rem;">Sucesso!</h2>
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
    </script>
    <script src="<?= BASE_URL ?>/js/pdv/state.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/cart.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/tables.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv/checkout.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
