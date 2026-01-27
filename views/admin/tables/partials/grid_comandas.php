<?php
/**
 * Partial: Grid de Comandas/Clientes
 * Variáveis esperadas: $clientOrders (array de pedidos de clientes)
 */
?>
<div class="comanda-grid">
    <?php if (empty($clientOrders)): ?>
        <div class="table-grid__empty">
            <i data-lucide="clipboard-list" style="width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
            <p>Nenhuma comanda aberta no momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($clientOrders as $order): ?>
            <?php
                $orderId = (int) ($order['id'] ?? 0);
                $isPaid = !empty($order['is_paid']) && $order['is_paid'] == 1;
                $clientNameRaw = (string) ($order['client_name'] ?? 'Cliente');
                $clientNameHtml = \App\Helpers\ViewHelper::e($clientNameRaw);
                $total = (float) ($order['total'] ?? 0);
                $clientId = (int) ($order['client_id'] ?? 0);
                $cardClass = $isPaid ? 'table-card--pago' : 'table-card--aberto';
                $statusText = $isPaid ? 'PAGO' : 'ABERTO';
                $ariaLabel = 'Comanda de ' . $clientNameRaw . ' - ' . $statusText . ' - R$ ' . number_format($total, 2, ',', '.');
            ?>
            
            <?php
                // Se for DELIVERY (Entrega), vai para o fluxo de delivery/modal
                // Se for RETIRADA/PICKUP, o cliente quer tratar no balcão (PDV), então vai para balcao
                $isDelivery = in_array($order['order_type'] ?? '', ['delivery', 'entrega']); 
                
                // [FIX] Se for DELIVERY mas estiver 'novo' ou 'aberto', permite abrir no PDV para edição
                // Apenas redireciona para DeliveryUI se já estiver em fluxo logístico (aguardando, em_preparo, etc)
                $status = $order['status'] ?? 'novo';
                if ($isDelivery && in_array($status, ['novo', 'aberto'])) {
                    $isDelivery = false; // Trata como comum para abrir no PDV
                }

                $deliveryUrl = BASE_URL . '/admin/loja/delivery';
                $pdvUrl = BASE_URL . '/admin/loja/pdv?order_id=' . $orderId;
                if ($isDelivery) {
                    // Fluxo Delivery (Logística): Tenta abrir modal de detalhes ou vai para aba delivery
                    $clickAction = "if(window.DeliveryUI) { DeliveryUI.openDetailsModal({$order['id']}); } else { if(typeof AdminSPA!=='undefined') AdminSPA.navigateTo('delivery'); else window.location.href='".BASE_URL."/admin/loja/delivery'; }";
                } else {
                    // Fluxo Balcão/Retirada/Local/Edição: Abre no PDV com o ID do pedido
                    $clickAction = "if(typeof AdminSPA!=='undefined') AdminSPA.navigateTo('balcao',true,true,{order_id:{$order['id']}}); else window.location.href='".BASE_URL."/admin/loja/pdv?order_id={$order['id']}'";
                }
            ?>
            <?php if ($isPaid): ?>
                <div class="table-card <?= \App\Helpers\ViewHelper::e($cardClass) ?>"
                     onclick='showPaidOrderOptions(<?= $orderId ?>, <?= \App\Helpers\ViewHelper::js($clientNameRaw) ?>, <?= (float) $total ?>, <?= (int) $clientId ?>)'
                     tabindex="0"
                     role="button"
                     aria-label="<?= \App\Helpers\ViewHelper::e($ariaLabel) ?>"
                     onkeypress='if(event.key==="Enter") showPaidOrderOptions(<?= $orderId ?>, <?= \App\Helpers\ViewHelper::js($clientNameRaw) ?>, <?= (float) $total ?>, <?= (int) $clientId ?>)'>
            <?php else: ?>
                <div class="table-card <?= \App\Helpers\ViewHelper::e($cardClass) ?>"
                     <?php if ($isDelivery): ?>
                     onclick='DeliveryUI.openDetailsModal(<?= (int) $orderId ?>)'
                     <?php else: ?>
                     onclick='if(typeof AdminSPA!=="undefined") AdminSPA.navigateTo("balcao", true, true, {order_id: <?= (int) $orderId ?>}); else window.location.href=<?= json_encode($pdvUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;'
                     <?php endif; ?>
                     tabindex="0"
                     role="button"
                     aria-label="<?= \App\Helpers\ViewHelper::e($ariaLabel) ?>"
                     <?php if ($isDelivery): ?>
                     onkeypress='if(event.key==="Enter") DeliveryUI.openDetailsModal(<?= (int) $orderId ?>)'
                     <?php else: ?>
                     onkeypress='if(event.key==="Enter"){ if(typeof AdminSPA!=="undefined") AdminSPA.navigateTo("balcao", true, true, {order_id: <?= (int) $orderId ?>}); else window.location.href=<?= json_encode($pdvUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>; }'
                     <?php endif; ?>>
            <?php endif; ?>
                
                <span class="comanda-card__name"><?= \App\Helpers\ViewHelper::e($clientNameHtml) ?></span>
                <span class="comanda-card__status"><?= \App\Helpers\ViewHelper::e($statusText) ?></span>
                <span class="comanda-card__total">R$ <?= number_format($total, 2, ',', '.') ?></span>

                <?php if (!empty($order['order_type'])): ?>
                    <?php if ($order['order_type'] === 'delivery' || $order['order_type'] === 'entrega'): ?>
                        <span class="table-card__badge" style="background:#059669; font-size:9px; padding:2px 6px; top:5px; right:5px; position:absolute; border-radius:4px;">ENTREGA</span>
                    <?php elseif ($order['order_type'] === 'pickup' || $order['order_type'] === 'retirada'): ?>
                        <span class="table-card__badge" style="background:#0284c7; font-size:9px; padding:2px 6px; top:5px; right:5px; position:absolute; border-radius:4px;">RETIRADA</span>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
