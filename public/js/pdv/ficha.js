/**
 * PDV FICHA - Modal de Ficha do Cliente/Mesa
 * 
 * Funções para exibir, fechar e imprimir a ficha de consumo.
 * Dependências: Nenhuma
 */

window.PDVFicha = {

    /**
     * Abre o modal de ficha do cliente/mesa
     */
    open: function () {
        const modal = document.getElementById('fichaModal');
        if (modal) {
            modal.style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    },

    /**
     * Fecha o modal de ficha
     */
    close: function () {
        const modal = document.getElementById('fichaModal');
        if (modal) modal.style.display = 'none';
    },

    /**
     * Imprime a ficha do cliente/mesa
     */
    print: function () {
        const content = document.getElementById('fichaContent');
        if (!content) return;

        const printWindow = window.open('', '_blank', 'width=400,height=600');

        // Clone content and remove buttons
        const clone = content.cloneNode(true);
        clone.querySelectorAll('button').forEach(btn => btn.remove());

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Ficha do Cliente</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 20px; 
                        max-width: 320px; 
                        margin: 0 auto;
                    }
                    .header { text-align: center; margin-bottom: 15px; border-bottom: 2px dashed #000; padding-bottom: 10px; }
                    .header h1 { font-size: 18px; margin-bottom: 5px; }
                    .header p { font-size: 12px; color: #666; }
                    .item { padding: 8px 0; border-bottom: 1px solid #ddd; }
                    .item-name { font-weight: bold; font-size: 14px; }
                    .item-extras { font-size: 12px; color: #666; padding-left: 10px; }
                    .item-price { text-align: right; font-weight: bold; font-size: 14px; }
                    .total { margin-top: 15px; padding-top: 15px; border-top: 2px solid #000; text-align: right; }
                    .total-label { font-size: 16px; font-weight: bold; }
                    .total-value { font-size: 24px; font-weight: bold; }
                    @media print {
                        body { padding: 5px; }
                    }
                </style>
            </head>
            <body>
                ${clone.innerHTML}
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.onload = function () {
            printWindow.print();
        };
    }
};

// Expõe globalmente para uso no HTML
// window.PDVFicha = PDVFicha; // Já definido acima

// Aliases globais para compatibilidade com onclick no HTML
window.openFichaModal = () => PDVFicha.open();
window.closeFichaModal = () => PDVFicha.close();
window.printFicha = () => PDVFicha.print();
