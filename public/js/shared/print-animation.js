/**
 * Shared Print Animation Module
 * Gerencia o overlay de animação "Imprimindo..."
 */
(function () {
    'use strict';

    window.PrintAnimation = {

        init: function () {
            this.ensureOverlay();
        },

        ensureOverlay: function () {
            if (document.getElementById('printing-overlay-shared')) return;

            const css = `
                .printing-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px); z-index: 99999; display: flex; flex-direction: column; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
                .printer-anim { position: relative; width: 60px; height: 60px; margin-bottom: 20px; }
                .printer-base { position: absolute; bottom: 0; left: 0; right: 0; height: 25px; background: #e2e8f0; border-radius: 8px 8px 4px 4px; border: 2px solid #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 10; }
                .printer-paper { position: absolute; top: 5px; left: 10px; right: 10px; height: 35px; background: #fff; border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); z-index: 5; animation: printSlip 1.5s infinite ease-in-out; }
                @keyframes printSlip { 0% { transform: translateY(0); opacity: 0; } 20% { opacity: 1; } 80% { transform: translateY(-25px); opacity: 1; } 100% { transform: translateY(-30px); opacity: 0; } }
                .printing-text { color: white; font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
                @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            `;

            const style = document.createElement('style');
            style.textContent = css;
            document.head.appendChild(style);

            const div = document.createElement('div');
            div.id = 'printing-overlay-shared';
            div.className = 'printing-overlay';
            div.style.display = 'none';
            div.innerHTML = `<div class="printer-anim"><div class="printer-paper"></div><div class="printer-base"></div></div><div class="printing-text">Imprimindo...</div>`;
            document.body.appendChild(div);
        },

        show: function () {
            this.ensureOverlay();
            const overlay = document.getElementById('printing-overlay-shared');
            if (overlay) overlay.style.display = 'flex';
        },

        hide: function () {
            const overlay = document.getElementById('printing-overlay-shared');
            if (overlay) overlay.style.display = 'none';
        }
    };

    // Auto-init se desejado, ou deixar para o consumidor chamar
    window.PrintAnimation.init();

})();
