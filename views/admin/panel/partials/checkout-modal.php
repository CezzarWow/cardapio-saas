<?php
/**
 * ============================================
 * MODAL CHECKOUT - Partial do PDV (Orquestrador)
 *
 * Arquivo refatorado que inclui:
 * - _checkout_main.php (Área principal)
 * - _checkout_order_type.php (Tipo de pedido)
 * - _checkout_footer.php (Botões de ação)
 * - _checkout_delivery_panel.php (Painel de entrega)
 *
 * VARIÁVEIS REQUERIDAS NO ESCOPO:
 * - $isEditingPaid (bool) - Modo edição de pedido pago
 * - $contaAberta (array|null) - Dados da conta aberta
 * - $deliveryFee (float) - Taxa de entrega
 * ============================================
 */
?>

<div id="checkoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center;">
    
    <!-- Container Flex: Checkout + Painel Entrega -->
    <div style="display: flex; gap: 0; align-items: stretch;">
        
        <?php // Checkout Principal?>
        <?php \App\Core\View::renderFromScope('admin/panel/partials/_checkout_main.php', get_defined_vars()); ?>
        
        <?php // Painel Lateral de Entrega?>
        <?php \App\Core\View::renderFromScope('admin/panel/partials/_checkout_delivery_panel.php', get_defined_vars()); ?>
        
    </div>
    <!-- FIM Container Flex -->
    
</div>
