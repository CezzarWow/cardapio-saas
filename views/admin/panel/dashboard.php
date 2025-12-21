<?php 
require __DIR__ . '/layout/header.php'; 
require __DIR__ . '/layout/sidebar.php'; 
?>

<main class="main-content">
    <section class="catalog-section">

        <?php if (isset($isEditing) && $isEditing): ?>
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
                                        <img src="<?= BASE_URL ?>/uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
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
                    <span>Já na Mesa</span>
                    <span>Total: R$ <?= number_format($contaAberta['total'], 2, ',', '.') ?></span>
                </h3>
                <div style="max-height: 150px; overflow-y: auto;">
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

        <div class="cart-footer">
            
        <?php if (!$mesa_id): ?>
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #e5e7eb;">
                <label style="font-size: 0.8rem; font-weight: 700; color: #374151; margin-bottom: 5px; display: block;">Identificar Mesa / Cliente</label>
                
                    <div id="client-search-area" style="display: flex; gap: 8px; align-items: flex-start;">
                    <!-- Wrapper relativo apenas para o Input e Resultados -->
                    <div style="position: relative; flex: 1;">
                        <input type="text" id="client-search" placeholder="Clique para ver mesas ou digite..." autocomplete="off"
                               style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;">
                        
                        <!-- Dropdown Redesenhado (Visual mais limpo e integrado) -->
                        <div id="client-results" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); max-height: 250px; overflow-y: auto; z-index: 1000; margin-top: 6px;">
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
            <!-- SE ESTIVER EM MESA, NÃO MOSTRA BUSCA, MAS PRECISA DOS INPUTS PRA JS NÃO QUEBRAR -->
             <input type="hidden" id="current_client_id" name="client_id">
             <input type="hidden" id="client-search"> 
             <!-- Hack simples p/ evitar erro JS "element not found" sem refatorar tudo -->
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
                <button id="btn-finalizar" class="btn-primary" disabled onclick="finalizeSale()" style="flex: 1;">
                    Finalizar
                </button>

                <?php if (!empty($contaAberta)): ?>
                    <button onclick="fecharContaMesa(<?= $mesa_id ?>)" 
                            style="flex: 1; background: #ef4444; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer;">
                        Fechar
                    </button>
                    <!-- Hidden input para o JS ler o valor inicial da mesa -->
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
    
    <div style="background: white; width: 600px; max-width: 95%; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); display: flex; flex-direction: column;">
        
        <div style="background: #f8fafc; padding: 20px; border-bottom: 1px solid #e2e8f0; text-align: center; display: flex; justify-content: space-between; align-items: center;">
            <div style="text-align: left;">
                <h2 style="margin: 0; color: #1e293b; font-size: 1.4rem; font-weight: 800;">Pagamento</h2>
                <p style="margin: 0; color: #64748b; font-size: 0.85rem;">Selecione a forma de pagamento</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.8rem; font-weight: 700; color: #64748b;">TOTAL A PAGAR</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: #2563eb;">
                    R$ <span id="checkout-total-display">0,00</span>
                </div>
            </div>
        </div>

        <div style="padding: 25px;">
            
            <div id="payment-list" style="margin-bottom: 20px; max-height: 120px; overflow-y: auto; display: none;">
                </div>

            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 10px;">ESCOLHA O MÉTODO:</label>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                <button onclick="setMethod('dinheiro')" id="btn-dinheiro" class="pay-btn" style="padding: 15px 5px; border: 2px solid #e2e8f0; background: white; border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px; transition: all 0.2s;">
                    <span style="font-size: 1.5rem;">💵</span>
                    <span style="font-weight: 700; font-size: 0.85rem; color: #475569;">Dinheiro</span>
                </button>
                <button onclick="setMethod('pix')" id="btn-pix" class="pay-btn" style="padding: 15px 5px; border: 2px solid #e2e8f0; background: white; border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px; transition: all 0.2s;">
                    <span style="font-size: 1.5rem;">💠</span>
                    <span style="font-weight: 700; font-size: 0.85rem; color: #475569;">Pix</span>
                </button>
                <button onclick="setMethod('credito')" id="btn-credito" class="pay-btn" style="padding: 15px 5px; border: 2px solid #e2e8f0; background: white; border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px; transition: all 0.2s;">
                    <span style="font-size: 1.5rem;">💳</span>
                    <span style="font-weight: 700; font-size: 0.85rem; color: #475569;">Crédito</span>
                </button>
                <button onclick="setMethod('debito')" id="btn-debito" class="pay-btn" style="padding: 15px 5px; border: 2px solid #e2e8f0; background: white; border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px; transition: all 0.2s;">
                    <span style="font-size: 1.5rem;">💳</span>
                    <span style="font-weight: 700; font-size: 0.85rem; color: #475569;">Débito</span>
                </button>
            </div>

            <div style="background: #f1f5f9; padding: 15px; border-radius: 12px; display: flex; gap: 15px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 5px;">VALOR A PAGAR (R$)</label>
                    <input type="number" id="pay-amount" step="0.01" 
                           style="width: 100%; padding: 12px; border: 2px solid #cbd5e1; border-radius: 8px; font-weight: 800; font-size: 1.5rem; color: #1e293b; outline: none;">
                </div>
                <button onclick="addPayment()" style="padding: 0 25px; height: 55px; background: #0f172a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 1rem;">
                    LANÇAR <i data-lucide="arrow-down"></i>
                </button>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div id="remaining-box">
                    <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">FALTAM:</span>
                    <span id="checkout-remaining" style="font-size: 1.5rem; font-weight: 800; color: #dc2626; margin-left: 5px;">R$ 0,00</span>
                </div>

                <div id="change-box" style="display: none; background: #dcfce7; padding: 10px 20px; border-radius: 8px; border: 1px solid #86efac;">
                    <span style="font-size: 0.9rem; font-weight: 700; color: #166534;">TROCO:</span>
                    <span id="checkout-change" style="font-size: 1.5rem; font-weight: 800; color: #166534; margin-left: 5px;">R$ 0,00</span>
                </div>
            </div>

        </div>

        <div style="padding: 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; gap: 15px;">
            <button onclick="closeCheckout()" style="flex: 1; padding: 15px; background: white; border: 1px solid #cbd5e1; color: #475569; border-radius: 10px; font-weight: 700; cursor: pointer;">Cancelar</button>
            
            <button id="btn-finish-sale" onclick="submitSale()" disabled 
                    style="flex: 2; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 10px; font-weight: 800; cursor: not-allowed; font-size: 1.1rem; display: flex; justify-content: center; align-items: center; gap: 10px;">
                CONCLUIR VENDA <i data-lucide="check-circle"></i>
            </button>
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
    </script>
    <script src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
