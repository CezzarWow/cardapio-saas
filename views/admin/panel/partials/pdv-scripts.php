<?php
/**
 * PDV-SCRIPTS.PHP - Scripts JavaScript do PDV
 * 
 * Contém: Variáveis globais JS e inclusão de scripts modulares
 * Variáveis esperadas: $cartRecovery, $isEditingPaid, $originalPaidTotalFromDB, $editingOrderId, $deliveryFee
 */
?>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    // Injeta o carrinho recuperado do PHP para o JS
    const recoveredCart = <?= json_encode($cartRecovery ?? []) ?>;
    
    // Modo edição de pedido PAGO (para cobrar só a diferença)
    const isEditingPaidOrder = <?= ($isEditingPaid ?? false) ? 'true' : 'false' ?>;
    const originalPaidTotal = <?= $originalPaidTotalFromDB ?? 0 ?>;
    const editingPaidOrderId = <?= $editingOrderId ?? 'null' ?>;
    
    // [NOVO] Taxa de entrega configurada
    const PDV_DELIVERY_FEE = <?= $deliveryFee ?>;

    /**
     * Deleta item já salvo da mesa/comanda
     * @param {number} itemId - ID do order_item
     * @param {number} orderId - ID do pedido
     */
    function deleteSavedItem(itemId, orderId) {
        if (!confirm('Remover este item do pedido?')) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(BASE_URL + '/admin/loja/venda/remover-item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ item_id: itemId, order_id: orderId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Não foi possível remover o item'));
            }
        })
        .catch(err => {
            alert('Erro de conexão: ' + err.message);
        });
    }

    /**
     * Cancela todo o pedido da mesa
     * @param {number} tableId - ID da mesa
     * @param {number} orderId - ID do pedido
     */
    function cancelTableOrder(tableId, orderId) {
        if (!confirm('ATENÇÃO: Isso cancelará TODO o pedido desta mesa.\n\nOs itens voltarão ao estoque.\n\nDeseja continuar?')) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(BASE_URL + '/admin/loja/mesa/cancelar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ table_id: tableId, order_id: orderId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Pedido cancelado com sucesso!');
                window.location.href = BASE_URL + '/admin/loja/mesas';
            } else {
                alert('Erro: ' + (data.message || 'Não foi possível cancelar o pedido'));
            }
        })
        .catch(err => {
            alert('Erro de conexão: ' + err.message);
        });
    }

    /**
     * Abre modal de ficha do cliente
     */
    function openFichaModal() {
        const modal = document.getElementById('fichaModal');
        if (modal) {
            modal.style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    /**
     * Fecha modal de ficha do cliente
     */
    function closeFichaModal() {
        const modal = document.getElementById('fichaModal');
        if (modal) modal.style.display = 'none';
    }

    /**
     * Imprime a ficha do cliente
     */
    function printFicha() {
        const content = document.getElementById('fichaContent');
        if (!content) return;

        const printWindow = window.open('', '_blank', 'width=400,height=600');
        
        // Clone content and remove buttons
        const clone = content.cloneNode(true);
        clone.querySelectorAll('button').forEach(btn => btn.remove());
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Ficha do Cliente</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 20px; 
                        max-width: 320px; 
                        margin: 0 auto;
                    }
                    .header { text-align: center; margin-bottom: 15px; border-bottom: 2px dashed #000; padding-bottom: 10px; }
                    .header h1 { font-size: 18px; margin-bottom: 5px; }
                    .header p { font-size: 12px; color: #666; }
                    .item { padding: 8px 0; border-bottom: 1px solid #ddd; }
                    .item-name { font-weight: bold; font-size: 14px; }
                    .item-extras { font-size: 12px; color: #666; padding-left: 10px; }
                    .item-price { text-align: right; font-weight: bold; font-size: 14px; }
                    .total { margin-top: 15px; padding-top: 15px; border-top: 2px solid #000; text-align: right; }
                    .total-label { font-size: 16px; font-weight: bold; }
                    .total-value { font-size: 24px; font-weight: bold; }
                    @media print {
                        body { padding: 5px; }
                    }
                </style>
            </head>
            <body>
                ${clone.innerHTML}
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.onload = function() { 
            printWindow.print(); 
        };
    }
</script>

<?php require __DIR__ . '/extras-modal.php'; ?>

<!-- Scripts do PDV -->
<script src="<?= BASE_URL ?>/js/pdv/state.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart-core.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart-ui.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/cart-extras-modal.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables-mesa.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables-cliente.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/tables-client-modal.js?v=<?= time() ?>"></script>

<!-- Módulos de Checkout (ordem de dependência obrigatória) -->
<script src="<?= BASE_URL ?>/js/pdv/checkout/helpers.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/state.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/totals.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/ui.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/payments.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/services/checkout-service.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/services/checkout-validator.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/submit.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/orderType.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/retirada.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/entrega.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/pickup.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/flow.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/pdv/checkout/index.js?v=<?= time() ?>"></script>

<script src="<?= BASE_URL ?>/js/pdv.js?v=<?= time() ?>"></script>
