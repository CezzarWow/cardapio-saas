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

<div class="spa-padded-container" style="padding-bottom: 100px;">
    
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

<!-- Mesas Bundle -->
<script data-spa-script="mesas-bundle" src="<?= BASE_URL ?>/js/bundles/mesas-bundle.js?v=<?= APP_VERSION ?>"></script>

<!-- Modal de Impressão (compartilhado) -->
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/print_slip.php', get_defined_vars()); ?>

<!-- QZ Tray Library (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>

<!-- Print Animation (Shared) -->
<script data-spa-script="print-animation" src="<?= BASE_URL ?>/js/shared/print-animation.js?v=<?= APP_VERSION ?>"></script>

<!-- Print Bundle (QZ Tray + Ficha Térmica) -->
<script data-spa-script="print-bundle" src="<?= BASE_URL ?>/js/bundles/print-bundle.js?v=<?= APP_VERSION ?>"></script>

<!-- Modal de Detalhes de Delivery (compartilhado) -->
<script data-spa-script="delivery-details" src="<?= BASE_URL ?>/js/shared/delivery-details-modal.js?v=<?= APP_VERSION ?>"></script>
