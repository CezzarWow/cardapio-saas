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
use App\Validators\AdditionalValidator;

/**
 * Controller de Adicionais - Super Thin (v3)
 * Usa handleValidatedPost() e handleDelete() do BaseController
 */
class AdditionalController extends BaseController {

    private const BASE = '/admin/loja/adicionais';
    private AdditionalValidator $v;

    public function __construct() {
        $this->v = new AdditionalValidator();
    }

    // === VIEW ===
    public function index() {
        $rid = $this->getRestaurantId();
        $query = new AdditionalQueryService();
        
        $groups = $query->getAllGroupsWithItems($rid);
        $allItems = $query->getAllItems($rid);
        $categories = (new AdditionalCategoryRepository())->findAllCategories($rid);

        require __DIR__ . '/../../../views/admin/additionals/index.php';
    }

    // === GRUPO CRUD ===
    public function storeGroup() {
        $this->handleValidatedPost(
            fn() => $this->v->validateGroup($_POST),
            fn() => $this->v->sanitizeGroup($_POST),
            fn($data, $rid) => (new CreateAdditionalGroupService())->execute($rid, $data),
            self::BASE, 'grupo_criado'
        );
    }

    public function deleteGroup() {
        $this->handleDelete(
            fn($id, $rid) => (new DeleteGroupService())->execute($id, $rid),
            self::BASE
        );
    }

    // === ITEM CRUD ===
    public function storeItemWithGroups() {
        $this->handleValidatedPost(
            fn() => $this->v->validateItem($_POST),
            fn() => $this->v->sanitizeItem($_POST),
            fn($data, $rid) => (new CreateItemService())->execute($rid, $data),
            self::BASE, 'item_criado'
        );
    }

    public function updateItemWithGroups() {
        $this->handleValidatedPost(
            fn() => $this->v->validateItemUpdate($_POST),
            fn() => $this->v->sanitizeItem($_POST),
            fn($data, $rid) => (new UpdateItemService())->execute($rid, $data),
            self::BASE, 'item_atualizado'
        );
    }

    public function deleteItem() {
        $this->handleDelete(
            fn($id, $rid) => (new DeleteItemService())->execute($id, $rid),
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
        
        (new LinkItemService())->linkSingle($groupId, $itemId, $rid);
        $this->redirect(self::BASE);
    }

    public function linkMultipleItems() {
        $this->handleValidatedPost(
            fn() => $this->v->validateMultipleLink($_POST),
            fn() => ['group_id' => $this->postInt('group_id'), 'item_ids' => $_POST['item_ids'] ?? []],
            fn($data, $rid) => (new LinkItemService())->linkMultiple($data['group_id'], $data['item_ids'], $rid),
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
        
        (new UnlinkItemService())->execute($groupId, $itemId, $rid);
        $this->redirect(self::BASE);
    }

    public function linkCategory() {
        $this->handleValidatedPost(
            fn() => $this->v->validateCategoryLink($_POST),
            fn() => ['group_id' => $this->postInt('group_id'), 'category_ids' => $_POST['category_ids'] ?? []],
            fn($data, $rid) => (new LinkCategoryService())->execute($data['group_id'], $data['category_ids'], $rid),
            self::BASE, 'vinculo_sincronizado'
        );
    }

    // === APIs JSON ===
    public function getLinkedCategories() {
        $rid = $this->getRestaurantId();
        $groupId = $this->getInt('group_id');
        
        $this->json($groupId <= 0 ? [] : (new LinkCategoryService())->getLinkedCategories($groupId, $rid));
    }

    public function getItemData() {
        try {
            $rid = $this->getRestaurantId();
            $id = $this->getInt('id');
            
            if ($id <= 0) {
                $this->json(['error' => 'ID inválido'], 400);
            }
            
            $data = (new AdditionalQueryService())->getItemData($id, $rid);
            $this->json($data ?: ['error' => 'Item não encontrado'], $data ? 200 : 404);
            
        } catch (\Throwable $e) {
            error_log('getItemData Error: ' . $e->getMessage());
            $this->json(['error' => 'Erro no servidor'], 500);
        }
    }

    public function getProductExtras() {
        $rid = $this->getRestaurantId();
        $productId = $this->getInt('product_id');
        
        $this->json($productId <= 0 ? [] : (new AdditionalQueryService())->getProductExtras($productId, $rid));
    }
}
