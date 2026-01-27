<?php
/**
 * ============================================
 * PARTIAL: Aba Pagamentos
 * ============================================
 */
?>

<!-- Card Formas de Pagamento -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="wallet"></i>
        <h3 class="cardapio-admin-card-title">Formas de Pagamento</h3>
    </div>

    <div class="cardapio-admin-toggle-row">
        <span class="cardapio-admin-toggle-label">💵 Dinheiro</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="accept_cash" id="accept_cash" value="1"
                   <?= ((int) ($config['accept_cash'] ?? 1)) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <div class="cardapio-admin-toggle-row">
        <span class="cardapio-admin-toggle-label">💳 Cartão de Crédito</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="accept_credit" id="accept_credit" value="1"
                   <?= ((int) ($config['accept_credit'] ?? 1)) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <div class="cardapio-admin-toggle-row">
        <span class="cardapio-admin-toggle-label">💳 Cartão de Débito</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="accept_debit" id="accept_debit" value="1"
                   <?= ((int) ($config['accept_debit'] ?? 1)) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <div class="cardapio-admin-toggle-row" style="border-bottom: none;">
        <span class="cardapio-admin-toggle-label">💠 PIX</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="accept_pix" id="accept_pix" value="1"
                   <?= ((int) ($config['accept_pix'] ?? 1)) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>
</div>

<!-- Card Chave PIX -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="key"></i>
        <h3 class="cardapio-admin-card-title">Configuração do PIX</h3>
    </div>

    <div id="pix-fields" style="<?= ((int) ($config['accept_pix'] ?? 1)) ? '' : 'display: none;' ?>">
        <div class="cardapio-admin-grid cardapio-admin-grid-2">
            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label" for="pix_key">Chave PIX</label>
                <input type="text" 
                       class="cardapio-admin-input" 
                       id="pix_key" 
                       name="pix_key" 
                       placeholder="Sua chave PIX"
                       value="<?= htmlspecialchars($config['pix_key'] ?? '') ?>">
            </div>

            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label" for="pix_key_type">Tipo da Chave</label>
                <?php $pixType = (string) ($config['pix_key_type'] ?? ''); ?>
                <select class="cardapio-admin-input" id="pix_key_type" name="pix_key_type">
                    <option value="telefone" <?= \App\Helpers\ViewHelper::e($pixType === 'telefone' ? 'selected' : '') ?>>Telefone</option>
                    <option value="cpf" <?= \App\Helpers\ViewHelper::e($pixType === 'cpf' ? 'selected' : '') ?>>CPF</option>
                    <option value="cnpj" <?= \App\Helpers\ViewHelper::e($pixType === 'cnpj' ? 'selected' : '') ?>>CNPJ</option>
                    <option value="email" <?= \App\Helpers\ViewHelper::e($pixType === 'email' ? 'selected' : '') ?>>E-mail</option>
                    <option value="aleatoria" <?= \App\Helpers\ViewHelper::e($pixType === 'aleatoria' ? 'selected' : '') ?>>Aleatória</option>
                </select>
            </div>
        </div>
    </div>

    <div id="pix-disabled-msg" style="<?= ((int) ($config['accept_pix'] ?? 1)) ? 'display: none;' : '' ?>; color: #6b7280; font-size: 0.9rem;">
        <i data-lucide="info" style="width: 16px; height: 16px; display: inline;"></i>
        Habilite o PIX acima para configurar a chave.
    </div>
</div>
