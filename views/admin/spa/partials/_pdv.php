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
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/modals.css?v=<?= APP_VERSION ?>">

<div id="pdv-container" class="pdv-wrapper" style="height: 100%; display: flex; overflow: hidden;">
    
    <!-- Configuração JSON escondida -->
    <div id="pdv-config" style="display:none;" data-config='<?= json_encode($pdvConfig, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'></div>
    
    <!-- Inputs Hidden de Estado (Compatibilidade legado) -->
    <input type="hidden" id="current_table_id" value="<?= $mesa_id ?: '' ?>">
    <input type="hidden" id="current_table_number" value="<?= $mesa_numero ?? '' ?>">
    <input type="hidden" id="current_order_id" value="<?= $contaAberta['id'] ?? '' ?>">
    <input type="hidden" id="current_client_id" value="<?= $contaAberta['client_id'] ?? '' ?>">
    <input type="hidden" id="current_client_name" value="<?= $contaAberta['client_name'] ?? '' ?>">
    <input type="hidden" id="table-initial-total" value="<?= $contaAberta['total'] ?? 0 ?>">


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
<!-- Delivery Print Modules (reusados pelo PDV) -->
<script data-spa-script="delivery-print-helpers" src="<?= BASE_URL ?>/js/delivery/print-helpers.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-print-generators" src="<?= BASE_URL ?>/js/delivery/print-generators.js?v=<?= APP_VERSION ?>"></script>
<script data-spa-script="delivery-print-qz" src="<?= BASE_URL ?>/js/delivery/print-qz.js?v=<?= APP_VERSION ?>"></script>
<!-- PDV Print Modal Controller -->
<script data-spa-script="pdv-print" src="<?= BASE_URL ?>/js/pdv/pdv-print.js?v=<?= APP_VERSION ?>"></script>


<!-- PDV Bundle (Reativado após build) -->
<script data-spa-script="pdv-bundle" src="<?= BASE_URL ?>/js/bundles/pdv-bundle.js?v=<?= APP_VERSION ?>"></script>

