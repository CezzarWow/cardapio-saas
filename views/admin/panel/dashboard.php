<?php
/**
 * DASHBOARD.PHP - Orquestrador do PDV
 *
 * Este arquivo inicializa variáveis e orquestra os partials.
 * Os blocos de UI estão em arquivos separados:
 * - partials/pdv-header.php (Banners + Header)
 * - partials/pdv-products.php (Grid de Produtos)
 * - partials/pdv-cart-sidebar.php (Sidebar do Carrinho)
 * - partials/pdv-scripts.php (Scripts JS)
 *
 * ORDEM DE CARGA:
 * 1. Layout (header, sidebar)
 * 2. Variáveis PHP
 * 3. Partials de UI
 * 4. Modais
 * 5. Scripts
 * 6. Footer
 */

\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<main class="main-content">
    <section class="catalog-section">

        <?php
          // Variáveis já inicializadas no PdvController
          // ($isEditingPaid, $originalPaidTotalFromDB, $deliveryFee, etc)
?>

        <?php // HEADER (Banners + Título + Busca)?>
        <?php \App\Core\View::renderFromScope('admin/panel/partials/pdv-header.php', get_defined_vars()); ?>

        <?php // GRID DE PRODUTOS?>
        <?php \App\Core\View::renderFromScope('admin/panel/partials/pdv-products.php', get_defined_vars()); ?>

    </section>

    <input type="hidden" id="current_table_id" value="<?= $mesa_id ?? '' ?>">
    <input type="hidden" id="current_table_number" value="<?= $mesa_numero ?? '' ?>">
    <input type="hidden" id="current_order_id" value="<?= $contaAberta['id'] ?? '' ?>">
    <input type="hidden" id="current_client_id" value="<?= $contaAberta['client_id'] ?? '' ?>">
    <input type="hidden" id="current_client_name" value="<?= $contaAberta['client_name'] ?? '' ?>">
    <input type="hidden" id="table-initial-total" value="<?= $contaAberta['total'] ?? 0 ?>">

    <?php // SIDEBAR DO CARRINHO?>
    <?php \App\Core\View::renderFromScope('admin/panel/partials/pdv-cart-sidebar.php', get_defined_vars()); ?>

</main>

<?php // MODAIS?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/success-modal.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/checkout-modal.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/client-modal.php', get_defined_vars()); ?>

<?php // SCRIPTS?>
<?php \App\Core\View::renderFromScope('admin/panel/partials/pdv-scripts.php', get_defined_vars()); ?>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
