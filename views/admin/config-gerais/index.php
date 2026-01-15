<?php
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
        
        <div style="text-align: center; max-width: 500px;">
            <div style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); border-radius: 50%; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                <i data-lucide="settings" size="60" style="color: #9333ea;"></i>
            </div>
            
            <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
                ⚙️ Configurações em Construção
            </h1>
            
            <p style="color: #6b7280; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem;">
                Estamos preparando opções avançadas de configuração para seu estabelecimento. 
                Em breve você poderá personalizar horários, taxas, notificações e muito mais!
            </p>
            
            <div style="background: #f3e8ff; border: 1px solid #c4b5fd; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 12px;">
                <i data-lucide="info" size="24" style="color: #7c3aed;"></i>
                <span style="color: #5b21b6; font-size: 0.95rem;">
                    Para configurar logo e dados da loja, clique no ícone do topo do menu.
                </span>
            </div>
        </div>
        
    </div>
</main>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
