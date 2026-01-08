<?php
/**
 * PARTIAL: Lista de Combos Ativos
 * Extraído de _tab_promocoes.php
 */
?>

<!-- Lista de Combos Ativos (Histórico) -->
<div class="cardapio-admin-card" style="margin-top: 40px;">
    <div class="cardapio-admin-card-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i data-lucide="list"></i>
            <h3 class="cardapio-admin-card-title">Promoções Ativas</h3>
        </div>
    </div>
    
    <div id="param-list-combos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php if (!empty($combos)): ?>
            <?php foreach ($combos as $combo): ?>
                <div class="cardapio-admin-combo-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    
                    <!-- Cabeçalho: Nome, Tag e Toggle -->
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h4 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0;"><?= htmlspecialchars($combo['name']) ?></h4>
                            <?php if (!empty($combo['discount_percent']) && $combo['discount_percent'] > 0): ?>
                                <span style="background: #fff7ed; color: #ea580c; font-size: 0.8rem; font-weight: 600; padding: 2px 8px; border-radius: 999px;">
                                    -<?= $combo['discount_percent'] ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Toggle Ativo/Inativo -->
                         <label class="cardapio-admin-toggle" title="<?= $combo['is_active'] ? 'Desativar' : 'Ativar' ?>">
                            <input type="checkbox" onchange="toggleComboActive(<?= $combo['id'] ?>, this.checked)" <?= $combo['is_active'] ? 'checked' : '' ?>>
                            <span class="cardapio-admin-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Descrição dos Itens -->
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5;">
                        <?= htmlspecialchars($combo['items_description'] ?? $combo['description']) ?>
                    </p>

                    <!-- Divisor -->
                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin-bottom: 15px;">

                    <!-- Rodapé: Preços e Ações -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        
                        <!-- Preços -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.2rem; font-weight: 700; color: #ea580c;">
                                R$ <?= number_format($combo['price'], 2, ',', '.') ?>
                            </span>
                            <?php if (!empty($combo['original_price']) && $combo['original_price'] > $combo['price']): ?>
                                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.9rem;">
                                    R$ <?= number_format($combo['original_price'], 2, ',', '.') ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Data e Botões -->
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="display: flex; align-items: center; gap: 4px; color: #16a34a; font-size: 0.85rem;" title="Validade">
                                <?php 
                                    if (empty($combo['valid_until'])) {
                                        echo '<i data-lucide="infinity" style="width: 14px; height: 14px;"></i> <span>Ativo</span>';
                                    } elseif ($combo['valid_until'] == date('Y-m-d')) {
                                        echo '<i data-lucide="clock" style="width: 14px; height: 14px;"></i> <span>Hoje</span>';
                                    } else {
                                        echo '<i data-lucide="calendar" style="width: 14px; height: 14px;"></i> <span>' . date('d/m/y', strtotime($combo['valid_until'])) . '</span>';
                                    }
                                ?>
                            </div>

                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="cardapio-admin-btn-icon" style="color: #475569; padding: 4px; background: transparent; border: none; cursor: pointer;" 
                                        onclick="CardapioAdmin.loadComboForEdit(<?= $combo['id'] ?>)" title="Editar">
                                    <i data-lucide="pencil" size="18"></i>
                                </button>
                                <button type="button" class="cardapio-admin-btn-icon" style="color: #ef4444; padding: 4px; background: transparent; border: none; cursor: pointer;" 
                                        onclick="if(confirm('Excluir este combo?')) location.href='<?= BASE_URL ?>/admin/loja/cardapio/combo/deletar?id=<?= $combo['id'] ?>'" title="Excluir">
                                    <i data-lucide="trash-2" size="18"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="padding: 20px; text-align: center; color: #94a3b8;">Nenhuma promoção ativa no momento.</p>
        <?php endif; ?>
    </div>
</div>
