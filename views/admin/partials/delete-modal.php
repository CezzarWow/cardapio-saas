<!-- Modal de Exclusão Genérico -->
<!--
    USO: \App\Core\View::renderFromScope('admin/partials/delete-modal.php', get_defined_vars());
    
    FUNÇÕES JS NECESSÁRIAS (em additionals.js ou global):
    - openDeleteModal(actionUrl, itemName)
    - closeDeleteModal()
-->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 0; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div style="background: #fee2e2; padding: 1.5rem; text-align: center;">
            <div style="background: #fecaca; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i data-lucide="alert-triangle" size="32" style="color: #ef4444;"></i>
            </div>
            <h3 style="margin-top: 1rem; color: #991b1b; font-weight: 700; font-size: 1.25rem;">Excluir Item?</h3>
        </div>
        
        <div style="padding: 1.5rem;">
            <p style="text-align: center; color: #4b5563; margin-bottom: 1.5rem; line-height: 1.5;">
                Tem certeza que deseja excluir <strong id="deleteItemName" style="color: #1f2937;">este item</strong>?<br>
                <span style="font-size: 0.9rem; color: #6b7280;">Esta ação não pode ser desfeita.</span>
            </p>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="closeDeleteModal()" style="flex: 1; padding: 10px; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    Cancelar
                </button>
                <a id="confirmDeleteBtn" href="#" style="flex: 1; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    Sim, Excluir
                </a>
            </div>
        </div>
    </div>
</div>
