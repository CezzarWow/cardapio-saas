<?php 
/**
 * ============================================
 * ADMIN CARDÁPIO - PÁGINA PRINCIPAL
 * Arquivo: views/admin/cardapio/index.php
 * ============================================
 */

require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';
?>

<!-- Cardápio Admin - CSS Modular -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/base.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/tabs.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/cards.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/forms.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/toggles.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/grids.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/buttons.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/utilities.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/featured/index.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/delivery-tab.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/responsive.css?v=<?= time() ?>">

<main class="main-content">
    <?php require __DIR__ . '/../panel/layout/messages.php'; ?>
    
    <div class="cardapio-admin-container">
        
        <!-- Header -->
        <div class="cardapio-admin-header" style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 class="cardapio-admin-title">Configurações do Cardápio</h1>
                <p class="cardapio-admin-subtitle">Configure como seu cardápio web funciona</p>
            </div>
        </div>

        <!-- Formulário (inicia aqui para incluir o botão Salvar na linha das abas) -->
        <form id="formCardapio" method="POST" action="<?= BASE_URL ?>/admin/loja/cardapio/salvar" onsubmit="return window.CardapioAdmin.validateForm()">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
        
        <!-- Abas + Ações (mesma linha) -->
        <div class="cardapio-admin-tabs" style="justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="cardapio-admin-tab-btn active" data-tab="operacao">
                    <i data-lucide="settings"></i>
                    <span>Operação</span>
                </button>
                <button type="button" class="cardapio-admin-tab-btn" data-tab="horarios">
                    <i data-lucide="calendar"></i>
                    <span>Horários</span>
                </button>
                <button type="button" class="cardapio-admin-tab-btn" data-tab="delivery">
                    <i data-lucide="truck"></i>
                    <span>Delivery</span>
                </button>

                <button type="button" class="cardapio-admin-tab-btn" data-tab="promocoes">
                    <i data-lucide="tag"></i>
                    <span>Promoções</span>
                </button>
                <button type="button" class="cardapio-admin-tab-btn" data-tab="destaques">
                    <i data-lucide="star"></i>
                    <span>Destaques</span>
                </button>
            </div>
            
            <!-- Ações à direita -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="<?= BASE_URL ?>/cardapio/<?= htmlspecialchars($restaurantSlug) ?>" 
                   target="_blank" 
                   class="cardapio-admin-btn" 
                   style="background: white; border: 1px solid #e2e8f0; color: #2563eb; padding: 10px 16px;">
                    <i data-lucide="external-link"></i>
                    Ver Cardápio
                </a>
                
                <button type="submit" class="cardapio-admin-btn cardapio-admin-btn-primary cardapio-admin-btn-save" style="padding: 10px 16px;">
                    <i data-lucide="save"></i>
                    Salvar
                </button>
            </div>
        </div>


            <!-- Aba Operação (antiga WhatsApp + Status) -->
            <div class="cardapio-admin-tab-content active" id="tab-operacao">
                <?php require __DIR__ . '/partials/_tab_operacao.php'; ?>
            </div>

            <!-- Aba Horários -->
            <div class="cardapio-admin-tab-content" id="tab-horarios">
                <?php require __DIR__ . '/partials/_tab_horarios.php'; ?>
            </div>

            <!-- Aba Delivery -->
            <div class="cardapio-admin-tab-content" id="tab-delivery">
                <?php require __DIR__ . '/partials/_tab_delivery.php'; ?>
            </div>



            <!-- Aba Promoções -->
            <div class="cardapio-admin-tab-content" id="tab-promocoes">
                <?php require __DIR__ . '/partials/_tab_promocoes.php'; ?>
            </div>

            <!-- Aba Destaques -->
            <div class="cardapio-admin-tab-content" id="tab-destaques">
                <?php require __DIR__ . '/partials/_tab_destaques.php'; ?>
            </div>

        </form>

    </div>
</main>

<!-- Cardapio Admin - Modular Scripts (v2.0) -->
<script src="<?= BASE_URL ?>/js/cardapio-admin/utils.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/pix.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/whatsapp.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms-tabs.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms-toggles.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms-validation.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms-hours.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms-delivery.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms-cards.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/combos.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/combos-save.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/combos-edit.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/combos-helpers.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/combos-ui.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/featured.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/featured-edit.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/featured-dragdrop.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/featured-tabs.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/featured-categories.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/index.js?v=<?= time() ?>"></script>

<script>
    // Limpar parâmetros da URL (evita mensagem repetida ao dar F5)
    if (window.history.replaceState) {
        const url = new URL(window.location.href);
        if (url.searchParams.has('success') || url.searchParams.has('error')) {
            url.searchParams.delete('success');
            url.searchParams.delete('error');
            window.history.replaceState({path: url.href}, '', url.href);
        }
    }
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
