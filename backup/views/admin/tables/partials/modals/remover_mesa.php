<?php
/**
 * Partial: Modal Remover Mesa
 * Variáveis esperadas: nenhuma
 */
?>
<div id="removeTableModal" 
     class="table-modal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="removeTableModalTitle"
     aria-hidden="true">
    <div class="table-modal__content table-modal__content--small">
        <h3 id="removeTableModalTitle" class="table-modal__title" style="color: #b91c1c; margin-bottom: 15px;">Remover Mesa</h3>
        <p style="font-size: 0.9rem; color: #64748b; margin: 0 0 10px 0;">Digite o número da mesa para excluir.</p>
        
        <input type="number" 
               id="remove_table_number" 
               placeholder="Número (Ex: 5)" 
               class="table-modal__input table-modal__input--danger"
               aria-label="Número da mesa a ser removida"
               style="margin-bottom: 20px;">
        
        <div class="table-modal__footer" style="padding: 0; border: none; background: transparent;">
            <button onclick="document.getElementById('removeTableModal').style.display='none'; document.getElementById('removeTableModal').setAttribute('aria-hidden', 'true');" 
                    class="btn-action" 
                    style="flex: 1; background: #f3f4f6; color: #475569;">
                Cancelar
            </button>
            <button onclick="removeTable()" 
                    class="btn-action" 
                    style="flex: 1; background: #ef4444; color: white;">
                Excluir
            </button>
        </div>
    </div>
</div>
