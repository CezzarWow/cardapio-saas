<?php
/**
 * PARTIAL: Lista de Produtos (Combos, Destaques, Categorias)
 * Espera:
 * - $categories (array)
 * - $combos (array)
 * - $featuredProducts (array)
 * - $productsByCategory (array)
 */
?>
<!-- LISTA DE PRODUTOS -->
<div class="cardapio-products">
    
    <?php foreach ($categories as $category): ?>
        <?php
            $catType = $category['category_type'] ?? 'default';
        $catName = $category['name'];
        $catId = $category['id'];

        // RENDERIZAÇÃO: COMBOS
        if ($catType === 'combos' && !empty($combos)):
            ?>
            <div class="cardapio-category-section" data-category-id="<?= (int) $catId ?>" style="margin-bottom: 20px;">
                <h2 class="cardapio-category-title" style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(90deg, #f59e0b, #d97706); color: white; padding: 6px 14px; border-radius: 20px; margin-bottom: 12px; font-size: 0.95rem;">
                    <i data-lucide="package-plus" size="16"></i>
                    <?= htmlspecialchars($catName) ?>
                </h2>
                
                <?php foreach ($combos as $combo): ?>
                <div class="cardapio-product-card cardapio-card-combo"
                    data-combo-id="<?= (int) ($combo['id'] ?? 0) ?>"
                    data-combo-name="<?= htmlspecialchars($combo['name']) ?>"
                    data-combo-price="<?= number_format($combo['price'], 2, '.', '') ?>"
                    onclick="CardapioModals.openCombo(<?= (int) ($combo['id'] ?? 0) ?>)" 
                    style="cursor: pointer;"
                >
                    <div class="cardapio-badge cardapio-badge-combo">COMBO</div>
                    <div class="cardapio-product-image-wrapper">
                        <?php if (!empty($combo['image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($combo['image']) ?>" alt="<?= htmlspecialchars($combo['name']) ?>" class="cardapio-product-image" loading="lazy">
                        <?php else: ?>
                            <div class="cardapio-product-image-placeholder placeholder-combo">
                                <i data-lucide="package" size="40"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="cardapio-product-info">
                        <h3 class="cardapio-product-name"><?= htmlspecialchars($combo['name']) ?></h3>
                        <p class="cardapio-product-description"><?= htmlspecialchars($combo['description'] ?? '') ?></p>
                        <?php if (!empty($combo['products_list'])): ?>
                        <p class="cardapio-product-includes">
                            <strong>Inclui:</strong> <?= htmlspecialchars($combo['products_list']) ?>
                        </p>
                        <?php endif; ?>
                        <div class="cardapio-product-footer">
                            <span class="cardapio-product-price price-combo">R$ <?= number_format($combo['price'], 2, ',', '.') ?></span>
                        </div>
                    </div>
                    <button class="cardapio-add-btn"><i data-lucide="plus" size="16"></i></button>
                </div>
                <?php endforeach; ?>
            </div>

        <?php
                // RENDERIZAÇÃO: DESTAQUES
                elseif ($catType === 'featured' && !empty($featuredProducts)):
                    ?>
            <div class="cardapio-category-section" data-category-id="<?= (int) $catId ?>">
                <h2 class="cardapio-category-title" style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(90deg, #ef4444, #dc2626); color: white; padding: 6px 14px; border-radius: 20px; margin-bottom: 12px; font-size: 0.95rem;">
                    <i data-lucide="star" size="16" style="fill: white;"></i>
                    <?= htmlspecialchars($catName) ?>
                </h2>
                
                <?php foreach ($featuredProducts as $product): ?>
                    <!-- Card de produto destaque -->
                    <div 
                        class="cardapio-product-card cardapio-card-featured" 
                        data-product-id="<?= (int) ($product['id'] ?? 0) ?>"
                        data-product-name="<?= htmlspecialchars($product['name']) ?>"
                        data-product-price="<?= number_format($product['price'], 2, '.', '') ?>"
                        onclick="openProductModal(<?= (int) ($product['id'] ?? 0) ?>)" style="cursor: pointer;"
                    >
                        
                        <div class="cardapio-product-image-wrapper">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($product['image']) ?>" class="cardapio-product-image" loading="lazy">
                            <?php elseif (!empty($product['icon_as_photo']) && !empty($product['icon'])): ?>
                                <div class="cardapio-product-icon-placeholder" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; border-radius: 8px;">
                                    <span style="font-size: 3rem;"><?= \App\Helpers\ViewHelper::e($product['icon'] ?? '') ?></span>
                                </div>
                            <?php else: ?>
                                <div class="cardapio-product-image-placeholder"><i data-lucide="image" size="24"></i></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cardapio-product-info">
                            <h3 class="cardapio-product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="cardapio-product-description"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                            <div class="cardapio-product-footer">
                                <span class="cardapio-product-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                        <button class="cardapio-add-btn"><i data-lucide="plus" size="16"></i></button>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php
                // RENDERIZAÇÃO: CATEGORIAS PADRÃO
                elseif ($catType === 'default' && !empty($productsByCategory[$catName])):
                     // Verifica se é a primeira categoria padrão (mantém renderizada para SEO/LCP)
                     static $renderedDefaultCats = 0;
                     $isFirstDefault = ($renderedDefaultCats === 0);
                     $renderedDefaultCats++;
                    ?>
            
            <?php if ($isFirstDefault): ?>
                <!-- Renderização Normal (SSR) para a primeira categoria -->
            <div class="cardapio-category-section" data-category-id="<?= (int) $catId ?>">
                    <h2 class="cardapio-category-title" style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(90deg, #ea580c, #c2410c); color: white; padding: 6px 14px; border-radius: 20px; margin-bottom: 12px; font-size: 0.95rem;">
                        <i data-lucide="package" size="16"></i>
                        <?= htmlspecialchars($catName) ?>
                    </h2>
                    
                    <?php foreach ($productsByCategory[$catName] as $product): ?>
                        <div 
                            class="cardapio-product-card" 
                            data-product-id="<?= (int) ($product['id'] ?? 0) ?>"
                            data-product-name="<?= htmlspecialchars($product['name']) ?>"
                            data-product-price="<?= number_format($product['price'], 2, '.', '') ?>"
                            onclick="openProductModal(<?= (int) ($product['id'] ?? 0) ?>)" style="cursor: pointer;"
                        >
                            <div class="cardapio-product-image-wrapper">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($product['image']) ?>" class="cardapio-product-image" loading="lazy">
                                <?php elseif (!empty($product['icon_as_photo']) && !empty($product['icon'])): ?>
                                    <!-- Emoji colorido como fallback -->
                                    <div class="cardapio-product-icon-placeholder" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; border-radius: 8px;">
                                        <span style="font-size: 3rem;"><?= \App\Helpers\ViewHelper::e($product['icon'] ?? '') ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="cardapio-product-image-placeholder"><i data-lucide="image" size="24"></i></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cardapio-product-info">
                                <h3 class="cardapio-product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="cardapio-product-description"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                                <div class="cardapio-product-footer">
                                    <span class="cardapio-product-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                            <button class="cardapio-add-btn"><i data-lucide="plus" size="16"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Renderização Lazy (CSR) para demais categorias -->
                <div class="cardapio-category-section" data-category-id="<?= (int) $catId ?>" data-lazy-category="<?= htmlspecialchars($catName) ?>" style="min-height: 100px;">
                    <h2 class="cardapio-category-title" style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(90deg, #ea580c, #c2410c); color: white; padding: 6px 14px; border-radius: 20px; margin-bottom: 12px; font-size: 0.95rem;">
                        <i data-lucide="package" size="16"></i>
                        <?= htmlspecialchars($catName) ?>
                    </h2>
                    <!-- Produtos serão injetados via JS -->
                    <div class="cardapio-lazy-placeholder" style="padding: 20px; text-align: center; color: #999;">
                        <i data-lucide="loader-2" class="animate-spin"></i>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    <?php endforeach; ?>
</div>
