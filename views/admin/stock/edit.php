<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; justify-content: center; overflow-y: auto;">
        
        <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 600px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: fit-content;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700; color: #1f2937;">Editar Produto</h2>
            
            <form action="<?= BASE_URL ?>/admin/loja/produtos/atualizar" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Nome do Produto</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Categoria</label>
                        <select name="category_id" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="flex: 1; position: relative;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Vincular Adicionais</label>
                        
                        <div class="custom-select-container" style="position: relative;">
                            <?php 
                                $checkedCount = 0;
                                foreach($additionalGroups as $g) {
                                    if(in_array($g['id'], $linkedGroups)) $checkedCount++;
                                }
                                $triggerLabel = $checkedCount > 0 ? $checkedCount . ' Selecionado(s)' : 'Selecione...';
                                $triggerStyle = $checkedCount > 0 ? 'color: #1f2937; font-weight: 600;' : 'color: #6b7280;';
                            ?>
                            <div class="select-trigger" onclick="toggleSelect(this)" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                <span class="trigger-text" style="<?= $triggerStyle ?>"><?= $triggerLabel ?></span>
                                <i data-lucide="chevron-down" size="16" style="color: #9ca3af;"></i>
                            </div>
                            
                            <div class="options-list" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10; padding: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                <?php if (empty($additionalGroups)): ?>
                                    <p style="color: #9ca3af; font-size: 0.9rem; padding: 8px; text-align: center; margin: 0;">Nenhum grupo cadastrado</p>
                                <?php else: ?>
                                    <?php foreach ($additionalGroups as $group): ?>
                                        <label style="display: flex; align-items: center; gap: 8px; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.1s;">
                                            <input type="checkbox" name="additional_groups[]" value="<?= $group['id'] ?>" 
                                                <?= in_array($group['id'], $linkedGroups) ? 'checked' : '' ?>
                                                onchange="updateTriggerText(this)" style="width: 16px; height: 16px;">
                                            <span style="font-size: 0.95rem; color: #374151;"><?= htmlspecialchars($group['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Preço (R$)</label>
                        <input type="text" name="price" id="priceInput" required 
                               value="<?= number_format($product['price'], 2, ',', '') ?>" 
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1.1rem; font-weight: 600; text-align: right;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Estoque Atual</label>
                        <input type="number" name="stock" value="<?= intval($product['stock']) ?>" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <small style="color: #6b7280; font-size: 0.85rem;">Quantidade (pode ser negativo)</small>
                    </div>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Descrição</label>
                    <textarea name="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: sans-serif;"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Foto do Produto</label>
                    <?php if (!empty($product['image'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= BASE_URL ?>/uploads/<?= $product['image'] ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <span style="margin-left: 10px; color: #6b7280; font-size: 0.9rem;">Imagem atual</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" style="width: 100%; padding: 10px; border: 1px dashed #d1d5db; border-radius: 8px; background: #f9fafb;">
                    <small style="color: #6b7280; font-size: 0.85rem;">Deixe vazio para manter a imagem atual</small>
                </div>

                <div style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
                    <button type="submit" class="btn-primary" style="flex: 1;">Salvar Alterações</button>
                    <a href="<?= BASE_URL ?>/admin/loja/produtos" style="padding: 12px; color: #6b7280; text-decoration: none; font-weight: 600;">Cancelar</a>
                </div>

            </form>
        </div>

    </div>
</main>

<!-- Máscara de preço estilo calculadora -->
<script>
(function() {
    const priceInput = document.getElementById('priceInput');
    if (!priceInput) return;
    
    // Converte valor inicial para centavos
    let initialValue = priceInput.value.replace(/\D/g, '');
    let cents = parseInt(initialValue) || 0;
    
    // Formata centavos para exibição
    function formatCents(c) {
        const str = String(c).padStart(3, '0');
        const integer = str.slice(0, -2) || '0';
        const decimal = str.slice(-2);
        return integer + ',' + decimal;
    }
    
    // Exibe valor formatado
    priceInput.value = formatCents(cents);
    
    // Ao focar, seleciona tudo
    priceInput.addEventListener('focus', function() {
        this.select();
    });
    
    // Ao clicar, também seleciona tudo
    priceInput.addEventListener('click', function() {
        this.select();
    });
    
    // Controla a digitação
    priceInput.addEventListener('keydown', function(e) {
        // Permite: backspace, delete, tab, enter, escape
        if ([8, 46, 9, 13, 27].includes(e.keyCode)) {
            if (e.keyCode === 8 || e.keyCode === 46) {
                e.preventDefault();
                cents = Math.floor(cents / 10);
                this.value = formatCents(cents);
            }
            return;
        }
        
        // Bloqueia tudo que não for número
        if (e.key < '0' || e.key > '9') {
            e.preventDefault();
            return;
        }
        
        e.preventDefault();
        
        if (cents > 999999) return;
        
        cents = cents * 10 + parseInt(e.key);
        this.value = formatCents(cents);
    });
    
    // Move cursor pro final sempre
    priceInput.addEventListener('input', function() {
        const len = this.value.length;
        this.setSelectionRange(len, len);
    });
})();

// --- LÓGICA DO MULTI-SELECT CUSTOMIZADO ---
function toggleSelect(el) {
    const container = el.parentElement;
    const list = container.querySelector('.options-list');
    
    // Fecha outros
    document.querySelectorAll('.options-list').forEach(l => {
        if (l !== list) l.style.display = 'none';
    });

    if (list.style.display === 'block') {
        list.style.display = 'none';
    } else {
        list.style.display = 'block';
    }
}

function updateTriggerText(checkbox) {
    const container = checkbox.closest('.custom-select-container');
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const triggerText = container.querySelector('.trigger-text');
    
    if (checkedCount === 0) {
        triggerText.textContent = 'Selecione...';
        triggerText.style.color = '#6b7280';
        triggerText.style.fontWeight = '400';
    } else {
        triggerText.textContent = checkedCount + ' Selecionado(s)';
        triggerText.style.color = '#1f2937';
        triggerText.style.fontWeight = '600';
    }
}

// Fechar ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-select-container')) {
        document.querySelectorAll('.options-list').forEach(l => l.style.display = 'none');
    }
});
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>


