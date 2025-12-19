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
            <div class="products-grid">
                
                <div class="product-card">
                    <div class="product-icon icon-orange">X</div>
                    <div class="product-info">
                        <h3>X-Salada Especial</h3>
                        <span>Lanches</span>
                    </div>
                    <div class="product-price">R$ 25,00</div>
                </div>

                <div class="product-card">
                    <div class="product-icon icon-red">C</div>
                    <div class="product-info">
                        <h3>Coca-Cola Lata</h3>
                        <span>Bebidas</span>
                    </div>
                    <div class="product-price">R$ 6,00</div>
                </div>

            </div>
        </div>
    </section>

    <aside class="cart-sidebar">
        <div class="cart-header">
            <h2 class="cart-title">
                <i data-lucide="shopping-cart" color="#2563eb"></i> Cesta
            </h2>
            <button class="btn-icon"><i data-lucide="trash-2"></i></button>
        </div>
        
        <div class="cart-empty">
            <i data-lucide="shopping-cart" size="48" color="#e5e7eb" style="margin-bottom: 1rem;"></i>
            <p>Carrinho Vazio</p>
        </div>

        <div class="cart-footer">
            <div class="total-row">
                <span class="total-label">Total</span>
                <span class="total-value">R$ 0,00</span>
            </div>
            <button class="btn-primary" disabled>
                Finalizar Venda
            </button>
        </div>
    </aside>

</main>

<?php require __DIR__ . '/layout/footer.php'; ?>
