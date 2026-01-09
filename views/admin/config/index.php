<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; justify-content: center; overflow-y: auto;">
        
        <div style="background: white; padding: 2.5rem; border-radius: 16px; width: 100%; max-width: 700px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); height: fit-content;">
            
            <div style="margin-bottom: 2rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem;">
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #1f2937;">Identidade da Loja</h1>
                <p style="color: #6b7280;">Personalize como seus clientes veem seu restaurante</p>
            </div>

            <form action="<?= BASE_URL ?>/admin/loja/config/salvar" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
                <?= \App\Helpers\ViewHelper::csrfField() ?>
                
                <?php 
                $isEditing = isset($_GET['edit']) && $_GET['edit'] === 'true';
                $disabled = $isEditing ? '' : 'disabled';
                $inputBg = $isEditing ? 'white' : '#f3f4f6';
                ?>
                
                <div style="display: flex; gap: 20px; align-items: flex-start;">
                    <div style="width: 120px; text-align: center;">
                        <div class="logo-preview-container" style="width: 100px; height: 100px; border-radius: 50%; background: #f3f4f6; border: 2px dashed #d1d5db; display: flex; align-items: center; justify-content: center; overflow: hidden; margin: 0 auto 10px auto;">
                            <?php if(!empty($loja['logo'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= $loja['logo'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i data-lucide="store" color="#9ca3af" size="40"></i>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($isEditing): ?>
                            <label for="logo-upload" style="cursor: pointer; font-size: 0.8rem; color: #2563eb; font-weight: 600;">Alterar Logo</label>
                            <input id="logo-upload" type="file" name="logo" accept="image/*" style="display: none;">
                        <?php endif; ?>
                    </div>

                    <div style="flex: 1; display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label style="font-weight: 600; font-size: 0.9rem; color: #374151;">Nome do Restaurante</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($loja['name']) ?>" required <?= $disabled ?>
                                   style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-top: 5px; background: <?= $inputBg ?>;">
                        </div>
                        
                        <div>
                            <label style="font-weight: 600; font-size: 0.9rem; color: #374151;">Cor do Sistema</label>
                            <div style="display: flex; gap: 10px; margin-top: 5px;">
                                <input type="color" name="primary_color" value="<?= $loja['primary_color'] ?? '#2563eb' ?>" <?= $disabled ?> style="height: 40px; width: 60px; border: none; cursor: pointer; border-radius: 6px;">
                                <input type="text" value="<?= $loja['primary_color'] ?? '#2563eb' ?>" readonly style="flex: 1; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; color: #6b7280;">
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid #f3f4f6;">

                <!-- Linha 1: Telefone e CEP -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="font-weight: 600; font-size: 0.9rem; color: #374151;">Telefone / WhatsApp</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($loja['phone'] ?? '') ?>" placeholder="(00) 00000-0000" <?= $disabled ?>
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-top: 5px; background: <?= $inputBg ?>;">
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 0.9rem; color: #374151;">CEP</label>
                        <input type="text" name="zip_code" value="<?= htmlspecialchars($loja['zip_code'] ?? '') ?>" placeholder="00000-000" <?= $disabled ?>
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-top: 5px; background: <?= $inputBg ?>;">
                    </div>
                </div>

                <!-- Linha 2: Endereço e Número -->
                <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 15px;">
                    <div>
                        <label style="font-weight: 600; font-size: 0.9rem; color: #374151;">Endereço</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($loja['address'] ?? '') ?>" placeholder="Rua, Bairro..." <?= $disabled ?>
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-top: 5px; background: <?= $inputBg ?>;">
                    </div>
                     <div>
                        <label style="font-weight: 600; font-size: 0.9rem; color: #374151;">Número</label>
                        <input type="text" name="address_number" value="<?= htmlspecialchars($loja['address_number'] ?? '') ?>" placeholder="Nº" <?= $disabled ?>
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-top: 5px; background: <?= $inputBg ?>;">
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <?php if ($isEditing): ?>
                        <a href="<?= BASE_URL ?>/admin/loja/config" style="flex: 1; padding: 15px; text-align: center; border: 1px solid #d1d5db; border-radius: 12px; font-weight: 700; color: #374151; text-decoration: none; background: white; transition: all 0.2s;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary" style="flex: 2; padding: 15px; font-size: 1rem; border-radius: 12px; border: none; background: #2563eb; color: white; font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4);">
                            Salvar Alterações
                        </button>
                    <?php else: ?>
                        <a href="?edit=true" style="width: 100%; padding: 15px; font-size: 1rem; border-radius: 12px; border: 2px solid #2563eb; background: #eff6ff; color: #2563eb; font-weight: 700; cursor: pointer; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s;">
                            <i data-lucide="edit-3" style="width: 18px;"></i> Editar Informações
                        </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>
</main>
<script>
    // Preview da Logo antes de salvar
    document.getElementById('logo-upload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.querySelector('.logo-preview-container');
                // Limpa o ícone antigo se existir e coloca a imagem
                imgContainer.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
