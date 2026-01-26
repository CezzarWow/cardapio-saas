<?php
/**
 * Partial: Grid de Mesas
 * Variáveis esperadas: $tables (array de mesas)
 */
?>
<div class="table-grid">
    <?php foreach ($tables as $mesa): ?>
        <?php
        $isOccupied = ($mesa['status'] == 'ocupada');
        $cardClass = $isOccupied ? 'table-card--ocupada' : 'table-card--livre';
        $statusText = $isOccupied ? 'OCUPADA' : 'LIVRE';
        $valor = $isOccupied ? 'R$ ' . number_format($mesa['order_total'] ?? 0, 2, ',', '.') : '';
        ?>

        <div class="table-card <?= $cardClass ?>" 
             onclick="abrirMesa(<?= $mesa['id'] ?>, <?= $mesa['number'] ?>)"
             tabindex="0"
             role="button"
             aria-label="Mesa <?= $mesa['number'] ?> - <?= $statusText ?>"
             onkeypress="if(event.key==='Enter') abrirMesa(<?= $mesa['id'] ?>, <?= $mesa['number'] ?>)">
            
            <?php if ($isOccupied && !empty($mesa['credit_limit']) && $mesa['credit_limit'] > 0): ?>
                <span class="table-card__badge">CREDIÁRIO</span>
            <?php endif; ?>

            <?php if ($isOccupied && !empty($mesa['order_type'])): ?>
                <?php if ($mesa['order_type'] === 'delivery' || $mesa['order_type'] === 'entrega'): ?>
                    <span class="table-card__badge" style="background:#059669; top: 25px;">ENTREGA</span>
                <?php elseif ($mesa['order_type'] === 'pickup' || $mesa['order_type'] === 'retirada'): ?>
                    <span class="table-card__badge" style="background:#0284c7; top: 25px;">RETIRADA</span>
                <?php endif; ?>
            <?php endif; ?>

            <span class="table-card__number"><?= $mesa['number'] ?></span>
            <span class="table-card__status"><?= $statusText ?></span>

            <?php if ($isOccupied): ?>
                <span class="table-card__value"><?= $valor ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
