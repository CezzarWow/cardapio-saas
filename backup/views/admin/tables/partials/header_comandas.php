<?php
/**
 * Partial: Header da seção Comandas
 * Variáveis esperadas: nenhuma
 */
?>
<hr class="section-divider">

<div class="section-header">
    <h2 class="section-title">
        <i data-lucide="users" size="24" color="#059669"></i> COMANDAS
    </h2>
    <div class="section-actions">
        <button onclick="openNewClientModal('PF')" 
                class="btn-action btn-action--success"
                aria-label="Cadastrar novo cliente pessoa física">
            <i data-lucide="user" size="18"></i> Novo Cliente
        </button>

        <button onclick="openNewClientModal('PJ')" 
                class="btn-action btn-action--purple"
                aria-label="Cadastrar nova empresa pessoa jurídica">
            <i data-lucide="building-2" size="18"></i> Nova Empresa
        </button>
    </div>
</div>
