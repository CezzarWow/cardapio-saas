<?php
/**
 * PDV-SCRIPTS.PHP - Scripts JavaScript do PDV
 *
 * Contém: Variáveis globais JS injetadas do PHP e inclusão de scripts modulares
 * Variáveis esperadas: $cartRecovery, $isEditingPaid, $originalPaidTotalFromDB, $editingOrderId, $deliveryFee, $mesa_id
 */
?>

<script>
    // ============================================
    // VARIÁVEIS GLOBAIS (Injetadas pelo PHP)
    // ============================================
    const BASE_URL = <?= \App\Helpers\ViewHelper::js(BASE_URL) ?>;
    
    // Carrinho recuperado do PHP (sessão ou banco)
    const recoveredCart = <?= json_encode($cartRecovery ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    
    // Modo edição de pedido PAGO (para cobrar só a diferença)
    const isEditingPaidOrder = <?= json_encode((bool) ($isEditingPaid ?? false)) ?>;
    const originalPaidTotal = <?= \App\Helpers\ViewHelper::js((float) ($originalPaidTotalFromDB ?? 0)) ?>;
    const editingPaidOrderId = <?= json_encode($editingOrderId ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    
    // Taxa de entrega configurada
    const PDV_DELIVERY_FEE = <?= \App\Helpers\ViewHelper::js((float) ($deliveryFee ?? 0)) ?>;
    
    // ID da Mesa (contexto)
    const PDV_TABLE_ID = <?= (int) ($mesa_id ?: 0) ?>;
</script>

<?php \App\Core\View::renderFromScope('admin/panel/partials/extras-modal.php', get_defined_vars()); ?>

<!-- ============================================ -->
<!-- MÓDULOS PDV (Ordem de Dependência) -->
<!-- ============================================ -->

<!-- Core: State e Carrinho -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/state.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/pdv-extras.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/pdv-cart.js?v=<?= time() ?>"></script>

<!-- Tables: Mesas e Clientes -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/tables.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/tables-mesa.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/tables-cliente.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/tables-client-modal.js?v=<?= time() ?>"></script>

<!-- Ações e Ficha -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/order-actions.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/ficha.js?v=<?= time() ?>"></script>

<!-- Checkout: Módulos de Pagamento (ordem de dependência obrigatória) -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/helpers.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/state.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/totals.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/ui.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/payments.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/services/checkout-service.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/services/checkout-validator.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/adjust.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/submit.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/orderType.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/retirada.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/entrega.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/flow.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/checkout/index.js?v=<?= time() ?>"></script>

<!-- Orquestrador Principal -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/pdv-events.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/pdv-search.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv.js?v=<?= time() ?>"></script>
