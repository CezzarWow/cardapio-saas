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

// Calcula totais
$totalPedidos = count($orders);
$totalValor = 0;     // Somente Entregues
$totalCancelado = 0; // Somente Cancelados

foreach ($orders as $order) {
    $st = $order['status'] ?? 'novo';
    $val = floatval($order['total'] ?? 0);
    
    if ($st === 'entregue') {
        $totalValor += $val;
    } elseif ($st === 'cancelado') {
        $totalCancelado += $val;
    }
}

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

<style>
.history-container { padding: 15px; }
.history-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
.history-title { font-size: 1.3rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px; }

/* Navegação antiga (pode ser removida futuramente, mas mantendo para segurança) */
.history-nav { display: flex; gap: 8px; }
.history-nav a { padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.2s; font-size: 0.9rem; }
.history-nav a.active { background: #1e293b; color: white; }
.history-nav a:not(.active) { background: #f1f5f9; color: #64748b; }
.history-nav a:not(.active):hover { background: #e2e8f0; }

.history-filter { background: white; padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.history-filter label { font-weight: 600; color: #475569; font-size: 0.9rem; }
.history-filter input[type="date"] { padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; font-weight: 600; }
.history-filter button { padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.9rem; }

.history-period { background: #eff6ff; padding: 10px 15px; border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; color: #1e40af; font-weight: 500; font-size: 0.9rem; }
.history-closed { background: #fef2f2; color: #dc2626; }

.history-summary { display: flex; gap: 12px; margin-bottom: 12px; }
.history-summary-card { flex: 1; background: white; padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center; }
.history-summary-value { font-size: 1.5rem; font-weight: 800; color: #1e293b; }
.history-summary-label { font-size: 0.8rem; color: #64748b; }

.history-table-wrapper { max-height: calc(100vh - 280px); overflow-y: auto; border-radius: 10px; border: 1px solid #e2e8f0; width: 100%; }
.history-table { width: 100%; background: white; border-collapse: collapse; table-layout: fixed; }
.history-table th { background: #f8fafc; padding: 12px 15px; text-align: left; font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; position: sticky; top: 0; }
.history-table td { padding: 12px 15px; border-top: 1px solid #f1f5f9; font-size: 0.95rem; }
.history-table tr:hover { background: #f8fafc; }
.history-badge { padding: 4px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; display: inline-block; }
.history-empty { text-align: center; padding: 50px; color: #94a3b8; background: white; }
</style>

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
<script src="<?= BASE_URL ?>/js/delivery/print.js?v=<?= time() ?>"></script>

<!-- Área de impressão (oculta) -->
<div id="print-area" style="display: none;"></div>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
