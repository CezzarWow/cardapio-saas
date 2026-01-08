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
    <div class="page-title">
        <?php if ($mesa_numero): ?>
            <h1 style="color: #b91c1c;">Mesa <?= $mesa_numero ?></h1>
            <p>Gerenciando Pedido</p>
        <?php elseif (!empty($contaAberta) && !$mesa_id): ?>
            <h1 style="color: #ea580c;">Comanda #<?= $contaAberta['id'] ?></h1>
            <p style="color: #9a3412; font-weight: 600;">Cliente: <?= htmlspecialchars($contaAberta['client_name'] ?? 'Cliente') ?></p>
        <?php else: ?>
            <h1>Balcão de Vendas</h1>
            <p>Venda Rápida</p>
        <?php endif; ?>
    </div>
    
    <div class="search-bar">
        <i data-lucide="search" class="search-icon"></i>
        <input type="text" id="product-search-input" placeholder="Buscar produtos (F2)..." class="search-input" />
    </div>

    <div class="status-badge">
        <div class="status-dot"></div> Online
    </div>
</header>
