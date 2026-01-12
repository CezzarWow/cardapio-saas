<?php
/**
 * Partial: Grid de Comandas/Clientes
 * Variáveis esperadas: $clientOrders (array de pedidos de clientes)
 */
?>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1.2rem; margin-bottom: 3rem;">
    <?php if (empty($clientOrders)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
            <i data-lucide="clipboard-list" style="width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
            <p style="margin: 0; font-weight: 500;">Nenhuma comanda aberta no momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($clientOrders as $order): ?>
            <?php 
                $isPaid = !empty($order['is_paid']) && $order['is_paid'] == 1; 
                
                if ($isPaid) {
                    $bg = '#f0fdf4';
                    $border = '#22c55e';
                    $textColor = '#15803d';
                    $statusText = 'PAGO';
                } else {
                    $bg = '#fffbeb';
                    $border = '#f59e0b';
                    $textColor = '#b45309';
                    $statusText = 'ABERTO';
                }
            ?>
            <?php if ($isPaid): ?>
                <div style="background: <?= $bg ?>; border: 2px solid <?= $border ?>; border-radius: 10px; cursor: default; height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); padding: 10px;">
            <?php else: ?>
                <div onclick="window.location.href='<?= BASE_URL ?>/admin/loja/pdv?order_id=<?= $order['id'] ?>'" 
                     style="background: <?= $bg ?>; border: 2px solid <?= $border ?>; border-radius: 10px; cursor: pointer; transition: transform 0.1s; height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); padding: 10px;">
            <?php endif; ?>
                
                <!-- Nome (truncado) -->
                <!-- Nome (truncado com CSS) -->
                <span style="font-size: 1.1rem; font-weight: 700; color: #1e293b; text-align: center; max-width: 100%; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                    <?= htmlspecialchars($order['client_name'] ?? 'Cliente') ?>
                </span>
                
                <!-- Status -->
                <span style="font-size: 0.65rem; font-weight: 700; color: <?= $textColor ?>; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <?= $statusText ?>
                </span>

                <!-- Valor -->
                <span style="font-size: 1rem; font-weight: 800; color: <?= $textColor ?>; margin-top: 6px;">
                    R$ <?= number_format($order['total'], 2, ',', '.') ?>
                </span>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
