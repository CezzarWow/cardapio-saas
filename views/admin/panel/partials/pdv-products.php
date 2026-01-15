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
                        
                        <div class="product-card product-card-compact js-add-product" 
                             data-category="<?= htmlspecialchars($category['name']) ?>"
                             data-id="<?= $product['id'] ?>"
                             data-name="<?= htmlspecialchars($product['name']) ?>"
                             data-price="<?= $product['price'] ?>"
                             data-has-extras="<?= $product['has_extras'] ? 'true' : 'false' ?>">
                            
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                            </div>
                            <div class="product-price">
                                R$ <?= number_format($product['price'], 2, ',', '.') ?>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>
