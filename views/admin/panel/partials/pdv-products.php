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
                        $displayPrice = $product['effective_price'] ?? $product['price'];
                        $originalPrice = $product['original_price'] ?? $product['price'];
                        $isPromo = !empty($product['is_promo_valid']);
                        // Calcula percentual de desconto
                        $discountPercent = 0;
                        if ($isPromo && $originalPrice > 0) {
                            $discountPercent = round((1 - ($displayPrice / $originalPrice)) * 100);
                        }
                        ?>
                        <div class="product-card product-card-compact<?= $isPromo ? ' product-on-promo' : '' ?>" 
                             onclick="PDV.clickProduct(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', '<?= $displayPrice ?>', '<?= $product['has_extras'] ? 'true' : 'false' ?>')"
                             data-category="<?= htmlspecialchars($category['name']) ?>"
                             data-id="<?= $product['id'] ?>"
                             data-name="<?= htmlspecialchars($product['name']) ?>"
                             data-price="<?= $displayPrice ?>"
                             data-original-price="<?= $originalPrice ?>"
                             data-is-promo="<?= $isPromo ? 'true' : 'false' ?>"
                             data-has-extras="<?= $product['has_extras'] ? 'true' : 'false' ?>">
                            
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                            </div>
                            <div class="product-price"<?= $isPromo ? ' style="display:flex;justify-content:space-between;align-items:center;color:#dc2626"' : '' ?>>
                                <span<?= $isPromo ? ' style="color:#dc2626"' : '' ?>>R$ <?= number_format($displayPrice, 2, ',', '.') ?></span>
                                <?php if ($isPromo && $discountPercent > 0): ?>
                                <span style="color:#dc2626;font-weight:700">-<?= $discountPercent ?>%</span>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>
