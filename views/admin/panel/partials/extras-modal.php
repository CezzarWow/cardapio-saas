<!-- MODAL DE ADICIONAIS -->
<!--
    Partial incluído via: \App\Core\View::renderFromScope('admin/panel/partials/extras-modal.php', get_defined_vars());
    FUNÇÕES JS NECESSÁRIAS:
    - closeExtrasModal()
    - confirmExtras()
    - decreaseExtrasQty()
    - increaseExtrasQty()
-->
    <div id="extrasModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; width: 500px; max-width: 95%; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fff;">
                <h3 id="extras-modal-title" style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700;">Opções</h3>
                <button onclick="closeExtrasModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            <div id="extras-modal-content" style="padding: 20px; overflow-y: auto; flex: 1;">
                <!-- Groups will be injected here -->
                <div style="text-align: center; color: #64748b;">Carregando opções...</div>
            </div>
            <div style="padding: 15px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                <!-- Seletor de Quantidade -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 600; color: #475569; font-size: 0.9rem;">Qtd:</span>
                    <div style="display: flex; align-items: center; gap: 5px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px;">
                        <button type="button" onclick="decreaseExtrasQty()" 
                                style="width: 32px; height: 32px; border: none; background: #fee2e2; color: #991b1b; border-radius: 6px; font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">−</button>
                        <span id="extras-qty-display" style="min-width: 35px; text-align: center; font-size: 1.1rem; font-weight: 700; color: #1e293b;">1</span>
                        <button type="button" onclick="increaseExtrasQty()" 
                                style="width: 32px; height: 32px; border: none; background: #dcfce7; color: #166534; border-radius: 6px; font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
                    </div>
                </div>
                
                <!-- Botões -->
                <div style="display: flex; gap: 10px;">
                    <button onclick="closeExtrasModal()" style="padding: 10px 16px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; cursor: pointer; color: #475569;">Cancelar</button>
                    <button id="btn-add-extras" onclick="confirmExtras()" style="padding: 10px 20px; background: #16a34a; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                        Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>
