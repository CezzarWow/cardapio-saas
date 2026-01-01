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
    
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* ========== ANTI-ZOOM MOBILE ========== */
        * {
            touch-action: manipulation;
        }
        
        /* ========== LAYOUT FLEX MOBILE ========== */
        html, body { 
            height: 100%;
            overflow: hidden; /* Scroll NÃO fica no body */
            background: transparent !important;
        }
        
        /* Container principal - 100vh flex column */
        .main-content {
            margin-left: 0 !important; 
            width: 100% !important;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: transparent;
        }
        
        /* Container do cardápio - cresce */
        .cardapio-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            background: #f3f4f6;
            /* Pega a área do safe-area */
            padding-bottom: env(safe-area-inset-bottom);
        }
        
        /* Header - tamanho fixo, não encolhe */
        .cardapio-header { flex-shrink: 0; }
        
        /* Busca - tamanho fixo */
        .cardapio-search-container { flex-shrink: 0; }
        
        /* Categorias - tamanho fixo */
        .cardapio-categories { flex-shrink: 0; }
        
        /* Lista de produtos - ELEMENTO QUE SCROLLA */
        .cardapio-products {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            background: transparent;
        }
        
        .sidebar { display: none !important; }

        /* Opções desabilitadas */
        .disabled-option {
            opacity: 0.5;
            pointer-events: none;
            filter: grayscale(100%);
            background-color: #f1f5f9;
            cursor: not-allowed;
            border-color: #cbd5e1;
        }
    </style>
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
    <?php
    // Achata array de produtos para JS
    $allProducts = [];
    if (!empty($productsByCategory)) {
        foreach ($productsByCategory as $cat => $prods) {
            foreach ($prods as $p) {
                // Garante que additionals seja array
                if (empty($p['additionals'])) $p['additionals'] = [];
                $allProducts[] = $p;
            }
        }
    }
    $jsProducts = json_encode($allProducts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    if ($jsProducts === false) $jsProducts = '[]'; // Fallback
    ?>
    
    // Injeção Segura
    const products = <?= $jsProducts ?>;
    const combos = <?= json_encode($combos ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const PRODUCT_RELATIONS = <?= json_encode($productRelations ?? []) ?>;
    window.BASE_URL = '<?= BASE_URL ?>';
    
    // [ETAPA 1.1] Configurações do cardápio admin
    window.CARDAPIO_CONFIG = {
        isOpen: <?= ($cardapioConfig['is_open'] ?? 1) ? 'true' : 'false' ?>,
        deliveryEnabled: <?= ($cardapioConfig['delivery_enabled'] ?? 1) ? 'true' : 'false' ?>,
        pickupEnabled: <?= ($cardapioConfig['pickup_enabled'] ?? 1) ? 'true' : 'false' ?>,
        dineInEnabled: <?= ($cardapioConfig['dine_in_enabled'] ?? 1) ? 'true' : 'false' ?>,
        deliveryFee: <?= floatval($cardapioConfig['delivery_fee'] ?? 5) ?>,
        minOrderValue: <?= floatval($cardapioConfig['min_order_value'] ?? 20) ?>,
        acceptCash: <?= ($cardapioConfig['accept_cash'] ?? 1) ? 'true' : 'false' ?>,
        acceptCredit: <?= ($cardapioConfig['accept_credit'] ?? 1) ? 'true' : 'false' ?>,
        acceptDebit: <?= ($cardapioConfig['accept_debit'] ?? 1) ? 'true' : 'false' ?>,
        acceptPix: <?= ($cardapioConfig['accept_pix'] ?? 1) ? 'true' : 'false' ?>,
        whatsappNumber: '<?= htmlspecialchars($cardapioConfig['whatsapp_number'] ?? '') ?>',
        closedMessage: '<?= htmlspecialchars($cardapioConfig['closed_message'] ?? 'Estamos fechados no momento') ?>'
    };

    // Diagnóstico
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof openProductModal === 'undefined') {
            console.error('CRITICAL: openProductModal not defined!');
            alert('Erro: Sistema não carregou corretamente. Verifique o console.');
        } else {
            console.log('✅ Sistema inicializado. Produtos carregados:', products.length);
            console.log('✅ Configs do cardápio:', window.CARDAPIO_CONFIG);
        }
    });
</script>

<!-- Scripts Modulares (Refatoração) -->
<script src="<?= BASE_URL ?>/js/cardapio/utils.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/cart.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/modals.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio/checkout.js?v=<?= time() ?>"></script>

<!-- Main Script (Listeners) -->
<script src="<?= BASE_URL ?>/js/cardapio.js?v=<?= time() ?>"></script>
<script>
    lucide.createIcons();
</script>

    <!-- CSS para Etapa 4 (Badges) -->
    <style>
    /* Badges */
    .cardapio-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 0.70rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        color: white;
        text-transform: uppercase;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 10;
        letter-spacing: 0.5px;
    }

    .cardapio-badge-combo {
        background: #f59e0b;
    }

    .cardapio-badge-featured {
        background: #ef4444;
    }

    .cardapio-card-combo {
        border: 2px solid #f59e0b;
        background: #fffbeb;
    }

    .price-combo {
        color: #d97706;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .placeholder-combo {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #d97706;
    }

    .cardapio-product-includes {
        font-size: 0.8rem; 
        color: #6b7280; 
        margin-top: 4px; 
        line-height: 1.3;
    }

    .cardapio-card-featured {
        border: 1px solid #fecaca;
        background: #fef2f2;
    }
    
    /* Smooth Scroll no Modal */
    .cardapio-modal-content {
        scroll-behavior: smooth;
    }
    </style>

    <script>
    // Configurações Globais (para acesso no JS)
    window.cardapioConfig = <?= json_encode($cardapioConfig) ?>;
    
    // [ETAPA 4] Micro UX - Wrapper para compatibilidade
    function openProductModal(productId) {
        // Usa CardapioModals (módulo refatorado)
        if (window.CardapioModals) {
            window.CardapioModals.openProduct(productId);
            
            // UX: Scroll suave pro topo da imagem (se modal abrir)
            setTimeout(() => {
                const modalImg = document.querySelector('#modalProductImage');
                if (modalImg && modalImg.offsetParent) {
                    modalImg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        } else {
            console.error('[Cardápio] CardapioModals não carregado!');
        }
    }
    </script>
</body>
</html>
