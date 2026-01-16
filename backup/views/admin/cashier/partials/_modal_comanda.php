<!-- Modal Comanda (Estilo Cupom Fiscal) -->
<div id="orderDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center;">
    <div style="background: #fff; padding: 0; border-radius: 5px; width: 320px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); font-family: 'Courier New', Courier, monospace; border: 1px solid #e5e7eb;">
        
        <!-- Cabeçalho do Cupom -->
        <div style="background: #fef3c7; padding: 15px; text-align: center; border-bottom: 2px dashed #d1d5db; border-radius: 5px 5px 0 0;">
            <h3 style="font-weight: 800; font-size: 1.1rem; color: #000; margin: 0; text-transform: uppercase;">Comprovante</h3>
            <div id="receiptDate" style="font-size: 0.75rem; color: #4b5563; margin-top: 5px;"></div>
        </div>
        
        <!-- Lista de Itens -->
        <div id="modalItemsList" style="padding: 15px; max-height: 400px; overflow-y: auto; background: #fffbeeb0;">
            Carregando...
        </div>

        <!-- Total e Rodapé -->
        <div style="padding: 15px; background: #fff; border-top: 2px dashed #d1d5db; border-radius: 0 0 5px 5px;">
            <div style="display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: 1.1rem;">
                <span>TOTAL</span>
                <span id="receiptTotal">R$ 0,00</span>
            </div>
            
            <button onclick="document.getElementById('orderDetailsModal').style.display='none'" 
                    style="width: 100%; margin-top: 15px; background: #000; color: #fff; border: none; padding: 10px; font-family: sans-serif; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-size: 0.8rem;">
                Fechar
            </button>
        </div>
    </div>
</div>
