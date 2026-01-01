<?php
/**
 * Partial: Grid de Mesas
 * Variáveis esperadas: $tables (array de mesas)
 */
?>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
    <?php foreach ($tables as $mesa): ?>
        <?php 
            $isOccupied = ($mesa['status'] == 'ocupada');
            
            // Visual Clássico que você gostou
            if ($isOccupied) {
                // OCUPADA: Fundo Vermelho claro, Borda Vermelha
                $bg = '#fef2f2';
                $border = '#ef4444';
                $textColor = '#b91c1c';
                $iconColor = '#ef4444';
                $statusText = 'OCUPADA';
                $valor = 'R$ ' . number_format($mesa['current_total'], 2, ',', '.');
            } else {
                // LIVRE: Fundo Branco, Borda Verde/Cinza
                $bg = 'white';
                $border = '#22c55e'; // Borda verde para destacar que está livre
                $textColor = '#15803d';
                $iconColor = '#22c55e';
                $statusText = 'LIVRE';
                $valor = 'Disponível';
            }
        ?>

        <div onclick="abrirMesa(<?= $mesa['id'] ?>, <?= $mesa['number'] ?>)" 
             style="background: <?= $bg ?>; border: 2px solid <?= $border ?>; border-radius: 12px; cursor: pointer; transition: transform 0.1s; position: relative; overflow: hidden; height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            
            <span style="font-size: 2.2rem; font-weight: 800; color: <?= $textColor ?>; line-height: 1;">
                <?= $mesa['number'] ?>
            </span>
            
            <span style="font-size: 0.75rem; font-weight: 700; color: <?= $iconColor ?>; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;">
                <?= $statusText ?>
            </span>

            <?php if ($isOccupied): ?>
                <div style="margin-top: 8px; background: rgba(255,255,255,0.6); padding: 2px 8px; border-radius: 4px; font-weight: 800; color: <?= $textColor ?>; font-size: 0.9rem;">
                    <?= $valor ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
