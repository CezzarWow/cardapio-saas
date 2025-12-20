<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Mapa de Mesas</h1>
            <p style="color: #6b7280;">Gerencie os pedidos por mesa</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1.5rem;">
            
            <?php foreach ($tables as $mesa): ?>
                <?php 
                    // Define a cor baseada no status
                    $bg = ($mesa['status'] == 'livre') ? 'white' : '#fee2e2'; // Branco ou Vermelho claro
                    $border = ($mesa['status'] == 'livre') ? '#e5e7eb' : '#ef4444'; // Cinza ou Vermelho
                    $iconColor = ($mesa['status'] == 'livre') ? '#2563eb' : '#b91c1c'; // Azul ou Vermelho Escuro
                    $statusText = ucfirst($mesa['status']);
                ?>

                <div onclick="abrirMesa(<?= $mesa['id'] ?>, <?= $mesa['number'] ?>)" 
                     style="background: <?= $bg ?>; border: 2px solid <?= $border ?>; border-radius: 16px; padding: 1.5rem; text-align: center; cursor: pointer; transition: transform 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    
                    <div style="font-size: 1.25rem; font-weight: 800; color: #1f2937; margin-bottom: 0.5rem;">
                        Mesa <?= $mesa['number'] ?>
                    </div>
                    
                    <div style="display: flex; justify-content: center; margin-bottom: 0.5rem;">
                        <i data-lucide="armchair" size="48" color="<?= $iconColor ?>"></i>
                    </div>

                    <span style="font-size: 0.8rem; font-weight: 600; color: <?= $iconColor ?>; text-transform: uppercase;">
                        <?= $statusText ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <div style="border: 2px dashed #e5e7eb; border-radius: 16px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; color: #9ca3af; min-height: 180px;">
                <i data-lucide="plus" size="32"></i>
                <span style="font-size: 0.9rem; margin-top: 10px;">Nova Mesa</span>
            </div>

        </div>

    </div>
</main>

<script>
function abrirMesa(id, numero) {
    // Redireciona para o PDV passando o ID e o Número da mesa na URL
    window.location.href = 'pdv?mesa_id=' + id + '&mesa_numero=' + numero;
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
