<?php
/**
 * Delivery Partial - Para carregamento no SPA Shell
 * 
 * Esta partial é carregada via AJAX pelo AdminSPA.
 * Contém o Kanban de pedidos e lógica de polling.
 * 
 * Variáveis recebidas do controller:
 * - $orders (Orders collection)
 * - $statusFilter (filtro opcional)
 * - $settings (configurações da loja)
 */
?>

<!-- CSS do Delivery -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/kanban.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/cards-compact.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/modals.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/states.css">

<div class="delivery-container">
    
    <!-- Header -->
    <div class="delivery-header">
        <h1 class="delivery-title">
            <i data-lucide="bike"></i>
            Delivery
        </h1>
        <div class="delivery-counter">
            <i data-lucide="package" style="width: 18px; height: 18px;"></i>
            <span id="delivery-count"><?= count($orders ?? []) ?></span> pedidos
        </div>
    </div>

    <!-- Filtros -->
    <?php \App\Core\View::renderFromScope('admin/delivery/partials/filters.php', get_defined_vars()); ?>

    <!-- Lista Kanban -->
    <?php \App\Core\View::renderFromScope('admin/delivery/partials/order_list_kanban.php', get_defined_vars()); ?>

</div>

<!-- Modais -->
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/order_details.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/cancel_order.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/print_slip.php', get_defined_vars()); ?>

<!-- Script para passar constantes PHP para JS -->
<script>
    window.DeliveryConfig = {
        BASE_URL: '<?= BASE_URL ?>',
        initialStatusFilter: '<?= $statusFilter ?? '' ?>'
    };
</script>

<!-- Delivery Bundle (11 scripts combinados) -->
<script data-spa-script="delivery-bundle" src="<?= BASE_URL ?>/js/bundles/delivery-bundle.js?v=<?= APP_VERSION ?>"></script>

<!-- 
    [BACKUP] Scripts originais para rollback:
<script data-spa-script="delivery-helpers" src="<?= BASE_URL ?>/js/delivery/helpers.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-constants" src="<?= BASE_URL ?>/js/delivery/constants.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-tabs" src="<?= BASE_URL ?>/js/delivery/tabs.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-actions" src="<?= BASE_URL ?>/js/delivery/actions.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-ui" src="<?= BASE_URL ?>/js/delivery/ui.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-polling" src="<?= BASE_URL ?>/js/delivery/polling.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-print-helpers" src="<?= BASE_URL ?>/js/delivery/print-helpers.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-print-generators" src="<?= BASE_URL ?>/js/delivery/print-generators.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-print-modal" src="<?= BASE_URL ?>/js/delivery/print-modal.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-print-actions" src="<?= BASE_URL ?>/js/delivery/print-actions.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-print-main" src="<?= BASE_URL ?>/js/delivery/print.js?v=<?= APP_VERSION ?>"></script>
-->
