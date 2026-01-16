<?php
/**
 * ============================================
 * COMPONENTE: Mensagens de Feedback
 * Arquivo: views/admin/panel/layout/messages.php
 * 
 * Como usar:
 * 1. Incluir após o header nas views:
 *    <?php require __DIR__ . '/layout/messages.php'; ?>
 * 
 * 2. Redirecionar com parâmetros:
 *    header('Location: ../produtos?success=salvo');
 *    header('Location: ../produtos?error=' . urlencode('Mensagem de erro'));
 * ============================================
 */

// Mapeamento de Mensagens
$msgMap = [
    'success' => [
        '1' => 'Operação realizada com sucesso!',
        'salvo' => 'Configurações salvas com sucesso!',
        'criado' => 'Registro criado com sucesso!',
        'atualizado' => 'Registro atualizado com sucesso!',
        'deletado' => 'Registro removido com sucesso!',
        'aberto' => 'Caixa aberto com sucesso!',
        'fechado' => 'Caixa fechado com sucesso!',
        'combo_criado' => '🎉 Combo criado com sucesso!',
        'combo_atualizado' => '✓ Combo atualizado com sucesso!',
        'combo_deletado' => 'Combo removido com sucesso!',
    ],
    'error' => [
        'combo_nao_encontrado' => 'Combo não encontrado.',
        'falha_salvar' => 'Falha ao salvar dados.',
    ]
];

function renderToast($type, $icon, $bg, $border, $text, $msgMap) {
    if (!isset($_GET[$type])) return;
    
    $code = $_GET[$type];
    $message = $msgMap[$type][$code] ?? htmlspecialchars(urldecode($code));
    
    // Fallback para sucesso genérico
    if ($type === 'success' && !isset($msgMap['success'][$code])) {
        $message = 'Operação realizada com sucesso!';
    }

    echo "
    <div class='admin-toast admin-toast-{$type}' style='
        background: {$bg}; border: 1px solid {$border}; color: {$text};
        padding: 12px 20px; border-radius: 8px; margin: 0 20px 20px;
        font-weight: 600; display: flex; align-items: center; gap: 10px;
        animation: slideIn 0.3s ease-out;'>
        <span style='width: 24px; height: 24px; background: {$text}; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;'>{$icon}</span>
        <span>{$message}</span>
        <button onclick='this.parentElement.remove()' style='margin-left: auto; background: none; border: none; cursor: pointer; font-size: 18px; color: {$text}; opacity: 0.6;'>×</button>
    </div>";
}

renderToast('success', '✓', '#dcfce7', '#86efac', '#166534', $msgMap);
renderToast('error', '✕', '#fee2e2', '#fca5a5', '#991b1b', $msgMap);
renderToast('warning', '!', '#fef3c7', '#fcd34d', '#92400e', $msgMap);
?>

<style>
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; transform: translateY(-10px); }
}

.admin-toast {
    animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-out 4.7s forwards;
}
</style>

<script>
// [ETAPA 4] Auto-hide toasts após 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.admin-toast');
    toasts.forEach(function(toast) {
        if (!toast) return;
        setTimeout(function() {
            if (toast && toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    });
});
</script>

