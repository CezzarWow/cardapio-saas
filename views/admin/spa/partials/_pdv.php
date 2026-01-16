<?php
/**
 * PDV Partial - Para carregamento no SPA Shell
 * 
 * Substitui views/admin/panel/dashboard.php
 */

// Garante que variáveis existam
$cartRecovery = $cartRecovery ?? [];
$isEditingPaid = $isEditingPaid ?? false;
$originalPaidTotalFromDB = $originalPaidTotalFromDB ?? 0;
$editingOrderId = $editingOrderId ?? null;
$deliveryFee = $deliveryFee ?? 0;
$mesa_id = $mesa_id ?? 0;
$contaAberta = $contaAberta ?? [];
$itensJaPedidos = $itensJaPedidos ?? [];

// Configuração serializada para o JS ler
$pdvConfig = [
    'baseUrl' => BASE_URL,
    'recoveredCart' => $cartRecovery,
    'isEditingPaidOrder' => $isEditingPaid,
    'originalPaidTotal' => $originalPaidTotalFromDB,
    'editingPaidOrderId' => $editingOrderId,
    'deliveryFee' => $deliveryFee,
    'tableId' => $mesa_id
];
?>

<div id="pdv-container" class="pdv-wrapper" style="height: 100%; display: flex; overflow: hidden;">
    
    <!-- Configuração JSON escondida -->
    <div id="pdv-config" style="display:none;" data-config='<?= json_encode($pdvConfig, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'></div>
    
    <!-- Inputs Hidden de Estado (Compatibilidade legado) -->
    <input type="hidden" id="current_table_id" value="<?= $mesa_id ?: '' ?>">
    <input type="hidden" id="current_table_number" value="<?= $mesa_numero ?? '' ?>">
    <input type="hidden" id="current_order_id" value="<?= $contaAberta['id'] ?? '' ?>">
    <input type="hidden" id="current_client_id" value="<?= $contaAberta['client_id'] ?? '' ?>">
    <input type="hidden" id="current_client_name" value="<?= $contaAberta['client_name'] ?? '' ?>">
    <input type="hidden" id="table-initial-total" value="<?= $contaAberta['total'] ?? 0 ?>">


    <!-- ÁREA PRINCIPAL (Esquerda) -->
    <section class="catalog-section" style="flex: 1; display: flex; flex-direction: column; overflow-y: auto;">
        
        <?php // HEADER (Banners + Título + Busca) ?>
        <?php \App\Core\View::renderFromScope('admin/panel/partials/pdv-header.php', get_defined_vars()); ?>

        <!-- Categorias e Produtos -->
        <?php \App\Core\View::renderFromScope('admin/panel/partials/pdv-products.php', get_defined_vars()); ?>

    </section>

    <!-- SIDEBAR CARRINHO (Direita) -->
    <?php \App\Core\View::renderFromScope('admin/panel/partials/pdv-cart-sidebar.php', get_defined_vars()); ?>

</div>

<!-- MODAIS -->
<?php \App\Core\View::renderFromScope('admin/panel/partials/success-modal.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/checkout-modal.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/client-modal.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/extras-modal.php', get_defined_vars()); ?>


<!-- SCRIPTS do PDV -->
<!-- Carregados sequencialmente via AdminSPA -->

<!-- Core: State e Carrinho -->
<script data-spa-script="pdv-state" src="<?= BASE_URL ?>/js/pdv/state.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-extras" src="<?= BASE_URL ?>/js/pdv/pdv-extras.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-cart" src="<?= BASE_URL ?>/js/pdv/pdv-cart.js?v=<?= time() ?>"></script>

<!-- Tables: Mesas e Clientes -->
<script data-spa-script="pdv-tables" src="<?= BASE_URL ?>/js/pdv/tables.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-tables-mesa" src="<?= BASE_URL ?>/js/pdv/tables-mesa.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-tables-cliente" src="<?= BASE_URL ?>/js/pdv/tables-cliente.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-tables-client-modal" src="<?= BASE_URL ?>/js/pdv/tables-client-modal.js?v=<?= time() ?>"></script>

<!-- Ações e Ficha -->
<script data-spa-script="pdv-order-actions" src="<?= BASE_URL ?>/js/pdv/order-actions.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-ficha" src="<?= BASE_URL ?>/js/pdv/ficha.js?v=<?= time() ?>"></script>

<!-- Checkout: Módulos de Pagamento -->
<script data-spa-script="checkout-helpers" src="<?= BASE_URL ?>/js/pdv/checkout/helpers.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-state" src="<?= BASE_URL ?>/js/pdv/checkout/state.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-totals" src="<?= BASE_URL ?>/js/pdv/checkout/totals.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-ui" src="<?= BASE_URL ?>/js/pdv/checkout/ui.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-payments" src="<?= BASE_URL ?>/js/pdv/checkout/payments.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-service" src="<?= BASE_URL ?>/js/pdv/checkout/services/checkout-service.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-validator" src="<?= BASE_URL ?>/js/pdv/checkout/services/checkout-validator.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-adjust" src="<?= BASE_URL ?>/js/pdv/checkout/adjust.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-submit" src="<?= BASE_URL ?>/js/pdv/checkout/submit.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-orderType" src="<?= BASE_URL ?>/js/pdv/checkout/orderType.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-retirada" src="<?= BASE_URL ?>/js/pdv/checkout/retirada.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-entrega" src="<?= BASE_URL ?>/js/pdv/checkout/entrega.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-flow" src="<?= BASE_URL ?>/js/pdv/checkout/flow.js?v=<?= time() ?>"></script>
<script data-spa-script="checkout-index" src="<?= BASE_URL ?>/js/pdv/checkout/index.js?v=<?= time() ?>"></script>

<!-- Orquestrador Principal -->
<script data-spa-script="pdv-events" src="<?= BASE_URL ?>/js/pdv/pdv-events.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-search" src="<?= BASE_URL ?>/js/pdv/pdv-search.js?v=<?= time() ?>"></script>
<script data-spa-script="pdv-main" src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>




