<?php
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; height: 100vh; overflow-y: auto; padding-bottom: 100px;">
        
        <?php \App\Core\View::renderFromScope('admin/tables/partials/header_mesas.php', get_defined_vars()); ?>

        <?php \App\Core\View::renderFromScope('admin/tables/partials/grid_mesas.php', get_defined_vars()); ?>

        <?php \App\Core\View::renderFromScope('admin/tables/partials/header_comandas.php', get_defined_vars()); ?>

        <?php \App\Core\View::renderFromScope('admin/tables/partials/grid_comandas.php', get_defined_vars()); ?>

    </div>
</main>

<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/nova_mesa.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/remover_mesa.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/cliente.php', get_defined_vars()); ?>

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


<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/dossie.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/pedido_pago.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
