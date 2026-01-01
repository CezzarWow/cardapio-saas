<?php
/**
 * Partial: Grid de Comandas/Clientes
 * Variáveis esperadas: $clientOrders (array de pedidos de clientes)
 */
?>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
    <?php if (empty($clientOrders)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
            <i data-lucide="clipboard-list" style="width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
            <p style="margin: 0; font-weight: 500;">Nenhuma comanda aberta no momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($clientOrders as $order): ?>
            <?php 
                $isPaid = !empty($order['is_paid']) && $order['is_paid'] == 1; 
                $borderColor = $isPaid ? '#22c55e' : '#f59e0b'; // Verde ou Laranja
                $bgColor = $isPaid ? '#f0fdf4' : 'white'; 
            ?>
            <?php if ($isPaid): ?>
                <div onclick="showPaidOrderOptions(<?= $order['order_id'] ?>, '<?= addslashes($order['client_name']) ?>', <?= $order['total'] ?>, <?= $order['client_id'] ?>)" 
                     style="background: <?= $bgColor ?>; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <?php else: ?>
                <div onclick="window.location.href='<?= BASE_URL ?>/admin/loja/pdv?order_id=<?= $order['order_id'] ?>'" 
                     style="background: <?= $bgColor ?>; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <?php endif; ?>
                
                <!-- Barra Lateral Colorida -->
                <div style="position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: <?= $borderColor ?>;"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; background: <?= $isPaid ? '#dcfce7' : '#fff7ed' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: <?= $borderColor ?>;">
                            <i data-lucide="<?= $isPaid ? 'check-circle' : 'user' ?>" size="20"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: #1e293b; font-size: 1rem; line-height: 1.2;">
                                <?= substr($order['client_name'], 0, 25) ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                Comanda #<?= $order['order_id'] ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($isPaid): ?>
                        <span style="background: #16a34a; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800;">PAGO</span>
                    <?php endif; ?>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                    <div style="display: flex; align-items: center; gap: 6px; color: #64748b; font-size: 0.8rem;">
                        <i data-lucide="clock" size="14"></i>
                        <?= date('H:i', strtotime($order['created_at'])) ?>
                    </div>
                    <div style="font-weight: 800; color: <?= $borderColor ?>; font-size: 1.25rem;">
                        R$ <?= number_format($order['total'], 2, ',', '.') ?>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
