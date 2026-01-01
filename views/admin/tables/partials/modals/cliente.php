<?php
/**
 * Partial: Modal Super Cliente (PF/PJ)
 * Variáveis esperadas: nenhuma
 */
?>
<div id="superClientModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center;">
    <div style="background: white; padding: 0; border-radius: 12px; width: 800px; max-width: 95%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        
        <div style="padding: 20px 25px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 1.25rem;">Novo Cadastro</h3>
                <p id="modal-subtitle" style="margin: 4px 0 0 0; font-size: 0.9rem; color: #64748b;">Preencha os dados do cliente</p>
            </div>
            <button onclick="document.getElementById('superClientModal').style.display='none'" style="border: none; background: none; color: #94a3b8; cursor: pointer; padding: 5px;">
                <i data-lucide="x" size="24"></i>
            </button>
        </div>

        <div style="padding: 25px 30px; overflow-y: auto; background: #fff;">
            
            <!-- SEÇÃO 1: DADOS -->
            <div style="margin-bottom: 25px;">
                <h4 id="header-dados" style="margin: 0 0 15px 0; font-size: 0.95rem; font-weight: 700; color: #2563eb; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">DADOS PESSOAIS</h4>
                
                <div style="margin-bottom: 15px;">
                    <label id="lbl-name" style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Nome Completo <span style="color: #ef4444">*</span></label>
                    <input type="text" id="cli_name" placeholder="Digite o nome completo" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label id="lbl-doc" style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">CPF</label>
                        <input type="text" id="cli_doc" placeholder="000.000.000-00" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Telefone</label>
                        <input type="text" id="cli_phone" placeholder="(00) 00000-0000" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 2: ENDEREÇO -->
            <div style="margin-bottom: 25px;">
                <h4 style="margin: 0 0 15px 0; font-size: 0.95rem; font-weight: 700; color: #2563eb; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">ENDEREÇO</h4>
                
                <div style="display: grid; grid-template-columns: 3fr 1fr 1.5fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Logradouro</label>
                        <input type="text" id="cli_addr" placeholder="Rua, Av, Travessa..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Número</label>
                        <input type="text" id="cli_num" placeholder="123" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">CEP</label>
                        <input type="text" id="cli_zip" placeholder="00000-000" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Bairro</label>
                        <input type="text" id="cli_bairro" placeholder="Bairro" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Cidade</label>
                        <input type="text" id="cli_city" placeholder="Cidade" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 3: CRÉDITO -->
            <div>
                <h4 style="margin: 0 0 15px 0; font-size: 0.95rem; font-weight: 700; color: #ea580c; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">FINANCEIRO (CREDIÁRIO)</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #fff7ed; padding: 15px; border-radius: 8px; border: 1px dashed #fdba74;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #c2410c; margin-bottom: 6px;">Limite de Crédito (R$)</label>
                        <input type="text" id="cli_limit" placeholder="R$ 0,00" style="width: 100%; padding: 10px; border: 1px solid #fdba74; border-radius: 6px; font-weight: 700; color: #c2410c; background: white;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #c2410c; margin-bottom: 6px;">Dia do Vencimento</label>
                        <input type="number" id="cli_due" placeholder="Dia (1-31)" min="1" max="31" style="width: 100%; padding: 10px; border: 1px solid #fdba74; border-radius: 6px; font-weight: 700; color: #c2410c; background: white;">
                    </div>
                </div>
            </div>

        </div>

        <div style="padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px;">
            <button onclick="document.getElementById('superClientModal').style.display='none'" style="padding: 10px 24px; background: white; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 700; color: #475569; cursor: pointer;">Cancelar</button>
            <button onclick="saveSuperClient()" style="padding: 10px 30px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">Salvar Cadastro</button>
        </div>
    </div>
</div>
