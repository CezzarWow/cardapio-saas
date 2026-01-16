<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%; background: #f3f4f6;">
        <div style="background: white; padding: 3rem; border-radius: 20px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
            <div style="background: #dcfce7; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
                <i data-lucide="unlock" size="40" color="#166534"></i>
            </div>
            <h1 style="font-size: 1.8rem; font-weight: 800; color: #1f2937; margin-bottom: 0.5rem;">Iniciar Dia</h1>
            <p style="color: #6b7280; margin-bottom: 2rem;">Informe o Fundo de Troco para abrir o caixa.</p>
            <form action="caixa/abrir" method="POST">
                <?= \App\Helpers\ViewHelper::csrfField() ?>
                <input type="text" name="opening_balance" required placeholder="R$ 0,00" style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1.5rem; font-weight: bold; text-align: center; color: #2563eb; margin-bottom: 20px;">
                <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem; border: none; border-radius: 12px; cursor: pointer; background: #16a34a; color: white; font-weight: bold;">Abrir Novo Caixa</button>
            </form>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
