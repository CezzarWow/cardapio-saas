<?php
/**
 * PARTIAL: Modal do Carrinho
 * Espera: (nenhuma variável - preenchido via JS)
 */
?>
<!-- MODAL DO CARRINHO -->
<div id="cartModal" class="cardapio-modal cardapio-cart-modal">
    <div class="cardapio-modal-content cardapio-cart-modal-content">
        <div class="cardapio-cart-header">
            <h2>Seu Pedido</h2>
            <button class="cardapio-modal-close-round" onclick="closeCartModal()">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="cardapio-cart-body" id="cartItemsContainer">
        </div>
        
        <div class="cardapio-cart-footer">
            <div class="cardapio-cart-total-row">
                <span class="cardapio-cart-total-label">Total:</span>
                <span id="cartModalTotal" class="cardapio-cart-total-value">R$ 0,00</span>
            </div>
            <button class="cardapio-checkout-btn" onclick="goToCheckout()">
                Próxima Etapa
                <i data-lucide="arrow-right" size="18"></i>
            </button>
        </div>
    </div>
</div>
