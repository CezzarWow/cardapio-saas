<?php 
require __DIR__ . '/layout/header.php'; 
require __DIR__ . '/layout/sidebar.php'; 
?>

<main class="main-content">
    <section class="catalog-section">
        
        <header class="top-header">
            <div class="page-title">
                <h1>Balcão de Vendas</h1>
                <p>Loja: <?= $_SESSION['loja_ativa_nome'] ?></p>
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
                        
                        <h3 style="font-weight: 700; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; margin: 1.5rem 0 0.5rem 0.25rem;">
                            <?= htmlspecialchars($category['name']) ?>
                        </h3>

                        <div class="products-grid">
                            <?php foreach ($category['products'] as $product): ?>
                                
                                <div class="product-card" 
                                     onclick="addToCart(<?= $product['id'] ?>, '<?= addslashes($product['name']) ?>', <?= $product['price'] ?>)">
                                    
                                    <div class="product-icon icon-orange">
                                        <?= strtoupper(substr($product['name'], 0, 1)) ?>
                                    </div>
                                    <div class="product-info">
                                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                                        <span><?= htmlspecialchars($category['name']) ?></span>
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

    <aside class="cart-sidebar">
        <div class="cart-header">
            <h2 class="cart-title">
                <i data-lucide="shopping-cart" color="#2563eb"></i> Cesta
            </h2>
            <button class="btn-icon"><i data-lucide="trash-2"></i></button>
        </div>
        
        <div id="cart-empty-state" class="cart-empty">
            <i data-lucide="shopping-cart" size="48" color="#e5e7eb" style="margin-bottom: 1rem;"></i>
            <p>Carrinho Vazio</p>
        </div>

        <div id="cart-items-area" style="flex: 1; overflow-y: auto; padding: 1rem; display: none;">
            </div>

        <div class="cart-footer">
            <div class="total-row">
                <span class="total-label">Total</span>
                <span id="cart-total" class="total-value">R$ 0,00</span>
            </div>
            <button id="btn-finalizar" class="btn-primary" disabled onclick="finalizeSale()">
                Finalizar Venda
            </button>
        </div>
    </aside>

</main>

<script src="../../js/pdv.js"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
