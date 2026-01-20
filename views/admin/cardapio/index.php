<?php
/**
 * ============================================
 * ADMIN CARDÁPIO - PÁGINA PRINCIPAL
 * Arquivo: views/admin/cardapio/index.php
 * ============================================
 */

\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<!-- Cardápio Admin - CSS Modular -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/base.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/tabs.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/cards.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/forms.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/toggles.css?v=<?= time() ?>">

<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/utilities.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/featured/index.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/delivery-tab.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/responsive.css?v=<?= time() ?>">

<main class="main-content" style="flex-direction: column;">
    <?php \App\Core\View::renderFromScope('admin/panel/layout/messages.php', get_defined_vars()); ?>
    
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
            

            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="<?= BASE_URL ?>/cardapio/<?= htmlspecialchars($restaurantSlug) ?>" 
                   target="_blank" 
                   class="cardapio-admin-btn">

                    <i data-lucide="external-link"></i>
                    Ver Cardápio
                </a>
                
                <button type="submit" class="cardapio-admin-btn cardapio-admin-btn-primary">
                    <i data-lucide="save"></i>
                    Salvar
                </button>
            </div>
        </div><!-- END cardapio-admin-tabs -->



            <!-- Aba Operação (antiga WhatsApp + Status) -->
            <div class="cardapio-admin-tab-content active" id="tab-operacao">
                <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_tab_operacao.php', get_defined_vars()); ?>
            </div>

            <!-- Aba Horários -->
            <div class="cardapio-admin-tab-content" id="tab-horarios">
                <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_tab_horarios.php', get_defined_vars()); ?>
            </div>

            <!-- Aba Delivery -->
            <div class="cardapio-admin-tab-content" id="tab-delivery">
                <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_tab_delivery.php', get_defined_vars()); ?>
            </div>



            <!-- Aba Promoções -->
            <div class="cardapio-admin-tab-content" id="tab-promocoes">
                <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_tab_promocoes.php', get_defined_vars()); ?>
            </div>

            <!-- Aba Destaques -->
            <div class="cardapio-admin-tab-content" id="tab-destaques">
                <?php \App\Core\View::renderFromScope('admin/cardapio/partials/_tab_destaques.php', get_defined_vars()); ?>
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
<script src="<?= BASE_URL ?>/js/cardapio-admin/promo-products.js?v=<?= time() ?>"></script>
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

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
