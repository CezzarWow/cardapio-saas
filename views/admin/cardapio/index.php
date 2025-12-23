<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
        
        <div style="text-align: center; max-width: 500px;">
            <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 50%; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                <i data-lucide="hard-hat" size="60" style="color: #0284c7;"></i>
            </div>
            
            <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
                🚧 Cardápio em Construção
            </h1>
            
            <p style="color: #6b7280; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem;">
                Estamos trabalhando para trazer uma experiência incrível de gerenciamento do seu cardápio digital. 
                Em breve você poderá personalizar categorias, produtos e muito mais!
            </p>
            
            <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 12px;">
                <i data-lucide="info" size="24" style="color: #d97706;"></i>
                <span style="color: #92400e; font-size: 0.95rem;">
                    Por enquanto, gerencie seus produtos na aba <strong>Estoque</strong>.
                </span>
            </div>
        </div>
        
    </div>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
