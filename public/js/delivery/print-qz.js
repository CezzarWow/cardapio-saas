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
