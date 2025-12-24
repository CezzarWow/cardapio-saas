<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$isEdit = isset($item) && $item;
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; max-width: 600px;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <a href="<?= BASE_URL ?>/admin/loja/adicionais/itens" style="color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 5px; margin-bottom: 10px;">
                <i data-lucide="arrow-left" size="16"></i> Voltar para Catálogo
            </a>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">
                <?= $isEdit ? 'Editar Item' : 'Novo Item' ?>
            </h1>
        </div>

        <!-- Form -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <form action="<?= BASE_URL ?>/admin/loja/adicionais/item/<?= $isEdit ? 'atualizar' : 'salvar' ?>" method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <?php endif; ?>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome do Item</label>
                    <input type="text" name="name" placeholder="Ex: Bacon, Queijo Extra, Maionese..." required 
                           value="<?= $isEdit ? htmlspecialchars($item['name']) : '' ?>"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Preço Adicional (R$)</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="price" id="priceInput" placeholder="0,00" 
                               value="<?= $isEdit ? number_format($item['price'], 2, ',', '') : '0' ?>"
                               style="flex: 1; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                        <button type="button" id="btnGratis" onclick="setGratis()" 
                                style="padding: 12px 20px; background: #f3f4f6; color: #6b7280; border: 2px solid #d1d5db; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                            🎁 Grátis
                        </button>
                    </div>
                    <small style="color: #6b7280; margin-top: 5px; display: block;">Clique em "Grátis" para zerar o preço</small>
                </div>

                <script>
                (function() {
                    const priceInput = document.getElementById('priceInput');
                    const btn = document.getElementById('btnGratis');
                    if (!priceInput) return;
                    
                    // Converte valor inicial para centavos
                    let initialValue = priceInput.value.replace(/\D/g, '');
                    let cents = parseInt(initialValue) || 0;
                    
                    function formatCents(c) {
                        const str = String(c).padStart(3, '0');
                        const integer = str.slice(0, -2) || '0';
                        const decimal = str.slice(-2);
                        return integer + ',' + decimal;
                    }
                    
                    // Exibe valor formatado
                    priceInput.value = formatCents(cents);
                    priceInput.style.textAlign = 'right';
                    priceInput.style.fontWeight = '600';
                    
                    // Função grátis global
                    window.setGratis = function() {
                        cents = 0;
                        priceInput.value = '0,00';
                        
                        btn.style.background = '#d1fae5';
                        btn.style.color = '#059669';
                        btn.style.borderColor = '#10b981';
                        
                        setTimeout(() => {
                            btn.style.background = '#f3f4f6';
                            btn.style.color = '#6b7280';
                            btn.style.borderColor = '#d1d5db';
                        }, 1500);
                    };
                    
                    priceInput.addEventListener('focus', function() {
                        this.select();
                    });
                    
                    priceInput.addEventListener('click', function() {
                        this.select();
                    });
                    
                    priceInput.addEventListener('keydown', function(e) {
                        if ([8, 46, 9, 13, 27].includes(e.keyCode)) {
                            if (e.keyCode === 8 || e.keyCode === 46) {
                                e.preventDefault();
                                cents = Math.floor(cents / 10);
                                this.value = formatCents(cents);
                            }
                            return;
                        }
                        
                        if (e.key < '0' || e.key > '9') {
                            e.preventDefault();
                            return;
                        }
                        
                        e.preventDefault();
                        
                        if (cents > 999999) return;
                        
                        cents = cents * 10 + parseInt(e.key);
                        this.value = formatCents(cents);
                    });
                    
                    priceInput.addEventListener('input', function() {
                        const len = this.value.length;
                        this.setSelectionRange(len, len);
                    });
                })();
                </script>

                <div style="display: flex; gap: 10px;">
                    <a href="<?= BASE_URL ?>/admin/loja/adicionais/itens" 
                       style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600; text-align: center;">
                        Cancelar
                    </a>
                    <button type="submit" style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <?= $isEdit ? 'Salvar Alterações' : 'Criar Item' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
