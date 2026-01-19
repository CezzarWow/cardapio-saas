<?php
/**
 * Partial: Filtros de status do Delivery
 * Tabs com JavaScript (instantâneo, sem reload)
 * AGORA HÍBRIDO: Funciona como Navegação quando está no Histórico
 */
$isHistory = strpos($_SERVER['REQUEST_URI'] ?? '', '/delivery/history') !== false;
?>
<style>
.delivery-filter-btn {
    padding: 8px 16px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
}
.delivery-filter-btn:hover {
    background: #f1f5f9;
}
.delivery-filter-btn.active {
    background: #1e293b;
    color: white;
    border-color: #1e293b;
}
/* Cores específicas para estados ativos */
.delivery-filter-btn[data-status="novo"].active { background: #3b82f6; border-color: #3b82f6; }
.delivery-filter-btn[data-status="preparo"].active { background: #8b5cf6; border-color: #8b5cf6; }
.delivery-filter-btn[data-status="rota"].active { background: #22c55e; border-color: #22c55e; }
.delivery-filter-btn[data-status="entregue"].active { background: #059669; border-color: #059669; }
.delivery-filter-btn[data-status="cancelado"].active { background: #dc2626; border-color: #dc2626; }
</style>

<div class="delivery-filters" style="display: flex; gap: 10px; margin-bottom: 1rem; flex-wrap: wrap;">
    
    <!-- Botão TODOS -->
    <?php if ($isHistory): ?>
        <a href="<?= BASE_URL ?>/admin/loja/delivery" class="delivery-filter-btn">
            Todos
        </a>
    <?php else: ?>
        <button type="button" class="delivery-filter-btn active" data-status="todos" onclick="DeliveryTabs.filter('todos')">
            Todos
        </button>
    <?php endif; ?>

    <!-- Botão NOVO -->
    <?php if ($isHistory): ?>
        <a href="<?= BASE_URL ?>/admin/loja/delivery?status=novo" class="delivery-filter-btn" data-status="novo">
            🆕 Novo
        </a>
    <?php else: ?>
        <button type="button" class="delivery-filter-btn" data-status="novo" onclick="DeliveryTabs.filter('novo')">
            🆕 Novo
        </button>
    <?php endif; ?>

    <!-- Botão PREPARO -->
    <?php if ($isHistory): ?>
        <a href="<?= BASE_URL ?>/admin/loja/delivery?status=preparo" class="delivery-filter-btn" data-status="preparo">
            🍳 Preparo
        </a>
    <?php else: ?>
        <button type="button" class="delivery-filter-btn" data-status="preparo" onclick="DeliveryTabs.filter('preparo')">
            🍳 Preparo
        </button>
    <?php endif; ?>

    <!-- Botão ROTA -->
    <?php if ($isHistory): ?>
        <a href="<?= BASE_URL ?>/admin/loja/delivery?status=rota" class="delivery-filter-btn" data-status="rota">
            🛵 Rota
        </a>
    <?php else: ?>
        <button type="button" class="delivery-filter-btn" data-status="rota" onclick="DeliveryTabs.filter('rota')">
            🛵 Rota
        </button>
    <?php endif; ?>

    <!-- Botão ENTREGUE -->
    <?php if ($isHistory): ?>
        <a href="<?= BASE_URL ?>/admin/loja/delivery?status=entregue" class="delivery-filter-btn" data-status="entregue">
            ✅ Entregue
        </a>
    <?php else: ?>
        <button type="button" class="delivery-filter-btn" data-status="entregue" onclick="DeliveryTabs.filter('entregue')">
            ✅ Entregue
        </button>
    <?php endif; ?>

    <!-- Botão CANCELADO -->
    <?php if ($isHistory): ?>
        <a href="<?= BASE_URL ?>/admin/loja/delivery?status=cancelado" class="delivery-filter-btn" data-status="cancelado">
            ❌ Cancelado
        </a>
    <?php else: ?>
        <button type="button" class="delivery-filter-btn" data-status="cancelado" onclick="DeliveryTabs.filter('cancelado')">
            ❌ Cancelado
        </button>
    <?php endif; ?>
    
    <!-- Botão HISTÓRICO -->
    <a href="<?= BASE_URL ?>/admin/loja/delivery/history" class="delivery-filter-btn <?= $isHistory ? 'active' : '' ?>">
        📋 Histórico
    </a>
</div>
