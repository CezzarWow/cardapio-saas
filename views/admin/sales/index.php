<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Histórico de Vendas</h1>
                <p style="color: #6b7280;">Extrato completo de pedidos</p>
            </div>
            <div style="background: #eff6ff; color: #1d4ed8; padding: 10px 20px; border-radius: 8px; font-weight: 700; border: 1px solid #dbeafe;">
                Total: R$ <?= number_format(array_sum(array_column($orders, 'calculated_total')), 2, ',', '.') ?>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;"># Pedido</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Data</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Valor</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="4" style="padding: 3rem; text-align: center; color: #9ca3af;">Nenhuma venda realizada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $sale): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 1rem; font-weight: 600; color: #1f2937;">
                                #<?= str_pad($sale['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td style="padding: 1rem; color: #4b5563;">
                                <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?>
                            </td>
                            <td style="padding: 1rem; font-weight: 700; color: #2563eb;">
                                R$ <?= number_format($sale['calculated_total'], 2, ',', '.') ?>
                            </td>
                            <td style="padding: 1rem;">
                                <button onclick="openOrderDetails(<?= $sale['id'] ?>)" 
                                        style="background: #f3f4f6; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; color: #4b5563; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                                    <i data-lucide="eye" style="width: 16px;"></i> Detalhes
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 20px; border-radius: 12px; width: 400px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
            <h3 style="font-weight: 700; color: #1f2937;">Itens do Pedido</h3>
            <button onclick="document.getElementById('orderModal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div id="modalItemsList" style="max-height: 300px; overflow-y: auto;">
            Carregando...
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="document.getElementById('orderModal').style.display='none'" 
                    style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
// Função para buscar e mostrar os itens
function openOrderDetails(orderId) {
    const modal = document.getElementById('orderModal');
    const list = document.getElementById('modalItemsList');
    
    // Mostra o modal carregando
    modal.style.display = 'flex';
    list.innerHTML = '<p style="text-align:center; color:#666;">Buscando itens...</p>';

    // Chama o PHP
    fetch('vendas/itens?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if(data.length === 0) {
                list.innerHTML = '<p>Nenhum item encontrado.</p>';
                return;
            }

            let html = '<ul style="list-style: none; padding: 0;">';
            data.forEach(item => {
                html += `
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                        <div>
                            <span style="font-weight: 600; color: #374151;">${item.quantity}x</span> 
                            ${item.name}
                        </div>
                        <div style="font-weight: 600; color: #1f2937;">
                            R$ ${parseFloat(item.price).toFixed(2).replace('.', ',')}
                        </div>
                    </li>
                `;
            });
            html += '</ul>';
            list.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            list.innerHTML = '<p style="color:red;">Erro ao carregar itens.</p>';
        });
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
