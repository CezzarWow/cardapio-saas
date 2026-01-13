<?php
/**
 * Partial: Header da seção Comandas
 * Variáveis esperadas: nenhuma
 */
?>
<hr style="border: 0; border-top: 2px dashed #e2e8f0; margin: 2rem 0;">

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.4rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="users" size="24" color="#059669"></i> COMANDAS
    </h2>
    <div style="display: flex; gap: 10px;">
        <button onclick="openNewClientModal('PF')" style="background: #059669; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 700; color: white; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);">
            <i data-lucide="user" size="18"></i> Novo Cliente
        </button>

        <button onclick="openNewClientModal('PJ')" style="background: #4f46e5; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 700; color: white; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);">
            <i data-lucide="building-2" size="18"></i> Nova Empresa
        </button>
    </div>
</div>
