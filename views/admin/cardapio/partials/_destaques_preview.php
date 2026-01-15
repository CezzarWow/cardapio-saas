<?php
/**
 * PARTIAL: Preview de Destaques
 * Extraído de _tab_destaques.php
 *
 * Requer $featuredProducts e $categories já definidos no escopo
 */
?>

<!-- Coluna Direita (30%) - Preview -->
<div class="cardapio-admin-destaques-preview">
    <div class="cardapio-admin-card" style="position: sticky; top: 20px;">
        <div class="cardapio-admin-card-header">
            <i data-lucide="eye"></i>
            <h3 class="cardapio-admin-card-title">Preview</h3>
        </div>

        <div class="cardapio-admin-destaques-preview-notice">
            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
            Salve para atualizar o preview
        </div>

        <!-- Seção Destaques -->
        <div class="cardapio-admin-destaques-preview-section">
            <h5 class="cardapio-admin-destaques-preview-title">⭐ Destaques</h5>
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $product): ?>
                <div class="cardapio-admin-destaques-preview-item featured">
                    <span><?= htmlspecialchars($product['name']) ?></span>
                    <span class="price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="cardapio-admin-destaques-preview-empty">Nenhum produto destacado</p>
            <?php endif; ?>
        </div>

        <!-- Categorias na ordem -->
        <div class="cardapio-admin-destaques-preview-section">
            <h5 class="cardapio-admin-destaques-preview-title">📂 Categorias</h5>
            <?php if (!empty($categories)): ?>
                <?php
                $sortedCategories = $categories;
                usort($sortedCategories, fn ($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
                ?>
                <?php foreach ($sortedCategories as $idx => $cat): ?>
                <div class="cardapio-admin-destaques-preview-item">
                    <span><?= $idx + 1 ?>. <?= htmlspecialchars($cat['name']) ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="cardapio-admin-destaques-preview-empty">Nenhuma categoria</p>
            <?php endif; ?>
        </div>

    </div>
</div>
