/**
 * PRINT-QZ.JS - Integração QZ Tray
 * Módulo: DeliveryPrint.QZ
 * 
 * Dependências: qz-tray.js (CDN)
 */

(function () {
    'use strict';

    window.DeliveryPrint = window.DeliveryPrint || {};

    let isConnected = false;
    let printerName = null; // Guardar nome da impressora

    window.DeliveryPrint.QZ = {

        /**
         * Inicializa conexão
         */
        init: async function () {
            if (typeof qz === 'undefined') {
                console.error('[QZ] Biblioteca qz-tray.js não carregada!');
                alert('QZ Tray não está disponível. Verifique se o programa está rodando.');
                return false;
            }

            if (isConnected) return true;

            // Verifica se já está conectado
            if (qz.websocket.isActive()) {
                isConnected = true;
                console.log('[QZ] Já estava conectado!');
                return true;
            }

            try {
                console.log('[QZ] Tentando conectar ao QZ Tray...');

                // Para localhost, não precisa de certificado
                // O QZ vai abrir um popup pedindo permissão
                await qz.websocket.connect();

                isConnected = true;
                console.log('[QZ] Conectado com sucesso!');
                return true;
            } catch (e) {
                console.error('[QZ] Falha na conexão:', e);
                alert('Não foi possível conectar ao QZ Tray.\n\nVerifique:\n1. O QZ Tray está rodando (ícone verde)?\n2. Aceite a permissão quando aparecer.');
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
                    printerName = await qz.printers.getDefault();
                }
                console.log('[QZ] Impressora selecionada:', printerName);
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
                await this.findPrinter(); // Pega default
            }

            if (!printerName) return;

            // Converte HTML para texto puro
            const rawText = this._htmlToRaw(htmlContent);
            console.log('[QZ] Texto RAW:', rawText);

            // Configuração para impressora RAW
            const config = qz.configs.create(printerName, {
                altPrinting: true  // Usa modo alternativo
            });

            // Comandos ESC/POS para impressora térmica
            const ESC = '\x1B';
            const GS = '\x1D';

            // Inicializa + Texto + Corte
            const data = [
                ESC + '@',           // Reset impressora
                ESC + 'a' + '\x00',  // Alinhar à ESQUERDA
                rawText,
                '\n\n\n',            // Espaço antes do corte
                GS + 'V' + '\x00'    // Corte parcial
            ];

            try {
                await qz.print(config, data);
                console.log('[QZ] Enviado para impressão RAW!');

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
            // Cria elemento temporário
            const temp = document.createElement('div');
            temp.innerHTML = html;

            // Pega texto e limpa
            let text = temp.textContent || temp.innerText || '';

            // Remove espaços extras e linhas vazias múltiplas
            text = text.replace(/[ \t]+/g, ' ');
            text = text.replace(/\n\s*\n\s*\n/g, '\n\n');
            text = text.trim();

            // Formata para 32 caracteres de largura (58mm)
            const lines = text.split('\n');
            const formatted = lines.map(line => {
                line = line.trim();
                // Se a linha tem === ou ---, centraliza
                if (line.match(/^[=\-]{5,}$/)) {
                    return '================================';
                }
                return line;
            }).join('\n');

            return formatted;
        }
    };

})();
