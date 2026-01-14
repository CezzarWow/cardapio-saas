<?php
namespace App\Validators;

class DeliveryValidator {

    public function validateStatusUpdate(array $data): array {
        $errors = [];
        
        if (empty($data['order_id']) || !is_numeric($data['order_id'])) {
            $errors['order_id'] = 'ID do pedido inválido';
        }
        
        // Status válidos (coincidir com TRANSITIONS do DeliveryService)
        $allowedStatus = ['novo', 'preparo', 'rota', 'entregue', 'cancelado'];
        if (empty($data['new_status']) || !in_array($data['new_status'], $allowedStatus)) {
            $errors['new_status'] = 'Status inválido';
        }
        
        return $errors;
    }

    public function validateSendToTable(array $data): array {
        $errors = [];
        
        if (empty($data['order_id']) || !is_numeric($data['order_id'])) {
            $errors['order_id'] = 'ID do pedido inválido';
        }
        
        return $errors;
    }

    public function hasErrors(array $errors): bool {
        return !empty($errors);
    }
}
