<?php
/**
 * PARTIAL: Botões de Categorias
 * Espera:
 * - $categories (array)
 */
?>
<!-- CATEGORIAS -->
<div class="cardapio-categories">
    <button class="cardapio-category-btn active" data-category="todos">
        Todos
    </button>
    <?php foreach ($categories as $category): ?>
        <?php
            $catType = $category['category_type'] ?? 'default';
        // Mostra categorias normais E combos (não mostra featured)
        if ($catType === 'default' || $catType === 'combos'):
            ?>
            <button class="cardapio-category-btn" data-category="<?= $category['id'] ?>">
                <?= htmlspecialchars($category['name']) ?>
            </button>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
