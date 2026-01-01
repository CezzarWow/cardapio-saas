<?php
/**
 * PARTIAL: Modal de Combo
 * Espera: (nenhuma variável - preenchido via JS)
 */
?>
<!-- MODAL DE COMBOS (NOVO) -->
<div id="comboModal" class="cardapio-modal">
    <div class="cardapio-modal-content">
        <div class="cardapio-modal-image-wrapper">
            <img id="modalComboImage" src="" alt="" class="cardapio-modal-image">
            <button class="cardapio-modal-close" onclick="CardapioModals.closeCombo()">
                <i data-lucide="chevron-left" size="20"></i>
            </button>
        </div>
        
        <div class="cardapio-modal-body">
            <div class="cardapio-modal-header">
                <h2 id="modalComboName" class="cardapio-modal-title"></h2>
                <span id="modalComboPrice" class="cardapio-modal-price"></span>
            </div>
            
            <p id="modalComboDescription" class="cardapio-modal-description"></p>
            
            <!-- Quantidade -->
            <div class="cardapio-quantity-control" style="margin-top: 15px; margin-bottom: 15px;">
                <span class="cardapio-quantity-label">Quantidade</span>
                <div class="cardapio-quantity-buttons">
                    <button class="cardapio-qty-btn" onclick="CardapioModals.decreaseComboQty()">
                        <i data-lucide="minus" size="16"></i>
                    </button>
                    <span id="modalComboQuantity" class="cardapio-quantity-value">1</span>
                    <button class="cardapio-qty-btn" onclick="CardapioModals.increaseComboQty()">
                        <i data-lucide="plus" size="16"></i>
                    </button>
                </div>
            </div>
            
            <!-- Lista de Produtos do Combo (Colapsáveis) -->
            <div id="comboProductsContainer" class="combo-products-container" style="margin-top: 20px;">
                <!-- Preenchido via JS -->
            </div>
            

            
            <!-- Observações -->
            <div class="cardapio-observations" style="margin-top: 30px;">
                <h4 class="cardapio-observations-title">Observações do Combo</h4>
                <textarea 
                    id="modalComboObservation" 
                    class="cardapio-observations-textarea"
                    placeholder="Ex: Sem cebola no lanche, coca gelada..."
                    enterkeyhint="done"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur()}"
                ></textarea>
            </div>
        </div>
        
        <div class="cardapio-modal-footer">
            <button class="cardapio-add-cart-btn" onclick="CardapioModals.addComboToCart()">
                <span>Adicionar Combo</span>
                <span id="modalComboTotalPrice">R$ 0,00</span>
            </button>
        </div>
    </div>
</div>
