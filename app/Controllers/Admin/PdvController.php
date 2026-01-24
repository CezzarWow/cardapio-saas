<?php

namespace App\Controllers\Admin;

use App\Services\Pdv\PdvService;
use App\Services\RestaurantService;
use App\Repositories\TableRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Cardapio\CardapioConfigRepository;
use App\Core\View;

/**
 * PdvController - Painel de Vendas
 * Responsável pelo Frente de Caixa
 */
class PdvController extends BaseController
{
    private PdvService $service;
    private TableRepository $tableRepo;
    private RestaurantService $restaurantService;
    private OrderRepository $orderRepo;
    private CardapioConfigRepository $configRepo;

    public function __construct(
        PdvService $service, 
        TableRepository $tableRepo,
        RestaurantService $restaurantService,
        OrderRepository $orderRepo,
        CardapioConfigRepository $configRepo
    ) {
        $this->service = $service;
        $this->tableRepo = $tableRepo;
        $this->restaurantService = $restaurantService;
        $this->orderRepo = $orderRepo;
        $this->configRepo = $configRepo;
    }

    public function index()
    {
        $rid = $this->getRestaurantId();

        // Contexto (Mesa ou Comanda)
        $mesaId = $this->getInt('mesa_id');
        $mesaNumero = $_GET['mesa_numero'] ?? null;
        $orderId = $this->getInt('order_id');

        // Se acessa via order_id, buscar a mesa vinculada (se houver)
        if ($orderId > 0 && $mesaId <= 0) {
            $linkedTable = $this->tableRepo->findByOrderId($orderId);
            if ($linkedTable) {
                $mesaId = (int) $linkedTable['id'];
                $mesaNumero = $linkedTable['number'];
            }
        }

        // Se tem mesa_id mas não tem mesa_numero, buscar o número
        if ($mesaId > 0 && !$mesaNumero) {
            $tableData = $this->tableRepo->findById($mesaId);
            if ($tableData) {
                $mesaNumero = $tableData['number'];
            }
        }

        // Busca dados do contexto
        $context = $this->service->getContextData($rid, $mesaId > 0 ? $mesaId : null, $orderId > 0 ? $orderId : null);

        $contaAberta = $context['contaAberta'];
        $itensJaPedidos = $context['itensJaPedidos'];

        // Variáveis para View (snake_case)
        $mesa_id = $mesaId;
        $mesa_numero = $mesaNumero;

        // 1. Detecta modo edição (Pedido Pago/Retirada)
        // Nota: Acesso direto a GET mantido pois é parâmetro de rota/query
        $isEditingPaid = isset($_GET['edit_paid']) && $_GET['edit_paid'] == '1';
        $editingOrderId = $orderId;

        $originalPaidTotalFromDB = 0;
        if ($isEditingPaid && $editingOrderId) {
            $orderData = $this->orderRepo->find($editingOrderId);
            if ($orderData) {
                $originalPaidTotalFromDB = floatval($orderData['total'] ?? 0);
            }
        }

        // 2. Carrega Configurações (Taxa de Entrega do Banco)
        // [FIX] Ler do repositório correto
        $cardapioConfig = $this->configRepo->findOrCreate($rid);
        $deliveryFee = floatval($cardapioConfig['delivery_fee'] ?? 0);

        // 3. Lógica de Exibição de Botões (TableViewFlags)
        $showQuickSale = false;   // Botão "Finalizar" (Venda Rápida)
        $showCloseTable = false;  // Botão "Finalizar Mesa"
        $showCloseCommand = false;// Botão "Entregar/Finalizar" (Comanda)
        $showSaveCommand = false; // Botão "Salvar" (Comanda)
        $showIncludePaid = false; // Botão "Incluir" (Edição Pago)

        if ($mesa_id) {
            // Contexto: MESA
            $showCloseTable = true;
            $showSaveCommand = true; // Permite adicionar mais itens ao pedido existente
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

        // 4. Carrinho de Recuperação
        // Só carrega itens para o carrinho EDITÁVEL se estivermos 'editando' um pedido pago.
        // Nos demais casos (mesa/comanda aberta), o carrinho começa vazio para novos itens.
        $cartRecovery = ($isEditingPaid && $editingOrderId) ? $itensJaPedidos : [];

        // Carrega Cardápio
        $categories = $this->service->getMenu($rid);

        // Renderiza
        View::renderFromScope('admin/panel/dashboard', get_defined_vars());
    }

    /**
     * Cancela a edição e restaura o backup do pedido
     */
    public function cancelEdit()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
            die('Erro crítico ao cancelar edição: ' . $e->getMessage());
        }
    }
}
