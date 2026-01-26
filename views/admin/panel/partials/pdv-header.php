<?php
/**
 * PDV-HEADER.PHP - Header do PDV
 *
 * Contém: Banners de edição, Título dinâmico, Barra de busca
 * Variáveis esperadas: $mesa_numero, $mesa_id, $contaAberta, $isEditingPaid, $editingOrderId, $isEditing
 */
?>

<?php if ($isEditingPaid && $editingOrderId): ?>
    <div id="edit-paid-banner" style="background: #dcfce7; border-bottom: 2px solid #22c55e; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="background: #16a34a; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 800;">PAGO</span>
            <span style="font-weight: 700; color: #166534;">Pedido #<?= $editingOrderId ?> - Aguardando Retirada</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= BASE_URL ?>/admin/loja/mesas" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                ← Voltar
            </a>
            <button onclick="cancelPaidOrder(<?= $editingOrderId ?>)" style="background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                Cancelar Pedido
            </button>
        </div>
    </div>
<?php elseif (isset($isEditing) && $isEditing): ?>
    <div style="background: #fff7ed; border-bottom: 1px solid #fed7aa; padding: 10px; text-align: center; display: flex; justify-content: center; align-items: center; gap: 15px;">
        <span style="font-weight: 700; color: #9a3412;">✏️ Você está editando uma venda antiga.</span>
        <a href="pdv/cancelar-edicao" onclick="return confirm('Descartar alterações e restaurar a venda original?')" 
           style="background: #ef4444; color: white; padding: 5px 15px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
            Cancelar Edição
        </a>
    </div>
<?php endif; ?>

<header class="top-header">
    <!-- Barra de Busca (Esquerda) -->
    <div class="search-bar">
        <i data-lucide="search" class="search-icon"></i>
        <input type="text" id="product-search-input" placeholder="Buscar produtos (F2)..." class="search-input" />
    </div>

    <!-- Tipo de Pedido - Apenas no Balcão (sem mesa e sem comanda aberta) -->
    <?php if (!$mesa_id && empty($contaAberta['id'])): ?>
    <div style="display: flex; gap: 10px;">
        <input type="hidden" id="keep_open_value" value="false">
        <input type="hidden" id="selected_order_type" value="local">
        
        <button type="button" class="order-toggle-btn active" data-type="local" onclick="selectOrderType('local', this)"
                style="display: flex; align-items: center; justify-content: center; width: 90px; height: 40px; border: 2px solid #2563eb; border-radius: 8px; background: #eff6ff; color: #2563eb; font-size: 0.9rem; font-weight: 700; cursor: pointer;">
            Local
        </button>
        
        <button type="button" class="order-toggle-btn" data-type="retirada" onclick="selectOrderType('retirada', this)"
                style="display: flex; align-items: center; justify-content: center; width: 90px; height: 40px; border: 2px solid #cbd5e1; border-radius: 8px; background: white; color: #1e293b; font-size: 0.9rem; font-weight: 700; cursor: pointer;">
            Retirada
        </button>
        
        <button type="button" class="order-toggle-btn" data-type="entrega" onclick="selectOrderType('entrega', this)"
                style="display: flex; align-items: center; justify-content: center; width: 90px; height: 40px; border: 2px solid #cbd5e1; border-radius: 8px; background: white; color: #1e293b; font-size: 0.9rem; font-weight: 700; cursor: pointer;">
            Entrega
        </button>
    </div>
    <?php else: ?>
    <!-- Em Mesa/Comanda: hidden inputs -->
    <?php
        // [FIX] Respeita o tipo da comanda existente se houver
        $currentType = $contaAberta['order_type'] ?? 'local';
        // Mapeia tipos legados se necessário
        if ($currentType == 'balcao') $currentType = 'local';
    ?>
    <input type="hidden" id="keep_open_value" value="false">
    <input type="hidden" id="selected_order_type" value="<?= $currentType ?>">
    <?php endif; ?>

</header>
