<?php
/**
 * ============================================
 * REPOSIÇÃO DE ESTOQUE (Orquestrador)
 *
 * Arquivo refatorado que inclui:
 * - partials/_product_grid.php (Grid de produtos)
 * - partials/_adjust_modal.php (Modal de ajuste)
 *
 * JavaScript extraído para:
 * - public/js/admin/reposition.js
 * ============================================
 */

\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());

$STOCK_CRITICAL_LIMIT = 5;
$totalProducts = count($products);

// Contar produtos por status
$criticalCount = 0;
$negativeCount = 0;
foreach ($products as $p) {
    $s = intval($p['stock']);
    if ($s < 0) {
        $negativeCount++;
    } elseif ($s <= $STOCK_CRITICAL_LIMIT) {
        $criticalCount++;
    }
}
?>

<!-- stock-v2 removido - usando stock-consolidated.css global -->

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Reposição de Estoque</h1>
        </div>

        <!-- Sub-abas (STICKY) -->
        <div class="sticky-tabs">
            <div class="stock-tabs">
                <a href="<?= BASE_URL ?>/admin/loja/produtos" class="stock-tab">Produtos</a>
                <a href="<?= BASE_URL ?>/admin/loja/categorias" class="stock-tab">Categorias</a>
                <a href="<?= BASE_URL ?>/admin/loja/adicionais" class="stock-tab">Adicionais</a>
                <a href="<?= BASE_URL ?>/admin/loja/reposicao" class="stock-tab active">Reposição</a>
                <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" class="stock-tab">Movimentações</a>
            </div>
        </div>

        <!-- Busca + Indicadores -->
        <div class="stock-search-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <input type="text" id="searchProduct" placeholder="🔍 Buscar produto por nome..." 
                   class="stock-search-input" style="width: 100%; max-width: 350px;"
                   oninput="filterProducts()">
            
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

        <?php // Grid de Produtos?>
        <?php \App\Core\View::renderFromScope('admin/reposition/partials/_product_grid.php', get_defined_vars()); ?>

    </div>
</main>

<?php // Modal de Ajuste?>
<?php \App\Core\View::renderFromScope('admin/reposition/partials/_adjust_modal.php', get_defined_vars()); ?>

<?php // Script de Reposição?>
<script src="<?= BASE_URL ?>/js/admin/reposition.js?v=<?= time() ?>"></script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
