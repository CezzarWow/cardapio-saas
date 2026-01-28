/* print-bundle - Generated 2026-01-28T14:00:26.560Z */


/* ========== delivery/print-helpers.js ========== */
/**
 * PRINT-HELPERS.JS - Funções Auxiliares de Impressão
 * Módulo: DeliveryPrint.Helpers
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    window.DeliveryPrint.Helpers = {

        /**
         * Extrai e normaliza dados do pedido
         */
        extractOrderData: function (order) {
            let clientAddress = order.client_address || null;
            if (clientAddress && order.client_number) clientAddress += ', ' + order.client_number;

            return {
                clientName: order.client_name || null,
                clientPhone: order.client_phone || null,
                clientAddress: clientAddress || null,
                neighborhood: order.client_neighborhood || order.neighborhood || null,
                observations: order.observation || order.observations || null,
                paymentMethod: order.payment_method || null,
                changeFor: order.change_for || null,
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

    window.DeliveryPrint.Generators = {

        /**
         * Gera HTML da ficha UNIFICADA (Entrega ou Completa)
         */
        generateSlipHTML: function (order, items, title = 'FICHA DE ENTREGA') {
            const orderItems = items || order.items || [];
            const data = window.DeliveryPrint.Helpers.extractOrderData(order);
            const itemsHTML = window.DeliveryPrint.Helpers.generateItemsHTML(orderItems, true);
            const changeHTML = window.DeliveryPrint.Helpers.generateChangeHTML(data.paymentMethod, data.changeFor);

            return `
                <div class="print-slip">
                    <div class="print-slip-header">
                        <h2>================================</h2>
                        <h2>${title}</h2>
                        <div>Pedido #${data.orderId}</div>
                        <div style="font-size: 10px;">${data.date}</div>
                        <h2>================================</h2>
                    </div>

                    <div class="print-slip-section">
                        <h4>CLIENTE:</h4>
                        <div><strong>Nome:</strong> ${data.clientName || 'Não identificado'}</div>
                        <div><strong>Fone:</strong> ${data.clientPhone || '--'}</div>
                    </div>

                    <div class="print-slip-section">
                        <h4>ENDERECO:</h4>
                        <div>${data.clientAddress || 'Endereço não informado'}</div>
                        ${data.neighborhood ? '<div><strong>Bairro:</strong> ' + data.neighborhood + '</div>' : ''}
                        ${data.observations ? '<div style="margin-top: 5px;"><strong>OBS:</strong> ' + data.observations + '</div>' : ''}
                    </div>

                    <div class="print-slip-section">
                        <h4>ITENS:</h4>
                        ${itemsHTML}
                    </div>

                    <div class="print-slip-section">
                        <h4>PAGAMENTO:</h4>
                        <div style="font-weight: bold;">${(data.paymentMethod || 'Não informado').toUpperCase()}</div>
                        ${changeHTML}
                    </div>

                    <div class="print-slip-total">
                        ================================
                        <br>TOTAL: R$ ${data.total}
                        <br>================================
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
                'delivery': '*** ENTREGA ***',
                'pickup': '*** RETIRADA ***',
                'local': '*** CONSUMO LOCAL ***'
            };
            const typeLabel = typeLabels[orderType] || '*** CONSUMO LOCAL ***';

            // [FIX] Filtra Taxa de Entrega da COZINHA
            const kitchenItems = (items || []).filter(item => item.name !== 'Taxa de Entrega');

            const itemsHTML = window.DeliveryPrint.Helpers.generateItemsHTML(kitchenItems, false);

            return `
                <div class="print-slip" style="font-size: 14px;">
                    <!-- Espaco para grampear -->
                    <div style="height: 15mm;"></div>
                    
                    <div class="print-slip-header" style="text-align: center; padding: 10px 0; border-bottom: 2px dashed #000;">
                        <h2 style="margin: 0; font-size: 20px;">** COZINHA **</h2>
                        <div style="font-size: 11px; margin-top: 5px;">${date}</div>
                    </div>

                    <div style="text-align: center; padding: 10px; margin: 8px 0; border: 1px dashed #000;">
                        <div style="font-size: 18px; font-weight: bold;">${typeLabel}</div>
                        <div style="margin-top: 5px;">Pedido #${order.id}</div>
                    </div>

                    <div style="padding: 8px 0;">
                        <h4 style="margin: 0 0 8px 0; font-size: 14px; text-transform: uppercase; border-bottom: 1px dashed #000; padding-bottom: 5px;">ITENS:</h4>
                        ${itemsHTML}
                    </div>
                    
                    <div style="text-align: center; padding-top: 10px; border-top: 2px dashed #000;">
                        --------------------------------
                    </div>
                </div>
            `;
        },

        /**
         * Gera HTML de RECIBO SIMPLES (Estilo Nota de Mercado)
         * Sem dados de entrega, focado em itens e totais
         */
        generateReceiptHTML: function (order, items) {
            const orderItems = items || order.items || [];
            const data = window.DeliveryPrint.Helpers.extractOrderData(order);

            // [FIX] Separa Taxa de Entrega dos itens normais
            const deliveryFeeItem = orderItems.find(item => item.name === 'Taxa de Entrega');
            const normalItems = orderItems.filter(item => item.name !== 'Taxa de Entrega');

            // Itens simplificados (sem observações longas se possível)
            const itemsHTML = normalItems.map(item => {
                const qtd = item.quantity || 1;
                const price = parseFloat(item.price || 0);
                const total = qtd * price;

                // Formatação
                const priceFmt = price.toFixed(2).replace('.', ',');
                const totalFmt = total.toFixed(2).replace('.', ',');

                // Adicionais
                let extrasHTML = '';
                if (item.additionals && item.additionals.length > 0) {
                    const extrasNames = item.additionals.map(a => `+ ${a.name}`).join(', ');
                    extrasHTML = `<div style="font-size: 10px; color: #444; margin-left: 10px;">${extrasNames}</div>`;
                }

                return `
                    <div style="margin-bottom: 5px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>${qtd}x ${item.name}</span>
                            <span>R$ ${totalFmt}</span>
                        </div>
                        ${extrasHTML}
                    </div>
                `;
            }).join('');

            // HTML da Taxa Separada
            let feeHTML = '';
            if (deliveryFeeItem) {
                const feeVal = parseFloat(deliveryFeeItem.price || 0).toFixed(2).replace('.', ',');
                feeHTML = `
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; border-top: 1px dotted #999; padding-top: 5px;">
                        <span>Taxa de Entrega</span>
                        <span>R$ ${feeVal}</span>
                    </div>
                `;
            }

            return `
                <div class="print-slip" style="font-size: 12px; font-family: 'Courier New', monospace;">
                    
                    <div style="text-align: center; margin-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold;">EXTRATO DE VENDA</h3>
                        <div style="margin-top: 5px;">Pedido #${data.orderId}</div>
                        <div style="font-size: 10px;">${data.date}</div>
                    </div>

                    <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-bottom: 10px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">ITEM &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; VALOR</div>
                        ${itemsHTML}
                        ${feeHTML}
                    </div>

                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-bottom: 5px;">
                        <span>TOTAL</span>
                        <span>R$ ${data.total}</span>
                    </div>

                    <div style="border-top: 1px dashed #000; padding-top: 5px; margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Pagamento:</span>
                            <span>${(data.paymentMethod || 'A Receber').toUpperCase()}</span>
                        </div>
                        ${data.changeFor ? `
                        <div style="display: flex; justify-content: space-between;">
                            <span>Troco:</span>
                            <span>R$ ${data.changeFor}</span>
                        </div>` : ''}
                    </div>

                    ${data.clientName ? `
                    <div style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px; text-align: center;">
                        Consumidor: ${data.clientName}
                    </div>` : ''}

                    <div style="text-align: center; margin-top: 20px; font-size: 10px;">
                        OBRIGADO PELA PREFERENCIA
                    </div>
                </div>
            `;
        }
    };

})();


/* ========== delivery/print-qz.js ========== */
/**
 * PRINT-QZ.JS - Integração QZ Tray
 * Módulo: DeliveryPrint.QZ
 * 
 * Dependências: qz-tray.js (CDN)
 * 
 * IMPORTANTE: A segurança (certificado + assinatura) é configurada
 * IMEDIATAMENTE quando este arquivo é carregado, ANTES de qualquer
 * chamada a qz.websocket.connect().
 */

(function () {
    'use strict';

    window.DeliveryPrint = window.DeliveryPrint || {};

    let isConnected = false;
    let isSecurityConfigured = false;
    let printerName = null;

    // ============================================================
    // 1️⃣ CONFIGURAR SEGURANÇA IMEDIATAMENTE (NO LOAD DO ARQUIVO)
    // ============================================================
    function setupSecurityNow() {
        if (typeof qz === 'undefined') {
            console.warn('[QZ] Biblioteca qz-tray.js ainda não carregada. Tentando novamente em 100ms...');
            setTimeout(setupSecurityNow, 100);
            return;
        }

        if (isSecurityConfigured) {
            return;
        }

        const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';

        // 1️⃣ CERTIFICADO - Formato oficial QZ Tray
        qz.security.setCertificatePromise(function (resolve, reject) {
            fetch(baseUrl + '/qz/certificate.php', { cache: 'no-store', headers: { 'Content-Type': 'text/plain' } })
                .then(function (data) { data.ok ? resolve(data.text()) : reject(data.text()); });
        });

        // 2️⃣ ALGORITMO - Obrigatório desde QZ 2.1
        qz.security.setSignatureAlgorithm("SHA512");

        // 3️⃣ ASSINATURA - Formato oficial QZ Tray
        qz.security.setSignaturePromise(function (toSign) {
            return function (resolve, reject) {
                fetch(baseUrl + '/qz/sign.php?request=' + encodeURIComponent(toSign), { cache: 'no-store', headers: { 'Content-Type': 'text/plain' } })
                    .then(function (data) { data.ok ? resolve(data.text()) : reject(data.text()); });
            };
        });

        isSecurityConfigured = true;
    }

    // Executa imediatamente ao carregar o arquivo
    setupSecurityNow();

    // ============================================================
    // 2️⃣ MÓDULO QZ (conexão e impressão)
    // ============================================================
    window.DeliveryPrint.QZ = {

        /**
         * Conecta ao QZ Tray (segurança já configurada)
         */
        init: async function () {
            if (typeof qz === 'undefined') {
                console.error('[QZ] Biblioteca qz-tray.js não carregada!');
                alert('QZ Tray não está disponível. Verifique se o programa está rodando.');
                return false;
            }

            // Garante que segurança foi configurada
            if (!isSecurityConfigured) {
                setupSecurityNow();
            }

            if (isConnected) return true;

            // Se já está conectado mas não por nós
            if (qz.websocket.isActive()) {
                isConnected = true;
                return true;
            }

            try {
                await qz.websocket.connect();
                isConnected = true;
                return true;
            } catch (e) {
                console.error('[QZ] Falha na conexão:', e);
                alert('Não foi possível conectar ao QZ Tray.\n\nVerifique:\n1. O QZ Tray está rodando (ícone verde)?\n2. Os arquivos de certificado foram gerados?\n3. Aceite a permissão quando aparecer.');
                return false;
            }
        },

        /**
         * Encontra impressora (padrão ou nome específico)
         */
        findPrinter: async function (name = null) {
            if (!isConnected) await this.init();

            try {
                if (name) {
                    printerName = await qz.printers.find(name);
                } else {
                    // Tenta pegar a impressora padrão
                    try {
                        printerName = await qz.printers.getDefault();
                    } catch (defaultErr) {
                        // Sem impressora padrão, lista todas
                        const allPrinters = await qz.printers.find();

                        if (allPrinters && allPrinters.length > 0) {
                            // Tenta encontrar uma térmica (geralmente tem "POS", "58", "80", "Thermal" no nome)
                            const thermalKeywords = ['pos', 'thermal', '58', '80', 'receipt', 'termica'];
                            const thermalPrinter = allPrinters.find(p =>
                                thermalKeywords.some(k => p.toLowerCase().includes(k))
                            );

                            printerName = thermalPrinter || allPrinters[0];
                        } else {
                            throw new Error('Nenhuma impressora encontrada');
                        }
                    }
                }
                return printerName;
            } catch (e) {
                console.error('[QZ] Impressora não encontrada:', e);
                alert('Impressora não encontrada! Verifique o QZ Tray.');
                return null;
            }
        },

        /**
         * Imprime usando texto RAW (melhor para térmicas)
         */
        printHTML: async function (htmlContent) {
            if (!isConnected) {
                const ok = await this.init();
                if (!ok) return;
            }

            if (!printerName) {
                await this.findPrinter();
            }

            if (!printerName) return;

            // Converte HTML para texto puro
            const rawText = this._htmlToRaw(htmlContent);

            // Configuração para impressora RAW
            const config = qz.configs.create(printerName, {
                altPrinting: true
            });

            // Comandos ESC/POS para impressora térmica
            const ESC = '\x1B';
            const GS = '\x1D';

            const data = [
                ESC + '@',           // Reset impressora
                ESC + 'a' + '\x00',  // Alinhar à ESQUERDA
                rawText,
                '\n',                // Espaço mínimo antes do corte
                GS + 'V' + '\x00'    // Corte parcial
            ];

            try {
                await qz.print(config, data);

                // Fecha o modal de impressão
                if (window.DeliveryPrint.Modal) {
                    window.DeliveryPrint.Modal.close();
                }
            } catch (e) {
                console.error('[QZ] Erro ao imprimir:', e);
                alert('Erro ao enviar para impressora: ' + e);
            }
        },

        /**
         * Converte HTML para texto puro formatado
         */
        _htmlToRaw: function (html) {
            const temp = document.createElement('div');
            temp.innerHTML = html;

            let text = temp.textContent || temp.innerText || '';
            text = text.replace(/[ \t]+/g, ' ');
            text = text.replace(/\n\s*\n\s*\n/g, '\n\n');
            text = text.trim();

            const lines = text.split('\n');
            const formatted = lines.map(line => {
                line = line.trim();
                if (line.match(/^[=\-]{5,}$/)) {
                    return '================================';
                }
                return line;
            }).join('\n');

            return formatted;
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

    window.DeliveryPrint.Modal = {

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

            // Salva elemento com foco para restaurar depois
            this._previouslyFocused = document.activeElement;

            if (tabsContainer) {
                tabsContainer.style.display = 'none';
            }

            content.innerHTML = '<div style="padding: 40px; text-align: center; color: #64748b;">Carregando...</div>';
            content.innerHTML = '<div style="padding: 40px; text-align: center; color: #64748b;">Carregando...</div>';

            // ✅ ABRE MODAL (inert + hidden)
            modal.removeAttribute('hidden');
            modal.removeAttribute('inert');
            modal.style.display = 'flex';

            // Move foco para botão de fechar
            const closeBtn = modal.querySelector('.delivery-modal__close');
            if (closeBtn) setTimeout(() => closeBtn.focus(), 50);

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
                    return window.DeliveryPrint.Generators.generateKitchenSlipHTML(order, items);
                case 'complete':
                    return window.DeliveryPrint.Generators.generateSlipHTML(order, items, 'FICHA DO PEDIDO');
                default:
                    return window.DeliveryPrint.Generators.generateSlipHTML(order, items, 'FICHA DE ENTREGA');
            }
        },

        /**
         * Alterna para ficha do motoboy
         */
        showDeliverySlip: function () {
            slipType = 'delivery';
            const content = document.getElementById('print-slip-content');
            if (content && currentOrderData) {
                content.innerHTML = window.DeliveryPrint.Generators.generateSlipHTML(currentOrderData, currentItemsData, 'FICHA DE ENTREGA');
            }
        },

        /**
         * Alterna para ficha da cozinha
         */
        showKitchenSlip: function () {
            slipType = 'kitchen';
            const content = document.getElementById('print-slip-content');
            if (content && currentOrderData) {
                content.innerHTML = window.DeliveryPrint.Generators.generateKitchenSlipHTML(currentOrderData, currentItemsData);
            }
        },

        /**
         * Fecha modal
         */
        close: function () {
            const modal = document.getElementById('deliveryPrintModal');
            if (!modal) return;

            // ✅ PASSO 1: INERT IMEDIATO
            modal.setAttribute('inert', '');

            // ✅ PASSO 2: Move foco para FORA
            if (this._previouslyFocused && typeof this._previouslyFocused.focus === 'function') {
                try { this._previouslyFocused.focus(); } catch (e) { }
            } else {
                document.body.focus();
            }
            this._previouslyFocused = null;

            // ✅ PASSO 3: Oculta
            modal.style.display = 'none';
            modal.setAttribute('hidden', '');
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

    window.DeliveryPrint.Actions = {

        /**
         * Imprime a ficha atual
         */
        print: function () {
            const content = document.getElementById('print-slip-content');
            const printArea = document.getElementById('print-area');

            if (!content || !printArea) return;

            printArea.innerHTML = content.innerHTML;
            window.print();

            window.DeliveryPrint.Modal.close();
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

            const html = window.DeliveryPrint.Generators.generateSlipHTML(orderData, orderData.items, '📋 FICHA DO PEDIDO');
            printArea.innerHTML = html;
            window.print();
        },

        /**
         * Imprime diretamente pelo ID e Tipo (Pula prévia)
         */
        printDirect: async function (orderId, type) {
            let orderData = null;
            let itemsData = null;

            // Tenta usar dados já carregados no UI Principal para ser instantâneo
            if (window.DeliveryUI && window.DeliveryUI.currentOrder && window.DeliveryUI.currentOrder.id == orderId) {
                orderData = window.DeliveryUI.currentOrder;
                itemsData = orderData.items || [];
            } else {
                // Fetch silencioso se necessário
                try {
                    const baseUrl = window.DeliveryHelpers ? window.DeliveryHelpers.getBaseUrl() : '';
                    const response = await fetch(baseUrl + '/admin/loja/delivery/details?id=' + orderId);
                    const data = await response.json();
                    if (data.success) {
                        orderData = data.order;
                        itemsData = data.items;
                    }
                } catch (e) {
                    console.error('Erro ao buscar dados para impressão direta', e);
                    return;
                }
            }

            if (!orderData) return;

            const printArea = document.getElementById('print-area');
            if (!printArea) return;

            let html = '';
            // Gera o HTML correspondente
            if (type === 'kitchen') {
                html = window.DeliveryPrint.Generators.generateKitchenSlipHTML(orderData, itemsData);
            } else {
                html = window.DeliveryPrint.Generators.generateSlipHTML(orderData, itemsData, 'FICHA DE ENTREGA');
            }

            printArea.innerHTML = html;

            // [QZ Tray] Tentativa de impressão silenciosa
            if (window.DeliveryPrint.QZ) {
                // Tenta init se não estiver conectado
                await window.DeliveryPrint.QZ.init();
                // Envia para impressora
                // printHTML cuida de achar a printer default
                const qzSuccess = await window.DeliveryPrint.QZ.printHTML(html);

                // Se o script QZ rodou sem erro (retornou promise resolved), consideramos impresso
                // Mas printHTML retorna void ou undefined em sucesso, e alerta em erro.
                // Vamos assumir que se não lançou exceção global, foi.
                // Mas para garantir o fallback, vamos fazer o seguinte:
                // Se o usuário cancelou o certificado ou QZ não está rodando, init retorna false.
                return;
            }

            // Fallback para navegador
            setTimeout(() => {
                window.print();
            }, 50);
        },

        /**
         * Imprime usando o conteúdo já renderizado no modal de prévia
         */
        printFromModal: async function () {
            const content = document.getElementById('print-slip-content');
            if (!content) {
                alert('Conteúdo de impressão não encontrado');
                return;
            }

            const html = content.innerHTML;

            // Usa QZ Tray se disponível
            if (window.DeliveryPrint.QZ) {
                await window.DeliveryPrint.QZ.printHTML(html);
            } else {
                // Fallback: impressão pelo navegador
                const printArea = document.getElementById('print-area');
                if (printArea) {
                    printArea.innerHTML = html;
                    window.print();
                }
            }
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
DeliveryPrint.print = () => window.DeliveryPrint.Actions.print();
DeliveryPrint.printComplete = (orderData) => window.DeliveryPrint.Actions.printComplete(orderData);
DeliveryPrint.printDirect = (orderId, type) => window.DeliveryPrint.Actions.printDirect(orderId, type);
DeliveryPrint.printFromModal = () => window.DeliveryPrint.Actions.printFromModal();

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



