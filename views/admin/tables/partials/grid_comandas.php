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
                $isPaid = !empty($order['is_paid']) && $order['is_paid'] == 1;
                $clientName = htmlspecialchars($order['client_name'] ?? 'Cliente');
                $total = floatval($order['total'] ?? 0);
                $clientId = intval($order['client_id'] ?? 0);
                $cardClass = $isPaid ? 'table-card--pago' : 'table-card--aberto';
                $statusText = $isPaid ? 'PAGO' : 'ABERTO';
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

                $clickAction = "";
                if ($isDelivery) {
                    // Fluxo Delivery (Logística): Tenta abrir modal de detalhes ou vai para aba delivery
                    $clickAction = "if(window.DeliveryUI) { DeliveryUI.openDetailsModal({$order['id']}); } else { if(typeof AdminSPA!=='undefined') AdminSPA.navigateTo('delivery'); else window.location.href='".BASE_URL."/admin/loja/delivery'; }";
                } else {
                    // Fluxo Balcão/Retirada/Local/Edição: Abre no PDV com o ID do pedido
                    $clickAction = "if(typeof AdminSPA!=='undefined') AdminSPA.navigateTo('balcao',true,true,{order_id:{$order['id']}}); else window.location.href='".BASE_URL."/admin/loja/pdv?order_id={$order['id']}'";
                }
            ?>
            <?php if ($isPaid): ?>
                <div class="table-card <?= $cardClass ?>"
                     onclick="showPaidOrderOptions(<?= $order['id'] ?>, '<?= addslashes($clientName) ?>', <?= $total ?>, <?= $clientId ?>)"
                     tabindex="0"
                     role="button"
                     aria-label="Comanda de <?= $clientName ?> - <?= $statusText ?> - R$ <?= number_format($total, 2, ',', '.') ?>"
                     onkeypress="if(event.key==='Enter') showPaidOrderOptions(<?= $order['id'] ?>, '<?= addslashes($clientName) ?>', <?= $total ?>, <?= $clientId ?>)">
            <?php else: ?>
                <div class="table-card <?= $cardClass ?>"
                     onclick="<?= $clickAction ?>"
                     tabindex="0"
                     role="button"
                     aria-label="Comanda de <?= $clientName ?> - <?= $statusText ?> - R$ <?= number_format($total, 2, ',', '.') ?>"
                     onkeypress="if(event.key==='Enter') { <?= $clickAction ?> }">
            <?php endif; ?>
                
                <span class="comanda-card__name"><?= $clientName ?></span>
                <span class="comanda-card__status"><?= $statusText ?></span>
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
