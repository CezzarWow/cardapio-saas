<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; height: 100vh; overflow-y: auto; padding-bottom: 100px;">
        
        <?php require __DIR__ . '/partials/header_mesas.php'; ?>

        <?php require __DIR__ . '/partials/grid_mesas.php'; ?>

        <?php require __DIR__ . '/partials/header_comandas.php'; ?>

        <?php require __DIR__ . '/partials/grid_comandas.php'; ?>

    </div>
</main>

<?php require __DIR__ . '/partials/modals/nova_mesa.php'; ?>

<?php require __DIR__ . '/partials/modals/remover_mesa.php'; ?>

<?php require __DIR__ . '/partials/modals/cliente.php'; ?>

<script>const BASE_URL = '<?= BASE_URL ?>';</script>

<!-- Cliente Modules (ordem: dependências primeiro) -->
<script src="<?= BASE_URL ?>/js/shared/masks.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/client-validator.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/clientes.js?v=<?= time() ?>"></script>

<!-- TablesAdmin Modules (carregar HELPERS primeiro) -->
<script src="<?= BASE_URL ?>/js/admin/tables-helpers.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/tables-crud.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/tables-clients.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/tables-paid-orders.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/tables-dossier.js?v=<?= time() ?>"></script>
<!-- Orquestrador (carregar POR ÚLTIMO) -->
<script src="<?= BASE_URL ?>/js/admin/tables.js?v=<?= time() ?>"></script>


<?php require __DIR__ . '/partials/modals/dossie.php'; ?>

<?php require __DIR__ . '/partials/modals/pedido_pago.php'; ?>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
