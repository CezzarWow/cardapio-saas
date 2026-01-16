/**
 * ============================================
 * DELIVERY JS — Polling
 * Com notificação sonora para novos pedidos
 * 
 * Regras:
 * - Falha no polling não quebra nada
 * - Não atualiza se modal estiver aberto
 * - Para quando aba não está ativa
 * - Toca som quando entra pedido novo
 * ============================================
 */

const DeliveryPolling = {

    // Configuração
    interval: 10000, // 10 segundos
    timerId: null,
    isActive: true,
    isPaused: false,
    lastNewCount: 0, // Guarda quantidade de pedidos novos

    // Som de notificação
    audio: null,

    /**
     * Inicializa o som
     */
    initSound: function () {
        try {
            this.audio = new Audio(DeliveryHelpers.getBaseUrl() + '/sounds/new-order.mp3');
            this.audio.volume = 1.0; // Volume máximo
            // this.audio.playbackRate = 1.5; // Desativado - velocidade normal
        } catch (e) {
            console.warn('[Delivery] Audio não suportado');
        }
    },

    /**
     * Toca som de notificação
     */
    playSound: function () {
        if (!this.audio) return;

        try {
            this.audio.currentTime = 0;
            this.audio.play();
        } catch (e) {
            console.warn('[Delivery] Erro ao tocar som:', e);
        }
    },

    /**
     * Inicia polling
     */
    start: function () {
        if (this.timerId) return; // Já está rodando

        this.initSound();

        // Conta pedidos novos atuais
        const currentNew = document.querySelectorAll('.delivery-column--novo .delivery-card-compact').length;
        this.lastNewCount = currentNew;

        this.isActive = true;
        this.timerId = setInterval(() => this.poll(), this.interval);

        // DESATIVADO: Continua polling mesmo em segundo plano (para tocar som)
        // document.addEventListener('visibilitychange', () => {
        //     if (document.hidden) {
        //         this.pause();
        //     } else {
        //         this.resume();
        //     }
        // });
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
    },

    /**
     * Pausa temporariamente
     */
    pause: function () {
        this.isPaused = true;
    },

    /**
     * Retoma polling
     */
    resume: function () {
        if (this.isPaused) {
            this.isPaused = false;
            this.poll(); // Atualiza imediatamente
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
            const url = DeliveryHelpers.getBaseUrl() + '/admin/loja/delivery/list';
            const response = await fetch(url);

            if (!response.ok) {
                console.warn('[Delivery] Polling falhou:', response.status);
                return;
            }

            const html = await response.text();

            // Conta novos pedidos ANTES de atualizar
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const newCount = tempDiv.querySelectorAll('.delivery-column--novo .delivery-card-compact').length;
            // Se tem mais pedidos novos, toca som!
            if (newCount > this.lastNewCount) {
                this.playSound();
            }
            this.lastNewCount = newCount;

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

                // Reaplica filtro atual se estiver ativo
                if (typeof DeliveryTabs !== 'undefined' && DeliveryTabs.currentFilter !== 'todos') {
                    DeliveryTabs.filter(DeliveryTabs.currentFilter);
                }
            }

        } catch (err) {
            // Falha silenciosa - não quebra nada
            console.warn('[Delivery] Erro no polling:', err.message);
        }
    }
};

// Expõe globalmente
window.DeliveryPolling = DeliveryPolling;

// Alias para padronização SPA
DeliveryPolling.init = function () {
    this.start();
};

// Auto-start APENAS se não estiver no SPA Shell (modo legado)
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('spa-content')) {
        DeliveryPolling.start();
    }
});
