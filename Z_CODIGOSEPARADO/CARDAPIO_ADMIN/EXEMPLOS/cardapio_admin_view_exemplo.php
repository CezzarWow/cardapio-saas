<?php 
/**
 * =====================================================
 * VIEW: Admin do Cardápio - Configurações
 * Arquivo: views/admin/cardapio/index.php
 * 
 * EXEMPLO DE IMPLEMENTAÇÃO
 * =====================================================
 */

// Inclui layout padrão admin
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <section class="catalog-section">
        
        <header class="top-header">
            <div class="page-title">
                <h1>Configurações do Cardápio</h1>
                <p>Personalize seu cardápio web</p>
            </div>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: #dcfce7; border: 1px solid #86efac; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; color: #166534; font-weight: 600;">
                ✓ Configurações salvas com sucesso!
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/admin/loja/cardapio/salvar" style="max-width: 800px;">
            
            <!-- SEÇÃO: IDENTIDADE VISUAL -->
            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #1e293b; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">
                    🎨 Identidade Visual
                </h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 5px;">Cor Primária</label>
                        <input type="color" name="primary_color" value="<?= htmlspecialchars($config['primary_color'] ?? '#2563eb') ?>" 
                               style="width: 100%; height: 50px; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 5px;">Cor Secundária</label>
                        <input type="color" name="secondary_color" value="<?= htmlspecialchars($config['secondary_color'] ?? '#f59e0b') ?>" 
                               style="width: 100%; height: 50px; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer;">
                    </div>
                </div>
            </div>

            <!-- SEÇÃO: HORÁRIO DE FUNCIONAMENTO -->
            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #1e293b; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">
                    🕐 Horário de Funcionamento
                </h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: end;">
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 5px;">Abre às</label>
                        <input type="time" name="opening_time" value="<?= htmlspecialchars($config['opening_time'] ?? '08:00') ?>" 
                               style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 5px;">Fecha às</label>
                        <input type="time" name="closing_time" value="<?= htmlspecialchars($config['closing_time'] ?? '22:00') ?>" 
                               style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_open" value="1" <?= ($config['is_open'] ?? 1) ? 'checked' : '' ?>
                                   style="width: 20px; height: 20px;">
                            <span style="font-weight: 600; color: #16a34a;">Aberto Agora</span>
                        </label>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 5px;">Mensagem quando fechado</label>
                    <input type="text" name="closed_message" value="<?= htmlspecialchars($config['closed_message'] ?? 'Estamos fechados no momento') ?>" 
                           placeholder="Ex: Voltamos amanhã às 8h!" maxlength="255"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                </div>
            </div>

            <!-- SEÇÃO: DELIVERY -->
            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #1e293b; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">
                    🚗 Delivery
                </h2>
                
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 20px;">
                    <input type="checkbox" name="delivery_enabled" value="1" <?= ($config['delivery_enabled'] ?? 1) ? 'checked' : '' ?>
                           style="width: 20px; height: 20px;">
                    <span style="font-weight: 700; color: #1e293b;">Habilitar Delivery</span>
                </label>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Taxa de Entrega (R$)</label>
                        <input type="text" name="delivery_fee" value="<?= number_format($config['delivery_fee'] ?? 5, 2, ',', '.') ?>" 
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Pedido Mínimo (R$)</label>
                        <input type="text" name="min_order_value" value="<?= number_format($config['min_order_value'] ?? 20, 2, ',', '.') ?>" 
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Tempo Min (min)</label>
                        <input type="number" name="delivery_time_min" value="<?= $config['delivery_time_min'] ?? 30 ?>" 
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Tempo Máx (min)</label>
                        <input type="number" name="delivery_time_max" value="<?= $config['delivery_time_max'] ?? 45 ?>" 
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                </div>
            </div>

            <!-- SEÇÃO: WHATSAPP -->
            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #1e293b; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">
                    📱 WhatsApp
                </h2>
                
                <div>
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 5px;">Número do WhatsApp</label>
                    <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($config['whatsapp_number'] ?? '') ?>" 
                           placeholder="Ex: 5511999999999 (com DDD e 55)" maxlength="20"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <p style="font-size: 0.8rem; color: #64748b; margin-top: 5px;">Formato: 55 + DDD + Número (ex: 5511999999999)</p>
                </div>
            </div>

            <!-- SEÇÃO: PAGAMENTOS -->
            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #1e293b; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">
                    💳 Formas de Pagamento
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <input type="checkbox" name="accept_cash" value="1" <?= ($config['accept_cash'] ?? 1) ? 'checked' : '' ?>>
                        <span>💵 Dinheiro</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <input type="checkbox" name="accept_credit" value="1" <?= ($config['accept_credit'] ?? 1) ? 'checked' : '' ?>>
                        <span>💳 Crédito</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <input type="checkbox" name="accept_debit" value="1" <?= ($config['accept_debit'] ?? 1) ? 'checked' : '' ?>>
                        <span>💳 Débito</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <input type="checkbox" name="accept_pix" value="1" <?= ($config['accept_pix'] ?? 1) ? 'checked' : '' ?>>
                        <span>💠 PIX</span>
                    </label>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Chave PIX</label>
                        <input type="text" name="pix_key" value="<?= htmlspecialchars($config['pix_key'] ?? '') ?>" 
                               placeholder="Sua chave PIX"
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Tipo da Chave</label>
                        <select name="pix_key_type" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                            <option value="telefone" <?= ($config['pix_key_type'] ?? '') == 'telefone' ? 'selected' : '' ?>>Telefone</option>
                            <option value="cpf" <?= ($config['pix_key_type'] ?? '') == 'cpf' ? 'selected' : '' ?>>CPF</option>
                            <option value="cnpj" <?= ($config['pix_key_type'] ?? '') == 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
                            <option value="email" <?= ($config['pix_key_type'] ?? '') == 'email' ? 'selected' : '' ?>>E-mail</option>
                            <option value="aleatoria" <?= ($config['pix_key_type'] ?? '') == 'aleatoria' ? 'selected' : '' ?>>Aleatória</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- BOTÃO SALVAR -->
            <button type="submit" style="width: 100%; padding: 16px; background: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 1.1rem; cursor: pointer;">
                Salvar Configurações
            </button>
            
        </form>

    </section>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
