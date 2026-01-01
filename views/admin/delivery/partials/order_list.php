<?php
/**
 * Partial: Lista de pedidos Delivery
 * FASE 2: Renderização real com mensagem de erro
 * Variáveis esperadas: $orders (array de pedidos), $dbError (opcional)
 */
?>
<div class="delivery-grid">
    <?php if (!empty($dbError)): ?>
        <!-- Erro de banco (coluna não existe) -->
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: #fef2f2; border-radius: 12px; border: 2px solid #fecaca;">
            <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i data-lucide="alert-triangle" style="width: 30px; height: 30px; color: #dc2626;"></i>
            </div>
            <h3 style="color: #b91c1c; font-weight: 700; margin-bottom: 8px;">Configuração Pendente</h3>
            <p style="color: #dc2626; font-size: 0.9rem; margin-bottom: 15px;"><?= htmlspecialchars($dbError) ?></p>
            <code style="background: #1e293b; color: #22c55e; padding: 10px 15px; border-radius: 6px; font-size: 0.85rem; display: inline-block;">
                ALTER TABLE orders ADD COLUMN order_type ENUM('local','delivery') DEFAULT 'local';
            </code>
        </div>
    <?php elseif (empty($orders)): ?>
        <!-- Estado vazio normal -->
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 12px; border: 2px dashed #e2e8f0;">
            <div style="width: 80px; height: 80px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i data-lucide="bike" style="width: 40px; height: 40px; color: #f59e0b;"></i>
            </div>
            <h3 style="color: #1e293b; font-weight: 700; margin-bottom: 8px;">Nenhum pedido no momento</h3>
            <p style="color: #64748b; font-size: 0.95rem;">Os pedidos de delivery aparecerão aqui automaticamente.</p>
        </div>
    <?php else: ?>
        <!-- Lista de pedidos -->
        <?php foreach ($orders as $order): ?>
            <?php require __DIR__ . '/order_card.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
