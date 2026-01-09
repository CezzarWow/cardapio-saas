<?php
/**
 * ============================================
 * DELIVERY — Histórico de Pedidos
 * Filtro por dia operacional
 * ============================================
 */
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 

// [VIEW CLEANUP] Lógica de datas e status movida para o Controller (ViewModel)
// Variáveis disponíveis: $displayDate, $dayName, $orders (com formatted_* e status_*), $total*Formatted
?>

<!-- CSS do Delivery -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/delivery/history.css">

<main class="main-content">
    <div class="history-container">
        
        <!-- Header com navegação -->
        <div class="history-header">
            <h1 class="history-title">
                <i data-lucide="history"></i>
                Histórico
            </h1>
        </div>

        <!-- Abas Unificadas (Filtros) -->
        <?php require __DIR__ . '/partials/filters.php'; ?>

        <!-- Barra superior: Filtro + Período + Totais -->
        <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; align-items: stretch;">
            
            <!-- 1. Filtro de data -->
            <form class="history-filter" method="GET" style="margin-bottom: 0; flex: 1; min-width: 200px; justify-content: center;">
                <label for="date" style="margin-right: -5px;">📅</label>
                <input type="date" name="date" id="date" value="<?= $selectedDate ?>" style="width: auto;">
                <button type="submit">Buscar</button>
            </form>

            <!-- 2. Pedidos -->
            <div class="history-summary-card" style="margin-bottom: 0; flex: 1; min-width: 150px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span style="color: #64748b; font-weight: 600;">Pedidos:</span>
                <span style="font-size: 1.3rem; font-weight: 800; color: #1e293b;"><?= $totalPedidos ?></span>
            </div>

            <!-- 3. Valor Total (Entregue) -->
            <div class="history-summary-card" style="margin-bottom: 0; flex: 1; min-width: 180px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span style="color: #64748b; font-weight: 600;">Receita:</span>
                <span style="font-size: 1.3rem; font-weight: 800; color: #059669;">R$ <?= $totalValorFormatted ?></span>
            </div>

            <!-- 4. Valor Cancelado -->
            <div class="history-summary-card" style="margin-bottom: 0; flex: 1; min-width: 180px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span style="color: #64748b; font-weight: 600;">Cancelado:</span>
                <span style="font-size: 1.3rem; font-weight: 800; color: #dc2626;">R$ <?= $totalCanceladoFormatted ?></span>
            </div>
        </div>

        <!-- Tabela de pedidos -->
        <?php if (empty($orders)): ?>
            <div class="history-table-wrapper">
                <div class="history-empty">
                    <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <br>Nenhum pedido neste dia
                </div>
            </div>
        <?php else: ?>
            <div class="history-table-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Horário</th>
                            <th>Status</th>
                            <th>Pagamento</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr onclick="HistoryModal.open(<?= $order['id'] ?>)" style="cursor: pointer;">
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= htmlspecialchars($order['client_name'] ?? 'Cliente') ?></td>
                                <td><?= $order['formatted_time'] ?></td>
                                <td>
                                    <span class="history-badge" style="background: <?= $order['status_bg_rgba'] ?>; color: <?= $order['status_color'] ?>;">
                                        <?= $order['status_label'] ?>
                                    </span>
                                </td>
                                <td><?= $order['payment_method_label'] ?></td>
                                <td><strong><?= $order['formatted_total'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Modal do Histórico -->
<?php require __DIR__ . '/partials/modals/history_details.php'; ?>

<!-- Modal de Impressão -->
<?php require __DIR__ . '/partials/modals/print_slip.php'; ?>

<!-- JS -->
<script>
    const BASE_URL = '<?= BASE_URL ?>';
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
<!-- DeliveryPrint Modules (carregar SUB-MÓDULOS primeiro) -->
<script src="<?= BASE_URL ?>/js/delivery/print-helpers.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/print-generators.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/print-modal.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/delivery/print-actions.js?v=<?= time() ?>"></script>
<!-- Orquestrador (carregar POR ÚLTIMO) -->
<script src="<?= BASE_URL ?>/js/delivery/print.js?v=<?= time() ?>"></script>

<!-- Área de impressão (oculta) -->
<div id="print-area" style="display: none;"></div>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
