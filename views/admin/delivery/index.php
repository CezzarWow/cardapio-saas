<?php
/**
 * ============================================
 * DELIVERY ADMIN — View Principal (Orquestrador)
 * Layout Kanban (4 colunas)
 * ============================================
 */
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());

$statusFilter = $_GET['status'] ?? null;
?>

<!-- CSS do Delivery -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/kanban.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/cards-compact.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/modals.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/states.css">

<main class="main-content">
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
</main>

<!-- Modais -->
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/order_details.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/cancel_order.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/print_slip.php', get_defined_vars()); ?>

<!-- JS do Delivery -->
<script>
    const BASE_URL = '<?= BASE_URL ?>';
</script>
<!-- Constantes e helpers compartilhados (carregar PRIMEIRO) -->
<script src="<?= BASE_URL ?>/js/delivery/helpers.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/constants.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/tabs.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/actions.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/ui.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/polling.js?v=<?= time() ?>"></script>

<!-- DeliveryPrint Modules (carregar SUB-MÓDULOS primeiro) -->
<script src="<?= BASE_URL ?>/js/delivery/print-helpers.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/print-generators.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/print-modal.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/print-actions.js?v=<?= time() ?>"></script>
<!-- Orquestrador (carregar POR ÚLTIMO) -->
<script src="<?= BASE_URL ?>/js/delivery/print.js?v=<?= time() ?>"></script>
<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // Auto-filtro se vier da URL (ex: do histórico)
    const urlParams = new URLSearchParams(window.location.search);
    const statusParam = urlParams.get('status');
    if(statusParam && window.DeliveryTabs) {
        DeliveryTabs.filter(statusParam);
    }
</script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
