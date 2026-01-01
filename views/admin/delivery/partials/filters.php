<?php
/**
 * Partial: Filtros de status do Delivery
 * Tabs com JavaScript (instantâneo, sem reload)
 */
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
}
.delivery-filter-btn:hover {
    background: #f1f5f9;
}
.delivery-filter-btn.active {
    background: #1e293b;
    color: white;
    border-color: #1e293b;
}
.delivery-filter-btn[data-status="novo"].active { background: #3b82f6; border-color: #3b82f6; }
.delivery-filter-btn[data-status="preparo"].active { background: #8b5cf6; border-color: #8b5cf6; }
.delivery-filter-btn[data-status="rota"].active { background: #22c55e; border-color: #22c55e; }
.delivery-filter-btn[data-status="entregue"].active { background: #059669; border-color: #059669; }
.delivery-filter-btn[data-status="cancelado"].active { background: #dc2626; border-color: #dc2626; }
</style>

<div class="delivery-filters" style="display: flex; gap: 10px; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <button type="button" class="delivery-filter-btn active" data-status="todos" onclick="DeliveryTabs.filter('todos')">
        Todos
    </button>
    <button type="button" class="delivery-filter-btn" data-status="novo" onclick="DeliveryTabs.filter('novo')">
        🆕 Novo
    </button>
    <button type="button" class="delivery-filter-btn" data-status="preparo" onclick="DeliveryTabs.filter('preparo')">
        🍳 Preparo
    </button>
    <button type="button" class="delivery-filter-btn" data-status="rota" onclick="DeliveryTabs.filter('rota')">
        🛵 Rota
    </button>
    <button type="button" class="delivery-filter-btn" data-status="entregue" onclick="DeliveryTabs.filter('entregue')">
        ✅ Entregue
    </button>
    <button type="button" class="delivery-filter-btn" data-status="cancelado" onclick="DeliveryTabs.filter('cancelado')">
        ❌ Cancelado
    </button>
</div>
