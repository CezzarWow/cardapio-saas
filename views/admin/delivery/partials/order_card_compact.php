<?php
/**
 * Partial: Card Compacto para Kanban
 * Variáveis: $order
 * 
 * Refatorado: Acessibilidade adicionada (ARIA, tabindex, keyboard)
 */

$status = $order['status'] ?? 'novo';
$orderType = $order['order_type'] ?? 'delivery';

// Ações diferentes para cada tipo de pedido
if ($orderType === 'local') {
    // Local: só aparece em "novo", depois vai para Mesas
    $nextActions = [
        'novo' => ['label' => 'Enviar p/ Mesa', 'icon' => 'utensils'],
    ];
} elseif ($orderType === 'pickup') {
    $nextActions = [
        'novo' => ['label' => 'Preparo', 'icon' => 'chef-hat'],
        'preparo' => ['label' => 'Pronto', 'icon' => 'package-check'],
        'rota' => ['label' => 'Retirado', 'icon' => 'check-circle'],
    ];
} else {
    // Delivery
    $nextActions = [
        'novo' => ['label' => 'Preparo', 'icon' => 'chef-hat'],
        'preparo' => ['label' => 'Saiu', 'icon' => 'bike'],
        'rota' => ['label' => 'Entregue', 'icon' => 'check-circle'],
    ];
}

$action = $nextActions[$status] ?? null;

// Tempo decorrido
$createdAt = new DateTime($order['created_at']);
$now = new DateTime();
$diff = $now->diff($createdAt);
$timeAgo = $diff->h > 0 ? $diff->h . 'h' : $diff->i . 'm';

// Cliente para aria-label
$clientName = htmlspecialchars($order['client_name'] ?? 'Cliente');
$statusLabel = ucfirst($status);

// JSON para modal
$orderJson = htmlspecialchars(json_encode([
    'id' => $order['id'],
    'status' => $status,
    'client_name' => $order['client_name'] ?? null,
    'client_phone' => $order['client_phone'] ?? null,
    'client_address' => $order['client_address'] ?? null,
    'total' => $order['total'] ?? 0,
    'payment_method' => $order['payment_method'] ?? null,
    'is_paid' => $order['is_paid'] ?? 0,
    'created_at' => date('d/m/Y H:i', strtotime($order['created_at'])),
    'items' => []
]), ENT_QUOTES, 'UTF-8');
?>

<div class="delivery-card-compact delivery-card-compact--<?= $status ?>" 
     onclick='DeliveryUI.openDetailsModal(<?= $orderJson ?>)'
     tabindex="0"
     role="button"
     aria-label="Pedido de <?= $clientName ?> - Status: <?= $statusLabel ?> - R$ <?= number_format($order['total'] ?? 0, 2, ',', '.') ?>"
     onkeypress="if(event.key==='Enter') DeliveryUI.openDetailsModal(<?= $orderJson ?>)">
    
    <div class="delivery-card-compact-header">
        <span class="delivery-card-compact-id">
            <?php
                // Prioridade: cliente > mesa > fallback
                if (!empty($order['client_name'])) {
                    echo $clientName;
                } elseif (!empty($order['table_number'])) {
                    echo 'Mesa ' . htmlspecialchars($order['table_number']);
                } else {
                    echo 'Cliente';
                }
            ?>
        </span>
        <span class="delivery-card-compact-time">
            <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
            <?= $timeAgo ?>
        </span>
    </div>

    <div class="delivery-card-compact-info">
        <?php
            // Define cor e label baseado no tipo
            if ($orderType === 'local') {
                $badgeColor = '#7c3aed'; // Roxo
                $badgeLabel = '🍽️ Local';
            } elseif ($orderType === 'pickup') {
                $badgeColor = '#ea580c'; // Laranja
                $badgeLabel = '🏪 Retirada';
            } else {
                $badgeColor = '#3b82f6'; // Azul
                $badgeLabel = '🚚 Delivery';
            }
        ?>
        <span class="delivery-card-compact-customer" style="color: <?= $badgeColor ?>; font-weight: 700;">
            <?= $badgeLabel ?>
        </span>
        
        <?php
            // Badge de Pagamento - usa DeliveryConstants via JS render seria ideal,
            // mas mantemos PHP para SSR
            $isPaid = $order['is_paid'] ?? 0;
            $paymentMethod = $order['payment_method'] ?? '';
            
            if ($isPaid == 1) {
                $paymentBadge = '✅ PAGO';
                $paymentColor = '#16a34a'; // Verde
            } else {
                // Labels de pagamento (sincronizado com constants.js)
                $paymentBadge = match($paymentMethod) {
                    'dinheiro' => '💵 Dinheiro',
                    'pix' => '📱 Pix',
                    'credito' => '💳 Crédito',
                    'debito' => '💳 Débito',
                    'multiplo' => '💰 Múltiplo',
                    default => '💰 A pagar'
                };
                $paymentColor = '#dc2626'; // Vermelho
            }
        ?>
        <span style="font-size: 0.75rem; font-weight: 600; color: <?= $paymentColor ?>;">
            <?= $paymentBadge ?>
        </span>
        
        <span class="delivery-card-compact-total">
            R$ <?= number_format($order['total'] ?? 0, 2, ',', '.') ?>
        </span>
    </div>

    <div class="delivery-card-compact-actions" onclick="event.stopPropagation()">
        <?php if ($action): ?>
            <button class="delivery-card-compact-btn delivery-card-compact-btn--primary"
                    onclick="DeliveryActions.advance(<?= $order['id'] ?>, '<?= $status ?>', '<?= $orderType ?>')"
                    aria-label="<?= $action['label'] ?>">
                <i data-lucide="<?= $action['icon'] ?>" style="width: 14px; height: 14px;"></i>
                <?= $action['label'] ?>
            </button>
        <?php endif; ?>

        <?php if (in_array($status, ['novo', 'preparo', 'rota'])): ?>
            <button class="delivery-card-compact-btn delivery-card-compact-btn--cancel"
                    onclick="DeliveryUI.openCancelModal(<?= $order['id'] ?>)"
                    aria-label="Cancelar pedido">
                <i data-lucide="x" style="width: 14px; height: 14px;"></i>
            </button>
        <?php endif; ?>
    </div>
</div>
