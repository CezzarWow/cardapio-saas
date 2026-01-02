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
            this.audio = new Audio(BASE_URL + '/sounds/new-order.mp3');
            this.audio.volume = 1.0; // Volume máximo
            // this.audio.playbackRate = 1.5; // Desativado - velocidade normal
            console.log('[Delivery] Som carregado');
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
            console.log('[Delivery] 🔔 Som de novo pedido!');
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
            const url = BASE_URL + '/admin/loja/delivery/list';
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

            console.log('[Delivery] Pedidos novos:', newCount, '| Anterior:', this.lastNewCount);

            // Se tem mais pedidos novos, toca som!
            if (newCount > this.lastNewCount) {
                console.log('[Delivery] 🔔 Novo pedido detectado! Tocando som...');
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

// Auto-start quando carrega
document.addEventListener('DOMContentLoaded', () => {
    DeliveryPolling.start();
});

console.log('[Delivery] Polling carregado ✓');
