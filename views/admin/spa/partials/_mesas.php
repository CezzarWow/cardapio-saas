<?php
/**
 * Mesas Partial - Para carregamento no SPA Shell
 * 
 * Esta partial é carregada via AJAX pelo AdminSPA.
 * Contém o Grid de Mesas e Comandas.
 * 
 * Variáveis recebidas do controller:
 * - $tables (Lista de mesas)
 * - $clientOrders (Comandas de clientes)
 */
?>

<div style="padding: 2rem; width: 100%; height: 100%; overflow-y: auto; padding-bottom: 100px;">
    
    <!-- Header Mesas -->
    <?php \App\Core\View::renderFromScope('admin/tables/partials/header_mesas.php', get_defined_vars()); ?>

    <!-- Grid Mesas -->
    <?php \App\Core\View::renderFromScope('admin/tables/partials/grid_mesas.php', get_defined_vars()); ?>

    <!-- Header Comandas -->
    <?php \App\Core\View::renderFromScope('admin/tables/partials/header_comandas.php', get_defined_vars()); ?>

    <!-- Grid Comandas -->
    <?php \App\Core\View::renderFromScope('admin/tables/partials/grid_comandas.php', get_defined_vars()); ?>

</div>

<!-- Modais -->
<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/nova_mesa.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/remover_mesa.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/cliente.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/dossie.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/tables/partials/modals/pedido_pago.php', get_defined_vars()); ?>

<script>
    window.TablesConfig = {
        BASE_URL: '<?= BASE_URL ?>'
    };
</script>

<!-- Scripts de Mesas -->
<!-- Carregados sequencialmente via AdminSPA -->

<!-- Dependências de Cliente -->
<script data-spa-script="shared-masks" src="<?= BASE_URL ?>/js/shared/masks.js?v=<?= time() ?>"></script>
<script data-spa-script="admin-client-validator" src="<?= BASE_URL ?>/js/admin/client-validator.js?v=<?= time() ?>"></script>
<script data-spa-script="admin-clientes" src="<?= BASE_URL ?>/js/admin/clientes.js?v=<?= time() ?>"></script>

<!-- Módulos TablesAdmin -->
<script data-spa-script="tables-helpers" src="<?= BASE_URL ?>/js/admin/tables-helpers.js?v=<?= time() ?>"></script>
<script data-spa-script="tables-crud" src="<?= BASE_URL ?>/js/admin/tables-crud.js?v=<?= time() ?>"></script>
<script data-spa-script="tables-clients" src="<?= BASE_URL ?>/js/admin/tables-clients.js?v=<?= time() ?>"></script>
<script data-spa-script="tables-paid-orders" src="<?= BASE_URL ?>/js/admin/tables-paid-orders.js?v=<?= time() ?>"></script>
<script data-spa-script="tables-dossier" src="<?= BASE_URL ?>/js/admin/tables-dossier.js?v=<?= time() ?>"></script>
<script data-spa-script="tables-main" src="<?= BASE_URL ?>/js/admin/tables.js?v=<?= time() ?>"></script>
