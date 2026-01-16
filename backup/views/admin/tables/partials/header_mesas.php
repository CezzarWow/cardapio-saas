<?php
/**
 * Partial: Header da seção Mesas
 * Variáveis esperadas: nenhuma
 */
?>
<div class="section-header section-header--mesas">
    <h2 class="section-title">
        <i data-lucide="layout-grid" size="24" color="#2563eb"></i> SALÃO (MESAS)
    </h2>
    
    <div class="section-actions">
        <button onclick="openRemoveTableModal()" 
                class="btn-action btn-action--danger-outline"
                aria-label="Remover mesa existente">
            <i data-lucide="minus-circle" size="18"></i> Remover Mesa
        </button>

        <button onclick="openNewTableModal()" 
                class="btn-action btn-action--primary"
                aria-label="Adicionar nova mesa">
            <i data-lucide="plus-circle" size="18"></i> Nova Mesa
        </button>
    </div>
</div>
