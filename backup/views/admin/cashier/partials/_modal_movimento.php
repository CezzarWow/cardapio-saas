<!-- Modal Movimentação (Suprimento/Sangria) -->
<div id="modalMovimento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 400px; max-width: 90%;">
        <h3 id="modalTitle" style="font-weight: 800; font-size: 1.2rem; margin-bottom: 15px;">Nova Movimentação</h3>
        
        <form action="caixa/movimentar" method="POST">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <input type="hidden" name="type" id="movType">
            
            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Valor (R$)</label>
            <input type="text" name="amount" required placeholder="0,00" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px;">

            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Motivo / Descrição</label>
            <input type="text" name="description" required placeholder="Ex: Pagamento Fornecedor" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalMovimento').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid #ddd; background: white; border-radius: 8px; cursor: pointer;">Cancelar</button>
                <button type="submit" class="btn-primary" style="flex: 1; padding: 10px; border: none; border-radius: 8px; cursor: pointer;">Salvar</button>
            </div>
        </form>
    </div>
</div>
