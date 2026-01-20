<?php
/**
 * Estoque Partial - Para carregamento no SPA Shell
 * 
 * Esta partial é carregada via AJAX pelo AdminSPA.
 * Contém o dashboard do estoque SEM header/sidebar (já estão no shell).
 * 
 * Variáveis recebidas do controller:
 * - $totalProducts
 * - $totalCategories
 */
?>

<!-- Stock Dashboard CSS (cache bust) -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/stock/index.css?v=<?= time() ?>">

<!-- Ajuste de alinhamento igual ao Delivery -->
<style>
    .stock-dashboard-container {
        padding: 15px 20px 20px 20px !important;
        width: 100%;
        background: #f8fafc;
    }
    .stock-dashboard-container > div:first-child {
        margin-bottom: 15px !important;
        height: 31px !important;
    }
    .stock-dashboard-container h1 {
        font-size: 1.3rem !important;
        line-height: 31px !important;
    }
    /* Container de conteúdo com scroll */
    .stock-content-container {
        overflow-y: auto;
        max-height: calc(100vh - 200px);
        padding-bottom: 80px;
    }
    /* Padding inferior no grid para compensar rodapé fixo */
    .stock-products-grid {
        padding-bottom: 100px !important;
    }
    /* Rodapé fixo */
    .delivery-footer {
        position: fixed;
        bottom: 0;
        left: 160px;
        right: 0;
        height: 50px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        z-index: 100;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    .delivery-footer-text {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 500;
    }
</style>

<div class="stock-dashboard-container">
    
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
        <div id="stock-content">
            <!-- Conteúdo carregado pelo StockSPA.js -->
        </div>
    </div>

    <!-- Espaçador Estrutural -->
    <div class="stock-spacer-bottom"></div>

</div>

<!-- Rodapé Sutil Fixo -->
<div class="delivery-footer">
    <span class="delivery-footer-text">📦 Área de Estoque</span>
</div>

<!-- Script marker for AdminSPA to execute -->
<script data-spa-script="stock-spa" src="<?= BASE_URL ?>/js/admin/stock-spa.js?v=<?= time() ?>"></script>
