/**
 * REPOSITION.JS - Reposição de Estoque
 * 
 * Funções para gerenciar reposição e ajuste de estoque.
 * Refatorado para modularização (RepositionManager).
 */

(function () {
    'use strict';

    const RepositionManager = {
        // ==========================================
        // ESTADO
        // ==========================================
        state: {
            currentProductId: null,
            currentStock: 0,
            selectedCategory: ''
        },

        // ==========================================
        // INICIALIZAÇÃO
        // ==========================================
        init: function () {
            this.bindEvents();
            console.log('[RepositionManager] Initialized');
        },

        bindEvents: function () {
            // Filtro por chips de categoria
            document.querySelectorAll('.category-chip').forEach(chip => {
                chip.addEventListener('click', (e) => {
                    this.ui.handleCategoryClick(e.target);
                });
            });

            // Preview do resultado
            const adjustAmountInput = document.getElementById('adjustAmount');
            if (adjustAmountInput) {
                adjustAmountInput.addEventListener('input', (e) => {
                    this.ui.handlePreviewUpdate(e.target.value);
                });
            }

            // Fechar modal ao clicar fora
            const modal = document.getElementById('adjustModal');
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) this.ui.closeModal();
                });
            }
        },

        // ==========================================
        // UI - INTERFACE
        // ==========================================
        ui: {
            handleCategoryClick: function (chip) {
                document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                RepositionManager.state.selectedCategory = chip.dataset.category;
                RepositionManager.ui.filterProducts();
            },

            filterProducts: function () {
                const searchInput = document.getElementById('searchProduct');
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const rows = document.querySelectorAll('.product-row');

                rows.forEach(row => {
                    const cat = row.dataset.category;
                    const name = row.dataset.name || '';
                    const matchCategory = !RepositionManager.state.selectedCategory || cat === RepositionManager.state.selectedCategory;
                    const matchSearch = !searchTerm || name.includes(searchTerm);
                    row.style.display = (matchCategory && matchSearch) ? '' : 'none';
                });
            },

            openModal: function (productId, productName, stock) {
                RepositionManager.state.currentProductId = productId;
                RepositionManager.state.currentStock = stock;

                const modal = document.getElementById('adjustModal');
                if (!modal) return;

                document.getElementById('modalProductName').textContent = productName;
                document.getElementById('modalCurrentStock').textContent = stock;

                const input = document.getElementById('adjustAmount');
                input.value = '';

                document.getElementById('previewResult').style.display = 'none';
                modal.style.display = 'flex';

                setTimeout(() => input.focus(), 50);
            },

            closeModal: function () {
                const modal = document.getElementById('adjustModal');
                if (modal) modal.style.display = 'none';
                RepositionManager.state.currentProductId = null;
            },

            handlePreviewUpdate: function (value) {
                const amount = parseInt(value) || 0;
                const previewResult = document.getElementById('previewResult');
                const previewStock = document.getElementById('previewStock');

                if (amount !== 0) {
                    const newStock = RepositionManager.state.currentStock + amount;
                    previewStock.textContent = newStock;
                    previewResult.style.display = 'block';

                    const span = previewResult.querySelector('span');

                    if (newStock < 0) {
                        previewResult.style.background = '#fecaca';
                        if (span) span.style.color = '#dc2626';
                    } else if (newStock <= 5) {
                        previewResult.style.background = '#fef3c7';
                        if (span) span.style.color = '#d97706';
                    } else {
                        previewResult.style.background = '#d1fae5';
                        if (span) span.style.color = '#059669';
                    }
                } else {
                    previewResult.style.display = 'none';
                }
            },

            updateStockDisplay: function (productId, newStock) {
                const stockEl = document.getElementById('stock-' + productId);
                const row = stockEl?.closest('.product-row');

                if (stockEl && row) {
                    stockEl.textContent = newStock;
                    row.dataset.stock = newStock;

                    // Cores
                    if (newStock < 0) stockEl.style.color = '#dc2626';
                    else if (newStock <= 5) stockEl.style.color = '#d97706';
                    else stockEl.style.color = '#059669';

                    // Badge
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
        },

        // ==========================================
        // API - SERVER
        // ==========================================
        api: {
            _getBaseUrl: function () {
                if (typeof window.BASE_URL !== 'undefined') return window.BASE_URL;

                // Fallback: Detecta base via URL
                const parts = window.location.pathname.split('/');

                // Se estiver dentro de /public/ (ex: localhost/cardapio/public/admin...)
                const publicIndex = parts.indexOf('public');
                if (publicIndex !== -1) return parts.slice(0, publicIndex + 1).join('/');

                // Se estiver na raiz (ex: localhost/cardapio/admin...)
                const adminIndex = parts.indexOf('admin');
                if (adminIndex !== -1) return parts.slice(0, adminIndex).join('/');

                return '';
            },

            adjustStock: function () {
                const amountInput = document.getElementById('adjustAmount');
                const amount = parseInt(amountInput.value) || 0;

                if (amount === 0) {
                    alert('Quantidade não pode ser zero');
                    return;
                }

                const productId = RepositionManager.state.currentProductId;
                if (!productId) return;

                const baseUrl = this._getBaseUrl();

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`${baseUrl}/admin/loja/reposicao/ajustar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        amount: amount
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            RepositionManager.ui.updateStockDisplay(productId, data.new_stock);
                            RepositionManager.ui.closeModal();
                            alert('Estoque ajustado com sucesso!');
                        } else {
                            alert('Erro: ' + (data.message || 'Erro desconhecido'));
                        }
                    })
                    .catch(err => {
                        console.error('[RepositionManager] Error:', err);
                        alert('Erro ao ajustar estoque. Verifique o console.');
                    });
            }
        }
    };

    // ==========================================
    // EXPORTAR GLOBALMENTE (Compatibilidade)
    // ==========================================
    window.RepositionManager = RepositionManager;

    // Aliases para onclicks do HTML
    window.openAdjustModal = (id, name, stock) => RepositionManager.ui.openModal(id, name, stock);
    window.closeAdjustModal = () => RepositionManager.ui.closeModal();
    window.submitAdjust = () => RepositionManager.api.adjustStock();
    window.filterProducts = () => RepositionManager.ui.filterProducts();

    // Init
    document.addEventListener('DOMContentLoaded', () => RepositionManager.init());

})();
