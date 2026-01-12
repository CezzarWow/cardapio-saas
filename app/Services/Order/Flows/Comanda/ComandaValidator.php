<?php

namespace App\Services\Order\Flows\Comanda;

use App\Services\Order\TotalCalculator;

/**
 * Validador do Fluxo Comanda
 * 
 * Valida contratos de payload para operações de comanda.
 * COMANDA é vinculada obrigatoriamente a cliente.
 */
class ComandaValidator
{
    /**
     * Valida payload para ABRIR comanda
     * 
     * @param array $data Payload recebido
     * @return array Erros encontrados (vazio = válido)
     */
    public function validateOpen(array $data): array
    {
        $errors = [];
        
        // 1. flow_type obrigatório
        if (($data['flow_type'] ?? '') !== 'comanda') {
            $errors['flow_type'] = 'flow_type deve ser "comanda"';
        }
        
        // 2. client_id OBRIGATÓRIO (diferença principal da Mesa)
        if (empty($data['client_id']) || !is_numeric($data['client_id'])) {
            $errors['client_id'] = 'Cliente é obrigatório para comanda';
        }
        
        // 3. Carrinho obrigatório para abrir
        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho não pode estar vazio para abrir comanda';
        } else {
            $this->validateCartItems($data['cart'], $errors);
        }
        
        // NOTA: Pagamento NÃO é obrigatório na abertura
        
        return $errors;
    }
    
    /**
     * Valida payload para ADICIONAR ITENS à comanda
     */
    public function validateAddItems(array $data): array
    {
        $errors = [];
        
        // 1. flow_type
        if (($data['flow_type'] ?? '') !== 'comanda_add') {
            $errors['flow_type'] = 'flow_type deve ser "comanda_add"';
        }
        
        // 2. order_id obrigatório (comanda já aberta)
        if (empty($data['order_id']) || !is_numeric($data['order_id'])) {
            $errors['order_id'] = 'ID do pedido é obrigatório';
        }
        
        // 3. Carrinho obrigatório
        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho não pode estar vazio';
        } else {
            $this->validateCartItems($data['cart'], $errors);
        }
        
        return $errors;
    }
    
    /**
     * Valida payload para FECHAR comanda
     */
    public function validateClose(array $data): array
    {
        $errors = [];
        
        // 1. flow_type
        if (($data['flow_type'] ?? '') !== 'comanda_fechar') {
            $errors['flow_type'] = 'flow_type deve ser "comanda_fechar"';
        }
        
        // 2. order_id obrigatório
        if (empty($data['order_id']) || !is_numeric($data['order_id'])) {
            $errors['order_id'] = 'ID da comanda é obrigatório';
        }
        
        // 3. Pagamento OBRIGATÓRIO para fechar
        if (empty($data['payments']) || !is_array($data['payments'])) {
            $errors['payments'] = 'Pagamento obrigatório para fechar comanda';
        } else {
            $this->validatePayments($data['payments'], $errors);
        }
        
        return $errors;
    }
    
    /**
     * Valida itens do carrinho
     */
    private function validateCartItems(array $cart, array &$errors): void
    {
        foreach ($cart as $index => $item) {
            if (empty($item['id'])) {
                $errors["cart.{$index}.id"] = 'ID do produto obrigatório';
            }
            if (!isset($item['price']) || floatval($item['price']) <= 0) {
                $errors["cart.{$index}.price"] = 'Preço deve ser maior que zero';
            }
            if (!isset($item['quantity']) || intval($item['quantity']) < 1) {
                $errors["cart.{$index}.quantity"] = 'Quantidade mínima é 1';
            }
        }
    }
    
    /**
     * Valida pagamentos
     */
    private function validatePayments(array $payments, array &$errors): void
    {
        foreach ($payments as $index => $payment) {
            if (!isset($payment['amount']) || floatval($payment['amount']) <= 0) {
                $errors["payments.{$index}.amount"] = 'Valor do pagamento deve ser maior que zero';
            }
            if (empty($payment['method'])) {
                $errors["payments.{$index}.method"] = 'Método de pagamento obrigatório';
            }
        }
    }
    
    /**
     * Valida se pagamento cobre o total da comanda
     */
    public function validatePaymentCoversTotal(float $orderTotal, array $payments): array
    {
        $errors = [];
        $paid = TotalCalculator::fromPayments($payments);
        
        if ($paid < $orderTotal) {
            $errors['payments'] = sprintf(
                'Pagamento insuficiente. Total: R$ %.2f, Pago: R$ %.2f',
                $orderTotal,
                $paid
            );
        }
        
        return $errors;
    }
}
