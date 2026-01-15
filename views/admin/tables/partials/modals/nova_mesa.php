<?php
/**
 * Partial: Modal Nova Mesa
 * Variáveis esperadas: nenhuma
 */
?>
<div id="newTableModal" 
     class="table-modal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="newTableModalTitle"
     aria-hidden="true">
    <div class="table-modal__content table-modal__content--small">
        <h3 id="newTableModalTitle" class="table-modal__title" style="margin-bottom: 15px;">Nova Mesa</h3>
        
        <input type="number" 
               id="new_table_number" 
               min="0" 
               placeholder="Número (Ex: 10)" 
               class="table-modal__input"
               aria-label="Número da nova mesa"
               style="margin-bottom: 20px;">
        
        <div class="table-modal__footer" style="padding: 0; border: none; background: transparent;">
            <button onclick="document.getElementById('newTableModal').style.display='none'; document.getElementById('newTableModal').setAttribute('aria-hidden', 'true');" 
                    class="btn-action" 
                    style="flex: 1; background: #f3f4f6; color: #475569;">
                Cancelar
            </button>
            <button onclick="saveTable()" 
                    class="btn-action btn-action--primary" 
                    style="flex: 1;">
                Salvar
            </button>
        </div>
    </div>
</div>
