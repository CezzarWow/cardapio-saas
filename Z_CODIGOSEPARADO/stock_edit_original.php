<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; justify-content: center; overflow-y: auto;">
        
        <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 1100px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: fit-content;">
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

                <!-- PREÇO E ESTOQUE -->
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Preço (R$)</label>
                        <input type="text" name="price" id="priceInput" required 
                               value="<?= number_format($product['price'], 2, ',', '') ?>" 
                               style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; font-weight: 600; text-align: right;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Estoque</label>
                        <input type="number" name="stock" value="<?= intval($product['stock']) ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                </div>

                <!-- FOTO E ÍCONE (TABELA COLAPSÁVEL) -->
                <?php 
                $icons = [
                    '🍔' => 'Hambúrguer',
                    '🌭' => 'Cachorro Quente',
                    '🍺' => 'Cerveja Garrafa',
                    '🍾' => 'Cerveja Longneck',
                    '🥫' => 'Latinha',
                    '🍻' => 'Chopp',
                    '🍕' => 'Pizza',
                    '🍢' => 'Petiscos',
                    '🥘' => 'Porções',
                    '🥤' => 'Refrigerante',
                    '🧃' => 'Sucos',
                    '🍬' => 'Doces e Balas',
                    '🍫' => 'Chocolate',
                    '🍟' => 'Batata Frita',
                    '🍱' => 'Combos',
                    '🍰' => 'Sobremesas',
                    '🍦' => 'Sorvete'
                ];

                $currentIcon = $product['icon'] ?? '🍔'; 
                // Valida se o ícone atual existe na lista, se não, usa padrão
                if (!array_key_exists($currentIcon, $icons)) {
                    $currentIcon = '🍔';
                }
                ?>
                <?php $hasImage = !empty($product['image']); $iconAsPhoto = ($product['icon_as_photo'] ?? 0) == 1; ?>
                <div style="display: flex; gap: 20px;">
                    <!-- FOTO -->
                    <div style="flex: 0.8;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Foto do Produto</label>
                        <?php if (!empty($product['image'])): ?>
                            <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                <img src="<?= BASE_URL ?>/uploads/<?= $product['image'] ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="color: #6b7280; font-size: 0.75rem;">Atual</span>
                                    <button type="button" onclick="openCropper('<?= BASE_URL ?>/uploads/<?= $product['image'] ?>')" 
                                            style="background: white; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.1s; font-size: 0.8rem; color: #374151;"
                                            onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'"
                                            title="Recortar novamente">
                                        <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                                        Editar
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" id="imageInput" accept="image/*" style="width: 100%; padding: 8px; border: 1px dashed #d1d5db; border-radius: 8px; background: #f9fafb; font-size: 0.8rem;">
                        <div id="iconAsPhotoOption" style="display: <?= $hasImage ? 'none' : 'block' ?>; margin-top: 8px; padding: 8px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" name="icon_as_photo" id="iconAsPhotoCheckbox" <?= $iconAsPhoto ? 'checked' : '' ?> style="width: 14px; height: 14px;">
                                <span style="font-weight: 600; color: #92400e; font-size: 0.75rem;">Usar ícone como foto</span>
                            </label>
                        </div>
                    </div>

                    <!-- ÍCONE (COLAPSÁVEL) -->
                    <div style="flex: 2;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">
                            Ícone do Produto <span style="color: #dc2626;">*</span>
                            <small style="font-weight: 400; color: #6b7280;">(Balcão PDV e Cardápio)</small>
                        </label>
                        <input type="hidden" name="icon" id="selectedIcon" value="<?= $currentIcon ?>" required>
                        
                        <!-- Container da "Tabela" de Abrir -->
                        <div class="icon-selector-container" style="border: 1px solid #e5e7eb; border-radius: 8px; background: white; position: relative;">
                            <div class="icon-selector-header" onclick="toggleIconGrid()" style="padding: 10px 15px; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <!-- Exibe o ícone atual ou padrão -->
                                    <span id="selectedIconDisplay" style="font-size: 1.5rem;"><?= $currentIcon ?></span>
                                    <span style="font-weight: 600; color: #374151; font-size: 0.95rem;">Selecionar Ícone</span>
                                </div>
                                <i data-lucide="chevron-down" size="20" style="color: #9ca3af; transition: transform 0.2s;" id="iconChevron"></i>
                            </div>
                            
                            <div id="iconGrid" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 50; max-height: 300px; overflow-y: auto; grid-template-columns: repeat(auto-fill, minmax(50px, 1fr)); gap: 8px; padding: 15px; background: white; border: 1px solid #e5e7eb; border-top: none; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                                <?php 
                                foreach ($icons as $emoji => $label): 
                                    $isSelected = ($emoji === $currentIcon);
                                ?>
                                    <div class="icon-option" data-icon="<?= $emoji ?>" onclick="selectIcon('<?= $emoji ?>')" 
                                         title="<?= $label ?>"
                                         style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 50px; background: <?= $isSelected ? '#eff6ff' : 'white' ?>; border: 2px solid <?= $isSelected ? '#2563eb' : '#e5e7eb' ?>; border-radius: 6px; cursor: pointer; transition: all 0.15s;">
                                        <span style="font-size: 1.5rem;"><?= $emoji ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DESCRIÇÃO (FINAL) -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Descrição</label>
                    <textarea name="description" rows="3" placeholder="Ingredientes, detalhes, etc." style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: sans-serif; resize: vertical;"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>



                <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                    <a href="<?= BASE_URL ?>/admin/loja/produtos" style="width: 150px; text-align: center; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; text-decoration: none; font-weight: 600; background: white; transition: background 0.15s;">Cancelar</a>
                    <button type="submit" class="btn-primary" style="width: 150px; padding: 12px; border-radius: 8px; font-weight: 600;">Salvar</button>
                </div>

            </form>
        </div>

    </div>
</main>

<!-- Script do Seletor de Ícone -->
<script>

// --- LÓGICA DO SELETOR DE ÍCONE ---
function toggleIconGrid() {
    const grid = document.getElementById('iconGrid');
    const chevron = document.getElementById('iconChevron');
    
    if (grid.style.display === 'none' || grid.style.display === '') {
        grid.style.display = 'grid';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        grid.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}

function selectIcon(iconName) {
    document.getElementById('selectedIcon').value = iconName;
    document.getElementById('selectedIconDisplay').textContent = iconName;
    
    // Remove seleção anterior visual
    document.querySelectorAll('.icon-option').forEach(opt => {
        opt.style.borderColor = '#e5e7eb';
        opt.style.background = 'white';
    });
    
    // Marca selecionado visual
    const selected = document.querySelector(`.icon-option[data-icon="${iconName}"]`);
    if (selected) {
        selected.style.borderColor = '#2563eb';
        selected.style.background = '#eff6ff';
    }
    
    // Fecha o grid após selecionar
    setTimeout(() => {
        toggleIconGrid();
    }, 150);
}

document.getElementById('imageInput')?.addEventListener('change', function() {
    // A lógica de change agora é controlada pelo script do Cropper abaixo
    // para evitar conflitos, mas a parte visual do ícone pode ser mantida lá dentro
});

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

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

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!-- Modal de Recorte -->
<div id="cropperModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; flex-direction: column;">
    <div style="background: white; padding: 20px; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; gap: 15px;">
        <h3 style="margin: 0; font-weight: 700; color: #1f2937;">Recortar Imagem</h3>
        
        <div style="width: 100%; height: 400px; background: #f3f4f6; overflow: hidden; border-radius: 8px;">
            <img id="imageToCrop" src="" style="max-width: 100%; display: block;">
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" id="cancelCrop" style="padding: 10px 20px; border: 1px solid #d1d5db; background: white; border-radius: 6px; font-weight: 600; cursor: pointer;">Cancelar</button>
            <button type="button" id="confirmCrop" style="padding: 10px 20px; border: none; background: #2563eb; color: white; border-radius: 6px; font-weight: 600; cursor: pointer;">Confirmar Recorte</button>
        </div>
    </div>
</div>

<script>
(function() {
    let cropper;
    const imageInput = document.getElementById('imageInput');
    const modal = document.getElementById('cropperModal');
    const image = document.getElementById('imageToCrop');
    const cancelBtn = document.getElementById('cancelCrop');
    const confirmBtn = document.getElementById('confirmCrop');
    
    // Variável para armazenar o arquivo original (para re-recorte)
    let originalFile = null;
    let isInternalChange = false;

    // Função para abrir o modal
    window.openCropper = function(source) {
        if (!source) return;

        // Se for File object ou Blob
        if (source instanceof Blob || source instanceof File) {
             const reader = new FileReader();
             reader.onload = function(evt) {
                 image.src = evt.target.result;
                 startCropper();
             };
             reader.readAsDataURL(source);
        } else if (typeof source === 'string') {
            // Se for URL
            image.src = source;
            // Hack para CORS se necessário, mas local geralmente ok
            image.crossOrigin = 'anonymous'; 
            startCropper();
        }
    };

    function startCropper() {
        modal.style.display = 'flex';
        // Destrói anterior se existir
        if (cropper) cropper.destroy();
        
        // Inicia Cropper
        cropper = new Cropper(image, {
            aspectRatio: 1, // Quadrado
            viewMode: 0, // 0 = Livre
            dragMode: 'move',
            autoCropArea: 0.8,
            guides: true,
            background: true,
            responsive: true
        });
    }

    imageInput.addEventListener('change', function(e) {
        if (isInternalChange) {
            isInternalChange = false;
            return;
        }

        const files = e.target.files;
        if (files && files.length > 0) {
            originalFile = files[0];
            
            // Valida se é imagem
            if (!originalFile.type.startsWith('image/')) return;

            openCropper(originalFile);
            
            // Limpa o input temporariamente para permitir cancelar
            this.value = '';
        }
    });

    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        if (cropper) cropper.destroy();
        // Se usuário cancelar, não faz nada com o input (se já tinha valor) ou deixa vazio se estava subindo novo
    });

    confirmBtn.addEventListener('click', function() {
        if (!cropper) return;

        // Obtém canvas cortado
        const canvas = cropper.getCroppedCanvas({
            width: 600,
            height: 600,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        canvas.toBlob(function(blob) {
            // Cria novo arquivo (PNG para preservar transparência)
            const file = new File([blob], "cropped_image.png", { type: "image/png" });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            
            isInternalChange = true;
            imageInput.files = dataTransfer.files;

            // Mostra preview ou esconde a opção de ícone
            const optionDiv = document.getElementById('iconAsPhotoOption');
            if (optionDiv) {
                optionDiv.style.display = 'none';
                document.getElementById('iconAsPhotoCheckbox').checked = false;
            }

            modal.style.display = 'none';
            if (cropper) cropper.destroy();

        }, 'image/png');
    });
})();
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>


