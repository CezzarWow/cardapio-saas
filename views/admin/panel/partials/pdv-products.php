<?php
/**
 * PDV-PRODUCTS.PHP - Grid de Produtos
 *
 * Contém: Filtros de categoria (chips), Grid de produtos
 * Variáveis esperadas: $categories
 */
?>

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
                        <?php 
                        // Usa preço efetivo (promocional se válido)
                        $productId = (int) ($product['id'] ?? 0);
                        $productName = (string) ($product['name'] ?? '');
                        $categoryName = (string) ($category['name'] ?? '');
                        $hasExtras = !empty($product['has_extras']);

                        $displayPrice = $product['effective_price'] ?? $product['price'];
                        $originalPrice = $product['original_price'] ?? $product['price'];
                        $isPromo = !empty($product['is_promo_valid']);
                        $promoClass = $isPromo ? ' product-on-promo' : '';
                        $promoStyle = $isPromo ? ' style="display:flex;justify-content:space-between;align-items:center;color:#dc2626"' : '';
                        $promoSpanStyle = $isPromo ? ' style="color:#dc2626"' : '';
                        $isPromoAttr = $isPromo ? 'true' : 'false';
                        $hasExtrasAttr = $hasExtras ? 'true' : 'false';
                        // Calcula percentual de desconto
                        $discountPercent = 0;
                        if ($isPromo && $originalPrice > 0) {
                            $discountPercent = round((1 - ($displayPrice / $originalPrice)) * 100);
                        }
                        ?>
                        <div class="product-card product-card-compact<?= \App\Helpers\ViewHelper::e($promoClass) ?>" 
                             onclick='PDV.clickProduct(<?= (int) $productId ?>, <?= \App\Helpers\ViewHelper::js($productName) ?>, <?= \App\Helpers\ViewHelper::js((float) $displayPrice) ?>, <?= \App\Helpers\ViewHelper::e($hasExtrasAttr) ?>)'
                             data-category="<?= \App\Helpers\ViewHelper::e($categoryName) ?>"
                             data-id="<?= (int) $productId ?>"
                             data-name="<?= \App\Helpers\ViewHelper::e($productName) ?>"
                             data-price="<?= (float) $displayPrice ?>"
                             data-original-price="<?= (float) $originalPrice ?>"
                             data-is-promo="<?= \App\Helpers\ViewHelper::e($isPromoAttr) ?>"
                             data-has-extras="<?= \App\Helpers\ViewHelper::e($hasExtrasAttr) ?>">
                            
                            <div class="product-info">
                                <h3><?= \App\Helpers\ViewHelper::e($productName) ?></h3>
                            </div>
                            <div class="product-price"<?= \App\Helpers\ViewHelper::e($promoStyle) ?>>
                                <span<?= \App\Helpers\ViewHelper::e($promoSpanStyle) ?>>R$ <?= number_format($displayPrice, 2, ',', '.') ?></span>
                                <?php if ($isPromo && $discountPercent > 0): ?>
                                <span style="color:#dc2626;font-weight:700">-<?= (int) $discountPercent ?>%</span>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>
