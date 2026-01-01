<?php 
/**
 * ============================================
 * DELIVERY ADMIN — View Principal (Orquestrador)
 * Layout Kanban (4 colunas)
 * ============================================
 */
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 

$statusFilter = $_GET['status'] ?? null;
?>

<!-- CSS do Delivery -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/kanban.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/cards-compact.css?v=<?= time() ?>">
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
        <?php require __DIR__ . '/partials/filters.php'; ?>

        <!-- Lista Kanban -->
        <?php require __DIR__ . '/partials/order_list_kanban.php'; ?>

    </div>
</main>

<!-- Modais -->
<?php require __DIR__ . '/partials/modals/order_details.php'; ?>
<?php require __DIR__ . '/partials/modals/cancel_order.php'; ?>
<?php require __DIR__ . '/partials/modals/print_slip.php'; ?>

<!-- JS do Delivery -->
<script>
    const BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/js/delivery/tabs.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/actions.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/ui.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/polling.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/print.js?v=<?= time() ?>"></script>
<script>
    console.log('[Delivery] Kanban carregado');
    console.log('[Delivery] Pedidos:', <?= count($orders ?? []) ?>);
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
