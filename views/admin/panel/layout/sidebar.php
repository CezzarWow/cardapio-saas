<?php use App\Core\ViewHelper; ?>

<aside class="sidebar">
    <div class="brand-icon">
        <i data-lucide="store" color="white"></i>
    </div>
    
    <nav style="display: flex; flex-direction: column; gap: 10px; width: 100%; align-items: center;">
        <a href="pdv" class="nav-item <?= ViewHelper::isRouteActive('pdv') ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard" size="22"></i>
            <span class="nav-label">Balcão</span>
        </a>

        <a href="mesas" class="nav-item <?= ViewHelper::isRouteActive('mesas') ? 'active' : '' ?>">
            <i data-lucide="utensils-crossed" size="22"></i>
            <span class="nav-label">Mesas</span>
        </a>

        <a href="produtos" class="nav-item <?= ViewHelper::isRouteActive('produtos') ? 'active' : '' ?>">
            <i data-lucide="package" size="22"></i>
            <span class="nav-label">Estoque</span>
        </a>

        <a href="categorias" class="nav-item <?= ViewHelper::isRouteActive('categorias') ? 'active' : '' ?>">
            <i data-lucide="tags" size="22"></i>
            <span class="nav-label">Categ.</span>
        </a>
    </nav>

    <div class="nav-bottom">
        <a href="../../admin" class="nav-item" title="Sair">
            <i data-lucide="log-out" size="22"></i>
        </a>
    </div>
</aside>
