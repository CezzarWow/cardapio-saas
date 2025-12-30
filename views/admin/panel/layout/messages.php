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

// Mensagem de Sucesso
if (isset($_GET['success'])): ?>
    <div style="
        background: #dcfce7; 
        border: 1px solid #86efac; 
        padding: 12px 20px; 
        border-radius: 8px; 
        margin: 0 20px 20px; 
        color: #166534; 
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease-out;
    ">
        <span style="
            width: 24px; 
            height: 24px; 
            background: #16a34a; 
            color: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 14px;
        ">✓</span>
        <span>
        <?php
        // Mapeamento de códigos para mensagens amigáveis
        $successMessages = [
            '1' => 'Operação realizada com sucesso!',
            'salvo' => 'Dados salvos com sucesso!',
            'criado' => 'Registro criado com sucesso!',
            'atualizado' => 'Registro atualizado com sucesso!',
            'deletado' => 'Registro removido com sucesso!',
            'aberto' => 'Caixa aberto com sucesso!',
            'fechado' => 'Caixa fechado com sucesso!',
        ];
        echo $successMessages[$_GET['success']] ?? 'Operação realizada com sucesso!';
        ?>
        </span>
    </div>
<?php endif; ?>

<?php // Mensagem de Erro
if (isset($_GET['error'])): ?>
    <div style="
        background: #fee2e2; 
        border: 1px solid #fca5a5; 
        padding: 12px 20px; 
        border-radius: 8px; 
        margin: 0 20px 20px; 
        color: #991b1b; 
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease-out;
    ">
        <span style="
            width: 24px; 
            height: 24px; 
            background: #dc2626; 
            color: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 14px;
        ">✕</span>
        <span><?= htmlspecialchars(urldecode($_GET['error'])) ?></span>
    </div>
<?php endif; ?>

<?php // Mensagem de Aviso
if (isset($_GET['warning'])): ?>
    <div style="
        background: #fef3c7; 
        border: 1px solid #fcd34d; 
        padding: 12px 20px; 
        border-radius: 8px; 
        margin: 0 20px 20px; 
        color: #92400e; 
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease-out;
    ">
        <span style="
            width: 24px; 
            height: 24px; 
            background: #f59e0b; 
            color: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 14px;
        ">!</span>
        <span><?= htmlspecialchars(urldecode($_GET['warning'])) ?></span>
    </div>
<?php endif; ?>

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
</style>
