<?php
/**
 * Partial: Modal Dossiê do Cliente
 * Variáveis esperadas: nenhuma
 */
?>
<!-- MODAL: Dossiê do Cliente -->
<div id="dossierModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center;">
    <div style="background: white; width: 600px; max-width: 95%; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        
        <div style="background: #1e293b; padding: 20px; color: white; display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h2 id="dos_name" style="margin: 0; font-size: 1.4rem; font-weight: 700;">Carregando...</h2>
                <p id="dos_info" style="margin: 5px 0 0 0; color: #94a3b8; font-size: 0.9rem;">...</p>
            </div>
            <button onclick="document.getElementById('dossierModal').style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 2rem; line-height: 1;">&times;</button>
        </div>

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

        <div style="padding: 15px 20px; display: flex; gap: 10px; border-bottom: 1px solid #e2e8f0; background: white;">
            <button id="btn-dossier-order" style="flex: 1; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i data-lucide="shopping-cart" size="18"></i> NOVO PEDIDO / ABRIR
            </button>
        </div>

        <div style="flex: 1; overflow-y: auto; padding: 20px; background: white;">
            <h4 style="margin-top: 0; color: #475569; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-size: 0.9rem;">Últimas Movimentações</h4>
            <div id="dos_history_list"></div>
        </div>
    </div>
</div>
