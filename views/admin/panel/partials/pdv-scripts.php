<?php
/**
 * PDV-SCRIPTS.PHP - Scripts JavaScript do PDV
 * 
 * Contém: Variáveis globais JS e inclusão de scripts modulares
 * Variáveis esperadas: $cartRecovery, $isEditingPaid, $originalPaidTotalFromDB, $editingOrderId, $deliveryFee
 */
?>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    // Injeta o carrinho recuperado do PHP para o JS
    const recoveredCart = <?= json_encode($cartRecovery ?? []) ?>;
    
    // Modo edição de pedido PAGO (para cobrar só a diferença)
    const isEditingPaidOrder = <?= ($isEditingPaid ?? false) ? 'true' : 'false' ?>;
    const originalPaidTotal = <?= $originalPaidTotalFromDB ?? 0 ?>;
    const editingPaidOrderId = <?= $editingOrderId ?? 'null' ?>;
    
    // [NOVO] Taxa de entrega configurada
    const PDV_DELIVERY_FEE = <?= $deliveryFee ?>;
</script>

<?php require __DIR__ . '/extras-modal.php'; ?>

<!-- Scripts do PDV -->
<script src="<?= BASE_URL ?>/js/pdv/state.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart-core.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart-ui.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart-extras-modal.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables-mesa.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables-cliente.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables-client-modal.js?v=<?= time() ?>"></script>

<!-- Módulos de Checkout (ordem de dependência obrigatória) -->
<script src="<?= BASE_URL ?>/js/pdv/checkout/helpers.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/state.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/totals.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/ui.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/payments.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/services/checkout-service.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/services/checkout-validator.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/submit.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/orderType.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/retirada.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/entrega.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/pickup.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/flow.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/index.js?v=<?= time() ?>"></script>

<script src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>
