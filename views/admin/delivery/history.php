<?php
/**
 * ============================================
 * DELIVERY — Histórico de Pedidos
 * Filtro por dia operacional
 * ============================================
 */
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());

// [VIEW CLEANUP] Lógica de datas e status movida para o Controller (ViewModel)
// Variáveis disponíveis: $displayDate, $dayName, $orders (com formatted_* e status_*), $total*Formatted
?>

<!-- CSS do Delivery (cache bust) -->
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/base.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/history.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/css/delivery/modals.css?v=<?= time() ?>">

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
        <?php \App\Core\View::renderFromScope('admin/delivery/partials/filters.php', get_defined_vars()); ?>

        <!-- Barra superior: Filtro + Período + Totais -->
        <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; align-items: stretch;">
            
            <!-- 1. Filtro de data -->
            <form class="history-filter" method="GET" style="margin-bottom: 0; flex: 1; min-width: 200px; justify-content: center;">
                <label for="date" style="margin-right: -5px;">📅</label>
                <input type="date" name="date" id="date" value="<?= \App\Helpers\ViewHelper::e($selectedDate ?? '') ?>" style="width: auto;">
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
                            <?php
                                $orderId = (int) ($order['id'] ?? 0);
                                $statusBg = \App\Helpers\ViewHelper::cssColor($order['status_bg_rgba'] ?? '', 'rgba(148,163,184,0.35)');
                                $statusColor = \App\Helpers\ViewHelper::cssColor($order['status_color'] ?? '', '#0f172a');
                            ?>
                            <tr onclick="HistoryModal.open(<?= $orderId ?>)" style="cursor: pointer;">
                                <td><strong>#<?= $orderId ?></strong></td>
                                <td><?= htmlspecialchars($order['client_name'] ?? 'Cliente') ?></td>
                                <td><?= \App\Helpers\ViewHelper::e($order['formatted_time'] ?? '') ?></td>
                                <td>
                                    <span class="history-badge" style="background: <?= \App\Helpers\ViewHelper::e($statusBg) ?>; color: <?= \App\Helpers\ViewHelper::e($statusColor) ?>;">
                                        <?= \App\Helpers\ViewHelper::e($order['status_label'] ?? '') ?>
                                    </span>
                                </td>
                                <td><?= \App\Helpers\ViewHelper::e($order['payment_method_label'] ?? '') ?></td>
                                <td><strong><?= \App\Helpers\ViewHelper::e($order['formatted_total'] ?? '') ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Modal do Histórico -->
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/history_details.php', get_defined_vars()); ?>

<!-- Modal de Impressão -->
<?php \App\Core\View::renderFromScope('admin/delivery/partials/modals/print_slip.php', get_defined_vars()); ?>

<!-- JS -->
<script>
    const BASE_URL = <?= \App\Helpers\ViewHelper::js(BASE_URL) ?>;
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
<!-- Constantes e helpers compartilhados (carregar PRIMEIRO) -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/delivery/helpers.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/delivery/constants.js?v=<?= time() ?>"></script>
<!-- DeliveryPrint Modules (carregar SUB-MÓDULOS primeiro) -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/delivery/print-helpers.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/delivery/print-generators.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/delivery/print-modal.js?v=<?= time() ?>"></script>
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/delivery/print-actions.js?v=<?= time() ?>"></script>
<!-- Orquestrador (carregar POR ÚLTIMO) -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/delivery/print.js?v=<?= time() ?>"></script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
