<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$STOCK_CRITICAL_LIMIT = 5;
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Reposição de Estoque</h1>
            <p style="color: #6b7280; margin-top: 5px;">Ajuste a quantidade em estoque de forma operacional</p>
        </div>

        <!-- Sub-abas do Estoque -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/admin/loja/produtos" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Produtos
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/categorias" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Categorias
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/adicionais" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Adicionais
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/reposicao" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #2563eb; color: white;">
                Reposição
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Movimentações
            </a>
        </div>

        <!-- Filtro por Categoria -->
        <div style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <select id="filterCategory" style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; background: white; min-width: 200px;" onchange="filterProducts()">
                <option value="">Todas as categorias</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tabela de Produtos -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;" id="productsTable">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Imagem</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Nome</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Estoque Atual</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Status</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="5" style="padding: 2rem; text-align: center; color: #999;">Nenhum produto cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                        <?php 
                            $stock = intval($prod['stock']);
                            $isCritical = $stock <= $STOCK_CRITICAL_LIMIT && $stock >= 0;
                            $isNegative = $stock < 0;
                            $isNormal = $stock > $STOCK_CRITICAL_LIMIT;
                        ?>
                        <tr class="product-row" 
                            data-id="<?= $prod['id'] ?>"
                            data-name="<?= htmlspecialchars($prod['name']) ?>"
                            data-stock="<?= $stock ?>"
                            data-category="<?= htmlspecialchars($prod['category_name']) ?>"
                            style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 10px;">
                                <?php if($prod['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= $prod['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <i data-lucide="image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <strong style="color: #1f2937;"><?= htmlspecialchars($prod['name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($prod['category_name']) ?></small>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <span id="stock-<?= $prod['id'] ?>" style="font-size: 1.25rem; font-weight: 700; color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                                    <?= $stock ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <span style="padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 600;
                                    background: <?= $isNegative ? '#fecaca' : ($isCritical ? '#fef3c7' : '#d1fae5') ?>;
                                    color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                                    <?= $isNegative ? 'Negativo' : ($isCritical ? 'Crítico' : 'Normal') ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <button onclick="openAdjustModal(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['name'])) ?>', <?= $stock ?>)"
                                        style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <i data-lucide="plus-minus" size="16"></i> Ajustar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

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

<script>
let currentProductId = null;
let currentStock = 0;

// Filtrar por categoria
function filterProducts() {
    const category = document.getElementById('filterCategory').value;
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const cat = row.dataset.category;
        const matchCategory = !category || cat === category;
        row.style.display = matchCategory ? '' : 'none';
    });
}

// Abrir modal de ajuste
function openAdjustModal(productId, productName, stock) {
    currentProductId = productId;
    currentStock = stock;
    
    document.getElementById('modalProductName').textContent = productName;
    document.getElementById('modalCurrentStock').textContent = stock;
    document.getElementById('adjustAmount').value = '';
    document.getElementById('previewResult').style.display = 'none';
    
    document.getElementById('adjustModal').style.display = 'flex';
    document.getElementById('adjustAmount').focus();
}

// Fechar modal
function closeAdjustModal() {
    document.getElementById('adjustModal').style.display = 'none';
    currentProductId = null;
}

// Preview do resultado
document.getElementById('adjustAmount').addEventListener('input', function() {
    const amount = parseInt(this.value) || 0;
    if (amount !== 0) {
        const newStock = currentStock + amount;
        document.getElementById('previewStock').textContent = newStock;
        document.getElementById('previewResult').style.display = 'block';
        
        // Cor baseada no resultado
        const preview = document.getElementById('previewResult');
        if (newStock < 0) {
            preview.style.background = '#fecaca';
            preview.querySelector('span').style.color = '#dc2626';
        } else if (newStock <= 5) {
            preview.style.background = '#fef3c7';
            preview.querySelector('span').style.color = '#d97706';
        } else {
            preview.style.background = '#d1fae5';
            preview.querySelector('span').style.color = '#059669';
        }
    } else {
        document.getElementById('previewResult').style.display = 'none';
    }
});

// Enviar ajuste
function submitAdjust() {
    const amount = parseInt(document.getElementById('adjustAmount').value) || 0;
    
    if (amount === 0) {
        alert('Quantidade não pode ser zero');
        return;
    }
    
    fetch('<?= BASE_URL ?>/admin/loja/reposicao/ajustar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_id: currentProductId,
            amount: amount
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Atualiza o estoque na tabela
            const stockEl = document.getElementById('stock-' + currentProductId);
            const row = stockEl?.closest('.product-row');
            
            if (stockEl && row) {
                const newStock = data.new_stock;
                stockEl.textContent = newStock;
                
                // Atualiza cor do número
                if (newStock < 0) {
                    stockEl.style.color = '#dc2626';
                } else if (newStock <= 5) {
                    stockEl.style.color = '#d97706';
                } else {
                    stockEl.style.color = '#059669';
                }
                
                // Atualiza data-stock para próximo ajuste
                row.dataset.stock = newStock;
                
                // [FIX] Atualiza o badge de Status também
                const statusCell = row.querySelector('td:nth-child(4) span');
                if (statusCell) {
                    if (newStock < 0) {
                        statusCell.textContent = 'Negativo';
                        statusCell.style.background = '#fecaca';
                        statusCell.style.color = '#dc2626';
                    } else if (newStock <= 5) {
                        statusCell.textContent = 'Crítico';
                        statusCell.style.background = '#fef3c7';
                        statusCell.style.color = '#d97706';
                    } else {
                        statusCell.textContent = 'Normal';
                        statusCell.style.background = '#d1fae5';
                        statusCell.style.color = '#059669';
                    }
                }
            }
            
            closeAdjustModal();
            
            // Feedback visual
            alert('Estoque ajustado com sucesso!');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(err => {
        alert('Erro ao ajustar estoque');
        console.error(err);
    });
}

// Fechar modal ao clicar fora
document.getElementById('adjustModal').addEventListener('click', function(e) {
    if (e.target === this) closeAdjustModal();
});
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
