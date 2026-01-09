<?php
namespace App\Controllers\Admin;

use App\Services\Pdv\PdvService;

/**
 * PdvController - Painel de Vendas (Super Thin)
 * Responsável pelo Frente de Caixa
 */
class PdvController extends BaseController {

    private PdvService $service;

    public function __construct(PdvService $service) {
        $this->service = $service;
    }

    public function index() {
        $rid = $this->getRestaurantId();
        
        // Contexto (Mesa ou Comanda)
        $mesaId = $this->getInt('mesa_id'); 
        $mesaNumero = $_GET['mesa_numero'] ?? null;
        $orderId = $this->getInt('order_id');

        // Busca dados do contexto
        $context = $this->service->getContextData($rid, $mesaId > 0 ? $mesaId : null, $orderId > 0 ? $orderId : null);
        
        $contaAberta = $context['contaAberta'];
        $itensJaPedidos = $context['itensJaPedidos'];
        
        // Variáveis para View (snake_case)
        $mesa_id = $mesaId;
        $mesa_numero = $mesaNumero;

        // --- Lógica movida da View (dashboard.php) ---
        
        // 1. Detecta modo edição (Pedido Pago/Retirada)
        $isEditingPaid = isset($_GET['edit_paid']) && $_GET['edit_paid'] == '1';
        $editingOrderId = $orderId;

        $originalPaidTotalFromDB = 0;
        if ($isEditingPaid && $editingOrderId) {
            $conn = \App\Core\Database::connect();
            $stmt = $conn->prepare("SELECT total FROM orders WHERE id = :oid");
            $stmt->execute(['oid' => $editingOrderId]);
            $orderData = $stmt->fetch(\PDO::FETCH_ASSOC);
            $originalPaidTotalFromDB = floatval($orderData['total'] ?? 0);
        }

        // 2. Carrega Configurações (Taxa de Entrega)
        $deliveryFee = 5.0;
        $settingsPath = __DIR__ . '/../../../data/restaurants/' . $rid . '/cardapio_settings.json';
        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true);
            $deliveryFee = floatval($settings['delivery_fee'] ?? 5.0);
        }

        // 3. Lógica de Exibição de Botões (TableViewFlags)
        $showQuickSale = false;   // Botão "Finalizar" (Venda Rápida)
        $showCloseTable = false;  // Botão "Finalizar Mesa"
        $showCloseCommand = false;// Botão "Entregar/Finalizar" (Comanda)
        $showSaveCommand = false; // Botão "Salvar" (Comanda)
        $showIncludePaid = false; // Botão "Incluir" (Edição Pago)

        if ($mesa_id) {
            // Contexto: MESA
            $showCloseTable = true; 
        } elseif (!empty($contaAberta['id'])) {
            // Contexto: COMANDA EXISTENTE
            if ($isEditingPaid) {
                $showIncludePaid = true;
            } else {
                $showCloseCommand = true;
                $showSaveCommand = true;
            }
        } else {
            // Contexto: BALCÃO LIVRE (Nova Venda)
            // Se não tem mesa e não tem comanda aberta, é venda rápida
            $showQuickSale = true;
            $showSaveCommand = true; // Habilita botão salvar (oculto por padrão até selecionar cliente)
        }

        // Carrega Cardápio
        $categories = $this->service->getMenu($rid);

        // Renderiza
        require __DIR__ . '/../../../views/admin/panel/dashboard.php';
    }

    /**
     * Cancela a edição e restaura o backup do pedido
     */
    public function cancelEdit() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['edit_backup'])) {
            header('Location: ../../admin/loja/pdv');
            exit;
        }

        try {
            $this->service->restoreOrder($_SESSION['edit_backup']);

            unset($_SESSION['edit_backup']);
            unset($_SESSION['cart_recovery']);

            header('Location: ../../admin/loja/caixa');
            exit;

        } catch (\Exception $e) {
            die("Erro crítico ao cancelar edição: " . $e->getMessage());
        }
    }
}
