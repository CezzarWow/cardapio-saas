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
    'cancelado' => ['label' => 'Cancelado', 'icon' => 'x-circle'],
];

// Define quais colunas mostrar inicialmente baseado no filtro PHP (fallback)
// Se não houver filtro, mostra todas MENOS cancelado (via style no loop)
$statusFilter = $statusFilter ?? 'todos';
?>

<div class="delivery-kanban">
    <?php foreach ($columns as $status => $col):
        // Lógica de exibição inicial:
        // - Se filtro for 'todos' (ou nulo): mostra tudo MENOS cancelado
        // - Se filtro for específico: mostra apenas ele (já tratado pelo JS, mas aqui garantimos o render inicial correto)
        $style = 'display: flex;';

        if ($statusFilter === 'todos' || $statusFilter === null) {
            if ($status === 'cancelado') {
                $style = 'display: none;';
            }
        } else {
            if ($status !== $statusFilter) {
                $style = 'display: none;';
            }
        }
        ?>
        <div class="delivery-column delivery-column--<?= \App\Helpers\ViewHelper::e($status) ?>" style="<?= \App\Helpers\ViewHelper::e($style) ?>">
            
            <div class="delivery-column-header">
                <span class="delivery-column-title">
                    <i data-lucide="<?= \App\Helpers\ViewHelper::e($col['icon']) ?>" style="width: 18px; height: 18px;"></i>
                    <?= \App\Helpers\ViewHelper::e($col['label']) ?>
                </span>
                <span class="delivery-column-count"><?= (int) count($ordersByStatus[$status] ?? []) ?></span>
            </div>

            <div class="delivery-column-body">
                <?php if (empty($ordersByStatus[$status])): ?>
                    <div class="delivery-column-empty">
                        <i data-lucide="inbox" style="width: 32px; height: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
                        <br>Nenhum pedido
                    </div>
                <?php else: ?>
                    <?php foreach ($ordersByStatus[$status] as $order): ?>
                        <?php \App\Core\View::renderFromScope('admin/delivery/partials/order_card_compact.php', get_defined_vars()); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>
</div>
