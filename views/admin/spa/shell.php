<?php
/**
 * Shell SPA - Layout Principal com Sidebar Fixa
 * 
 * Este arquivo é o "app shell" do SPA.
 * - Header e Sidebar são fixos
 * - Conteúdo é carregado dinamicamente via AJAX no #spa-content
 */

use App\Core\ViewHelper;

\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
?>
<!-- SPA CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/spa.css?v=<?= APP_VERSION ?>">

<?php // Sidebar com navegação SPA (links interceptados pelo AdminSPA.js) ?>
<aside class="sidebar">
    <div class="brand-area">
        <a href="<?= BASE_URL ?>/admin/loja/config" title="Configurações da Loja">
            <?php if (!empty($_SESSION['loja_ativa_logo'])): ?>
                <img src="<?= BASE_URL ?>/uploads/<?= \App\Helpers\ViewHelper::e($_SESSION['loja_ativa_logo'] ?? '') ?>" class="store-logo" alt="Logo">
            <?php else: ?>
                <div class="brand-icon">
                    <i data-lucide="store" color="white" size="32"></i>
                </div>
            <?php endif; ?>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#balcao" class="nav-item" data-section="balcao">
            <i data-lucide="layout-dashboard" size="36"></i>
            <span class="nav-label">Balcão</span>
        </a>

        <a href="#mesas" class="nav-item" data-section="mesas">
            <i data-lucide="utensils-crossed" size="36"></i>
            <span class="nav-label">Mesas</span>
        </a>

        <a href="#delivery" class="nav-item" data-section="delivery">
            <i data-lucide="bike" size="36"></i>
            <span class="nav-label">Delivery</span>
        </a>

        <a href="#cardapio" class="nav-item" data-section="cardapio">
            <i data-lucide="book-open" size="36"></i>
            <span class="nav-label">Cardápio</span>
        </a>

        <a href="#estoque" class="nav-item" data-section="estoque">
            <i data-lucide="package" size="36"></i>
            <span class="nav-label">Estoque</span>
        </a>

        <a href="#caixa" class="nav-item" data-section="caixa">
            <i data-lucide="wallet" size="36"></i>
            <span class="nav-label">Caixa</span>
        </a>

        <a href="<?= BASE_URL ?>/admin" class="nav-item center-exit" title="Sair">
            <i data-lucide="log-out" size="22"></i>
        </a>
    </nav>
</aside>

<?php // Container principal do SPA - conteúdo carregado via AJAX ?>
<main class="main-content">
    <div id="spa-content" class="spa-content-container">
        <!-- Skeleton inicial enquanto carrega primeira seção -->
        <div class="skeleton-container">
            <div class="skeleton-header"></div>
            <div class="skeleton-grid">
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
            </div>
        </div>
    </div>
</main>

<?php // Scripts do SPA ?>
<script>if(typeof BASE_URL === 'undefined') { const BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>; window.BASE_URL = BASE_URL; }</script>
<script src="<?= BASE_URL ?>/js/admin/spa-config.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/spa-ui.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/admin-spa.js?v=<?= APP_VERSION ?>"></script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
