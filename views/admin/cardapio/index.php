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

<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin.css?v=<?= time() ?>">

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
                <?php require __DIR__ . '/partials/_tab_whatsapp.php'; ?>
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

<script src="<?= BASE_URL ?>/js/cardapio-admin.js?v=<?= time() ?>"></script>

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
