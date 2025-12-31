<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$STOCK_CRITICAL_LIMIT = 5;
$totalProducts = count($products);

// Contar produtos por status
$criticalCount = 0;
$negativeCount = 0;
foreach ($products as $p) {
    $s = intval($p['stock']);
    if ($s < 0) $negativeCount++;
    elseif ($s <= $STOCK_CRITICAL_LIMIT) $criticalCount++;
}

?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>/admin">Painel</a> › 
            <span>Estoque</span> › 
            <strong>Reposição</strong>
        </div>

        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Reposição de Estoque</h1>
            <p style="color: #6b7280; margin-top: 5px;">Ajuste a quantidade em estoque de forma operacional</p>
        </div>

        <!-- Sub-abas (STICKY) -->
        <div class="sticky-tabs">
            <div class="stock-tabs">
                <a href="<?= BASE_URL ?>/admin/loja/produtos" class="stock-tab">
                    Produtos
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/categorias" class="stock-tab">
                    Categorias
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/adicionais" class="stock-tab">
                    Adicionais
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/reposicao" class="stock-tab active">
                    Reposição
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" class="stock-tab">
                    Movimentações
                </a>
            </div>
        </div>

        <!-- Indicadores -->
        <div class="stock-indicators">
            <div class="stock-indicator">
                <div style="background: #dbeafe; padding: 10px; border-radius: 8px;">
                    <i data-lucide="package" size="24" style="color: #2563eb;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;"><?= $totalProducts ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Produtos</div>
                </div>
            </div>
            <div class="stock-indicator">
                <div style="background: #fef3c7; padding: 10px; border-radius: 8px;">
                    <i data-lucide="alert-triangle" size="24" style="color: #d97706;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;"><?= $criticalCount ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Críticos</div>
                </div>
            </div>
            <div class="stock-indicator">
                <div style="background: #fecaca; padding: 10px; border-radius: 8px;">
                    <i data-lucide="trending-down" size="24" style="color: #dc2626;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;"><?= $negativeCount ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Negativos</div>
                </div>
            </div>
        </div>

        <!-- Chips de Categorias -->
        <div class="category-chips-container">
            <div class="category-chips">
                <button class="category-chip active" data-category="">
                    📂 Todas
                </button>
                <?php foreach ($categories as $cat): ?>
                    <?php if (!in_array($cat['category_type'] ?? 'default', ['featured', 'combos'])): ?>
                        <button class="category-chip" data-category="<?= htmlspecialchars($cat['name']) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tabela de Produtos -->
        <div class="stock-table-container">
            <table id="productsTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">Imagem</th>
                        <th>Produto</th>
                        <th style="text-align: center;">Estoque</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center; width: 150px;">Ação</th>
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
                            data-category="<?= htmlspecialchars($prod['category_name']) ?>">
                            <td>
                                <?php if($prod['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= $prod['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <i data-lucide="image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: #1f2937;"><?= htmlspecialchars($prod['name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($prod['category_name']) ?></small>
                            </td>
                            <td style="text-align: center;">
                                <span id="stock-<?= $prod['id'] ?>" style="font-size: 1.25rem; font-weight: 700; color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                                    <?= $stock ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span style="padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 600;
                                    background: <?= $isNegative ? '#fecaca' : ($isCritical ? '#fef3c7' : '#d1fae5') ?>;
                                    color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                                    <?= $isNegative ? 'Negativo' : ($isCritical ? 'Crítico' : 'Normal') ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <button onclick="openAdjustModal(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['name'])) ?>', <?= $stock ?>)"
                                        class="btn-stock-action" style="background: #2563eb; color: white;">
                                    <i data-lucide="plus-minus" size="14"></i> Ajustar
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
let selectedCategory = '';

// Filtro por chips de categoria
document.querySelectorAll('.category-chip').forEach(chip => {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        selectedCategory = this.dataset.category;
        filterProducts();
    });
});

function filterProducts() {
    const rows = document.querySelectorAll('.product-row');
    rows.forEach(row => {
        const cat = row.dataset.category;
        row.style.display = (!selectedCategory || cat === selectedCategory) ? '' : 'none';
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
            const stockEl = document.getElementById('stock-' + currentProductId);
            const row = stockEl?.closest('.product-row');
            
            if (stockEl && row) {
                const newStock = data.new_stock;
                stockEl.textContent = newStock;
                
                if (newStock < 0) {
                    stockEl.style.color = '#dc2626';
                } else if (newStock <= 5) {
                    stockEl.style.color = '#d97706';
                } else {
                    stockEl.style.color = '#059669';
                }
                
                row.dataset.stock = newStock;
                
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

document.getElementById('adjustModal').addEventListener('click', function(e) {
    if (e.target === this) closeAdjustModal();
});
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
