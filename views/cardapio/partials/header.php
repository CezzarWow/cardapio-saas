<?php
/**
 * PARTIAL: Header do Cardápio
 * Espera:
 * - $restaurant (array)
 * - $cardapioConfig (array)
 */
?>
<!-- HEADER DO CARDÁPIO -->
<header class="cardapio-header">
    <div class="cardapio-header-content">
        <div class="cardapio-brand">
            <?php if (!empty($restaurant['logo'])): ?>
                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($restaurant['logo']) ?>" alt="Logo" class="cardapio-logo">
            <?php else: ?>
                <div class="cardapio-logo-placeholder">
                    <i data-lucide="utensils" size="28"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="cardapio-title"><?= htmlspecialchars($restaurant['name']) ?></h1>
                <p class="cardapio-subtitle">Faça seu pedido</p>
            </div>
        </div>
        <div class="cardapio-info" style="display: flex; flex-direction: column; gap: 6px; width: max-content;">
            <p class="cardapio-delivery-time" style="display: flex; align-items: center; justify-content: center; margin: 0; white-space: nowrap;">
                <i data-lucide="clock" size="14" style="margin-right: 6px;"></i>
                <?= ($cardapioConfig['delivery_time_min'] ?? 30) ?>-<?= ($cardapioConfig['delivery_time_max'] ?? 45) ?> min
            </p>
            
            <?php if ($cardapioConfig['is_open_now'] ?? true): ?>
                <div style="display: flex; align-items: center; justify-content: center; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; background: #dcfce7; color: #16a34a; width: 100%; box-sizing: border-box;">
                    <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%; margin-right: 6px; flex-shrink: 0;"></span>
                    Aberto
                </div>
            <?php else: ?>
                <div style="display: flex; align-items: center; justify-content: center; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; background: #fee2e2; color: #dc2626; width: 100%; box-sizing: border-box;">
                    <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%; margin-right: 6px; flex-shrink: 0;"></span>
                    <?php
                        $reason = $cardapioConfig['closed_reason'] ?? '';
                if ($reason === 'outside_hours' && ($cardapioConfig['today_hours'] ?? null)) {
                    $hours = $cardapioConfig['today_hours'];
                    echo 'Abre ' . substr($hours['open_time'], 0, 5);
                } elseif ($reason === 'day_closed') {
                    echo 'Fechado hoje';
                } else {
                    echo 'Fechado';
                }
                ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
