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
            <button class="btn-icon"><i data-lucide="trash-2"></i></button>
        </div>
        
        <div id="cart-empty-state" class="cart-empty">
            <i data-lucide="shopping-cart" size="48" color="#e5e7eb" style="margin-bottom: 1rem;"></i>
            <p>Carrinho Vazio</p>
        </div>

        <div id="cart-items-area" style="flex: 1; overflow-y: auto; padding: 1rem; display: none;">
        </div>

        <?php if (!empty($itensJaPedidos)): ?>
            <div style="padding: 1rem; background: #fff7ed; border-bottom: 1px solid #fed7aa;">
                <h3 style="font-size: 0.85rem; font-weight: 700; color: #9a3412; margin-bottom: 0.5rem; display:flex; justify-content:space-between;">
                    <span>Já na Mesa</span>
                    <span>Total: R$ <?= number_format($contaAberta['total'], 2, ',', '.') ?></span>
                </h3>
                <div style="max-height: 150px; overflow-y: auto;">
                    <?php foreach ($itensJaPedidos as $itemAntigo): ?>
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #9a3412; margin-bottom: 4px;">
                            <span><?= $itemAntigo['quantity'] ?>x <?= $itemAntigo['name'] ?></span>
                            <span>R$ <?= number_format($itemAntigo['price'], 2, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="cart-footer">
            
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

    <script>
        // Injeta o carrinho recuperado do PHP para o JS
        const recoveredCart = <?= json_encode($cartRecovery ?? []) ?>;
    </script>
    <script src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
