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

require __DIR__ . '/layout/header.php'; 
require __DIR__ . '/layout/sidebar.php'; 
?>

<main class="main-content">
    <section class="catalog-section">

<?php 
// ==========================================
// VARIÁVEIS DO PDV
// ==========================================

// Detecta modo edição de pedido PAGO (Retirada)
$isEditingPaid = isset($_GET['edit_paid']) && $_GET['edit_paid'] == '1';
$editingOrderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

// Se está editando pedido pago, busca o total original do banco
$originalPaidTotalFromDB = 0;
if ($isEditingPaid && $editingOrderId) {
    $conn = \App\Core\Database::connect();
    $stmt = $conn->prepare("SELECT total FROM orders WHERE id = :oid");
    $stmt->execute(['oid' => $editingOrderId]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
    $originalPaidTotalFromDB = floatval($orderData['total'] ?? 0);
}

// Carrega taxa de entrega do cardápio
$deliveryFee = 5.0; // default
$restaurantId = $_SESSION['loja_ativa_id'] ?? null;
if ($restaurantId) {
    $settingsPath = __DIR__ . '/../../../data/restaurants/' . $restaurantId . '/cardapio_settings.json';
    if (file_exists($settingsPath)) {
        $settings = json_decode(file_get_contents($settingsPath), true);
        $deliveryFee = floatval($settings['delivery_fee'] ?? 5.0);
    }
}
?>

        <?php // HEADER (Banners + Título + Busca) ?>
        <?php require __DIR__ . '/partials/pdv-header.php'; ?>

        <?php // GRID DE PRODUTOS ?>
        <?php require __DIR__ . '/partials/pdv-products.php'; ?>

    </section>

    <input type="hidden" id="current_table_id" value="<?= $mesa_id ?? '' ?>">
    <input type="hidden" id="current_table_number" value="<?= $mesa_numero ?? '' ?>">

    <?php // SIDEBAR DO CARRINHO ?>
    <?php require __DIR__ . '/partials/pdv-cart-sidebar.php'; ?>

</main>

<?php // MODAIS ?>
<?php require __DIR__ . '/partials/success-modal.php'; ?>
<?php require __DIR__ . '/partials/checkout-modal.php'; ?>
<?php require __DIR__ . '/partials/client-modal.php'; ?>

<?php // SCRIPTS ?>
<?php require __DIR__ . '/partials/pdv-scripts.php'; ?>

<?php require __DIR__ . '/layout/footer.php'; ?>
