<?php
/**
 * ============================================
 * PARTIAL: Aba Promoções (Orquestrador)
 *
 * Arquivo refatorado que inclui:
 * - Promoções de Itens Individuais (novo)
 * - Combos Promocionais (existente)
 *
 * O JavaScript foi extraído para:
 * - combos-ui.js
 * - promo-products.js
 * ============================================
 */
?>

<?php // Formulário para Adicionar Item em Promoção (novo)?>
<?php \App\Core\View::renderFromScope('admin/cardapio/partials/_promo_product_form.php', get_defined_vars()); ?>

<?php // Lista de Itens em Promoção (novo)?>
<?php \App\Core\View::renderFromScope('admin/cardapio/partials/_promo_product_list.php', get_defined_vars()); ?>


<?php // Seção de Combos (título e separador removidos a pedido) ?>

<?php // Formulário de Criação/Edição de Combo?>
<?php \App\Core\View::renderFromScope('admin/cardapio/partials/_combo_form.php', get_defined_vars()); ?>

<?php // Lista de Combos Ativos?>
<?php \App\Core\View::renderFromScope('admin/cardapio/partials/_combo_list.php', get_defined_vars()); ?>

<style>
/* Estilos Específicos para Abas do Combo */
.combo-tab-btn.active {
    background: #fff !important;
    color: #3b82f6 !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.combo-product-card.selected {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}
.combo-product-card.selected .check-indicator {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
}
.combo-product-card.selected .check-indicator i {
    opacity: 1 !important;
}
</style>
