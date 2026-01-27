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

<!-- CSS do Delivery (cache bust) -->
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/base.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/kanban.css?v=<?= \App\Helpers\ViewHelper::e(APP_VERSION) ?>">
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/cards-compact.css?v=<?= \App\Helpers\ViewHelper::e(APP_VERSION) ?>">
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/modals.css?v=<?= \App\Helpers\ViewHelper::e(APP_VERSION) ?>">
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/states.css">

<!-- Forçar alinhamento igual ao Histórico -->
<style>
    .delivery-container {
        padding: 15px 20px 80px 20px !important;
    }
    .delivery-header {
        margin-bottom: 15px !important;
        margin-top: 0 !important;
        padding: 0 !important;
        border-bottom: none !important;
        height: 31px !important;
        min-height: 31px !important;
        max-height: 31px !important;
    }
    .delivery-title {
        font-size: 1.3rem !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 31px !important;
    }
    .delivery-counter {
        padding: 4px 12px !important;
        font-size: 0.8rem !important;
    }
</style>

<div class="delivery-container">
    
    <!-- Header -->
    <div class="delivery-header">
        <h1 class="delivery-title">
            <i data-lucide="bike"></i>
            Delivery
        </h1>
        <div class="delivery-counter">
            <i data-lucide="package" style="width: 18px; height: 18px;"></i>
            <span id="delivery-count"><?= (int) count($orders ?? []) ?></span> pedidos
        </div>
    </div>

    <!-- Filtros -->
    <?php \App\Core\View::renderFromScope('admin/delivery/partials/filters.php', get_defined_vars()); ?>

    <!-- Lista Kanban -->
    <?php \App\Core\View::renderFromScope('admin/delivery/partials/order_list_kanban.php', get_defined_vars()); ?>

</div>

<!-- Rodapé Sutil Fixo -->
<div class="delivery-footer">
    <span class="delivery-footer-text">🛵 Área de Delivery</span>
</div>

<!-- Modais -->
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/cancel_order.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/print_slip.php', get_defined_vars()); ?>

<!-- Script para passar constantes PHP para JS -->
<script>
    window.DeliveryConfig = <?= \App\Helpers\ViewHelper::js([
        'BASE_URL' => BASE_URL,
        'initialStatusFilter' => $statusFilter ?? ''
    ]) ?>;
</script>

<!-- QZ Tray Library (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>

<!-- Print Bundle (Shared with PDV) -->
<script data-spa-script="print-bundle" src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/bundles/print-bundle.js?v=<?= time() ?>"></script>

<!-- Print Animation (Shared) -->
<script data-spa-script="print-animation" src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/shared/print-animation.js?v=<?= time() ?>"></script>

<!-- Delivery Bundle (12 scripts combinados) -->
<script data-spa-script="delivery-bundle" src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/bundles/delivery-bundle.js?v=<?= time() ?>"></script>

<!-- Modal de Detalhes (compartilhado com Mesas) -->
<script data-spa-script="delivery-details" src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/shared/delivery-details-modal.js?v=<?= time() ?>"></script>
