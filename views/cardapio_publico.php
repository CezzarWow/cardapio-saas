<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($restaurant['name']) ?> - Cardápio</title>
    
    <!-- CSS Modular - Cardápio Público -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cards.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/modals.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/form.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cart.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio.css?v=<?= time() ?>">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* ========== LAYOUT FLEX MOBILE ========== */
        html, body { 
            height: 100%;
            overflow: hidden; /* Scroll NÃO fica no body */
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
            background: transparent;
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
    </style>
</head>
<body>

<main class="main-content">
    <div class="cardapio-container">
        
        <!-- HEADER DO CARDÁPIO -->
        <header class="cardapio-header">
            <div class="cardapio-header-content">
                <div class="cardapio-brand">
                    <?php if (!empty($restaurant['logo'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($restaurant['logo']) ?>" alt="Logo" class="cardapio-logo">
                    <?php else: ?>
                        <div class="cardapio-logo-placeholder">
                            <i data-lucide="utensils" size="28"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="cardapio-title"><?= htmlspecialchars($restaurant['name']) ?></h1>
                        <p class="cardapio-subtitle">Faça seu pedido</p>
                    </div>
                </div>
                <div class="cardapio-info">
                    <p class="cardapio-delivery-time">30-45 min</p>
                </div>
            </div>
        </header>

        <!-- BUSCA -->
        <div class="cardapio-search-container">
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

        <!-- CATEGORIAS -->
        <div class="cardapio-categories">
            <button class="cardapio-category-btn active" data-category="todos">
                Todos
            </button>
            <?php foreach ($categories as $category): ?>
                <button class="cardapio-category-btn" data-category="<?= htmlspecialchars($category['name']) ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- LISTA DE PRODUTOS -->
        <div class="cardapio-products">
            <?php foreach ($productsByCategory as $categoryName => $products): ?>
                <div class="cardapio-category-section" data-category-name="<?= htmlspecialchars($categoryName) ?>">
                    <h2 class="cardapio-category-title">
                        <i data-lucide="package" size="20"></i>
                        <?= htmlspecialchars($categoryName) ?>
                    </h2>
                    
                    <?php foreach ($products as $product): ?>
                        <div 
                            class="cardapio-product-card" 
                            data-product-id="<?= $product['id'] ?>"
                            data-product-name="<?= htmlspecialchars($product['name']) ?>"
                            data-product-price="<?= number_format($product['price'], 2, '.', '') ?>"
                            data-product-description="<?= htmlspecialchars($product['description'] ?? '') ?>"
                            data-product-image="<?= htmlspecialchars($product['image'] ?? '') ?>"
                            data-product-category="<?= htmlspecialchars($categoryName) ?>"
                        >
                            <div class="cardapio-product-image-wrapper">
                                <?php if (!empty($product['image'])): ?>
                                    <img 
                                        src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($product['image']) ?>" 
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="cardapio-product-image"
                                    >
                                <?php else: ?>
                                    <div class="cardapio-product-image-placeholder">
                                        <i data-lucide="image" size="24"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cardapio-product-info">
                                <h3 class="cardapio-product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="cardapio-product-description"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                                <div class="cardapio-product-footer">
                                    <span class="cardapio-product-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                            
                            <button class="cardapio-add-btn" onclick="openProductModal(<?= $product['id'] ?>)">
                                <i data-lucide="plus" size="16"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<!-- MODAL DE PRODUTO -->
<div id="productModal" class="cardapio-modal">
    <div class="cardapio-modal-content">
        <div class="cardapio-modal-image-wrapper" id="modalImageWrapper">
            <img id="modalProductImage" src="" alt="" class="cardapio-modal-image">
            <button class="cardapio-modal-close" onclick="closeProductModal()">
                <i data-lucide="chevron-left" size="20"></i>
            </button>
        </div>
        
        <div class="cardapio-modal-body">
            <div class="cardapio-modal-header">
                <h2 id="modalProductName" class="cardapio-modal-title"></h2>
                <span id="modalProductPrice" class="cardapio-modal-price"></span>
            </div>
            
            <p id="modalProductDescription" class="cardapio-modal-description"></p>
            
            <!-- Quantidade -->
            <div class="cardapio-quantity-control">
                <span class="cardapio-quantity-label">Quantidade</span>
                <div class="cardapio-quantity-buttons">
                    <button class="cardapio-qty-btn" onclick="decreaseQuantity()">
                        <i data-lucide="minus" size="16"></i>
                    </button>
                    <span id="modalQuantity" class="cardapio-quantity-value">1</span>
                    <button class="cardapio-qty-btn" onclick="increaseQuantity()">
                        <i data-lucide="plus" size="16"></i>
                    </button>
                </div>
            </div>
            
            <!-- Adicionais -->
            <div id="modalAdditionals" class="cardapio-additionals">
                <h4 class="cardapio-additionals-title">Extras</h4>
                <div id="additionalsList" class="cardapio-additionals-list">
                    <?php foreach ($additionalGroups as $group): ?>
                        <?php if (isset($additionalItems[$group['id']]) && count($additionalItems[$group['id']]) > 0): ?>
                            <div class="cardapio-additional-group">
                                <p class="cardapio-additional-group-name"><?= htmlspecialchars($group['name']) ?></p>
                                <?php foreach ($additionalItems[$group['id']] as $item): ?>
                                    <label class="cardapio-additional-item">
                                        <div class="cardapio-additional-item-info">
                                            <input 
                                                type="checkbox" 
                                                class="cardapio-additional-checkbox"
                                                data-additional-id="<?= $item['id'] ?>"
                                                data-additional-name="<?= htmlspecialchars($item['name']) ?>"
                                                data-additional-price="<?= number_format($item['price'], 2, '.', '') ?>"
                                            >
                                            <span class="cardapio-additional-name"><?= htmlspecialchars($item['name']) ?></span>
                                        </div>
                                        <span class="cardapio-additional-price">+R$ <?= number_format($item['price'], 2, ',', '.') ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Observações -->
            <div class="cardapio-observations">
                <h4 class="cardapio-observations-title">Observações</h4>
                <textarea 
                    id="modalObservation" 
                    class="cardapio-observations-textarea"
                    placeholder="Ex: Sem cebola, ponto da carne..."
                ></textarea>
            </div>
        </div>
        
        <div class="cardapio-modal-footer">
            <button class="cardapio-add-cart-btn" onclick="addToCart()">
                <span>Adicionar</span>
                <span id="modalTotalPrice">R$ 0,00</span>
            </button>
        </div>
    </div>
</div>

<!-- CARRINHO FLUTUANTE (BOTÃO COMPACTO) -->
<button id="floatingCart" class="cardapio-floating-cart-btn" onclick="openCartModal()">
    <i data-lucide="shopping-cart" size="20"></i>
    <span id="cartTotal">R$ 0,00</span>
    <i data-lucide="arrow-right" size="18"></i>
</button>

<!-- MODAL DO CARRINHO -->
<div id="cartModal" class="cardapio-modal cardapio-cart-modal">
    <div class="cardapio-modal-content cardapio-cart-modal-content">
        <div class="cardapio-cart-header">
            <h2>Seu Pedido</h2>
            <button class="cardapio-modal-close-round" onclick="closeCartModal()">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="cardapio-cart-body" id="cartItemsContainer">
        </div>
        
        <div class="cardapio-cart-footer">
            <div class="cardapio-cart-total-row">
                <span class="cardapio-cart-total-label">Total:</span>
                <span id="cartModalTotal" class="cardapio-cart-total-value">R$ 0,00</span>
            </div>
            <button class="cardapio-checkout-btn" onclick="goToCheckout()">
                Próxima Etapa
                <i data-lucide="arrow-right" size="18"></i>
            </button>
        </div>
    </div>
</div>

<!-- MODAL DE SUGESTÕES (BEBIDAS E MOLHOS) - TELA INTEIRA -->
<div id="suggestionsModal" class="cardapio-modal">
    <div class="cardapio-modal-content fullscreen cardapio-suggestions-modal">
        <div class="cardapio-suggestions-header">
            <button class="cardapio-back-btn" onclick="closeSuggestionsModal()">
                <i data-lucide="arrow-left" size="20"></i>
            </button>
            <h2>🥤 Quer adicionar algo?</h2>
        </div>
        
        <div class="cardapio-modal-body">
            <!-- Bebidas -->
            <div class="suggestion-section">
                <h3 class="suggestion-section-title">
                    <i data-lucide="cup-soda" size="20"></i>
                    Bebidas
                </h3>
                <div class="suggestion-items">
                    <?php 
                    // Filtrar produtos da categoria "Bebidas" ou similar
                    $drinks = [];
                    foreach ($allProducts as $p) {
                        $catLower = strtolower($p['category_name'] ?? '');
                        if (strpos($catLower, 'bebida') !== false || strpos($catLower, 'drink') !== false || strpos($catLower, 'refrigerante') !== false || strpos($catLower, 'suco') !== false) {
                            $drinks[] = $p;
                        }
                    }
                    if (empty($drinks)): ?>
                        <p class="suggestion-empty">Nenhuma bebida disponível</p>
                    <?php else: ?>
                        <?php foreach ($drinks as $drink): ?>
                            <div class="suggestion-item">
                                <div class="suggestion-item-info">
                                    <?php if (!empty($drink['image'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($drink['image']) ?>" class="suggestion-item-image" alt="">
                                    <?php else: ?>
                                        <div class="suggestion-item-image-placeholder">
                                            <i data-lucide="cup-soda" size="20"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="suggestion-item-name"><?= htmlspecialchars($drink['name']) ?></p>
                                        <p class="suggestion-item-price">R$ <?= number_format($drink['price'], 2, ',', '.') ?></p>
                                    </div>
                                </div>
                                <button class="suggestion-drink-btn" data-id="<?= $drink['id'] ?>" 
                                    onclick="addDrinkToCart(<?= $drink['id'] ?>, '<?= htmlspecialchars(addslashes($drink['name'])) ?>', <?= $drink['price'] ?>, '<?= htmlspecialchars($drink['image'] ?? '') ?>')">
                                    <i data-lucide="plus" size="16"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Molhos extras -->
            <div class="suggestion-section">
                <h3 class="suggestion-section-title">
                    <i data-lucide="droplet" size="20"></i>
                    Molhos Extras
                </h3>
                <div class="suggestion-items">
                    <?php 
                    $hasSauces = false;
                    foreach ($additionalGroups as $group): 
                        $groupLower = strtolower($group['name']);
                        if (strpos($groupLower, 'molho') !== false || strpos($groupLower, 'sauce') !== false):
                            $hasSauces = true;
                            if (isset($additionalItems[$group['id']])):
                                foreach ($additionalItems[$group['id']] as $sauce): ?>
                                    <div class="suggestion-item">
                                        <div class="suggestion-item-info">
                                            <div class="suggestion-item-image-placeholder sauce">
                                                <i data-lucide="droplet" size="18"></i>
                                            </div>
                                            <div>
                                                <p class="suggestion-item-name"><?= htmlspecialchars($sauce['name']) ?></p>
                                                <p class="suggestion-item-price"><?= $sauce['price'] > 0 ? 'R$ ' . number_format($sauce['price'], 2, ',', '.') : 'Grátis' ?></p>
                                            </div>
                                        </div>
                                        <button class="suggestion-sauce-btn" data-id="<?= $sauce['id'] ?>" 
                                            onclick="addSauceToCart(<?= $sauce['id'] ?>, '<?= htmlspecialchars(addslashes($sauce['name'])) ?>', <?= $sauce['price'] ?>)">
                                            <i data-lucide="plus" size="16"></i>
                                        </button>
                                    </div>
                                <?php endforeach;
                            endif;
                        endif;
                    endforeach;
                    if (!$hasSauces): ?>
                        <p class="suggestion-empty">Nenhum molho extra disponível</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="cardapio-cart-footer">
            <button class="cardapio-checkout-btn" onclick="finalizarPedido()">
                Finalizar Pedido
                <i data-lucide="arrow-right" size="18"></i>
            </button>
        </div>
    </div>
</div>

<script>
    window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/js/cardapio.js?v=<?= time() ?>"></script>
<script>
    lucide.createIcons();
</script>

</body>
</html>
