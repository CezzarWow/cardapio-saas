<?php
/**
 * Partial: Card individual de pedido Delivery
 * FASE 4: Com botões de ação e link para modal de detalhes
 * Variáveis esperadas: $order (array com dados do pedido)
 */

// Mapeia status
$statusClasses = [
    'novo' => 'novo',
    'aceito' => 'aceito',
    'preparo' => 'preparo',
    'rota' => 'rota',
    'entregue' => 'entregue',
    'cancelado' => 'cancelado',
];

$statusLabels = [
    'novo' => 'Novo',
    'aceito' => 'Aceito',
    'preparo' => 'Em Preparo',
    'rota' => 'Em Rota',
    'entregue' => 'Entregue',
    'cancelado' => 'Cancelado',
];

// Próxima ação por status
$nextActions = [
    'novo' => ['label' => 'Aceitar', 'next' => 'aceito', 'color' => '#16a34a'],
    'aceito' => ['label' => 'Iniciar Preparo', 'next' => 'preparo', 'color' => '#8b5cf6'],
    'preparo' => ['label' => 'Saiu p/ Entrega', 'next' => 'rota', 'color' => '#0891b2'],
    'rota' => ['label' => 'Entregue', 'next' => 'entregue', 'color' => '#059669'],
];

$status = $order['status'] ?? 'novo';
$statusClass = $statusClasses[$status] ?? 'novo';
$statusLabel = $statusLabels[$status] ?? 'Novo';
$action = $nextActions[$status] ?? null;

// Calcula tempo decorrido
$createdAt = new DateTime($order['created_at']);
$now = new DateTime();
$diff = $now->diff($createdAt);
$timeAgo = $diff->h > 0 ? $diff->h . 'h ' . $diff->i . 'min' : $diff->i . ' min';

// Prepara JSON para modal de detalhes
$orderJson = htmlspecialchars(json_encode([
    'id' => $order['id'],
    'status' => $status,
    'client_name' => $order['client_name'] ?? null,
    'client_phone' => $order['client_phone'] ?? null,
    'client_address' => $order['client_address'] ?? null,
    'total' => $order['total'] ?? 0,
    'payment_method' => $order['payment_method'] ?? null,
    'created_at' => date('d/m/Y H:i', strtotime($order['created_at'])),
    'items' => [] // TODO: buscar itens se necessário
]), ENT_QUOTES, 'UTF-8');
?>

<div class="delivery-card delivery-card--<?= $statusClass ?>">
    <!-- Clique no card abre detalhes -->
    <div onclick='DeliveryUI.openDetailsModal(<?= $orderJson ?>)' style="cursor: pointer;">
        <div class="delivery-card-header">
            <div>
                <div class="delivery-card-id">#<?= $order['id'] ?></div>
                <div class="delivery-card-time">
                    <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                    <?= date('H:i', strtotime($order['created_at'])) ?>
                    <span style="color: #94a3b8; margin-left: 4px;">(<?= $timeAgo ?>)</span>
                </div>
            </div>
            <span class="delivery-badge delivery-badge--<?= $statusClass ?>"><?= $statusLabel ?></span>
        </div>

        <div class="delivery-card-customer">
            <div class="delivery-card-customer-name">
                <i data-lucide="user" style="width: 14px; height: 14px; color: #64748b;"></i>
                <?= htmlspecialchars($order['client_name'] ?? 'Cliente não identificado') ?>
            </div>
            <?php if (!empty($order['client_phone'])): ?>
                <div class="delivery-card-customer-phone">
                    <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                    <?= htmlspecialchars($order['client_phone']) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($order['client_address'])): ?>
            <div class="delivery-card-address">
                <i data-lucide="map-pin" style="width: 14px; height: 14px; flex-shrink: 0;"></i>
                <span><?= htmlspecialchars($order['client_address']) ?></span>
            </div>
        <?php endif; ?>

        <div class="delivery-card-footer">
            <div class="delivery-card-total">
                R$ <?= number_format($order['total'] ?? 0, 2, ',', '.') ?>
            </div>
            <div class="delivery-card-items">
                <i data-lucide="package" style="width: 14px; height: 14px;"></i>
                <?= $order['items_count'] ?? 0 ?> <?= ($order['items_count'] ?? 0) == 1 ? 'item' : 'itens' ?>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div style="display: flex; gap: 8px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
        <?php if ($action): ?>
            <button onclick="event.stopPropagation(); DeliveryActions.advance(<?= $order['id'] ?>, '<?= $status ?>')"
                    style="flex: 1; padding: 10px; background: <?= $action['color'] ?>; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                <?= $action['label'] ?>
            </button>
        <?php endif; ?>

        <?php if (in_array($status, ['novo', 'aceito'])): ?>
            <button onclick="event.stopPropagation(); DeliveryUI.openCancelModal(<?= $order['id'] ?>)"
                    style="padding: 10px 15px; background: #fee2e2; color: #dc2626; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                    title="Cancelar pedido">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        <?php endif; ?>
    </div>
</div>
