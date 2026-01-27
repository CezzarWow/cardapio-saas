<?php
/**
 * PARTIAL: Modal de Sugestões (Bebidas e Molhos)
 * Espera:
 * - $allProducts (array)
 * - $additionalGroups (array)
 * - $additionalItems (array)
 */
?>
<!-- MODAL DE SUGESTÕES (BEBIDAS E MOLHOS) - TELA INTEIRA -->
<div id="suggestionsModal" class="cardapio-modal">
    <div class="cardapio-modal-content fullscreen cardapio-suggestions-modal">
        <div class="cardapio-suggestions-header">
            <button class="cardapio-back-btn" onclick="closeSuggestionsModal()">
                <i data-lucide="arrow-left" size="20"></i>
            </button>
            <h2>🥤 Quer adicionar algo?</h2>
        </div>
        
        <div class="cardapio-modal-body">
            <!-- Bebidas -->
            <div class="suggestion-section">
                <h3 class="suggestion-section-title">
                    <i data-lucide="cup-soda" size="20"></i>
                    Bebidas
                </h3>
                <div class="suggestion-items">
                    <?php
                    // Filtrar produtos da categoria "Bebidas" ou similar
                    $drinks = [];
foreach ($allProducts as $p) {
    $catLower = strtolower($p['category_name'] ?? '');
    if (strpos($catLower, 'bebida') !== false || strpos($catLower, 'drink') !== false || strpos($catLower, 'refrigerante') !== false || strpos($catLower, 'suco') !== false) {
        $drinks[] = $p;
    }
}
if (empty($drinks)): ?>
                        <p class="suggestion-empty">Nenhuma bebida disponível</p>
                    <?php else: ?>
                        <?php foreach ($drinks as $drink): ?>
                            <div class="suggestion-item">
                                <div class="suggestion-item-info">
                                    <?php if (!empty($drink['image'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($drink['image']) ?>" class="suggestion-item-image" alt="" loading="lazy">
                                    <?php else: ?>
                                        <div class="suggestion-item-image-placeholder">
                                            <i data-lucide="cup-soda" size="20"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="suggestion-item-name"><?= htmlspecialchars($drink['name']) ?></p>
                                        <p class="suggestion-item-price">R$ <?= number_format($drink['price'], 2, ',', '.') ?></p>
                                    </div>
                                </div>
                                <button class="suggestion-drink-btn" data-id="<?= (int) ($drink['id'] ?? 0) ?>" 
                                    onclick="addDrinkToCart(<?= (int) ($drink['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes((string) ($drink['name'] ?? ''))) ?>', <?= (float) ($drink['price'] ?? 0) ?>, '<?= htmlspecialchars((string) ($drink['image'] ?? '')) ?>')">
                                    <i data-lucide="plus" size="16"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Molhos extras -->
            <div class="suggestion-section">
                <h3 class="suggestion-section-title">
                    <i data-lucide="droplet" size="20"></i>
                    Molhos Extras
                </h3>
                <div class="suggestion-items">
                    <?php
$hasSauces = false;
foreach ($additionalGroups as $group):
    $groupLower = strtolower($group['name']);
    if (strpos($groupLower, 'molho') !== false || strpos($groupLower, 'sauce') !== false):
        $hasSauces = true;
        if (isset($additionalItems[$group['id']])):
            foreach ($additionalItems[$group['id']] as $sauce): ?>
                                    <div class="suggestion-item">
                                        <div class="suggestion-item-info">
                                            <div class="suggestion-item-image-placeholder sauce">
                                                <i data-lucide="droplet" size="18"></i>
                                            </div>
                                            <div>
                                                <p class="suggestion-item-name"><?= htmlspecialchars($sauce['name']) ?></p>
                                                <p class="suggestion-item-price"><?= $sauce['price'] > 0 ? 'R$ ' . number_format($sauce['price'], 2, ',', '.') : 'Grátis' ?></p>
                                            </div>
                                        </div>
                                        <button class="suggestion-sauce-btn" data-id="<?= (int) ($sauce['id'] ?? 0) ?>" 
                                            onclick="addSauceToCart(<?= (int) ($sauce['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes((string) ($sauce['name'] ?? ''))) ?>', <?= (float) ($sauce['price'] ?? 0) ?>)">
                                            <i data-lucide="plus" size="16"></i>
                                        </button>
                                    </div>
                                <?php endforeach;
        endif;
    endif;
endforeach;
if (!$hasSauces): ?>
                        <p class="suggestion-empty">Nenhum molho extra disponível</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>
    <!-- Botão fora do conteiner de conteúdo para não ser afetado pelo transform -->
    <button id="suggestionsFloatingCart" class="cardapio-floating-cart-btn suggestions-cart-btn show" onclick="CardapioCheckout.openOrderReview(); if(typeof closeSuggestionsModal === 'function') closeSuggestionsModal();">
        <i data-lucide="shopping-cart" size="20"></i>
        <span id="suggestionsCartTotal">R$ 0,00</span>
        <i data-lucide="arrow-right" size="18"></i>
    </button>
</div>
