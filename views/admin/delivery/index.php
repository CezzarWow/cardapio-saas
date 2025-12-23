<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
        
        <div style="text-align: center; max-width: 500px;">
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                <i data-lucide="bike" size="60" style="color: #d97706;"></i>
            </div>
            
            <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
                🛵 Delivery em Construção
            </h1>
            
            <p style="color: #6b7280; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem;">
                Estamos desenvolvendo uma experiência completa de delivery para seu estabelecimento. 
                Em breve você poderá gerenciar pedidos, entregadores e muito mais!
            </p>
            
            <div style="background: #dbeafe; border: 1px solid #93c5fd; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 12px;">
                <i data-lucide="info" size="24" style="color: #2563eb;"></i>
                <span style="color: #1e40af; font-size: 0.95rem;">
                    Fique atento às atualizações! Esta funcionalidade está em desenvolvimento.
                </span>
            </div>
        </div>
        
    </div>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
