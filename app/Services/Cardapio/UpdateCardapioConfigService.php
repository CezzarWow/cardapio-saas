<?php

namespace App\Services\Cardapio;

use App\Repositories\Cardapio\CardapioConfigRepository;
use App\Repositories\Cardapio\BusinessHoursRepository;
use App\Repositories\Cardapio\CategoryRepository;
use App\Repositories\Cardapio\ProductRepository;

/**
 * Service para atualizar configurações do Cardápio
 * Consolida toda a lógica do update() do controller
 */
class UpdateCardapioConfigService
{
    private CardapioConfigRepository $configRepository;
    private BusinessHoursRepository $hoursRepository;
    private CategoryRepository $categoryRepository;
    private ProductRepository $productRepository;

    public function __construct(
        CardapioConfigRepository $configRepository,
        BusinessHoursRepository $hoursRepository,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ) {
        $this->configRepository = $configRepository;
        $this->hoursRepository = $hoursRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Executa a atualização de todas as configurações
     */
    public function execute(int $restaurantId, array $postData): void
    {
        // 1. Atualizar configuração principal
        $configData = $this->parseConfigData($postData);
        $this->configRepository->update($restaurantId, $configData);

        // 2. Atualizar horários de funcionamento
        $hours = $postData['hours'] ?? [];
        $this->hoursRepository->save($restaurantId, $hours);

        // 3. Atualizar destaques de produtos
        $featured = $postData['featured'] ?? [];
        $this->productRepository->syncFeatured($restaurantId, $featured);

        // 4. Atualizar ordem e estado das categorias
        $categoryOrder = $postData['category_order'] ?? [];
        $categoryEnabled = $postData['category_enabled'] ?? [];
        $this->categoryRepository->syncOrderAndActive($restaurantId, $categoryOrder, $categoryEnabled);

        // 5. Atualizar ordem dos produtos
        $productOrder = $postData['product_order'] ?? [];
        $this->productRepository->syncDisplayOrder($restaurantId, $productOrder);
    }

    /**
     * Processa e valida os dados do formulário de configuração
     */
    private function parseConfigData(array $post): array
    {
        // Processa valores monetários (troca vírgula por ponto)
        $deliveryFee = str_replace(',', '.', $post['delivery_fee'] ?? '5');
        $deliveryFee = preg_replace('/[^\d.]/', '', $deliveryFee);
        
        $minOrderValue = str_replace(',', '.', $post['min_order_value'] ?? '20');
        $minOrderValue = preg_replace('/[^\d.]/', '', $minOrderValue);

        // WhatsApp (Ajuste: Listas Dinâmicas Antes/Depois)
        $jsonMessages = $this->parseWhatsappMessages($post);

        return [
            // WhatsApp
            'whatsapp_enabled' => isset($post['whatsapp_enabled']) ? 1 : 0,
            'whatsapp_number' => preg_replace('/\D/', '', $post['whatsapp_number'] ?? ''),
            'whatsapp_message' => $jsonMessages,
            
            // Operação
            'is_open' => isset($post['is_open']) ? 1 : 0,
            'opening_time' => $post['opening_time'] ?? '08:00',
            'closing_time' => $post['closing_time'] ?? '22:00',
            'closed_message' => trim($post['closed_message'] ?? 'Estamos fechados no momento'),
            
            // Delivery
            'delivery_enabled' => isset($post['delivery_enabled']) ? 1 : 0,
            'delivery_fee' => floatval($deliveryFee),
            'min_order_value' => floatval($minOrderValue),
            'delivery_time_min' => intval($post['delivery_time_min'] ?? 30),
            'delivery_time_max' => intval($post['delivery_time_max'] ?? 45),
            
            // Retirada e Local
            'pickup_enabled' => isset($post['pickup_enabled']) ? 1 : 0,
            'dine_in_enabled' => isset($post['dine_in_enabled']) ? 1 : 0,
            
            // Pagamentos
            'accept_cash' => isset($post['accept_cash']) ? 1 : 0,
            'accept_credit' => isset($post['accept_card']) ? 1 : 0,
            'accept_debit' => isset($post['accept_card']) ? 1 : 0,
            'accept_pix' => isset($post['accept_pix']) ? 1 : 0,
            'pix_key' => trim($post['pix_key'] ?? ''),
            'pix_key_type' => $post['pix_key_type'] ?? 'telefone',
        ];
    }

    /**
     * Processa mensagens do WhatsApp (estrutura antes/depois)
     */
    private function parseWhatsappMessages(array $post): string
    {
        $whatsappData = $post['whatsapp_data'] ?? null;
        
        if ($whatsappData && is_array($whatsappData)) {
            // Estrutura Nova: {before: [], after: []}
            $finalMessages = [
                'before' => array_values(array_filter($whatsappData['before'] ?? [], fn($m) => !empty(trim($m)))),
                'after' => array_values(array_filter($whatsappData['after'] ?? [], fn($m) => !empty(trim($m))))
            ];
            return json_encode($finalMessages, JSON_UNESCAPED_UNICODE);
        }
        
        // Legado (Array Simples ou String)
        $whatsappMessages = $post['whatsapp_messages'] ?? [];
        if (!is_array($whatsappMessages)) {
            $whatsappMessages = [$whatsappMessages];
        }
        $whatsappMessages = array_values(array_filter($whatsappMessages, fn($m) => !empty(trim($m))));
        
        return json_encode($whatsappMessages, JSON_UNESCAPED_UNICODE);
    }
}
