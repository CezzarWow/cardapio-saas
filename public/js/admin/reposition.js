/**
 * REPOSITION.JS - Reposição de Estoque
 * 
 * Funções para gerenciar reposição e ajuste de estoque.
 * Extraído de views/admin/reposition/index.php
 */

// ==========================================
// STATE
// ==========================================
let currentProductId = null;
let currentStock = 0;
let selectedCategory = '';

// ==========================================
// INICIALIZAÇÃO
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    // Filtro por chips de categoria
    document.querySelectorAll('.category-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            selectedCategory = this.dataset.category;
            filterProducts();
        });
    });

    // Preview do resultado no modal
    const adjustAmountInput = document.getElementById('adjustAmount');
    if (adjustAmountInput) {
        adjustAmountInput.addEventListener('input', handlePreviewUpdate);
    }

    // Fechar modal ao clicar fora
    const modal = document.getElementById('adjustModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeAdjustModal();
        });
    }

    console.log('Reposition JS Loaded');
});

// ==========================================
// FILTROS
// ==========================================
function filterProducts() {
    const searchInput = document.getElementById('searchProduct');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.product-row');

    rows.forEach(row => {
        const cat = row.dataset.category;
        const name = row.dataset.name || '';
        const matchCategory = !selectedCategory || cat === selectedCategory;
        const matchSearch = !searchTerm || name.includes(searchTerm);
        row.style.display = (matchCategory && matchSearch) ? '' : 'none';
    });
}

// ==========================================
// MODAL DE AJUSTE
// ==========================================
function openAdjustModal(productId, productName, stock) {
    currentProductId = productId;
    currentStock = stock;

    document.getElementById('modalProductName').textContent = productName;
    document.getElementById('modalCurrentStock').textContent = stock;
    document.getElementById('adjustAmount').value = '';
    document.getElementById('previewResult').style.display = 'none';

    document.getElementById('adjustModal').style.display = 'flex';
    document.getElementById('adjustAmount').focus();
}

function closeAdjustModal() {
    document.getElementById('adjustModal').style.display = 'none';
    currentProductId = null;
}

function handlePreviewUpdate() {
    const amount = parseInt(this.value) || 0;
    if (amount !== 0) {
        const newStock = currentStock + amount;
        document.getElementById('previewStock').textContent = newStock;
        document.getElementById('previewResult').style.display = 'block';

        const preview = document.getElementById('previewResult');
        if (newStock < 0) {
            preview.style.background = '#fecaca';
            preview.querySelector('span').style.color = '#dc2626';
        } else if (newStock <= 5) {
            preview.style.background = '#fef3c7';
            preview.querySelector('span').style.color = '#d97706';
        } else {
            preview.style.background = '#d1fae5';
            preview.querySelector('span').style.color = '#059669';
        }
    } else {
        document.getElementById('previewResult').style.display = 'none';
    }
}

// ==========================================
// SUBMIT DE AJUSTE
// ==========================================
function submitAdjust() {
    const amount = parseInt(document.getElementById('adjustAmount').value) || 0;

    if (amount === 0) {
        alert('Quantidade não pode ser zero');
        return;
    }

    // Obtém base URL do pathname atual
    const pathParts = window.location.pathname.split('/');
    const publicIndex = pathParts.indexOf('public');
    const baseUrl = publicIndex !== -1 ? pathParts.slice(0, publicIndex + 1).join('/') : '';

    fetch(baseUrl + '/admin/loja/reposicao/ajustar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_id: currentProductId,
            amount: amount
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateStockDisplay(currentProductId, data.new_stock);
                closeAdjustModal();
                alert('Estoque ajustado com sucesso!');
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => {
            alert('Erro ao ajustar estoque');
            console.error(err);
        });
}

function updateStockDisplay(productId, newStock) {
    const stockEl = document.getElementById('stock-' + productId);
    const row = stockEl?.closest('.product-row');

    if (stockEl && row) {
        stockEl.textContent = newStock;

        // Atualiza cor baseada no estoque
        if (newStock < 0) {
            stockEl.style.color = '#dc2626';
        } else if (newStock <= 5) {
            stockEl.style.color = '#d97706';
        } else {
            stockEl.style.color = '#059669';
        }

        row.dataset.stock = newStock;

        // Atualiza badge de status
        const statusBadge = row.querySelector('.stock-product-card-footer span:last-child');
        if (statusBadge) {
            if (newStock < 0) {
                statusBadge.textContent = 'Negativo';
                statusBadge.style.background = '#fecaca';
                statusBadge.style.color = '#dc2626';
            } else if (newStock <= 5) {
                statusBadge.textContent = 'Crítico';
                statusBadge.style.background = '#fef3c7';
                statusBadge.style.color = '#d97706';
            } else {
                statusBadge.textContent = 'Normal';
                statusBadge.style.background = '#d1fae5';
                statusBadge.style.color = '#059669';
            }
        }
    }
}
