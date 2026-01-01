/**
 * ============================================
 * DELIVERY JS — Polling
 * FASE 5: Atualização automática da lista
 * 
 * Regras:
 * - Falha no polling não quebra nada
 * - Não atualiza se modal estiver aberto
 * - Para quando aba não está ativa
 * ============================================
 */

const DeliveryPolling = {

    // Configuração
    interval: 15000, // 15 segundos
    timerId: null,
    isActive: true,
    isPaused: false,

    /**
     * Inicia polling
     */
    start: function () {
        if (this.timerId) return; // Já está rodando

        this.isActive = true;
        this.timerId = setInterval(() => this.poll(), this.interval);

        // Pausa quando aba não está visível
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else {
                this.resume();
            }
        });

        console.log('[Delivery] Polling iniciado (intervalo: ' + (this.interval / 1000) + 's)');
    },

    /**
     * Para polling
     */
    stop: function () {
        if (this.timerId) {
            clearInterval(this.timerId);
            this.timerId = null;
        }
        this.isActive = false;
        console.log('[Delivery] Polling parado');
    },

    /**
     * Pausa temporariamente
     */
    pause: function () {
        this.isPaused = true;
        console.log('[Delivery] Polling pausado (aba inativa)');
    },

    /**
     * Retoma polling
     */
    resume: function () {
        if (this.isPaused) {
            this.isPaused = false;
            this.poll(); // Atualiza imediatamente
            console.log('[Delivery] Polling retomado');
        }
    },

    /**
     * Executa uma atualização
     */
    poll: async function () {
        // Não atualiza se:
        // - Polling está pausado
        // - Modal está aberto
        if (this.isPaused) return;

        const detailsModal = document.getElementById('deliveryDetailsModal');
        const cancelModal = document.getElementById('deliveryCancelModal');

        if (detailsModal && detailsModal.style.display === 'flex') return;
        if (cancelModal && cancelModal.style.display === 'flex') return;

        try {
            // Pega filtro atual da URL
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status') || '';

            const url = BASE_URL + '/admin/loja/delivery/list' + (status ? '?status=' + status : '');

            const response = await fetch(url);

            if (!response.ok) {
                console.warn('[Delivery] Polling falhou:', response.status);
                return;
            }

            const html = await response.text();

            // Atualiza Kanban
            const kanban = document.querySelector('.delivery-kanban');
            if (kanban) {
                kanban.outerHTML = html;

                // Re-renderiza ícones Lucide
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Atualiza contador
                const cards = document.querySelectorAll('.delivery-card-compact');
                const counter = document.getElementById('delivery-count');
                if (counter) counter.textContent = cards.length;
            }

        } catch (err) {
            // Falha silenciosa - não quebra nada
            console.warn('[Delivery] Erro no polling:', err.message);
        }
    }
};

// Expõe globalmente
window.DeliveryPolling = DeliveryPolling;

// Auto-start quando carrega
document.addEventListener('DOMContentLoaded', () => {
    DeliveryPolling.start();
});

console.log('[Delivery] Polling carregado ✓');
