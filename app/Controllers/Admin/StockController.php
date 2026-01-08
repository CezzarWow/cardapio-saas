<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use App\Validators\StockValidator;
use PDO;

require_once __DIR__ . '/../../Helpers/ImageConverter.php';
use ImageConverter;

/**
 * StockController - Gerenciamento de Produtos (Super Thin v2)
 */
class StockController extends BaseController {

    private const BASE = '/admin/loja/produtos';
    private StockValidator $v;

    public function __construct() {
        $this->v = new StockValidator();
    }

    // === LISTAGEM ===
    public function index() {
        $rid = $this->getRestaurantId();
        $conn = Database::connect();
        
        $products = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.restaurant_id = :rid ORDER BY p.name
        ");
        $products->execute(['rid' => $rid]);
        $products = $products->fetchAll(PDO::FETCH_ASSOC);

        $categories = $this->getCategories($conn, $rid);
        
        require __DIR__ . '/../../../views/admin/stock/index.php';
    }

    // === FORMULÁRIO CRIAR ===
    public function create() {
        $rid = $this->getRestaurantId();
        $conn = Database::connect();
        
        $categories = $this->getCategories($conn, $rid);
        $additionalGroups = $this->getAdditionalGroups($conn, $rid);

        require __DIR__ . '/../../../views/admin/stock/create.php';
    }

    // === SALVAR NOVO ===
    public function store() {
        $this->handleValidatedPost(
            fn() => $this->v->validateProduct($_POST),
            fn() => $this->v->sanitizeProduct($_POST),
            fn($data, $rid) => $this->createProduct($data, $rid),
            self::BASE, 'criado'
        );
    }

    // === DELETAR ===
    public function delete() {
        $this->handleDelete(
            fn($id, $rid) => $this->deleteProduct($id, $rid),
            self::BASE
        );
    }

    // === FORMULÁRIO EDITAR ===
    public function edit() {
        $rid = $this->getRestaurantId();
        $id = $this->getInt('id');
        $conn = Database::connect();

        $product = $this->getProduct($conn, $id, $rid);
        if (!$product) {
            $this->redirect(self::BASE);
        }

        $categories = $this->getCategories($conn, $rid);
        $additionalGroups = $this->getAdditionalGroups($conn, $rid);
        $linkedGroups = $this->getLinkedGroups($conn, $id);

        require __DIR__ . '/../../../views/admin/stock/edit.php';
    }

    // === ATUALIZAR ===
    public function update() {
        $this->handleValidatedPost(
            fn() => $this->v->validateProductUpdate($_POST),
            fn() => $this->v->sanitizeProduct($_POST),
            fn($data, $rid) => $this->updateProduct($data, $rid),
            self::BASE, 'atualizado'
        );
    }

    // ============================================
    // MÉTODOS PRIVADOS (Lógica de Negócio)
    // ============================================

    private function createProduct(array $data, int $rid): void {
        $conn = Database::connect();
        
        // Upload de imagem
        $imageName = $this->handleImageUpload();
        
        // Próximo item_number
        $stmtMax = $conn->prepare("SELECT COALESCE(MAX(item_number), 0) + 1 AS next_num FROM products WHERE restaurant_id = :rid");
        $stmtMax->execute(['rid' => $rid]);
        $nextNumber = $stmtMax->fetch(PDO::FETCH_ASSOC)['next_num'];
        
        // Insert
        $stmt = $conn->prepare("
            INSERT INTO products (restaurant_id, category_id, name, description, price, image, icon, icon_as_photo, item_number, stock) 
            VALUES (:rid, :cid, :name, :desc, :price, :img, :icon, :iap, :inum, :stock)
        ");
        $stmt->execute([
            'rid' => $rid,
            'cid' => $data['category_id'],
            'name' => $data['name'],
            'desc' => $data['description'],
            'price' => $data['price'],
            'img' => $imageName,
            'icon' => $data['icon'],
            'iap' => $data['icon_as_photo'],
            'inum' => $nextNumber,
            'stock' => $data['stock']
        ]);
        
        // Vínculos com adicionais
        $this->syncAdditionalGroups($conn, $conn->lastInsertId(), $data['additional_groups']);
    }

    private function updateProduct(array $data, int $rid): void {
        $conn = Database::connect();
        $id = $data['id'];
        
        // Verifica se pertence à loja
        $product = $this->getProduct($conn, $id, $rid);
        if (!$product) {
            throw new \Exception('Produto não encontrado');
        }
        
        // Upload de imagem (só se nova)
        $imageName = $this->handleImageUpload() ?? $product['image'];
        
        // Update
        $stmt = $conn->prepare("
            UPDATE products SET 
                name = :name, price = :price, category_id = :cid, description = :desc, 
                stock = :stock, image = :img, icon = :icon, icon_as_photo = :iap
            WHERE id = :id AND restaurant_id = :rid
        ");
        $stmt->execute([
            'name' => $data['name'],
            'price' => $data['price'],
            'cid' => $data['category_id'],
            'desc' => $data['description'],
            'stock' => $data['stock'],
            'img' => $imageName,
            'icon' => $data['icon'],
            'iap' => $data['icon_as_photo'],
            'id' => $id,
            'rid' => $rid
        ]);
        
        // Atualiza vínculos
        $this->syncAdditionalGroups($conn, $id, $data['additional_groups']);
    }

    private function deleteProduct(int $id, int $rid): void {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $rid]);
    }

    // ============================================
    // HELPERS
    // ============================================

    private function getCategories($conn, int $rid): array {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $rid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAdditionalGroups($conn, int $rid): array {
        $stmt = $conn->prepare("SELECT * FROM additional_groups WHERE restaurant_id = :rid ORDER BY name");
        $stmt->execute(['rid' => $rid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getProduct($conn, int $id, int $rid): ?array {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $rid]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getLinkedGroups($conn, int $productId): array {
        $stmt = $conn->prepare("SELECT group_id FROM product_additional_relations WHERE product_id = :pid");
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function syncAdditionalGroups($conn, int $productId, array $groupIds): void {
        // Limpa anteriores
        $conn->prepare("DELETE FROM product_additional_relations WHERE product_id = :pid")->execute(['pid' => $productId]);
        
        // Insere novos
        if (!empty($groupIds)) {
            $stmt = $conn->prepare("INSERT INTO product_additional_relations (product_id, group_id) VALUES (:pid, :gid)");
            foreach ($groupIds as $gid) {
                $stmt->execute(['pid' => $productId, 'gid' => $gid]);
            }
        }
    }

    private function handleImageUpload(): ?string {
        if (empty($_FILES['image']['name'])) {
            return null;
        }
        
        $uploadDir = __DIR__ . '/../../../public/uploads/';
        return \ImageConverter::uploadAndConvert($_FILES['image'], $uploadDir, 85);
    }
}
