/* delivery-bundle - Generated 2026-01-17T19:33:17.461Z */


/* ========== delivery/helpers.js ========== */
/**
 * ============================================
 * DELIVERY JS — Helpers
 * Funções utilitárias compartilhadas
 * ============================================
 */

const DeliveryHelpers = {

    /**
     * Retorna a BASE_URL segura
     */
    getBaseUrl: function () {
        return typeof BASE_URL !== 'undefined' ? BASE_URL : '';
    },

    /**
     * Retorna o token CSRF
     */
    getCsrf: function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },

    /**
     * Formata valor como moeda BRL
     */
    formatCurrency: function (val) {
        return 'R$ ' + parseFloat(val || 0).toFixed(2).replace('.', ',');
    },

    /**
     * Headers padrão para requisições JSON com CSRF
     */
    getJsonHeaders: function () {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.getCsrf()
        };
    }
};

// Expõe globalmente
window.DeliveryHelpers = DeliveryHelpers;


/* ========== delivery/constants.js ========== */
/**
 * ============================================
 * DELIVERY JS — Constants
 * Constantes compartilhadas entre módulos
 * ============================================
 */

const DeliveryConstants = {

    /**
     * Labels de status para exibição
     */
    statusLabels: {
        'novo': 'Novo',
        'preparo': 'Em Preparo',
        'rota': 'Em Rota',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    },

    /**
     * Labels de métodos de pagamento
     */
    methodLabels: {
        'dinheiro': '💵 Dinheiro',
        'pix': '📱 Pix',
        'credito': '💳 Crédito',
        'debito': '💳 Débito',
        'multiplo': '💰 Múltiplo'
    },

    /**
     * Transições de status (delivery)
     */
    nextStatusDelivery: {
        'novo': 'preparo',
        'preparo': 'rota',
        'rota': 'entregue'
    },

    /**
     * Transições de status (pickup)
     */
    nextStatusPickup: {
        'novo': 'preparo',
        'preparo': 'rota',
        'rota': 'entregue'
    },

    /**
     * Retorna label do status
     */
    getStatusLabel: function (status) {
        return this.statusLabels[status] || status;
    },

    /**
     * Retorna label do método de pagamento
     */
    getMethodLabel: function (method) {
        return this.methodLabels[method] || method || 'A pagar';
    }
};

// Expõe globalmente
window.DeliveryConstants = DeliveryConstants;


/* ========== delivery/tabs.js ========== */
/**
 * ============================================
 * DELIVERY JS — Tabs (Filtros instantâneos)
 * Mostra/esconde colunas sem reload
 * ============================================
 */

const DeliveryTabs = {

    currentFilter: 'todos',

    /**
     * Filtra por status (instantâneo)
     */
    filter: function (status) {
        this.currentFilter = status;

        const columns = document.querySelectorAll('.delivery-column');
        const buttons = document.querySelectorAll('.delivery-filter-btn');

        // Atualiza botões
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.status === status) {
                btn.classList.add('active');
            }
        });

        // Mostra/esconde colunas
        columns.forEach(col => {
            if (status === 'todos') {
                // Se 'todos', mostra tudo MENOS cancelado
                if (col.classList.contains('delivery-column--cancelado')) {
                    col.style.display = 'none';
                } else {
                    col.style.display = 'flex';
                }
            } else {
                // Se status específico (incluindo 'cancelado'), mostra só ele
                if (col.classList.contains('delivery-column--' + status)) {
                    col.style.display = 'flex';
                } else {
                    col.style.display = 'none';
                }
            }
        });

        // Atualiza contador
        this.updateCounter();
    },

    /**
     * Atualiza contador de pedidos visíveis
     */
    updateCounter: function () {
        const visibleCards = document.querySelectorAll('.delivery-column[style*="flex"] .delivery-card-compact');
        const counter = document.getElementById('delivery-count');
        if (counter) counter.textContent = visibleCards.length;
    }
};

// Expõe globalmente
window.DeliveryTabs = DeliveryTabs;


/* ========== delivery/actions.js ========== */
/**
 * ============================================
 * DELIVERY JS — Actions
 * Ações de status (avançar, cancelar)
 * 
 * Dependências: constants.js, helpers.js (carregar antes)
 * ============================================
 */

const DeliveryActions = {

    /**
     * Avança para o próximo status
     * @param orderType - 'delivery', 'pickup' ou 'local'
     */
    advance: async function (orderId, currentStatus, orderType = 'delivery') {
        // Pedidos "local" vão para a aba Mesas em vez de avançar normalmente
        if (orderType === 'local') {
            await this.sendToTable(orderId);
            return;
        }

        const transitions = (orderType === 'pickup')
            ? DeliveryConstants.nextStatusPickup
            : DeliveryConstants.nextStatusDelivery;
        const next = transitions[currentStatus];
        if (!next) {
            alert('Este pedido já está no status final.');
            return;
        }
        await this.updateStatus(orderId, next);
    },

    /**
     * Envia pedido Local para a aba Mesas (Clientes/Comanda)
     */
    sendToTable: async function (orderId) {
        const btn = event?.target?.closest('button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:16px;height:16px;"></i>';
        }

        try {
            const response = await fetch(BASE_URL + '/admin/loja/delivery/send-to-table', {
                method: 'POST',
                headers: DeliveryHelpers.getJsonHeaders(),
                body: JSON.stringify({ order_id: orderId })
            });

            const data = await response.json();

            if (data.success) {
                // SPA Update
                if (window.DeliveryPolling) window.DeliveryPolling.poll();
                else location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Falha ao enviar para mesa'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Tentar novamente';
                }
            }
        } catch (err) {
            alert('Erro de conexão: ' + err.message);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Tentar novamente';
            }
        }
    },



    /**
     * Cancela pedido
     */
    cancel: async function (orderId) {
        if (!confirm('Tem certeza que deseja CANCELAR este pedido?')) return;
        await this.updateStatus(orderId, 'cancelado');
    },

    /**
     * Envia requisição de update
     */
    updateStatus: async function (orderId, newStatus) {
        const btn = event?.target?.closest('button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:16px;height:16px;"></i>';
        }

        try {
            const response = await fetch(BASE_URL + '/admin/loja/delivery/status', {
                method: 'POST',
                headers: DeliveryHelpers.getJsonHeaders(),
                body: JSON.stringify({ order_id: orderId, new_status: newStatus })
            });

            const data = await response.json();

            if (data.success) {
                // SPA Update
                if (window.DeliveryPolling) window.DeliveryPolling.poll();
                else location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Falha ao atualizar'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Tentar novamente';
                }
            }
        } catch (err) {
            alert('Erro de conexão: ' + err.message);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Tentar novamente';
            }
        }
    }
};

// Expõe globalmente
window.DeliveryActions = DeliveryActions;


/* ========== delivery/ui.js ========== */
/**
 * ============================================
 * DELIVERY JS — UI (Modais)
 * Abrir/fechar modais, sem lógica de negócio
 * 
 * Dependência: constants.js (carregar antes)
 * ============================================
 */

const DeliveryUI = {

    // Dados do pedido atual (para modais)
    currentOrder: null,

    /**
     * Abre modal de detalhes (aceita objeto ou ID)
     */
    openDetailsModal: async function (orderDataOrId) {
        // Se recebeu apenas ID, busca dados da API
        if (typeof orderDataOrId === 'number' || typeof orderDataOrId === 'string') {
            try {
                const response = await fetch(BASE_URL + '/admin/loja/delivery/details?id=' + orderDataOrId);
                if (!response.ok) throw new Error('Erro ao buscar pedido');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Erro ao buscar pedido');
                }

                // Monta objeto com dados do pedido + itens
                const orderData = {
                    ...result.order,
                    items: result.items || []
                };

                this.showDetailsModal(orderData);
            } catch (e) {
                console.error('[Delivery] Erro ao buscar detalhes:', e);
                alert('Erro ao carregar detalhes do pedido');
            }
        } else {
            // Recebeu objeto completo
            this.showDetailsModal(orderDataOrId);
        }
    },

    /**
     * Exibe modal com dados do pedido
     */
    showDetailsModal: function (orderData) {
        this.currentOrder = orderData;

        const modal = document.getElementById('deliveryDetailsModal');
        if (!modal) return;

        // Preenche dados
        document.getElementById('modal-order-id').textContent = orderData.id;
        document.getElementById('modal-client-name').textContent = orderData.client_name || 'Não identificado';
        document.getElementById('modal-client-phone').textContent = orderData.client_phone || '--';

        // Formata endereço completo
        let fullAddress = orderData.client_address || 'Endereço não informado';
        if (orderData.client_number) fullAddress += ', ' + orderData.client_number;
        if (orderData.client_neighborhood) fullAddress += ' - ' + orderData.client_neighborhood;

        document.getElementById('modal-address').textContent = fullAddress;

        // Observação do Pedido (Adicionar elemento se não existir no HTML, mas vamos injetar via JS)
        const addressContainer = document.getElementById('modal-address').parentElement.parentElement;
        let obsContainer = document.getElementById('modal-observation-container');

        if (!obsContainer) {
            obsContainer = document.createElement('div');
            obsContainer.id = 'modal-observation-container';
            obsContainer.style.marginBottom = '16px';
            addressContainer.after(obsContainer);
        }

        if (orderData.observation) {
            obsContainer.innerHTML = `
                <h4 style="font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;">Observação</h4>
                <div style="background: #fff7ed; padding: 12px; border-radius: 8px; border: 1px solid #ffedd5; color: #c2410c; font-size: 0.9rem;">
                    ${orderData.observation}
                </div>
            `;
            obsContainer.style.display = 'block';
        } else {
            obsContainer.style.display = 'none';
        }

        document.getElementById('modal-total').textContent = 'R$ ' + parseFloat(orderData.total || 0).toFixed(2).replace('.', ',');
        document.getElementById('modal-time').textContent = orderData.created_at || '--';

        // [NOVO] Exibe status de pagamento
        const paymentEl = document.getElementById('modal-payment');
        const paymentContainer = paymentEl.parentElement;

        // [DEBUG] Forçar conversão para número
        const isPaidValue = parseInt(orderData.is_paid) || 0;
        if (isPaidValue === 1) {
            paymentEl.textContent = '✅ PAGO';
            paymentContainer.style.background = '#dcfce7';
            paymentEl.style.color = '#166534';
        } else {
            paymentEl.textContent = DeliveryConstants.getMethodLabel(orderData.payment_method);
            paymentContainer.style.background = '#fee2e2';
            paymentEl.style.color = '#dc2626';
        }

        // Badge de status
        const badge = document.getElementById('modal-order-badge');
        badge.textContent = DeliveryConstants.getStatusLabel(orderData.status);
        badge.className = 'delivery-badge delivery-badge--' + orderData.status;

        // Lista de itens (se disponível)
        const itemsList = document.getElementById('modal-items-list');
        if (orderData.items && orderData.items.length > 0) {
            let html = '';
            orderData.items.forEach(item => {
                html += `<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                    <span>${item.quantity}x ${item.name}</span>
                    <span style="font-weight: 600;">R$ ${parseFloat(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                </div>`;
            });
            itemsList.innerHTML = html;
        } else {
            itemsList.innerHTML = '<div style="color: #94a3b8; text-align: center; padding: 10px;">Itens não disponíveis</div>';
        }

        // Exibe modal
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Fecha modal de detalhes
     */
    closeDetailsModal: function () {
        const modal = document.getElementById('deliveryDetailsModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        this.currentOrder = null;
    },

    /**
     * Abre modal de cancelamento
     */
    openCancelModal: function (orderId) {
        const modal = document.getElementById('deliveryCancelModal');
        if (!modal) return;

        document.getElementById('cancel-order-id').textContent = orderId;
        document.getElementById('cancel-order-id-value').value = orderId;
        document.getElementById('cancel-reason').value = '';

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Fecha modal de cancelamento
     */
    closeCancelModal: function () {
        const modal = document.getElementById('deliveryCancelModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    },

    /**
     * Confirma cancelamento (usa ação existente)
     */
    confirmCancel: function () {
        const orderId = document.getElementById('cancel-order-id-value').value;
        const reason = document.getElementById('cancel-reason').value;

        // TODO: Salvar motivo em coluna separada (futura fase)
        // Por agora, apenas cancela
        if (orderId) {
            this.closeCancelModal();
            DeliveryActions.updateStatus(orderId, 'cancelado');
        }
    }
};

// Expõe globalmente
window.DeliveryUI = DeliveryUI;


/* ========== delivery/polling.js ========== */
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


/* ========== delivery/print-helpers.js ========== */
/**
 * PRINT-HELPERS.JS - Funções Auxiliares de Impressão
 * Módulo: DeliveryPrint.Helpers
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    DeliveryPrint.Helpers = {

        /**
         * Extrai e normaliza dados do pedido
         */
        extractOrderData: function (order) {
            let clientAddress = order.client_address || 'Endereço não informado';
            if (order.client_number) clientAddress += ', ' + order.client_number;

            return {
                clientName: order.client_name || 'Não identificado',
                clientPhone: order.client_phone || '--',
                clientAddress: clientAddress,
                neighborhood: order.client_neighborhood || order.neighborhood || '',
                observations: order.observation || order.observations || '',
                paymentMethod: order.payment_method || 'Não informado',
                changeFor: order.change_for || '',
                total: parseFloat(order.total || 0).toFixed(2).replace('.', ','),
                date: order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--',
                orderId: order.id
            };
        },

        /**
         * Gera HTML da lista de itens
         */
        generateItemsHTML: function (items, showPrice = true) {
            if (!items || items.length === 0) {
                return '<div style="color: #999;">Sem itens</div>';
            }

            return items.map(item => {
                if (showPrice) {
                    const subtotal = (item.quantity * item.price).toFixed(2).replace('.', ',');
                    return `
                        <div class="print-slip-item">
                            <span>${item.quantity}x ${item.name}</span>
                            <span>R$ ${subtotal}</span>
                        </div>
                    `;
                } else {
                    return `
                        <div style="padding: 8px 0; border-bottom: 1px dashed #ccc; font-size: 14px;">
                            <strong style="font-size: 18px;">${item.quantity}x</strong> ${item.name}
                        </div>
                    `;
                }
            }).join('');
        },

        /**
         * Gera HTML do troco (se aplicável)
         */
        generateChangeHTML: function (paymentMethod, changeFor) {
            if (!paymentMethod) return '';
            if (paymentMethod.toLowerCase() === 'dinheiro' && changeFor) {
                const changeValue = parseFloat(changeFor).toFixed(2).replace('.', ',');
                return `<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-weight: bold;">💵 TROCO PARA: R$ ${changeValue}</div>`;
            }
            return '';
        }
    };


})();


/* ========== delivery/print-generators.js ========== */
/**
 * PRINT-GENERATORS.JS - Geração de HTML das Fichas
 * Módulo: DeliveryPrint.Generators
 * 
 * Dependências: DeliveryPrint.Helpers
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    DeliveryPrint.Generators = {

        /**
         * Gera HTML da ficha UNIFICADA (Entrega ou Completa)
         */
        generateSlipHTML: function (order, items, title = '🛵 FICHA DE ENTREGA') {
            const orderItems = items || order.items || [];
            const data = DeliveryPrint.Helpers.extractOrderData(order);
            const itemsHTML = DeliveryPrint.Helpers.generateItemsHTML(orderItems, true);
            const changeHTML = DeliveryPrint.Helpers.generateChangeHTML(data.paymentMethod, data.changeFor);

            return `
                <div class="print-slip">
                    <div class="print-slip-header">
                        <h2>${title}</h2>
                        <div>Pedido #${data.orderId}</div>
                        <div style="font-size: 10px; color: #666;">${data.date}</div>
                    </div>

                    <div class="print-slip-section">
                        <h4>👤 Dados do Cliente:</h4>
                        <div><strong>Nome:</strong> ${data.clientName}</div>
                        <div><strong>Telefone:</strong> ${data.clientPhone}</div>
                    </div>

                    <div class="print-slip-section">
                        <h4>📍 Endereço de Entrega:</h4>
                        <div style="padding: 8px; background: #f5f5f5; border-radius: 4px;">
                            ${data.clientAddress}
                            ${data.neighborhood ? '<br><strong>Bairro:</strong> ' + data.neighborhood : ''}
                        </div>
                        ${data.observations ? '<div style="margin-top: 8px; font-weight: bold; color: #000;">📝 ' + data.observations + '</div>' : ''}
                    </div>

                    <div class="print-slip-section">
                        <h4>📦 Itens:</h4>
                        ${itemsHTML}
                    </div>

                    <div class="print-slip-section">
                        <h4>💳 Pagamento:</h4>
                        <div style="font-weight: bold; font-size: 14px;">${(data.paymentMethod || 'Não informado').toUpperCase()}</div>
                        ${changeHTML}
                    </div>

                    <div class="print-slip-total">
                        TOTAL: R$ ${data.total}
                    </div>
                </div>
            `;
        },

        /**
         * Gera HTML da ficha da COZINHA
         */
        generateKitchenSlipHTML: function (order, items) {
            const date = order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--';
            const orderType = order.order_type || 'local';

            const typeLabels = {
                'delivery': '🛵 ENTREGA',
                'pickup': '🏃 RETIRADA',
                'local': '🍽️ CONSUMO LOCAL'
            };
            const typeLabel = typeLabels[orderType] || '🍽️ CONSUMO LOCAL';

            const itemsHTML = DeliveryPrint.Helpers.generateItemsHTML(items, false);

            return `
                <div class="print-slip" style="font-size: 14px;">
                    <div class="print-slip-header" style="text-align: center; padding: 15px 0; border-bottom: 3px solid #333;">
                        <h2 style="margin: 0; font-size: 24px;">🍳 COZINHA</h2>
                        <div style="font-size: 12px; margin-top: 5px;">${date}</div>
                    </div>

                    <div style="text-align: center; padding: 15px; background: #f0f0f0; margin: 10px 0; border-radius: 8px;">
                        <div style="font-size: 22px; font-weight: bold;">${typeLabel}</div>
                        <div style="margin-top: 5px;">Pedido #${order.id}</div>
                    </div>

                    <div style="padding: 10px 0;">
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 5px;">Itens do Pedido:</h4>
                        ${itemsHTML}
                    </div>
                </div>
            `;
        }
    };


})();


/* ========== delivery/print-modal.js ========== */
/**
 * PRINT-MODAL.JS - Controle do Modal de Impressão
 * Módulo: DeliveryPrint.Modal
 * 
 * Dependências: DeliveryPrint.Generators
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    // Estado privado
    let currentOrderId = null;
    let currentOrderData = null;
    let currentItemsData = null;
    let slipType = 'delivery';

    DeliveryPrint.Modal = {

        // Getters para estado
        getCurrentOrderId: () => currentOrderId,
        getCurrentOrderData: () => currentOrderData,
        getSlipType: () => slipType,

        /**
         * Abre modal de impressão
         */
        open: async function (orderId, type = 'delivery') {
            currentOrderId = orderId;
            slipType = type;

            const modal = document.getElementById('deliveryPrintModal');
            const content = document.getElementById('print-slip-content');
            const tabsContainer = document.getElementById('print-tabs-container');

            if (!modal || !content) return;

            if (tabsContainer) {
                tabsContainer.style.display = 'none';
            }

            content.innerHTML = '<div style="padding: 40px; text-align: center; color: #64748b;">Carregando...</div>';
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');

            try {
                const baseUrl = DeliveryHelpers.getBaseUrl();
                const response = await fetch(baseUrl + '/admin/loja/delivery/details?id=' + orderId);
                const data = await response.json();

                if (!data.success) {
                    content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro: ' + data.message + '</div>';
                    return;
                }

                currentOrderData = data.order;
                currentItemsData = data.items;

                const html = this._renderSlip(type, data.order, data.items);
                content.innerHTML = html;

                if (typeof lucide !== 'undefined') lucide.createIcons();

            } catch (err) {
                content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro de conexão</div>';
            }
        },

        /**
         * Renderiza o slip baseado no tipo
         */
        _renderSlip: function (type, order, items) {
            switch (type) {
                case 'kitchen':
                    return DeliveryPrint.Generators.generateKitchenSlipHTML(order, items);
                case 'complete':
                    return DeliveryPrint.Generators.generateSlipHTML(order, items, '📋 FICHA DO PEDIDO');
                default:
                    return DeliveryPrint.Generators.generateSlipHTML(order, items, '🛵 FICHA DE ENTREGA');
            }
        },

        /**
         * Alterna para ficha do motoboy
         */
        showDeliverySlip: function () {
            slipType = 'delivery';
            const content = document.getElementById('print-slip-content');
            if (content && currentOrderData) {
                content.innerHTML = DeliveryPrint.Generators.generateSlipHTML(currentOrderData, currentItemsData, '🛵 FICHA DE ENTREGA');
            }
        },

        /**
         * Alterna para ficha da cozinha
         */
        showKitchenSlip: function () {
            slipType = 'kitchen';
            const content = document.getElementById('print-slip-content');
            if (content && currentOrderData) {
                content.innerHTML = DeliveryPrint.Generators.generateKitchenSlipHTML(currentOrderData, currentItemsData);
            }
        },

        /**
         * Fecha modal
         */
        close: function () {
            const modal = document.getElementById('deliveryPrintModal');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
            currentOrderId = null;
        }
    };


})();


/* ========== delivery/print-actions.js ========== */
/**
 * PRINT-ACTIONS.JS - Ações de Impressão
 * Módulo: DeliveryPrint.Actions
 * 
 * Dependências: DeliveryPrint.Modal, DeliveryPrint.Generators
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    DeliveryPrint.Actions = {

        /**
         * Imprime a ficha atual
         */
        print: function () {
            const content = document.getElementById('print-slip-content');
            const printArea = document.getElementById('print-area');

            if (!content || !printArea) return;

            printArea.innerHTML = content.innerHTML;
            window.print();

            DeliveryPrint.Modal.close();
        },

        /**
         * Imprime ficha completa diretamente (sem modal)
         */
        printComplete: function (orderData) {
            if (!orderData) {
                alert('Dados do pedido não disponíveis');
                return;
            }

            const printArea = document.getElementById('print-area');
            if (!printArea) {
                alert('Área de impressão não encontrada');
                return;
            }

            const html = DeliveryPrint.Generators.generateSlipHTML(orderData, orderData.items, '📋 FICHA DO PEDIDO');
            printArea.innerHTML = html;
            window.print();
        }
    };


})();


/* ========== delivery/print.js ========== */
/**
 * PRINT.JS - Orquestrador de Impressão Delivery
 * Namespace: DeliveryPrint
 * 
 * Dependências (carregar ANTES deste arquivo):
 * - print-helpers.js
 * - print-generators.js
 * - print-modal.js
 * - print-actions.js
 */

const DeliveryPrint = window.DeliveryPrint || {};

// ==========================================
// DELEGAÇÃO PARA MÓDULOS
// ==========================================

// Modal Control
DeliveryPrint.openModal = (orderId, type) => DeliveryPrint.Modal.open(orderId, type);
DeliveryPrint.closeModal = () => DeliveryPrint.Modal.close();
DeliveryPrint.showDeliverySlip = () => DeliveryPrint.Modal.showDeliverySlip();
DeliveryPrint.showKitchenSlip = () => DeliveryPrint.Modal.showKitchenSlip();

// Actions
DeliveryPrint.print = () => DeliveryPrint.Actions.print();
DeliveryPrint.printComplete = (orderData) => DeliveryPrint.Actions.printComplete(orderData);

// Generators (acesso direto para uso externo)
DeliveryPrint.generateSlipHTML = (order, items, title) =>
    DeliveryPrint.Generators.generateSlipHTML(order, items, title);
DeliveryPrint.generateKitchenSlipHTML = (order, items) =>
    DeliveryPrint.Generators.generateKitchenSlipHTML(order, items);

// Helpers (acesso direto para uso externo)
DeliveryPrint.extractOrderData = (order) => DeliveryPrint.Helpers.extractOrderData(order);
DeliveryPrint.generateItemsHTML = (items, showPrice) => DeliveryPrint.Helpers.generateItemsHTML(items, showPrice);

// ==========================================
// EXPÕE GLOBALMENTE
// ==========================================

window.DeliveryPrint = DeliveryPrint;



