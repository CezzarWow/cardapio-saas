<?php
/**
 * PARTIAL: Modal de Produto
 * Espera:
 * - $additionalGroups (array)
 * - $additionalItems (array)
 */
?>
<!-- MODAL DE PRODUTO -->
<div id="productModal" class="cardapio-modal">
    <div class="cardapio-modal-content">
        <div class="cardapio-modal-image-wrapper" id="modalImageWrapper">
            <img id="modalProductImage" src="" alt="" class="cardapio-modal-image">
            <button class="cardapio-modal-close" onclick="closeProductModal()">
                <i data-lucide="chevron-left" size="20"></i>
            </button>
        </div>
        
        <div class="cardapio-modal-body">
            <div class="cardapio-modal-header">
                <h2 id="modalProductName" class="cardapio-modal-title"></h2>
                <span id="modalProductPrice" class="cardapio-modal-price"></span>
            </div>
            
            <p id="modalProductDescription" class="cardapio-modal-description"></p>
            
            <!-- Quantidade -->
            <div class="cardapio-quantity-control">
                <span class="cardapio-quantity-label">Quantidade</span>
                <div class="cardapio-quantity-buttons">
                    <button class="cardapio-qty-btn" onclick="decreaseQuantity()">
                        <i data-lucide="minus" size="16"></i>
                    </button>
                    <span id="modalQuantity" class="cardapio-quantity-value">1</span>
                    <button class="cardapio-qty-btn" onclick="increaseQuantity()">
                        <i data-lucide="plus" size="16"></i>
                    </button>
                </div>
            </div>
            
            <!-- Adicionais -->
            <div id="modalAdditionals" class="cardapio-additionals">
                <h4 class="cardapio-additionals-title">Extras</h4>
                <div id="additionalsList" class="cardapio-additionals-list">
                    <?php foreach ($additionalGroups as $group): ?>
                        <?php if (isset($additionalItems[$group['id']]) && count($additionalItems[$group['id']]) > 0): ?>
                            <div class="cardapio-additional-group" data-group-id="<?= $group['id'] ?>">
                                <p class="cardapio-additional-group-name"><?= htmlspecialchars($group['name']) ?></p>
                                <?php foreach ($additionalItems[$group['id']] as $item): ?>
                                    <label class="cardapio-additional-item">
                                        <div class="cardapio-additional-item-info">
                                            <input 
                                                type="checkbox" 
                                                class="cardapio-additional-checkbox"
                                                data-additional-id="<?= $item['id'] ?>"
                                                data-additional-name="<?= htmlspecialchars($item['name']) ?>"
                                                data-additional-price="<?= number_format($item['price'], 2, '.', '') ?>"
                                            >
                                            <span class="cardapio-additional-name"><?= htmlspecialchars($item['name']) ?></span>
                                        </div>
                                        <span class="cardapio-additional-price">+R$ <?= number_format($item['price'], 2, ',', '.') ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Observações -->
            <div class="cardapio-observations">
                <h4 class="cardapio-observations-title">Observações</h4>
                <textarea 
                    id="modalObservation" 
                    class="cardapio-observations-textarea"
                    placeholder="Ex: Sem cebola, ponto da carne..."
                    enterkeyhint="done"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}"
                    onfocus="setTimeout(()=>{this.parentElement.scrollIntoView({behavior:'smooth',block:'end'})},400)"
                ></textarea>
            </div>
        </div>
        
        <div class="cardapio-modal-footer">
            <button class="cardapio-add-cart-btn" onclick="addToCart()">
                <span>Adicionar</span>
                <span id="modalTotalPrice">R$ 0,00</span>
            </button>
        </div>
    </div>
</div>
