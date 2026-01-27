<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?= \App\Helpers\ViewHelper::e(\App\Helpers\ViewHelper::csrfToken()) ?>">
    <title><?= \App\Helpers\ViewHelper::e($restaurant['name'] ?? '') ?> - Cardápio Digital</title>
    
    <!-- CSS Modular - Cardápio Público -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cards.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/modals/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/form.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/publico/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/checkout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-badges.css">
    
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<main class="main-content">
    <div class="cardapio-container">
        
        <?php \App\Core\View::renderFromScope('cardapio/partials/header.php', get_defined_vars()); ?>

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

        <?php \App\Core\View::renderFromScope('cardapio/partials/categories.php', get_defined_vars()); ?>

        <?php \App\Core\View::renderFromScope('cardapio/partials/products.php', get_defined_vars()); ?>

    </div>
</main>

<?php \App\Core\View::renderFromScope('cardapio/partials/modals/product.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('cardapio/partials/modals/combo.php', get_defined_vars()); ?>

<!-- CARRINHO FLUTUANTE (BOTÃO COMPACTO) -->
<button id="floatingCart" class="cardapio-floating-cart-btn" onclick="openCartModal()">
    <i data-lucide="shopping-cart" size="20"></i>
    <span id="cartTotal">R$ 0,00</span>
    <i data-lucide="arrow-right" size="18"></i>
</button>

<?php \App\Core\View::renderFromScope('cardapio/partials/modals/cart.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('cardapio/partials/modals/suggestions.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('cardapio/partials/modals/order_review.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('cardapio/partials/modals/payment.php', get_defined_vars()); ?>

<script>
    // Variables injected by CardapioPublicoController
    window.products = <?= $jsProducts ?>;
    window.combos = <?= $jsCombos ?>;
    window.PRODUCT_RELATIONS = <?= $jsRelations ?>;
    window.BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>;
    
    // Configurações do cardápio admin
    window.CARDAPIO_CONFIG = <?= $jsConfig ?>;

    // Configurações completas (usado pelo checkout-order.js)
    window.cardapioConfig = <?= $jsConfigRaw ?>;

    // Diagnóstico silencioso
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof openProductModal === 'undefined') {
            console.error('CRITICAL: openProductModal not defined!');
        }
    });
</script>

<!-- Bundled & minified assets -->
<link rel="stylesheet" href="<?= BASE_URL ?><?= \App\Helpers\ViewHelper::asset('cardapio.css') ?>">
<script src="<?= BASE_URL ?><?= \App\Helpers\ViewHelper::asset('cardapio.js') ?>"></script>
<script>
    lucide.createIcons();
</script>
</body>
</html>
