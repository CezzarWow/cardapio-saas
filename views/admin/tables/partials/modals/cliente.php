<?php
/**
 * Partial: Modal Super Cliente (PF/PJ)
 * Variáveis esperadas: nenhuma
 */
?>
<div id="superClientModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center;">
    <div style="background: white; padding: 0; border-radius: 12px; width: 900px; max-width: 95%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        
        <div style="padding: 20px 25px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: baseline; gap: 10px;">
                <h3 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 1.25rem;">Novo Cadastro</h3>
                <span id="modal-subtitle" style="font-size: 0.95rem; color: #64748b; font-weight: 500;">Preencha os dados do cliente</span>
            </div>
            <button onclick="closeSuperClientModal()" style="border: none; background: none; color: #94a3b8; cursor: pointer; padding: 5px;">
                <i data-lucide="x" size="24"></i>
            </button>
        </div>

        <div style="padding: 20px 25px; overflow-y: auto; background: #fff;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <!-- COLUNA ESQUERDA: DADOS + FINANCEIRO -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    
                    <!-- DADOS PESSOAIS -->
                    <div>
                        <h4 id="header-dados" style="margin: 0 0 10px 0; font-size: 0.9rem; font-weight: 800; color: #2563eb; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="user" size="16"></i> DADOS PESSOAIS
                        </h4>
                        
                        <div style="margin-bottom: 10px;">
                            <label id="lbl-name" style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Nome Completo <span style="color: #ef4444">*</span></label>
                            <input type="text" id="cli_name" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="Nome do cliente" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; font-weight: 600; color: #1e293b;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label id="lbl-doc" style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">CPF</label>
                                <input type="text" id="cli_doc" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="000.000.000-00" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Telefone</label>
                                <input type="text" id="cli_phone" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="(00) 00000-0000" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            </div>
                        </div>
                    </div>

                    <!-- FINANCEIRO -->
                    <div style="background: #fff7ed; padding: 15px; border-radius: 8px; border: 1px dashed #fdba74;">
                        <h4 style="margin: 0 0 10px 0; font-size: 0.9rem; font-weight: 800; color: #ea580c; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="wallet" size="16"></i> FINANCEIRO
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #c2410c; margin-bottom: 4px;">Limite (R$)</label>
                                <input type="text" id="cli_limit" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="0,00" style="width: 100%; padding: 8px 10px; border: 1px solid #fdba74; border-radius: 6px; font-weight: 700; color: #c2410c; background: white;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #c2410c; margin-bottom: 4px;">Vencimento</label>
                                <input type="number" id="cli_due" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="Dia" min="1" max="31" style="width: 100%; padding: 8px 10px; border: 1px solid #fdba74; border-radius: 6px; font-weight: 700; color: #c2410c; background: white;">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- COLUNA DIREITA: ENDEREÇO -->
                <div>
                    <h4 style="margin: 0 0 10px 0; font-size: 0.9rem; font-weight: 800; color: #2563eb; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="map-pin" size="16"></i> ENDEREÇO
                    </h4>
                    
                    <div style="display: grid; grid-template-columns: 0.8fr 1.2fr; gap: 10px; margin-bottom: 10px;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">CEP</label>
                            <input type="text" id="cli_zip" placeholder="00000-000" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        </div>
                         <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Cidade</label>
                            <input type="text" id="cli_city" placeholder="Cidade" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Logradouro</label>
                        <input type="text" id="cli_addr" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="Rua, Av..." style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 0.8fr 1.2fr; gap: 10px;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Número</label>
                            <input type="text" id="cli_num" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="123" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Bairro</label>
                            <input type="text" id="cli_bairro" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" placeholder="Bairro" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div style="padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px;">
            <button onclick="closeSuperClientModal()" style="padding: 10px 24px; background: white; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 700; color: #475569; cursor: pointer;">Cancelar</button>
            <button onclick="saveSuperClient()" style="padding: 10px 30px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">Salvar Cadastro</button>
        </div>
    </div>
</div>
