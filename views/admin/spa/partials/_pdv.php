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

<!-- CSS necessário para modal de impressão -->
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/modals.css?v=<?= time() ?>">

<div id="pdv-container" class="pdv-wrapper" style="height: 100%; display: flex; overflow: hidden;">
    
    <!-- Configuração JSON escondida -->
    <div id="pdv-config" style="display:none;" data-config='<?= \App\Helpers\ViewHelper::e(\App\Helpers\ViewHelper::js($pdvConfig)) ?>'></div>
    
    <!-- Inputs Hidden de Estado (Compatibilidade legado) -->
    <input type="hidden" id="current_table_id" value="<?= (int) ($mesa_id ?: 0) ?>">
    <input type="hidden" id="current_table_number" value="<?= \App\Helpers\ViewHelper::e($mesa_numero ?? '') ?>">
    <input type="hidden" id="current_order_id" value="<?= (int) ($contaAberta['id'] ?? 0) ?>">
    <input type="hidden" id="current_client_id" value="<?= (int) ($contaAberta['client_id'] ?? 0) ?>">
    <input type="hidden" id="current_client_name" value="<?= \App\Helpers\ViewHelper::e($contaAberta['client_name'] ?? '') ?>">
    <input type="hidden" id="table-initial-total" value="<?= (float) ($contaAberta['total'] ?? 0) ?>">


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
<?php \App\Core\View::renderFromScope('admin/panel/partials/print-modal.php', get_defined_vars()); ?>

<!-- Script de impressão do PDV -->
<!-- QZ Tray Library (CDN) para impressão térmica silenciosa -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
<!-- Print Bundle (Shared with Delivery) -->
<script data-spa-script="print-bundle" src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/bundles/print-bundle.js?v=<?= time() ?>"></script>

<!-- PDV Print Modal Controller (Only for PDV) -->
<script data-spa-script="pdv-print" src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/pdv/pdv-print.js?v=<?= \App\Helpers\ViewHelper::e(APP_VERSION) ?>"></script>


<!-- PDV Bundle (Reativado após build) -->
<script data-spa-script="pdv-bundle" src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/bundles/pdv-bundle.js?v=<?= time() ?>"></script>

