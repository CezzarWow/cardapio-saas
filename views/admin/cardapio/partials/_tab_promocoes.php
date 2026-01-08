<?php
/**
 * ============================================
 * PARTIAL: Aba Promoções (Orquestrador)
 * 
 * Arquivo refatorado que inclui:
 * - _combo_form.php (Formulário de combo)
 * - _combo_list.php (Lista de combos ativos)
 * 
 * O JavaScript foi extraído para:
 * - combos-ui.js
 * ============================================
 */
?>

<?php // Formulário de Criação/Edição ?>
<?php require __DIR__ . '/_combo_form.php'; ?>

<?php // Lista de Combos Ativos ?>
<?php require __DIR__ . '/_combo_list.php'; ?>

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
