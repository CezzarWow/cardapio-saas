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
            $stock = intval($prod['stock']);
            $isCritical = $stock <= $STOCK_CRITICAL_LIMIT && $stock >= 0;
            $isNegative = $stock < 0;
            $isNormal = $stock > $STOCK_CRITICAL_LIMIT;
            $stockClass = $isNegative ? 'stock-product-card-stock--danger' : ($isCritical ? 'stock-product-card-stock--warning' : 'stock-product-card-stock--ok');
            $statusLabel = $isNegative ? 'Negativo' : ($isCritical ? 'Crítico' : 'Normal');
        ?>
        <div class="stock-product-card product-row" 
             data-id="<?= $prod['id'] ?>"
             data-name="<?= strtolower($prod['name']) ?>" 
             data-stock="<?= $stock ?>"
             data-category="<?= htmlspecialchars($prod['category_name']) ?>">
            
            <!-- Imagem -->
            <?php if($prod['image']): ?>
                <img src="<?= BASE_URL ?>/uploads/<?= $prod['image'] ?>" loading="lazy" 
                     style="width: 100%; height: 140px; object-fit: cover; border-radius: 12px 12px 0 0;"
                     alt="<?= htmlspecialchars($prod['name']) ?>">
            <?php else: ?>
                <div style="width: 100%; height: 140px; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                    <i data-lucide="image" style="width: 40px; height: 40px;"></i>
                </div>
            <?php endif; ?>
            
            <!-- Corpo -->
            <div class="stock-product-card-body">
                <div class="stock-product-card-name"><?= htmlspecialchars($prod['name']) ?></div>
                <span class="stock-product-card-category"><?= htmlspecialchars($prod['category_name']) ?></span>
                
                <div class="stock-product-card-footer">
                    <span id="stock-<?= $prod['id'] ?>" class="stock-product-card-stock <?= $stockClass ?>" style="font-size: 1.1rem;">
                        <?= $stock ?>
                    </span>
                    <span style="padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;
                        background: <?= $isNegative ? '#fecaca' : ($isCritical ? '#fef3c7' : '#d1fae5') ?>;
                        color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                        <?= $statusLabel ?>
                    </span>
                </div>
            </div>
            
            <!-- Ação -->
            <div class="stock-product-card-actions">
                <button onclick="openAdjustModal(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['name'])) ?>', <?= $stock ?>)"
                        style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; text-align: center; font-size: 0.9rem;">
                    📦 Repor
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
