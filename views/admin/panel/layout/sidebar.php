<?php use App\Core\ViewHelper; ?>

<aside class="sidebar">
    <div class="brand-area">
        <a href="<?= BASE_URL ?>/admin/loja/config" title="Configurações da Loja">
            <?php if (!empty($_SESSION['loja_ativa_logo'])): ?>
                <img src="<?= BASE_URL ?>/uploads/<?= $_SESSION['loja_ativa_logo'] ?>" class="store-logo" alt="Logo">
            <?php else: ?>
                <div class="brand-icon">
                    <i data-lucide="store" color="white" size="32"></i>
                </div>
            <?php endif; ?>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/admin/loja/pdv" class="nav-item <?= ViewHelper::isRouteActive('pdv') ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard" size="36"></i>
            <span class="nav-label">Balcão</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/loja/mesas" class="nav-item <?= ViewHelper::isRouteActive('mesas') ? 'active' : '' ?>">
            <i data-lucide="utensils-crossed" size="36"></i>
            <span class="nav-label">Mesas</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/loja/cardapio" class="nav-item <?= ViewHelper::isRouteActive('cardapio') ? 'active' : '' ?>">
            <i data-lucide="book-open" size="36"></i>
            <span class="nav-label">Cardápio</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/loja/delivery" class="nav-item <?= ViewHelper::isRouteActive('delivery') ? 'active' : '' ?>">
            <i data-lucide="bike" size="36"></i>
            <span class="nav-label">Delivery</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/loja/produtos" class="nav-item <?= ViewHelper::isRouteActive('produtos') ? 'active' : '' ?>">
            <i data-lucide="package" size="36"></i>
            <span class="nav-label">Estoque</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/loja/caixa" class="nav-item <?= ViewHelper::isRouteActive('caixa') ? 'active' : '' ?>">
            <i data-lucide="wallet" size="36"></i>
            <span class="nav-label">Caixa</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/loja/configuracoes-gerais" class="nav-item <?= ViewHelper::isRouteActive('configuracoes-gerais') ? 'active' : '' ?>">
            <i data-lucide="settings" size="36"></i>
            <span class="nav-label">Config</span>
        </a>





        <a href="<?= BASE_URL ?>/admin" class="nav-item center-exit" title="Sair">
            <i data-lucide="log-out" size="22"></i>
        </a>
    </nav>
</aside>
