<?php
/**
 * Products Partial - Para carregamento AJAX
 * Arquivo: views/admin/stock/partials/_products.php
 */
?>

<!-- Busca + Indicadores -->
<div class="stock-search-container">
    <input type="text" id="searchProduct" placeholder="🔍 Buscar produto por nome..." 
           class="stock-search-input">
    
    <div class="stock-indicators">
        <!-- Total Products -->
        <div class="stock-indicator-item">
            <div class="stock-indicator-icon total-products-icon">
                <i data-lucide="package" class="total-products-svg"></i>
            </div>
            <div>
                <span class="stock-indicator-text"><?= (int) ($totalProducts ?? 0) ?></span>
                <span class="stock-indicator-label"> produtos</span>
            </div>
        </div>
        
        <!-- Critical Stock -->
        <?php
            $criticalIconBg = $criticalStockCount > 0 ? 'critical-stock-icon-bg-danger' : 'critical-stock-icon-bg-warning';
            $criticalIconSvg = $criticalStockCount > 0 ? 'critical-stock-svg-danger' : 'critical-stock-svg-warning';
        ?>
        <div class="stock-indicator-item">
            <div class="stock-indicator-icon <?= \App\Helpers\ViewHelper::e($criticalIconBg) ?>">
                <i data-lucide="alert-triangle" style="width: 18px; height: 18px;" class="<?= \App\Helpers\ViewHelper::e($criticalIconSvg) ?>"></i>
            </div>
            <div>
                <span class="stock-indicator-text" style="color: <?= $criticalStockCount > 0 ? '#dc2626' : '#d97706' ?>;"><?= (int) ($criticalStockCount ?? 0) ?></span>
                <span class="stock-indicator-label"> críticos</span>
            </div>
        </div>
    </div>
</div>

<!-- Chips de Categorias -->
<div class="category-chips-container">
    <div class="category-chips">
        <button class="category-chip active" data-category="">📂 Todas</button>
        <?php foreach ($categories as $cat): ?>
            <?php if (!in_array($cat['category_type'] ?? 'default', ['featured', 'combos'])): ?>
                <button class="category-chip" data-category="<?= htmlspecialchars($cat['name']) ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Grid de Cards -->
<div id="stock-cards-view" class="stock-products-grid stock-fade-in">
    <?php if (empty($products)): ?>
        <div class="stock-empty-state">
            Nenhum produto cadastrado.
        </div>
    <?php else: ?>
        <?php foreach ($products as $prod): ?>
        <?php
            $prodId = (int) ($prod['id'] ?? 0);
            $prodName = (string) ($prod['name'] ?? '');
            $categoryName = (string) ($prod['category_name'] ?? '');
            $imageFile = !empty($prod['image']) ? basename((string) $prod['image']) : '';
            $displayIcon = (string) ($prod['display_icon'] ?? '');
            $stockClass = (string) ($prod['stock_class'] ?? '');
            $stockInt = (int) ($prod['stock_int'] ?? 0);
            $formattedPrice = (string) ($prod['formatted_price'] ?? '');
        ?>
        <div class="stock-product-card product-row" 
             data-product-id="<?= (int) $prodId ?>"
             data-name="<?= \App\Helpers\ViewHelper::e(strtolower($prodName)) ?>" 
             data-category="<?= \App\Helpers\ViewHelper::e($categoryName) ?>">
            
            <!-- Imagem (usando thumbnail para carregamento rápido) -->
            <?php if ($imageFile !== ''): ?>
                <img src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/uploads/thumb/<?= \App\Helpers\ViewHelper::e($imageFile) ?>" loading="lazy" 
                     class="stock-product-image"
                     alt="<?= \App\Helpers\ViewHelper::e($prodName) ?>">
            <?php elseif (($prod['icon_as_photo'] ?? 0) == 1): ?>
                <div class="stock-product-icon-container">
                    <?php if ($prod['is_lucide_icon']): ?>
                        <i data-lucide="<?= \App\Helpers\ViewHelper::e($displayIcon) ?>" style="width: 64px; height: 64px; color: #3b82f6;"></i>
                    <?php else: ?>
                        <span style="font-size: 4rem; line-height: 1;"><?= \App\Helpers\ViewHelper::e($displayIcon) ?></span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="stock-product-placeholder">
                    <i data-lucide="image" style="width: 40px; height: 40px;"></i>
                </div>
            <?php endif; ?>
            
            <!-- Corpo -->
            <div class="stock-product-card-body">
                <div class="stock-product-card-name"><?= \App\Helpers\ViewHelper::e($prodName) ?></div>
                <span class="stock-product-card-category"><?= \App\Helpers\ViewHelper::e($categoryName) ?></span>
                
                <div class="stock-product-card-footer">
                    <span class="stock-product-card-price">R$ <?= \App\Helpers\ViewHelper::e($formattedPrice) ?></span>
                    <span class="stock-product-card-stock <?= \App\Helpers\ViewHelper::e($stockClass) ?>"><?= (int) $stockInt ?></span>
                </div>
            </div>
            
            <!-- Ações -->
            <div class="stock-product-card-actions">
                <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/produtos/editar?id=<?= (int) $prodId ?>" class="btn-edit">
                    <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                    Editar
                </a>
                <a href="javascript:void(0)" 
                   onclick='StockSPA.openDeleteModal(<?= (int) $prodId ?>, <?= json_encode($prodName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-delete">
                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    Excluir
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal de Exclusão -->
<div id="deleteModal">
    <div class="delete-modal-content">
        <div class="delete-modal-icon-container">
            <i data-lucide="trash-2" style="width: 28px; height: 28px; color: #dc2626;"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Excluir Produto</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Tem certeza que deseja excluir <strong id="deleteProductName"></strong>?</p>
        <p style="color: #dc2626; font-size: 0.85rem; margin-bottom: 1.5rem;">⚠️ Esta ação não pode ser desfeita.</p>
        
        <div class="delete-modal-actions">
            <button onclick="StockSPA.closeDeleteModal()" class="btn-cancel-delete">
                Cancelar
            </button>
            <a id="deleteConfirmBtn" href="#" class="btn-confirm-delete">
                🗑️ Excluir
            </a>
        </div>
    </div>
</div>
