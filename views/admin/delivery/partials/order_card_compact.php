<?php
/**
 * Partial: Card Compacto para Kanban
 * Variáveis: $order
 */

$status = $order['status'] ?? 'novo';

$nextActions = [
    'novo' => ['label' => 'Preparo', 'icon' => 'chef-hat'],
    'preparo' => ['label' => 'Saiu', 'icon' => 'bike'],
    'rota' => ['label' => 'Entregue', 'icon' => 'check-circle'],
];

$action = $nextActions[$status] ?? null;

// Tempo decorrido
$createdAt = new DateTime($order['created_at']);
$now = new DateTime();
$diff = $now->diff($createdAt);
$timeAgo = $diff->h > 0 ? $diff->h . 'h' : $diff->i . 'm';

// JSON para modal
$orderJson = htmlspecialchars(json_encode([
    'id' => $order['id'],
    'status' => $status,
    'client_name' => $order['client_name'] ?? null,
    'client_phone' => $order['client_phone'] ?? null,
    'client_address' => $order['client_address'] ?? null,
    'total' => $order['total'] ?? 0,
    'payment_method' => $order['payment_method'] ?? null,
    'created_at' => date('d/m/Y H:i', strtotime($order['created_at'])),
    'items' => []
]), ENT_QUOTES, 'UTF-8');
?>

<div class="delivery-card-compact delivery-card-compact--<?= $status ?>" 
     onclick='DeliveryUI.openDetailsModal(<?= $orderJson ?>)'>
    
    <div class="delivery-card-compact-header">
        <span class="delivery-card-compact-id">
            <?= htmlspecialchars($order['client_name'] ?? 'Cliente') ?>
        </span>
        <span class="delivery-card-compact-time">
            <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
            <?= $timeAgo ?>
        </span>
    </div>

    <div class="delivery-card-compact-info">
        <span class="delivery-card-compact-customer">
            #<?= $order['id'] ?>
        </span>
        <span class="delivery-card-compact-total">
            R$ <?= number_format($order['total'] ?? 0, 2, ',', '.') ?>
        </span>
    </div>

    <div class="delivery-card-compact-actions" onclick="event.stopPropagation()">
        <?php if ($action): ?>
            <button class="delivery-card-compact-btn delivery-card-compact-btn--primary"
                    onclick="DeliveryActions.advance(<?= $order['id'] ?>, '<?= $status ?>')">
                <i data-lucide="<?= $action['icon'] ?>" style="width: 14px; height: 14px;"></i>
                <?= $action['label'] ?>
            </button>
        <?php endif; ?>

        <?php if (in_array($status, ['novo', 'preparo', 'rota'])): ?>
            <button class="delivery-card-compact-btn delivery-card-compact-btn--cancel"
                    onclick="DeliveryUI.openCancelModal(<?= $order['id'] ?>)">
                <i data-lucide="x" style="width: 14px; height: 14px;"></i>
            </button>
        <?php endif; ?>
    </div>
</div>
