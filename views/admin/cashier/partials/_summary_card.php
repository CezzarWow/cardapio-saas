<?php
/**
 * Summary Card Component
 * @param string $label - Título do card
 * @param float $value - Valor a exibir
 * @param string $color - Cor da borda esquerda (hex)
 * @param string $textColor - Cor do texto do valor (default: cor da borda)
 * @param string $subtitle - Subtítulo opcional
 */
if (!function_exists('renderSummaryCard')) {
    function renderSummaryCard($label, $value, $color, $textColor = null, $subtitle = null)
    {
        $textColor = $textColor ?? $color;
        ?>
        <div style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid <?= $color ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <span style="display: block; color: #6b7280; font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($label) ?></span>
            <span style="display: block; font-size: 1.5rem; font-weight: 800; color: <?= $textColor ?>; margin-top: 5px;">
                R$ <?= number_format($value, 2, ',', '.') ?>
            </span>
            <?php if ($subtitle): ?>
                <small style="color: #9ca3af; font-size: 0.75rem;"><?= htmlspecialchars($subtitle) ?></small>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>
