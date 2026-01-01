<?php
/**
 * Partial: Header da seção Mesas
 * Variáveis esperadas: nenhuma
 */
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="font-size: 1.4rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="layout-grid" size="24" color="#2563eb"></i> SALÃO (MESAS)
    </h2>
    
    <div style="display: flex; gap: 10px;">
        <button onclick="openRemoveTableModal()" style="background: white; border: 1px solid #fca5a5; padding: 10px 15px; border-radius: 8px; font-weight: 700; color: #b91c1c; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="minus-circle" size="18"></i> Remover Mesa
        </button>

        <button onclick="openNewTableModal()" style="background: #2563eb; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; color: white; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
            <i data-lucide="plus-circle" size="18"></i> Nova Mesa
        </button>
    </div>
</div>
