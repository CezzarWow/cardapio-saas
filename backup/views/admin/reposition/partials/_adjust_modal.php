<?php
/**
 * PARTIAL: Modal de Ajuste de Estoque
 * Extraído de reposition/index.php
 */
?>

<!-- Modal de Ajuste de Estoque -->
<div id="adjustModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Ajustar Estoque</h3>
        
        <div id="productInfo" style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 1rem;">
            <div style="font-weight: 600; color: #1f2937;" id="modalProductName">-</div>
            <div style="color: #6b7280; font-size: 0.9rem;">Estoque atual: <span id="modalCurrentStock" style="font-weight: 700;">0</span></div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Quantidade a ajustar</label>
            <input type="number" id="adjustAmount" placeholder="Ex: +10 ou -5" 
                   style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1.1rem; text-align: center;">
            <small style="color: #6b7280; font-size: 0.85rem;">Use valores positivos para entrada, negativos para saída</small>
        </div>

        <div id="previewResult" style="background: #dbeafe; padding: 12px; border-radius: 8px; margin-bottom: 1rem; text-align: center; display: none;">
            <span style="color: #1e40af;">Novo estoque: <strong id="previewStock">0</strong></span>
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="closeAdjustModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cancelar
            </button>
            <button onclick="submitAdjust()" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Confirmar
            </button>
        </div>
    </div>
</div>
