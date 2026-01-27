<?php use App\Core\ViewHelper;

?>
<?php
// Detecta contexto de mesa ou cliente (variÃ¡veis disponÃ­veis do PdvController)
$isInMesaContext = !empty($mesa_id);
$isInClienteContext = !empty($contaAberta['client_id'] ?? null);
$isInMesasSection = $isInMesaContext || $isInClienteContext;
$lojaLogoFile = basename((string) ($_SESSION['loja_ativa_logo'] ?? ''));
?>

<aside class="sidebar">
    <div class="brand-area">
        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/config" title="ConfiguraÃ§Ãµes da Loja">
            <?php if ($lojaLogoFile !== ''): ?>
                <img src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/uploads/<?= \App\Helpers\ViewHelper::e($lojaLogoFile) ?>" class="store-logo" alt="Logo">
            <?php else: ?>
                <div class="brand-icon">
                    <i data-lucide="store" color="white" size="32"></i>
                </div>
            <?php endif; ?>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/pdv" class="nav-item <?= (!$isInMesasSection && ViewHelper::isRouteActive('pdv')) ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard" size="36"></i>
            <span class="nav-label">BalcÃ£o</span>
        </a>

        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/mesas" class="nav-item <?= ($isInMesasSection || ViewHelper::isRouteActive('mesas')) ? 'active' : '' ?>">
            <i data-lucide="utensils-crossed" size="36"></i>
            <span class="nav-label">Mesas</span>
        </a>

        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/delivery" class="nav-item <?= ViewHelper::isRouteActive('delivery') ? 'active' : '' ?>">
            <i data-lucide="bike" size="36"></i>
            <span class="nav-label">Delivery</span>
        </a>

        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/cardapio" class="nav-item <?= ViewHelper::isRouteActive('cardapio') ? 'active' : '' ?>">
            <i data-lucide="book-open" size="36"></i>
            <span class="nav-label">CardÃ¡pio</span>
        </a>

        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/catalogo" class="nav-item <?= ViewHelper::isRouteActive('catalogo') || ViewHelper::isRouteActive('produtos') ? 'active' : '' ?>">
            <i data-lucide="package" size="36"></i>
            <span class="nav-label">Estoque</span>
        </a>

        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/caixa" class="nav-item <?= ViewHelper::isRouteActive('caixa') ? 'active' : '' ?>">
            <i data-lucide="wallet" size="36"></i>
            <span class="nav-label">Caixa</span>
        </a>

        <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin" class="nav-item center-exit" title="Sair">
            <i data-lucide="log-out" size="22"></i>
        </a>
    </nav>
</aside>


