<?php
namespace App\Controllers\Admin;

use App\Services\Additional\AdditionalQueryService;
use App\Services\Additional\CreateAdditionalGroupService;
use App\Services\Additional\CreateItemService;
use App\Services\Additional\UpdateItemService;
use App\Services\Additional\DeleteItemService;
use App\Services\Additional\DeleteGroupService;
use App\Services\Additional\LinkItemService;
use App\Services\Additional\UnlinkItemService;
use App\Services\Additional\LinkCategoryService;
use App\Repositories\AdditionalCategoryRepository;
use Exception;

/**
 * Controller de Adicionais - DDD Lite
 * Apenas HTTP handling: parse request → call service → redirect/json
 */
class AdditionalController {

    // ==========================================
    // LISTAGEM PRINCIPAL (VIEW)
    // ==========================================
    public function index() {
        $this->checkSession();
        $restaurantId = $_SESSION['loja_ativa_id'];
        
        $queryService = new AdditionalQueryService();
        $categoryRepository = new AdditionalCategoryRepository();
        
        $groups = $queryService->getAllGroupsWithItems($restaurantId);
        $allItems = $queryService->getAllItems($restaurantId);
        $categories = $categoryRepository->findAllCategories($restaurantId);

        require __DIR__ . '/../../../views/admin/additionals/index.php';
    }

    // ==========================================
    // GRUPO: CRIAR
    // ==========================================
    public function storeGroup() {
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $service = new CreateAdditionalGroupService();
                $service->execute($_SESSION['loja_ativa_id'], [
                    'name' => $_POST['name'] ?? '',
                    'item_ids' => $_POST['item_ids'] ?? []
                ]);
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=grupo_criado');
            } catch (Exception $e) {
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=' . urlencode($e->getMessage()));
            }
            exit;
        }
    }

    // ==========================================
    // GRUPO: DELETAR
    // ==========================================
    public function deleteGroup() {
        $this->checkSession();
        
        $id = intval($_GET['id'] ?? 0);
        $service = new DeleteGroupService();
        $service->execute($id, $_SESSION['loja_ativa_id']);

        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
    }

    // ==========================================
    // ITEM: CRIAR
    // ==========================================
    public function storeItemWithGroups() {
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $service = new CreateItemService();
                $service->execute($_SESSION['loja_ativa_id'], [
                    'name' => $_POST['name'] ?? '',
                    'price' => $_POST['price'] ?? '0',
                    'group_ids' => $_POST['group_ids'] ?? []
                ]);
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=item_criado');
            } catch (Exception $e) {
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=' . urlencode($e->getMessage()));
            }
            exit;
        }
    }

    // ==========================================
    // ITEM: ATUALIZAR
    // ==========================================
    public function updateItemWithGroups() {
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $service = new UpdateItemService();
                $service->execute($_SESSION['loja_ativa_id'], [
                    'id' => $_POST['id'] ?? 0,
                    'name' => $_POST['name'] ?? '',
                    'price' => $_POST['price'] ?? '0',
                    'group_ids' => $_POST['group_ids'] ?? []
                ]);
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=item_atualizado');
            } catch (Exception $e) {
                header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=' . urlencode($e->getMessage()));
            }
            exit;
        }
    }

    // ==========================================
    // ITEM: DELETAR
    // ==========================================
    public function deleteItem() {
        $this->checkSession();
        
        $id = intval($_GET['id'] ?? 0);
        $service = new DeleteItemService();
        $service->execute($id, $_SESSION['loja_ativa_id']);

        header('Location: ' . BASE_URL . '/admin/loja/adicionais/itens');
        exit;
    }

    // ==========================================
    // VÍNCULO: ITEM → GRUPO (SIMPLES)
    // ==========================================
    public function linkItem() {
        $this->checkSession();
        
        $groupId = intval($_POST['group_id'] ?? 0);
        $itemId = intval($_POST['item_id'] ?? 0);
        
        $service = new LinkItemService();
        $service->linkSingle($groupId, $itemId, $_SESSION['loja_ativa_id']);

        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
    }

    // ==========================================
    // VÍNCULO: MÚLTIPLOS ITENS → GRUPO
    // ==========================================
    public function linkMultipleItems() {
        $this->checkSession();
        
        $groupId = intval($_POST['group_id'] ?? 0);
        $itemIds = $_POST['item_ids'] ?? [];
        
        $service = new LinkItemService();
        $service->linkMultiple($groupId, $itemIds, $_SESSION['loja_ativa_id']);

        header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=itens_vinculados');
        exit;
    }

    // ==========================================
    // DESVÍNCULO: ITEM ← GRUPO
    // ==========================================
    public function unlinkItem() {
        $this->checkSession();
        
        $groupId = intval($_GET['grupo'] ?? 0);
        $itemId = intval($_GET['item'] ?? 0);
        
        $service = new UnlinkItemService();
        $service->execute($groupId, $itemId, $_SESSION['loja_ativa_id']);

        header('Location: ' . BASE_URL . '/admin/loja/adicionais');
        exit;
    }

    // ==========================================
    // VÍNCULO: CATEGORIA → GRUPO (BULK)
    // ==========================================
    public function linkCategory() {
        $this->checkSession();
        
        $groupId = intval($_POST['group_id'] ?? 0);
        $categoryIds = $_POST['category_ids'] ?? [];
        
        $service = new LinkCategoryService();
        $result = $service->execute($groupId, $categoryIds, $_SESSION['loja_ativa_id']);

        if ($result) {
            header('Location: ' . BASE_URL . '/admin/loja/adicionais?success=vinculo_sincronizado');
        } else {
            header('Location: ' . BASE_URL . '/admin/loja/adicionais?error=grupo_invalido');
        }
        exit;
    }

    // ==========================================
    // API: CATEGORIAS VINCULADAS (AJAX)
    // ==========================================
    public function getLinkedCategories() {
        $this->checkSession();
        header('Content-Type: application/json');

        $groupId = intval($_GET['group_id'] ?? 0);
        
        if ($groupId <= 0) {
            echo json_encode([]);
            exit;
        }

        $service = new LinkCategoryService();
        echo json_encode($service->getLinkedCategories($groupId, $_SESSION['loja_ativa_id']));
        exit;
    }

    // ==========================================
    // API: DADOS DO ITEM (AJAX)
    // ==========================================
    public function getItemData() {
        header('Content-Type: application/json');

        try {
            $this->checkSession();
            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['error' => 'ID inválido']);
                exit;
            }

            $queryService = new AdditionalQueryService();
            $data = $queryService->getItemData($id, $_SESSION['loja_ativa_id']);

            if (!$data) {
                echo json_encode(['error' => 'Item não encontrado']);
                exit;
            }

            echo json_encode($data);
            exit;

        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro no Servidor: ' . $e->getMessage()]);
            exit;
        }
    }

    // ==========================================
    // API: ADICIONAIS DO PRODUTO (PDV)
    // ==========================================
    public function getProductExtras() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $productId = intval($_GET['product_id'] ?? 0);
        $restaurantId = $_SESSION['loja_ativa_id'] ?? 0;

        if ($productId <= 0 || $restaurantId <= 0) {
            echo json_encode([]);
            exit;
        }

        $queryService = new AdditionalQueryService();
        echo json_encode($queryService->getProductExtras($productId, $restaurantId));
        exit;
    }

    // ==========================================
    // SESSÃO
    // ==========================================
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin/escolher-loja');
            exit;
        }
    }
}
