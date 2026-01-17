<?php
/**
 * ============================================
 * MODAL CHECKOUT - Partial do PDV (Orquestrador)
 *
 * Arquivo refatorado que inclui:
 * - _checkout_main.php (Área principal)
 * - _checkout_footer.php (Botões de ação)
 *
 * VARIÁVEIS REQUERIDAS NO ESCOPO:
 * - $isEditingPaid (bool) - Modo edição de pedido pago
 * - $contaAberta (array|null) - Dados da conta aberta
 * - $deliveryFee (float) - Taxa de entrega
 * ============================================
 */
?>

<div id="checkoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center;">
    
    <?php // Checkout Principal ?>
    <?php \App\Core\View::renderFromScope('admin/panel/partials/_checkout_main.php', get_defined_vars()); ?>
    
</div>

<?php // Modal de Entrega (Independente - fora do checkout) ?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/_checkout_delivery_panel.php', get_defined_vars()); ?>
