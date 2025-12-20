<?php 
// Reutiliza o mesmo Header e Sidebar do PDV para manter o padrão
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Histórico de Vendas</h1>
                <p style="color: #6b7280;">Acompanhe o faturamento da sua loja</p>
            </div>
            <div style="background: #dbeafe; color: #1e40af; padding: 10px 20px; border-radius: 8px; font-weight: 700;">
                Total: R$ <?= number_format(array_sum(array_column($orders, 'total')), 2, ',', '.') ?>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;"># Pedido</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Data / Hora</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Status</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="4" style="padding: 3rem; text-align: center; color: #9ca3af;">
                                Nenhuma venda realizada ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $sale): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 1rem; font-weight: 600; color: #1f2937;">
                                #<?= str_pad($sale['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td style="padding: 1rem; color: #4b5563;">
                                <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                    <?= strtoupper($sale['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; font-weight: 700; color: #2563eb;">
                                R$ <?= number_format($sale['total'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
