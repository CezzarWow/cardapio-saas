<?php
/**
 * Partial: Modal Dossiê do Cliente
 * Variáveis esperadas: nenhuma
 */
?>
<!-- MODAL: Dossiê do Cliente -->
<div id="dossierModal" 
     class="table-modal" 
     style="z-index: 300;"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="dossierModalTitle"
     aria-hidden="true">
    <div class="table-modal__content table-modal__content--large">
        
        <!-- Header -->
        <div class="table-modal__header table-modal__header--dark" style="padding: 20px; align-items: start;">
            <div>
                <h2 id="dossierModalTitle" class="table-modal__title" id="dos_name" style="font-size: 1.4rem;">Carregando...</h2>
                <p id="dos_info" style="margin: 5px 0 0 0; color: #94a3b8; font-size: 0.9rem;">...</p>
            </div>
            <button onclick="document.getElementById('dossierModal').style.display='none'; document.getElementById('dossierModal').setAttribute('aria-hidden', 'true');" 
                    class="table-modal__close"
                    aria-label="Fechar dossiê"
                    style="color: white;">
                &times;
            </button>
        </div>

        <!-- Stats Cards -->
        <div style="padding: 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 15px;">
            <div style="flex: 1; background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Limite Total</div>
                <div id="dos_limit" style="font-size: 1.2rem; font-weight: 800; color: #334155;">R$ 0,00</div>
            </div>
            <div style="flex: 1; background: #fff1f2; padding: 15px; border-radius: 8px; border: 1px solid #fecaca; text-align: center;">
                <div style="font-size: 0.75rem; font-weight: 700; color: #9f1239; text-transform: uppercase;">Dívida Atual</div>
                <div id="dos_debt" style="font-size: 1.5rem; font-weight: 800; color: #e11d48;">R$ 0,00</div>
            </div>
        </div>

        <!-- Actions -->
        <div style="padding: 15px 20px; display: flex; gap: 10px; border-bottom: 1px solid #e2e8f0; background: white;">
            <button id="btn-dossier-order" 
                    class="btn-action btn-action--primary" 
                    style="flex: 1; padding: 12px; justify-content: center;">
                <i data-lucide="shopping-cart" size="18"></i> NOVO PEDIDO / ABRIR
            </button>
        </div>

        <!-- History -->
        <div style="flex: 1; overflow-y: auto; padding: 20px; background: white;">
            <h4 style="margin-top: 0; color: #475569; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-size: 0.9rem;">Últimas Movimentações</h4>
            <div id="dos_history_list"></div>
        </div>
    </div>
</div>
