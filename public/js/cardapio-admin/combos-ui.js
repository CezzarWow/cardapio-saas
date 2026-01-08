/**
 * COMBOS-UI.JS - Interface de Combos
 * 
 * Funções de interface para a aba de promoções/combos.
 * Extraído de _tab_promocoes.php
 */

// ==========================================
// FUNÇÕES DE INTERFACE
// ==========================================

/**
 * Toggle de visibilidade do campo de data de validade
 */
function toggleValidityDate() {
    const select = document.getElementById('combo_validity_type');
    const input = document.getElementById('combo_valid_until');

    if (select && input) {
        if (select.value === 'date') {
            input.style.display = 'block';
        } else {
            input.style.display = 'none';
        }
    }
}

/**
 * Alterna entre abas de categorias de produtos
 */
function toggleComboTab(btn, tabId) {
    // Remove active
    document.querySelectorAll('.combo-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.combo-tab-content').forEach(c => c.style.display = 'none');

    // Create active
    btn.classList.add('active');
    const content = document.getElementById(tabId);
    if (content) content.style.display = 'grid';
}

/**
 * Atualiza quantidade de um produto no combo
 */
function updateComboQty(id, change) {
    const qtyInput = document.getElementById('qty_prod_' + id);
    const display = document.getElementById('display_qty_' + id);
    const card = document.getElementById('card_prod_' + id);

    if (!qtyInput || !display) return;

    let current = parseInt(qtyInput.value) || 0;
    let newValue = current + change;

    if (newValue < 0) newValue = 0;

    qtyInput.value = newValue;
    display.textContent = newValue;

    // Visual update
    if (newValue > 0) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }

    calculateComboOriginalPrice();
}

/**
 * Calcula e exibe o preço original somando os produtos selecionados
 */
function calculateComboOriginalPrice() {
    let total = 0;

    document.querySelectorAll('.combo-product-qty').forEach(input => {
        const qty = parseInt(input.value) || 0;
        const price = parseFloat(input.getAttribute('data-price') || 0);
        total += qty * price;
    });

    // Format Currency Br
    const formatter = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('combo_original_price').value = formatter.format(total);
}

/**
 * Preview de imagem do combo antes de upload
 */
function previewComboImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('combo_image_preview').innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
            `;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

console.log('Combos UI JS Loaded');
