<?php
/**
 * Reposition Partial - Para carregamento AJAX
 * Arquivo: views/admin/stock/partials/_reposition.php
 */
?>

<!-- Busca + Indicadores -->
<div class="stock-search-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
    <input type="text" id="searchProduct" placeholder="🔍 Buscar produto por nome..." 
           class="stock-search-input" style="width: 100%; max-width: 350px;"
           oninput="StockSPA.filterProducts()">
    
    <div style="display: flex; gap: 20px; align-items: center;">
        <!-- Total -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="background: #dbeafe; padding: 6px; border-radius: 6px;">
                <i data-lucide="package" style="width: 18px; height: 18px; color: #2563eb;"></i>
            </div>
            <div>
                <span style="font-weight: 700; color: #1f2937;"><?= $totalProducts ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;"> produtos</span>
            </div>
        </div>
        
        <!-- Críticos -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="background: #fef3c7; padding: 6px; border-radius: 6px;">
                <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: #d97706;"></i>
            </div>
            <div>
                <span style="font-weight: 700; color: #d97706;"><?= $criticalCount ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;"> críticos</span>
            </div>
        </div>
        
        <!-- Negativos -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="background: #fecaca; padding: 6px; border-radius: 6px;">
                <i data-lucide="trending-down" style="width: 18px; height: 18px; color: #dc2626;"></i>
            </div>
            <div>
                <span style="font-weight: 700; color: #dc2626;"><?= $negativeCount ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;"> negativos</span>
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

<!-- Grid de Produtos para Reposição -->
<div class="stock-products-grid stock-fade-in">
    <?php if (empty($products)): ?>
        <div class="stock-empty-state">
            Nenhum produto cadastrado.
        </div>
    <?php else: ?>
        <?php foreach ($products as $prod): ?>
        <?php
            $stock = intval($prod['stock']);
            $stockClass = 'stock-product-card-stock--ok';
            if ($stock < 0) {
                $stockClass = 'stock-product-card-stock--danger';
            } elseif ($stock <= 5) {
                $stockClass = 'stock-product-card-stock--warning';
            }
        ?>
        <div class="stock-product-card product-row" 
             data-name="<?= strtolower($prod['name']) ?>" 
             data-category="<?= htmlspecialchars($prod['category_name'] ?? '') ?>">
            
            <?php if ($prod['image']): ?>
                <img src="<?= BASE_URL ?>/uploads/thumb/<?= $prod['image'] ?>" loading="lazy" 
                     class="stock-product-image" alt="<?= htmlspecialchars($prod['name']) ?>">
            <?php else: ?>
                <div class="stock-product-placeholder">
                    <i data-lucide="image" style="width: 40px; height: 40px;"></i>
                </div>
            <?php endif; ?>
            
            <div class="stock-product-card-body">
                <div class="stock-product-card-name"><?= htmlspecialchars($prod['name']) ?></div>
                <span class="stock-product-card-category"><?= htmlspecialchars($prod['category_name'] ?? '') ?></span>
                
                <div class="stock-product-card-footer">
                    <span class="stock-product-card-stock <?= $stockClass ?>"><?= $stock ?> un</span>
                </div>
            </div>
            
            <div class="stock-product-card-actions">
                <button onclick="StockSPA.openAdjustModal(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['name'])) ?>', <?= $stock ?>)" 
                        class="btn-edit" style="flex: 1; justify-content: center;">
                    <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                    Ajustar
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal de Ajuste de Estoque -->
<div id="adjustModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Ajustar Estoque</h3>
        <p style="color: #6b7280; margin-bottom: 1rem;">Produto: <strong id="adjustProductName"></strong></p>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Estoque atual: <strong id="adjustCurrentStock"></strong></p>
        
        <input type="hidden" id="adjustProductId">
        
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Quantidade a adicionar/remover</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" onclick="StockSPA.adjustAmount(-1)" style="width: 40px; height: 40px; background: #f3f4f6; border: none; border-radius: 8px; font-size: 1.5rem; cursor: pointer;">-</button>
                <input type="number" id="adjustAmount" value="0" 
                       style="flex: 1; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; text-align: center;">
                <button type="button" onclick="StockSPA.adjustAmount(1)" style="width: 40px; height: 40px; background: #f3f4f6; border: none; border-radius: 8px; font-size: 1.5rem; cursor: pointer;">+</button>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="StockSPA.closeAdjustModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cancelar
            </button>
            <button type="button" onclick="StockSPA.submitAdjust()" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Confirmar
            </button>
        </div>
    </div>
</div>
