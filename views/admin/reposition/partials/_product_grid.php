<?php
/**
 * PARTIAL: Grid de Produtos para Reposição
 * Extraído de reposition/index.php
 */

$STOCK_CRITICAL_LIMIT = 5;
?>

<!-- Grid de Cards -->
<div id="stock-cards-view" class="stock-products-grid stock-fade-in">
    <?php if (empty($products)): ?>
        <div style="grid-column: 1 / -1; padding: 2rem; text-align: center; color: #999;">
            Nenhum produto cadastrado.
        </div>
    <?php else: ?>
        <?php foreach ($products as $prod): ?>
        <?php
            $prodId = (int) ($prod['id'] ?? 0);
            $prodName = (string) ($prod['name'] ?? '');
            $categoryName = (string) ($prod['category_name'] ?? '');
            $imageFile = !empty($prod['image']) ? basename((string) $prod['image']) : '';
            $imageUrl = $imageFile !== '' ? (BASE_URL . '/uploads/' . $imageFile) : '';

            $stock = intval($prod['stock']);
            $isCritical = $stock <= $STOCK_CRITICAL_LIMIT && $stock >= 0;
            $isNegative = $stock < 0;
            $isNormal = $stock > $STOCK_CRITICAL_LIMIT;
            $stockClass = $isNegative ? 'stock-product-card-stock--danger' : ($isCritical ? 'stock-product-card-stock--warning' : 'stock-product-card-stock--ok');
            $statusLabel = $isNegative ? 'Negativo' : ($isCritical ? 'Crítico' : 'Normal');
            ?>
        <div class="stock-product-card product-row" 
             data-id="<?= $prodId ?>"
             data-name="<?= \App\Helpers\ViewHelper::e(strtolower($prodName)) ?>" 
             data-stock="<?= (int) $stock ?>"
             data-category="<?= \App\Helpers\ViewHelper::e($categoryName) ?>">
            
            <!-- Imagem -->
            <?php if ($imageFile !== ''): ?>
                <img src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/uploads/<?= \App\Helpers\ViewHelper::e($imageFile) ?>" loading="lazy" 
                     style="width: 100%; height: 140px; object-fit: cover; border-radius: 12px 12px 0 0;"
                     alt="<?= \App\Helpers\ViewHelper::e($prodName) ?>">
            <?php else: ?>
                <div style="width: 100%; height: 140px; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                    <i data-lucide="image" style="width: 40px; height: 40px;"></i>
                </div>
            <?php endif; ?>
            
            <!-- Corpo -->
            <div class="stock-product-card-body">
                <div class="stock-product-card-name"><?= \App\Helpers\ViewHelper::e($prodName) ?></div>
                <span class="stock-product-card-category"><?= \App\Helpers\ViewHelper::e($categoryName) ?></span>
                
                <div class="stock-product-card-footer">
                    <span id="stock-<?= $prodId ?>" class="stock-product-card-stock <?= \App\Helpers\ViewHelper::e($stockClass) ?>" style="font-size: 1.1rem;">
                        <?= (int) $stock ?>
                    </span>
                    <span style="padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;
                        background: <?= $isNegative ? '#fecaca' : ($isCritical ? '#fef3c7' : '#d1fae5') ?>;
                        color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                        <?= \App\Helpers\ViewHelper::e($statusLabel) ?>
                    </span>
                </div>
            </div>
            
            <!-- Ação -->
            <div class="stock-product-card-actions">
                <button onclick='openAdjustModal(<?= $prodId ?>, <?= \App\Helpers\ViewHelper::js($prodName) ?>, <?= (int) $stock ?>)'
                        style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; text-align: center; font-size: 0.9rem;">
                    📦 Repor
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
