<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($restaurant['name']) ?> - Cardápio</title>
    
    <!-- CSS Modular - Cardápio Público -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cards.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/modals.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/form.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cart.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/checkout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-badges.css?v=<?= time() ?>">
    
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<main class="main-content">
    <div class="cardapio-container">
        
        <?php require __DIR__ . '/cardapio/partials/header.php'; ?>

        <!-- BUSCA -->
        <div class="cardapio-search-container" style="display: none;">
            <div class="cardapio-search-box">
                <i data-lucide="search" size="16" class="cardapio-search-icon"></i>
                <input 
                    type="text" 
                    id="cardapioSearchInput" 
                    placeholder="O que você procura?" 
                    class="cardapio-search-input"
                >
            </div>
        </div>

        <?php require __DIR__ . '/cardapio/partials/categories.php'; ?>

        <?php require __DIR__ . '/cardapio/partials/products.php'; ?>

    </div>
</main>

<?php require __DIR__ . '/cardapio/partials/modals/product.php'; ?>

<?php require __DIR__ . '/cardapio/partials/modals/combo.php'; ?>

<!-- CARRINHO FLUTUANTE (BOTÃO COMPACTO) -->
<button id="floatingCart" class="cardapio-floating-cart-btn" onclick="openCartModal()">
    <i data-lucide="shopping-cart" size="20"></i>
    <span id="cartTotal">R$ 0,00</span>
    <i data-lucide="arrow-right" size="18"></i>
</button>

<?php require __DIR__ . '/cardapio/partials/modals/cart.php'; ?>

<?php require __DIR__ . '/cardapio/partials/modals/suggestions.php'; ?>

<?php require __DIR__ . '/cardapio/partials/modals/order_review.php'; ?>

<?php require __DIR__ . '/cardapio/partials/modals/payment.php'; ?>

<script>
    // Variables injected by CardapioPublicoController
    window.products = <?= $jsProducts ?>;
    window.combos = <?= $jsCombos ?>;
    window.PRODUCT_RELATIONS = <?= $jsRelations ?>;
    window.BASE_URL = '<?= BASE_URL ?>';
    
    // Configurações do cardápio admin
    window.CARDAPIO_CONFIG = <?= $jsConfig ?>;

    // Configurações completas (usado pelo checkout-order.js)
    window.cardapioConfig = <?= $jsConfigRaw ?>;

    // Diagnóstico
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof openProductModal === 'undefined') {
            console.error('CRITICAL: openProductModal not defined!');
        } else {
            console.log('✅ Sistema inicializado. Produtos:', products.length);
        }
    });
</script>

<!-- Scripts Modulares (Refatoração) -->
<script src="<?= BASE_URL ?>/js/cardapio/utils.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/cart.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/modals.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/modals-product.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/modals-combo.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/checkout-order.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/checkout-fields.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/checkout-modals.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/checkout.js?v=<?= time() ?>"></script>

<!-- Main Script (Listeners) -->
<script src="<?= BASE_URL ?>/js/cardapio.js?v=<?= time() ?>"></script>
<script>
    lucide.createIcons();
</script>
</body>
</html>
