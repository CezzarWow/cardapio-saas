<?php
/**
 * ============================================
 * PARTIAL: Aba Destaques (Orquestrador)
 * Prioridade de categorias e produtos em destaque
 *
 * Arquivo refatorado que inclui:
 * - _destaques_categories.php (Prioridade das categorias)
 * - _destaques_products.php (Produtos em destaque)
 * - _destaques_preview.php (Preview lateral)
 *
 * CORREÇÃO: Código duplicado removido - filtro de
 * featuredProducts agora é executado uma única vez aqui.
 * ============================================
 */

// ÚNICO filtro de produtos destacados (antes era duplicado nas linhas 162 e 272)
$featuredProducts = array_filter($allProducts ?? [], fn ($p) => ($p['is_featured'] ?? 0));
usort($featuredProducts, fn ($a, $b) => ($a['display_order'] ?? 999) - ($b['display_order'] ?? 999));
?>

<div class="cardapio-admin-destaques-container">
    
    <!-- Coluna Esquerda (70%) -->
    <div class="cardapio-admin-destaques-main">
        
        <?php // Bloco 1: Prioridade das Categorias?>
        <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_destaques_categories.php', get_defined_vars()); ?>

        <?php // Bloco 2: Produtos em Destaque?>
        <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_destaques_products.php', get_defined_vars()); ?>

    </div>

    <?php // Coluna Direita: Preview?>
    <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_destaques_preview.php', get_defined_vars()); ?>

</div>
