<?php
/**
 * ============================================
 * PARTIAL: Aba Operação
 * ============================================
 */
?>

<!-- Card Status -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="power"></i>
        <h3 class="cardapio-admin-card-title">Status da Loja</h3>
    </div>

    <div class="cardapio-admin-toggle-row">
        <span class="cardapio-admin-toggle-label">Loja Aberta</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="is_open" id="is_open" value="1"
                   <?= ($config['is_open'] ?? 1) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <div class="cardapio-admin-form-group" style="margin-top: 1rem;">
        <label class="cardapio-admin-label" for="closed_message">Mensagem quando fechado</label>
        <input type="text" 
               class="cardapio-admin-input" 
               id="closed_message" 
               name="closed_message" 
               placeholder="Estamos fechados no momento"
               value="<?= htmlspecialchars($config['closed_message'] ?? 'Estamos fechados no momento') ?>">
    </div>
</div>

<!-- Card Horário -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="clock"></i>
        <h3 class="cardapio-admin-card-title">Horário de Funcionamento</h3>
    </div>

    <div class="cardapio-admin-grid cardapio-admin-grid-2">
        <div class="cardapio-admin-form-group">
            <label class="cardapio-admin-label" for="opening_time">Abre às</label>
            <input type="time" 
                   class="cardapio-admin-input" 
                   id="opening_time" 
                   name="opening_time" 
                   value="<?= htmlspecialchars($config['opening_time'] ?? '08:00') ?>">
        </div>

        <div class="cardapio-admin-form-group">
            <label class="cardapio-admin-label" for="closing_time">Fecha às</label>
            <input type="time" 
                   class="cardapio-admin-input" 
                   id="closing_time" 
                   name="closing_time" 
                   value="<?= htmlspecialchars($config['closing_time'] ?? '22:00') ?>">
        </div>
    </div>

    <p class="cardapio-admin-hint">
        <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
        Horários por dia da semana serão implementados em breve.
    </p>
</div>
