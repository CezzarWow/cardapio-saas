/**
 * PromoProducts - Gerenciamento de Promoções de Itens Individuais
 * Arquivo: public/js/cardapio-admin/promo-products.js
 */

window.PromoProducts = (function () {
    'use strict';

    /**
     * Obtém BASE_URL dinamicamente (definido no shell.php)
     */
    function getBaseUrl() {
        // Prioriza window.BASE_URL (definido no shell.php)
        if (typeof window.BASE_URL !== 'undefined') {
            return window.BASE_URL;
        }
        // Fallback para BASE_URL global
        if (typeof BASE_URL !== 'undefined') {
            return BASE_URL;
        }
        return '';
    }

    /**
     * Atualiza o preço original quando produto é selecionado
     */
    function onProductChange(select) {
        const selectedOption = select.options[select.selectedIndex];
        const originalPrice = parseFloat(selectedOption?.dataset?.price || 0);
        const originalPriceInput = document.getElementById('promo_original_price');

        if (originalPriceInput) {
            if (originalPrice > 0) {
                originalPriceInput.value = originalPrice.toFixed(2).replace('.', ',');
            } else {
                originalPriceInput.value = '';
            }
        }

        updateDiscountPreview();
    }

    /**
     * Toggle do campo de data baseado na seleção de validade
     */
    function toggleValidityDate() {
        const validityType = document.getElementById('promo_validity_type')?.value;
        const dateInput = document.getElementById('promo_expires_at');

        if (!dateInput) return;

        if (validityType === 'date') {
            dateInput.style.display = 'block';
            dateInput.required = true;
        } else {
            dateInput.style.display = 'none';
            dateInput.required = false;
            dateInput.value = '';
        }
    }

    /**
     * Calcula a data de expiração baseada no tipo de validade
     */
    function getExpirationDate() {
        const validityType = document.getElementById('promo_validity_type')?.value;
        const dateInput = document.getElementById('promo_expires_at');

        switch (validityType) {
            case 'always':
                return null;
            case 'today':
                return new Date().toISOString().split('T')[0];
            case 'date':
                return dateInput?.value || null;
            default:
                return null;
        }
    }

    /**
     * Salvar promoção de produto
     */
    /**
     * Helper para recarregar na aba correta
     */
    function reloadInTab() {
        window.location.hash = 'promocoes';
        window.location.reload();
    }

    /**
     * Salvar promoção de produto
     */
    async function save() {
        const productId = document.getElementById('promo_product_id')?.value;
        const priceInput = document.getElementById('promo_price')?.value;
        const expiresAt = getExpirationDate();

        if (!productId) {
            showToast('Selecione um produto', 'error');
            return;
        }

        if (!priceInput) {
            showToast('Informe o preço promocional', 'error');
            return;
        }

        const validityType = document.getElementById('promo_validity_type')?.value;
        if (validityType === 'date' && !document.getElementById('promo_expires_at')?.value) {
            showToast('Informe a data de validade', 'error');
            return;
        }

        try {
            const response = await fetch(`${getBaseUrl()}/admin/loja/cardapio/produto/promocao`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify({
                    product_id: productId,
                    promotional_price: priceInput,
                    promo_expires_at: expiresAt
                })
            });

            const data = await response.json();

            if (data.success) {
                showToast('Promoção adicionada com sucesso!', 'success');
                // Recarrega a página para mostrar o item na lista
                setTimeout(() => reloadInTab(), 500);
            } else {
                showToast(data.error || 'Erro ao salvar promoção', 'error');
            }
        } catch (error) {
            console.error('Erro ao salvar promoção:', error);
            showToast('Erro ao salvar promoção', 'error');
        }
    }

    /**
     * Toggle ativar/desativar promoção
     */
    async function togglePromotion(productId, active) {
        try {
            const response = await fetch(`${getBaseUrl()}/admin/loja/cardapio/produto/promocao/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify({
                    id: productId,
                    active: active
                })
            });

            const data = await response.json();

            if (data.success) {
                showToast(active ? 'Promoção ativada' : 'Promoção desativada', 'success');

                // Atualiza visual do card
                const card = document.querySelector(`[data-promo-id="${productId}"]`);
                if (card) {
                    if (active) {
                        card.style.opacity = '1';
                        card.style.background = 'white';
                        card.style.borderColor = '#e2e8f0';
                        setTimeout(() => reloadInTab(), 300);
                    } else {
                        card.style.opacity = '0.8';
                        card.style.background = '#f8fafc';
                        card.style.borderColor = '#cbd5e1';
                        setTimeout(() => reloadInTab(), 300); // Reload rápido para aplicar todas as cores do PHP
                    }
                }
            } else {
                showToast(data.error || 'Erro ao alterar status', 'error');
                // Reverte o toggle visualmente
                setTimeout(() => reloadInTab(), 500);
            }
        } catch (error) {
            console.error('Erro ao alterar status:', error);
            showToast('Erro ao alterar status', 'error');
        }
    }

    /**
     * Remover promoção de um produto
     */
    async function removePromotion(productId) {
        if (!confirm('Remover promoção deste item?')) {
            return;
        }

        try {
            const response = await fetch(`${getBaseUrl()}/admin/loja/cardapio/produto/promocao/remover`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify({ id: productId })
            });

            const data = await response.json();

            if (data.success) {
                showToast('Promoção removida', 'success');
                // Remove o card da lista
                const card = document.querySelector(`[data-promo-id="${productId}"]`);
                if (card) {
                    card.remove();
                    checkEmptyList();
                }
                // Recarrega para atualizar o select
                setTimeout(() => reloadInTab(), 500);
            } else {
                showToast(data.error || 'Erro ao remover promoção', 'error');
            }
        } catch (error) {
            console.error('Erro ao remover promoção:', error);
            showToast('Erro ao remover promoção', 'error');
        }
    }

    /**
     * Verifica se a lista está vazia e mostra mensagem
     */
    function checkEmptyList() {
        const list = document.getElementById('promo-products-list');
        const items = list?.querySelectorAll('[data-promo-id]');

        if (items && items.length === 0) {
            list.innerHTML = '<p id="no-promo-products-msg" style="padding: 20px; text-align: center; color: #94a3b8; grid-column: 1 / -1;">Nenhum item em promoção no momento.</p>';
        }
    }

    /**
     * Atualiza preview de desconto
     */
    function updateDiscountPreview() {
        const originalPriceInput = document.getElementById('promo_original_price');
        const promoPriceInput = document.getElementById('promo_price');
        const preview = document.getElementById('promo-discount-preview');
        const previewText = document.getElementById('promo-discount-text');

        if (!originalPriceInput || !promoPriceInput || !preview) return;

        const originalPrice = parseBRCurrency(originalPriceInput.value);
        const promoPrice = parseBRCurrency(promoPriceInput.value);

        if (originalPrice > 0 && promoPrice > 0 && promoPrice < originalPrice) {
            const discount = Math.round((1 - promoPrice / originalPrice) * 100);
            const savings = originalPrice - promoPrice;
            previewText.textContent = `Desconto de ${discount}% (economia de R$ ${savings.toFixed(2).replace('.', ',')})`;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

    /**
     * Inicializa preview de desconto ao mudar preço
     */
    function initDiscountPreview() {
        const priceInput = document.getElementById('promo_price');
        if (priceInput) {
            priceInput.addEventListener('keyup', updateDiscountPreview);
        }
    }

    /**
     * Parse moeda brasileira para float
     */
    function parseBRCurrency(value) {
        if (!value) return 0;
        return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
    }

    /**
     * Obter token CSRF
     */
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('input[name="csrf_token"]')?.value || '';
    }

    /**
     * Mostrar toast de notificação
     */
    function showToast(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }

        // Fallback simples
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; bottom: 20px; right: 20px; z-index: 9999;
            padding: 12px 24px; border-radius: 8px; color: white; font-weight: 500;
            background: ${type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#3b82f6'};
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Init on DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        initDiscountPreview();
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // Public API
    return {
        save,
        togglePromotion,
        removePromotion,
        onProductChange,
        toggleValidityDate
    };
})();
