<?php
/**
 * Partial: Modal Remover Mesa
 * Variáveis esperadas: nenhuma
 */
?>
<div id="removeTableModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 300px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="font-weight: 700; color: #b91c1c; margin-bottom: 15px;">Remover Mesa</h3>
        <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 10px;">Digite o número da mesa para excluir.</p>
        
        <input type="number" id="remove_table_number" placeholder="Número (Ex: 5)" style="width: 100%; padding: 12px; border: 2px solid #fca5a5; border-radius: 8px; margin-bottom: 20px; font-size: 1.2rem; text-align: center; font-weight: bold; color: #b91c1c;">
        
        <div style="display: flex; gap: 10px;">
            <button onclick="document.getElementById('removeTableModal').style.display='none'" style="flex: 1; padding: 10px; background: #f3f4f6; border: none; border-radius: 8px; font-weight: 600;">Cancelar</button>
            <button onclick="removeTable()" style="flex: 1; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600;">Excluir</button>
        </div>
    </div>
</div>
