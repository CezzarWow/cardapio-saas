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
        if (this.audio) return; // Mantém instância única se possível

        try {
            this.audio = new Audio(DeliveryHelpers.getBaseUrl() + '/sounds/new-order.mp3');
            this.audio.volume = 1.0;
        } catch (e) {
            console.warn('[Delivery] Audio não suportado');
        }
    },

    /**
     * Toca som de notificação
     */
    playSound: function () {
        if (!this.audio) this.initSound();
        if (!this.audio) return;

        const playPromise = this.audio.play();

        if (playPromise !== undefined) {
            playPromise
                .then(() => {
                    // Play success
                })
                .catch(error => {
                    console.warn('[Delivery] Auto-play bloqueado pelo navegador. Interaja com a página.', error);
                });
        }
    },

    /**
     * Inicia polling
     */
    start: function () {
        if (this.timerId) return; // Já está rodando

        this.initSound();

        // [FIX] Unlock Audio Context: Tenta tocar (muted) no primeiro clique
        // Isso "desbloqueia" o audio para tocar sozinho depois
        const unlockAudio = () => {
            if (this.audio) {
                this.audio.play().then(() => {
                    this.audio.pause();
                    this.audio.currentTime = 0;
                }).catch(() => { });
                document.removeEventListener('click', unlockAudio);
                document.removeEventListener('touchstart', unlockAudio);
                // console.log('[Delivery] Audio Context Unlocked');
            }
        };
        document.addEventListener('click', unlockAudio);
        document.addEventListener('touchstart', unlockAudio);

        // Conta pedidos novos atuais
        const currentNew = document.querySelectorAll('.delivery-column--novo .delivery-card-compact').length;
        this.lastNewCount = currentNew;

        this.isActive = true;
        this.timerId = setInterval(() => this.poll(), this.interval);
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
    // Estado do último hash
    lastHash: '',

    /**
     * Executa uma atualização (Polling Otimizado)
     * 1. Consulta JSON leve (/check)
     * 2. Se hash mudou -> Baixa HTML (/list)
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
            // 1. Check (Leve)
            const checkUrl = DeliveryHelpers.getBaseUrl() + '/admin/loja/delivery/check';
            const checkRes = await fetch(checkUrl);

            if (!checkRes.ok) return; // Silencioso

            const checkData = await checkRes.json();

            // Se o hash for igual ao último, não faz nada
            if (checkData.success && checkData.hash === this.lastHash) {
                // console.log('[Delivery] Polling: Sem mudanças');
                return;
            }

            // Se mudou, atualiza o hash
            if (checkData.success) {
                this.lastHash = checkData.hash;
            }

            // 2. Fetch HTML (Pesado)
            // console.log('[Delivery] Polling: Mudança detectada! Baixando HTML...');
            const url = DeliveryHelpers.getBaseUrl() + '/admin/loja/delivery/list';
            const response = await fetch(url);

            if (!response.ok) {
                console.warn('[Delivery] Polling HTML falhou:', response.status);
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
