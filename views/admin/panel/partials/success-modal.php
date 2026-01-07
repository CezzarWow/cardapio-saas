<!-- SUCCESS MODAL -->
<!--
    Partial incluído via: require __DIR__ . '/partials/success-modal.php';
    FUNÇÃO JS: Modal é exibido automaticamente via JS
-->
    <div id="successModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 500; align-items: center; justify-content: center; pointer-events: none;">
        <div style="background: white; padding: 30px 50px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column; align-items: center; gap: 15px; animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="width: 80px; height: 80px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="check" style="width: 40px; height: 40px; color: #16a34a; stroke-width: 3;"></i>
            </div>
            <h2 style="margin: 0; color: #166534; font-size: 1.5rem; font-weight: 800;">Sucesso!</h2>
            <p style="margin: 0; color: #475569; font-weight: 500;">Operação realizada.</p>
        </div>
    </div>
    <style>
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
