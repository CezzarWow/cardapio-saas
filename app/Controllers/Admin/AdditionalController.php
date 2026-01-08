<?php
namespace App\Controllers\Admin;

use App\Services\Additional\AdditionalService;
use App\Validators\AdditionalValidator;

/**
 * Controller de Adicionais - Super Thin (v4)
 * Usa AdditionalService consolidado
 */
class AdditionalController extends BaseController {

    private const BASE = '/admin/loja/adicionais';
    
    private AdditionalValidator $v;
    private AdditionalService $service;

    public function __construct() {
        $this->v = new AdditionalValidator();
        $this->service = new AdditionalService();
    }

    // === VIEW ===
    public function index() {
        $rid = $this->getRestaurantId();
        
        $groups = $this->service->getAllGroupsWithItems($rid);
        $allItems = $this->service->getAllItems($rid);
        
        // Mantendo o repository direto para categorias por enquanto,
        // já que o AdditionalService foca em grupos/itens/vínculos.
        $categories = (new \App\Repositories\AdditionalCategoryRepository())->findAllCategories($rid); 

        require __DIR__ . '/../../../views/admin/additionals/index.php';
    }

    // === GRUPO CRUD ===
    public function storeGroup() {
        $this->handleValidatedPost(
            fn() => $this->v->validateGroup($_POST),
            fn() => $this->v->sanitizeGroup($_POST),
            fn($data, $rid) => $this->service->createGroup($rid, $data),
            self::BASE, 'grupo_criado'
        );
    }

    public function deleteGroup() {
        $this->handleDelete(
            fn($id, $rid) => $this->service->deleteGroup($id, $rid),
            self::BASE
        );
    }

    // === ITEM CRUD ===
    public function storeItemWithGroups() {
        $this->handleValidatedPost(
            fn() => $this->v->validateItem($_POST),
            fn() => $this->v->sanitizeItem($_POST),
            fn($data, $rid) => $this->service->createItem($rid, $data),
            self::BASE, 'item_criado'
        );
    }

    public function updateItemWithGroups() {
        $this->handleValidatedPost(
            fn() => $this->v->validateItemUpdate($_POST),
            fn() => $this->v->sanitizeItem($_POST),
            fn($data, $rid) => $this->service->updateItem($rid, $data),
            self::BASE, 'item_atualizado'
        );
    }

    public function deleteItem() {
        $this->handleDelete(
            fn($id, $rid) => $this->service->deleteItem($id, $rid),
            self::BASE . '/itens'
        );
    }

    // === VÍNCULOS ===
    public function linkItem() {
        if (!$this->isPost()) return;
        
        $rid = $this->getRestaurantId();
        $groupId = $this->postInt('group_id');
        $itemId = $this->postInt('item_id');
        
        if ($groupId <= 0 || $itemId <= 0) {
            $this->redirect(self::BASE . '?error=dados_invalidos');
        }
        
        $this->service->linkItem($groupId, $itemId, $rid);
        $this->redirect(self::BASE);
    }

    public function linkMultipleItems() {
        $this->handleValidatedPost(
            fn() => $this->v->validateMultipleLink($_POST),
            fn() => ['group_id' => $this->postInt('group_id'), 'item_ids' => $_POST['item_ids'] ?? []],
            fn($data, $rid) => $this->service->linkMultipleItems($data['group_id'], $data['item_ids'], $rid),
            self::BASE, 'itens_vinculados'
        );
    }

    public function unlinkItem() {
        $rid = $this->getRestaurantId();
        $groupId = $this->getInt('grupo');
        $itemId = $this->getInt('item');
        
        if ($groupId <= 0 || $itemId <= 0) {
            $this->redirect(self::BASE . '?error=dados_invalidos');
        }
        
        $this->service->unlinkItem($groupId, $itemId, $rid);
        $this->redirect(self::BASE);
    }

    public function linkCategory() {
        $this->handleValidatedPost(
            fn() => $this->v->validateCategoryLink($_POST),
            fn() => ['group_id' => $this->postInt('group_id'), 'category_ids' => $_POST['category_ids'] ?? []],
            fn($data, $rid) => $this->service->updateCategoryLinks($data['group_id'], $data['category_ids'], $rid),
            self::BASE, 'vinculo_sincronizado'
        );
    }

    // === APIs JSON ===
    public function getLinkedCategories() {
        $rid = $this->getRestaurantId();
        $groupId = $this->getInt('group_id');
        $this->json($groupId <= 0 ? [] : $this->service->getLinkedCategories($groupId, $rid));
    }

    public function getItemData() {
        try {
            $rid = $this->getRestaurantId();
            $id = $this->getInt('id');
            
            if ($id <= 0) {
                $this->json(['error' => 'ID inválido'], 400);
            }
            
            $data = $this->service->getItemData($id, $rid);
            $this->json($data ?: ['error' => 'Item não encontrado'], $data ? 200 : 404);
            
        } catch (\Throwable $e) {
            error_log('getItemData Error: ' . $e->getMessage());
            $this->json(['error' => 'Erro no servidor'], 500);
        }
    }

    public function getProductExtras() {
        $rid = $this->getRestaurantId();
        $productId = $this->getInt('product_id');
        $this->json($productId <= 0 ? [] : $this->service->getProductExtras($productId, $rid));
    }
}
