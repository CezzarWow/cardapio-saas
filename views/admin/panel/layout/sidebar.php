<?php use App\Core\ViewHelper; ?>

<aside class="sidebar">
    <div class="brand-icon">
        <i data-lucide="store" color="white" size="36"></i>
    </div>
    
    <nav style="display: flex; flex-direction: column; gap: 10px; width: 100%; align-items: center;">
        <a href="../../admin/loja/pdv" class="nav-item <?= ViewHelper::isRouteActive('pdv') ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard" size="36"></i>
            <span class="nav-label">Balcão</span>
        </a>

        <a href="../../admin/loja/mesas" class="nav-item <?= ViewHelper::isRouteActive('mesas') ? 'active' : '' ?>">
            <i data-lucide="utensils-crossed" size="36"></i>
            <span class="nav-label">Mesas</span>
        </a>

        <a href="../../admin/loja/delivery" class="nav-item <?= ViewHelper::isRouteActive('delivery') ? 'active' : '' ?>">
            <i data-lucide="bike" size="36"></i>
            <span class="nav-label">Delivery</span>
        </a>

        <a href="../../admin/loja/produtos" class="nav-item <?= ViewHelper::isRouteActive('produtos') ? 'active' : '' ?>">
            <i data-lucide="package" size="36"></i>
            <span class="nav-label">Estoque</span>
        </a>

        <a href="../../admin/loja/vendas" class="nav-item <?= ViewHelper::isRouteActive('vendas') ? 'active' : '' ?>">
            <i data-lucide="shopping-bag" size="36"></i>
            <span class="nav-label">Vendas</span>
        </a>

        <a href="../../admin/loja/caixa" class="nav-item <?= ViewHelper::isRouteActive('caixa') ? 'active' : '' ?>">
            <i data-lucide="wallet" size="36"></i>
            <span class="nav-label">Caixa</span>
        </a>

        <a href="../../admin/loja/config" class="nav-item <?= ViewHelper::isRouteActive('config') ? 'active' : '' ?>">
            <i data-lucide="settings" size="36"></i>
            <span class="nav-label">Config</span>
        </a>
    </nav>

    <div class="nav-bottom">
        <a href="../../admin" class="nav-item" title="Sair">
            <i data-lucide="log-out" size="22"></i>
        </a>
    </div>
</aside>
