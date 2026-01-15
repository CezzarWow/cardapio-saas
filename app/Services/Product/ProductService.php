<?php

namespace App\Services\Product;

use App\Repositories\AdditionalGroupRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

/**
 * ProductService - Gerenciamento do Catálogo de Produtos
 * CAMADA: Application Layer
 */
class ProductService
{
    private ProductRepository $productRepo;
    private CategoryRepository $categoryRepo;
    private AdditionalGroupRepository $groupRepo;

    public function __construct(
        ProductRepository $productRepo,
        CategoryRepository $categoryRepo,
        AdditionalGroupRepository $groupRepo
    ) {
        $this->productRepo = $productRepo;
        $this->categoryRepo = $categoryRepo;
        $this->groupRepo = $groupRepo;
    }

    /**
     * Lista produtos com categoria
     */
    public function getProducts(int $restaurantId): array
    {
        return $this->productRepo->findAll($restaurantId);
    }

    /**
     * Busca produto por ID
     */
    public function getProduct(int $id, int $restaurantId): ?array
    {
        return $this->productRepo->find($id, $restaurantId);
    }

    /**
     * Cria novo produto
     */
    public function create(int $restaurantId, array $data, ?string $imageName = null): int
    {
        // Regra de Negócio: Calcular próximo número se não enviado
        $nextNumber = $this->productRepo->getNextItemNumber($restaurantId);
        $stockValue = isset($data['stock']) ? $data['stock'] : 0;

        $productData = [
            'restaurant_id' => $restaurantId,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'image' => $imageName,
            'icon' => $data['icon'],
            'icon_as_photo' => $data['icon_as_photo'],
            'item_number' => $nextNumber,
            'stock' => $stockValue
        ];

        $productId = $this->productRepo->create($productData);

        if (isset($data['additional_groups'])) {
            $this->productRepo->syncAdditionalGroups($productId, $data['additional_groups']);
        }

        return $productId;
    }

    /**
     * Atualiza produto existente
     */
    public function update(int $restaurantId, array $data, ?string $newImageName = null): void
    {
        $id = $data['id'];

        // Regra de Negócio: Verifica se pertence à loja
        $product = $this->getProduct($id, $restaurantId);
        if (!$product) {
            throw new \Exception('Produto não encontrado');
        }

        $imageName = $newImageName ?? $product['image'];

        $updateData = [
            'id' => $id,
            'restaurant_id' => $restaurantId,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image' => $imageName,
            'icon' => $data['icon'],
            'icon_as_photo' => $data['icon_as_photo']
        ];

        $this->productRepo->update($updateData);

        if (isset($data['additional_groups'])) {
            $this->productRepo->syncAdditionalGroups($id, $data['additional_groups']);
        }
    }

    /**
     * Deleta produto
     */
    public function delete(int $id, int $restaurantId): void
    {
        $this->productRepo->delete($id, $restaurantId);
    }

    /**
     * Processa upload de imagem
     * (Mantido como helper wrapper por enquanto)
     */
    public function handleImageUpload(?array $file): ?string
    {
        if (empty($file['name'])) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../../public/uploads/';
        return \App\Helpers\ImageConverter::uploadAndConvert($file, $uploadDir, 85);
    }

    /**
     * Lista categorias (Helper para Forms de Produto)
     */
    public function getCategories(int $restaurantId): array
    {
        return $this->categoryRepo->findAll($restaurantId);
    }

    /**
     * Lista grupos de adicionais (Helper para Forms de Produto)
     * Agora usa o AdditionalGroupRepository existente
     */
    public function getAdditionalGroups(int $restaurantId): array
    {
        // Adaptador: O repositório retorna o array, só repassamos
        // Nota: O método findAllWithItems do repo traz itens juntos, se quisermos só grupos simples talvez precise ajustar
        // Mas o código original fazia SELECT * FROM additional_groups.
        // O método findById do repository busca um.
        // Vamos verificar se existe um findAll simples no repo.
        // Olhando o arquivo: findAllWithItems existe. Mas findAll simples não.
        // Pelo contrato "Stage 2 increments", se faltar método, adicionamos no repo.
        // Mas vou usar o findAllWithItems por equanto ou adicionar um findAll simples no AdditionalGroupRepository se precisar.
        // O código original era: SELECT * FROM additional_groups ordered by name.
        // Vou adicionar findAll no AdditionalGroupRepository rapidinho ou usar SQL aqui? JAMAIS SQL AQUI.
        // Vou adicionar findAll no AdditionalGroupRepository em um passo separado se falhar, mas
        // por enquanto vou assumir que posso adicionar o método findAll no repo.

        // Espera, eu tenho acesso de escrita. Vou adicionar findAll ao AdditionalGroupRepository na próxima tool call se precisar.
        // Por ora, vou deixar o código chamando um método que vou criar.
        return $this->groupRepo->findAll($restaurantId);
    }

    /**
     * Lista grupos vinculados a um produto (Helper para Edição)
     */
    public function getLinkedGroups(int $productId): array
    {
        return $this->productRepo->getLinkedGroups($productId);
    }
}
