<?php
/**
 * ============================================
 * DELIVERY — Histórico de Pedidos
 * Filtro por dia operacional
 * ============================================
 */
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 

// Formata datas para exibição
$displayDate = date('d/m/Y', strtotime($selectedDate));
$dayName = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'][date('w', strtotime($selectedDate))];

// Status labels
$statusLabels = [
    'novo' => ['label' => 'Novo', 'color' => '#3b82f6'],
    'preparo' => ['label' => 'Preparo', 'color' => '#8b5cf6'],
    'rota' => ['label' => 'Em Rota', 'color' => '#22c55e'],
    'entregue' => ['label' => 'Entregue', 'color' => '#059669'],
    'cancelado' => ['label' => 'Cancelado', 'color' => '#dc2626'],
];
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
                <span style="font-size: 1.3rem; font-weight: 800; color: #059669;">R$ <?= number_format($totalValor, 2, ',', '.') ?></span>
            </div>

            <!-- 4. Valor Cancelado -->
            <div class="history-summary-card" style="margin-bottom: 0; flex: 1; min-width: 180px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span style="color: #64748b; font-weight: 600;">Cancelado:</span>
                <span style="font-size: 1.3rem; font-weight: 800; color: #dc2626;">R$ <?= number_format($totalCancelado, 2, ',', '.') ?></span>
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
                        <?php foreach ($orders as $order): 
                            $status = $order['status'] ?? 'novo';
                            $statusInfo = $statusLabels[$status] ?? ['label' => $status, 'color' => '#64748b'];
                        ?>
                            <tr onclick="HistoryModal.open(<?= $order['id'] ?>)" style="cursor: pointer;">
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= htmlspecialchars($order['client_name'] ?? 'Cliente') ?></td>
                                <td><?= date('H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <span class="history-badge" style="background: <?= $statusInfo['color'] ?>20; color: <?= $statusInfo['color'] ?>;">
                                        <?= $statusInfo['label'] ?>
                                    </span>
                                </td>
                                <td><?= ucfirst($order['payment_method'] ?? '-') ?></td>
                                <td><strong>R$ <?= number_format($order['total'] ?? 0, 2, ',', '.') ?></strong></td>
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
