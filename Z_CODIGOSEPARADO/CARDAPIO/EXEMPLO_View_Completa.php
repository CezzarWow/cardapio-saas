<!-- 
═══════════════════════════════════════════════════════════════════════════
EXEMPLO DE VIEW COMPLETA (BASEADA NO ESTOQUE)
═══════════════════════════════════════════════════════════════════════════

ARQUIVO DE REFERÊNCIA: views/admin/stock/index.php

Este é um exemplo de estrutura de view com todos os elementos comuns:
- Breadcrumb
- Título com indicadores
- Tabela com ações
- Modal de criação
- JavaScript para interatividade

Use como modelo para criar as views do Cardápio!
═══════════════════════════════════════════════════════════════════════════
-->

<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- ========== BREADCRUMB ========== -->
        <div class="breadcrumb" style="display: flex; gap: 8px; color: #6b7280; font-size: 0.875rem; margin-bottom: 10px;">
            <a href="<?= BASE_URL ?>/admin" style="color: #6b7280; text-decoration: none;">Painel</a> ›
            <strong style="color: #1f2937;">Cardápio</strong>
        </div>

        <!-- ========== TÍTULO E BOTÃO ========== -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 1.8rem; font-weight: 700; color: #1f2937;">Gerenciar Cardápio</h1>
                <p style="color: #6b7280; margin-top: 5px; font-size: 0.95rem;">Configure a exibição do seu cardápio digital</p>
            </div>
            <button onclick="openModal()" class="btn-stock-action" style="background: #10b981; color: white; padding: 12px 20px;">
                <i data-lucide="plus" size="18"></i> Novo Item
            </button>
        </div>

        <!-- ========== INDICADORES ========== -->
        <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
            <div class="stock-indicator">
                <i data-lucide="list" size="24" style="color: #2563eb;"></i>
                <div>
                    <p style="font-size: 0.8rem; color: #6b7280;">Total</p>
                    <p style="font-size: 1.4rem; font-weight: 700; color: #1f2937;"><?= count($items ?? []) ?></p>
                </div>
            </div>
            <div class="stock-indicator">
                <i data-lucide="eye" size="24" style="color: #10b981;"></i>
                <div>
                    <p style="font-size: 0.8rem; color: #6b7280;">Visíveis</p>
                    <p style="font-size: 1.4rem; font-weight: 700; color: #10b981;">-</p>
                </div>
            </div>
        </div>

        <!-- ========== BARRA DE PESQUISA ========== -->
        <div style="background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="position: relative;">
                <i data-lucide="search" size="18" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                <input type="text" id="searchInput" placeholder="Pesquisar..." 
                       oninput="filterTable(this.value)"
                       style="width: 100%; padding: 10px 12px 10px 40px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- ========== TABELA ========== -->
        <?php if (empty($items ?? [])): ?>
            <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i data-lucide="inbox" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhum item cadastrado</h3>
                <button onclick="openModal()" style="padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Primeiro Item
                </button>
            </div>
        <?php else: ?>
            <div class="stock-table-container">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Status</th>
                            <th style="text-align: center; width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php foreach ($items as $item): ?>
                        <tr class="table-row" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
                            <td>
                                <span style="font-weight: 600;"><?= htmlspecialchars($item['name']) ?></span>
                            </td>
                            <td>
                                <span style="padding: 4px 10px; background: #d1fae5; color: #065f46; border-radius: 12px; font-size: 0.8rem;">
                                    Ativo
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="<?= BASE_URL ?>/admin/loja/cardapio/editar?id=<?= $item['id'] ?>" 
                                       class="btn-stock-action">
                                        <i data-lucide="pencil" size="14"></i> Editar
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/loja/cardapio/deletar?id=<?= $item['id'] ?>" 
                                       onclick="return confirm('Tem certeza?')"
                                       class="btn-stock-action btn-stock-delete">
                                        <i data-lucide="trash-2" size="14"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- ========== MODAL DE CRIAÇÃO ========== -->
<div id="createModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">Novo Item</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/cardapio/salvar" method="POST">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome</label>
                <input type="text" name="name" required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeModal()" 
                        style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" 
                        style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========== JAVASCRIPT ========== -->
<script>
// Abrir modal
function openModal() {
    document.getElementById('createModal').style.display = 'flex';
}

// Fechar modal
function closeModal() {
    document.getElementById('createModal').style.display = 'none';
}

// Fechar ao clicar fora
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Filtrar tabela
function filterTable(query) {
    const rows = document.querySelectorAll('.table-row');
    const q = query.toLowerCase().trim();
    rows.forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
