<?php
/**
 * Partial: Lista Kanban de Pedidos
 * Variáveis: $orders, $statusFilter (opcional)
 * 5 colunas: Novo, Preparo, Rota, Entregue, Cancelado
 */

// Agrupa pedidos por status
$ordersByStatus = [
    'novo' => [],
    'preparo' => [],
    'rota' => [],
    'entregue' => [],
    'cancelado' => [],
];

foreach ($orders as $order) {
    $status = $order['status'] ?? 'novo';
    if (isset($ordersByStatus[$status])) {
        $ordersByStatus[$status][] = $order;
    }
}

$columns = [
    'novo' => ['label' => 'Novo Pedido', 'icon' => 'inbox'],
    'preparo' => ['label' => 'Em Preparo', 'icon' => 'chef-hat'],
    'rota' => ['label' => 'Em Rota / Retirada', 'icon' => 'bike'],
    'entregue' => ['label' => 'Entregue', 'icon' => 'check-circle'],
    // 'cancelado' removido do Kanban padrão - aparece apenas via filtro
];

// Coluna cancelado só aparece se filtrado diretamente
$statusFilter = $statusFilter ?? null;
if ($statusFilter === 'cancelado') {
    $showColumns = [
        'cancelado' => ['label' => 'Cancelado', 'icon' => 'x-circle'],
    ];
} else {
    $showColumns = $columns;
}
?>

<div class="delivery-kanban">
    <?php foreach ($showColumns as $status => $col): ?>
        <div class="delivery-column delivery-column--<?= $status ?>">
            
            <div class="delivery-column-header">
                <span class="delivery-column-title">
                    <i data-lucide="<?= $col['icon'] ?>" style="width: 18px; height: 18px;"></i>
                    <?= $col['label'] ?>
                </span>
                <span class="delivery-column-count"><?= count($ordersByStatus[$status] ?? []) ?></span>
            </div>

            <div class="delivery-column-body">
                <?php if (empty($ordersByStatus[$status])): ?>
                    <div class="delivery-column-empty">
                        <i data-lucide="inbox" style="width: 32px; height: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
                        <br>Nenhum pedido
                    </div>
                <?php else: ?>
                    <?php foreach ($ordersByStatus[$status] as $order): ?>
                        <?php require __DIR__ . '/order_card_compact.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>
</div>
