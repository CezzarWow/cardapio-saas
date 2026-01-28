<?php
/**
 * Partial: Grid de Comandas/Clientes
 * Variáveis esperadas: $clientOrders (array de pedidos de clientes)
 */
?>
<script src="<?= BASE_URL ?>/js/shared/client-hub.js?v=<?= time() ?>"></script>

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
                $clientId = (int) ($order['client_id'] ?? 0);
                
                // Lógica de Display Definitiva (Solicitação Técnico)
                // Card Comanda DEVE mostrar o total Global (Mesa + Delivery)
                
                $totalGlobal = (float) ($order['total'] ?? 0);
                $totalDelivery = (float) ($order['total_delivery'] ?? 0);
                $totalTable = (float) ($order['total_table'] ?? 0);
                
                // Variável usada no onclick de pagamento
                $total = $totalGlobal;
                
                // Display: Sempre Total Global
                $displayTotal = 'Total: R$ ' . number_format($totalGlobal, 2, ',', '.');
                
                $cardClass = $isPaid ? 'table-card--pago' : 'table-card--aberto';
                $statusText = $isPaid ? 'PAGO' : 'ABERTO';
                $ariaLabel = 'Comanda de ' . $clientNameRaw . ' - ' . $statusText . ' - ' . strip_tags($displayTotal);
            ?>
            
            <?php
                // Se for DELIVERY ou RETIRADA, usa o modal de detalhes (Hub Unificado)
                $isModalType = in_array($order['order_type'] ?? '', ['delivery', 'entrega', 'pickup', 'retirada']); 
                
                $deliveryUrl = BASE_URL . '/admin/loja/delivery';
                $pdvUrl = BASE_URL . '/admin/loja/pdv?order_id=' . $orderId;
                if ($isModalType) {
                    // Fluxo Modal: Abre Hub do Cliente
                    $clickAction = "if(window.ClientHub) { ClientHub.open({$order['id']}); } else { alert('Hub não carregado'); }";
                } else {
                    // Fluxo Balcão/Local/Edição: Abre no PDV com o ID do pedido
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
                     <?php if ($isModalType): ?>
                     onclick='ClientHub.open(<?= (int) $orderId ?>)'
                     <?php else: ?>
                     onclick='if(typeof AdminSPA!=="undefined") AdminSPA.navigateTo("balcao", true, true, {order_id: <?= (int) $orderId ?>}); else window.location.href=<?= json_encode($pdvUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;'
                     <?php endif; ?>
                     tabindex="0"
                     role="button"
                     aria-label="<?= \App\Helpers\ViewHelper::e($ariaLabel) ?>"
                     <?php if ($isModalType): ?>
                     onkeypress='if(event.key==="Enter") ClientHub.open(<?= (int) $orderId ?>)'
                     <?php else: ?>
                     onkeypress='if(event.key==="Enter"){ if(typeof AdminSPA!=="undefined") AdminSPA.navigateTo("balcao", true, true, {order_id: <?= (int) $orderId ?>}); else window.location.href=<?= json_encode($pdvUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>; }'
                     <?php endif; ?>>
            <?php endif; ?>
                
                <div style="margin-top: 15px;">
                    <span class="comanda-card__name"><?= \App\Helpers\ViewHelper::e($clientNameHtml) ?></span>
                    <span class="comanda-card__total"><?= $displayTotal // Já contém HTML seguro (number_format ou tags) ?></span>
                </div>

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
