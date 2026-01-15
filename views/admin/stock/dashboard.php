<?php
/**
 * ============================================
 * STOCK DASHBOARD - SPA ORQUESTRADOR
 * Arquivo: views/admin/stock/dashboard.php
 * 
 * Página principal do catálogo unificado.
 * Tabs carregam conteúdo via AJAX sem reload.
 * ============================================
 */

\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<!-- Stock Dashboard - CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/stock/index.css?v=<?= time() ?>">

<main class="main-content" style="flex-direction: column;">
    <?php \App\Core\View::renderFromScope('admin/panel/layout/messages.php', get_defined_vars()); ?>
    
    <div class="stock-dashboard-container" style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Gerenciar Catálogo</h1>
            <a href="<?= BASE_URL ?>/admin/loja/produtos/novo" class="btn" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="plus" style="width: 18px; height: 18px;"></i> Novo Produto
            </a>
        </div>

        <!-- Tabs SPA -->
        <div class="sticky-tabs" style="margin-bottom: 20px;">
            <div class="stock-tabs" id="stock-spa-tabs">
                <button type="button" class="stock-tab active" data-tab="produtos">
                    <i data-lucide="package" style="width: 16px; height: 16px;"></i>
                    Produtos
                    <span class="tab-badge"><?= $totalProducts ?? 0 ?></span>
                </button>
                <button type="button" class="stock-tab" data-tab="categorias">
                    <i data-lucide="tags" style="width: 16px; height: 16px;"></i>
                    Categorias
                    <span class="tab-badge"><?= $totalCategories ?? 0 ?></span>
                </button>
                <button type="button" class="stock-tab" data-tab="adicionais">
                    <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                    Adicionais
                </button>
                <button type="button" class="stock-tab" data-tab="reposicao">
                    <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
                    Reposição
                </button>
                <button type="button" class="stock-tab" data-tab="movimentacoes">
                    <i data-lucide="activity" style="width: 16px; height: 16px;"></i>
                    Movimentações
                </button>
            </div>
        </div>

        <!-- Container de Conteúdo AJAX -->
        <div id="stock-content-container" class="stock-content-container">
            <!-- Loading Indicator -->
            <div class="stock-loading" id="stock-loading" style="display: none;">
                <div class="stock-loading-spinner"></div>
                <span>Carregando...</span>
            </div>
            
            <!-- Conteúdo será carregado aqui via AJAX -->
            <div id="stock-content">
                <!-- Conteúdo inicial carregado pelo servidor -->
            </div>
        </div>

    </div>
</main>

<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/admin/stock-spa.js?v=<?= time() ?>" defer></script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
