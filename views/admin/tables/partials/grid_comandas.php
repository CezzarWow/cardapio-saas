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
            
            <?php if ($isPaid): ?>
                <div class="table-card <?= $cardClass ?>"
                     onclick="showPaidOrderOptions(<?= $order['id'] ?>, '<?= addslashes($clientName) ?>', <?= $total ?>, <?= $clientId ?>)"
                     tabindex="0"
                     role="button"
                     aria-label="Comanda de <?= $clientName ?> - <?= $statusText ?> - R$ <?= number_format($total, 2, ',', '.') ?>"
                     onkeypress="if(event.key==='Enter') showPaidOrderOptions(<?= $order['id'] ?>, '<?= addslashes($clientName) ?>', <?= $total ?>, <?= $clientId ?>)">
            <?php else: ?>
                <div class="table-card <?= $cardClass ?>"
                     onclick="if(typeof AdminSPA!=='undefined') AdminSPA.navigateTo('balcao',true,true,{order_id:<?= $order['id'] ?>}); else window.location.href='<?= BASE_URL ?>/admin/loja/pdv?order_id=<?= $order['id'] ?>'"
                     tabindex="0"
                     role="button"
                     aria-label="Comanda de <?= $clientName ?> - <?= $statusText ?> - R$ <?= number_format($total, 2, ',', '.') ?>"
                     onkeypress="if(event.key==='Enter') { if(typeof AdminSPA!=='undefined') AdminSPA.navigateTo('balcao',true,true,{order_id:<?= $order['id'] ?>}); else window.location.href='<?= BASE_URL ?>/admin/loja/pdv?order_id=<?= $order['id'] ?>'; }">
            <?php endif; ?>
                
                <span class="comanda-card__name"><?= $clientName ?></span>
                <span class="comanda-card__status"><?= $statusText ?></span>
                <span class="comanda-card__total">R$ <?= number_format($total, 2, ',', '.') ?></span>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
