<?php
/**
 * Partial: Modal Nova Mesa
 * Variáveis esperadas: nenhuma
 */
?>
<div id="newTableModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 300px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 15px;">Nova Mesa</h3>
        <input type="number" id="new_table_number" min="0" placeholder="Número (Ex: 10)" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 20px; font-size: 1.2rem; text-align: center; font-weight: bold;">
        <div style="display: flex; gap: 10px;">
            <button onclick="document.getElementById('newTableModal').style.display='none'" style="flex: 1; padding: 10px; background: #f3f4f6; border: none; border-radius: 8px; font-weight: 600;">Cancelar</button>
            <button onclick="saveTable()" style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600;">Salvar</button>
        </div>
    </div>
</div>
