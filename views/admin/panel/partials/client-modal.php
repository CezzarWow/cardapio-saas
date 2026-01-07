<!-- MODAL NOVO CLIENTE -->
<!--
    Partial incluído via: require __DIR__ . '/partials/client-modal.php';
    FUNÇÕES JS NECESSÁRIAS:
    - PDVTables.searchClientInModal()
    - saveClient()
-->
<div id="clientModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 350px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; color: #1e293b;">Novo Cliente</h3>
        
        <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Nome</label>
            <input type="text" id="new_client_name" autocomplete="off"
                   oninput="PDVTables.searchClientInModal(this.value)"
                   style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <div id="modal-client-results" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 6px; max-height: 150px; overflow-y: auto; z-index: 10; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Telefone (Opcional)</label>
            <input type="text" id="new_client_phone" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="document.body.removeChild(document.getElementById('clientModal'));" style="flex: 1; padding: 10px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; color: #475569; font-weight: 600;">Cancelar</button>
            <button id="btn-save-new-client" onclick="saveClient()" style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Salvar</button>
        </div>
    </div>
</div>
