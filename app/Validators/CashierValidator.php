<?php
namespace App\Validators;

/**
 * CashierValidator - Validação e Sanitização de operações de Caixa
 */
class CashierValidator {

    public function validateOpenCashier(array $data): array {
        $errors = [];
        
        $balance = str_replace(',', '.', $data['opening_balance'] ?? '');
        if (!is_numeric($balance) || floatval($balance) < 0) {
            $errors['opening_balance'] = 'Saldo inicial deve ser um número válido';
        }
        
        return $errors;
    }

    public function validateMovement(array $data): array {
        $errors = [];
        
        if (empty($data['type']) || !in_array($data['type'], ['sangria', 'suprimento'])) {
            $errors['type'] = 'Tipo de movimento inválido';
        }
        
        $amount = str_replace(',', '.', $data['amount'] ?? '');
        if (!is_numeric($amount) || floatval($amount) <= 0) {
            $errors['amount'] = 'Valor deve ser um número positivo';
        }
        
        return $errors;
    }

    public function sanitizeOpenCashier(array $data): array {
        return [
            'opening_balance' => floatval(str_replace(',', '.', $data['opening_balance'] ?? 0))
        ];
    }

    public function sanitizeMovement(array $data): array {
        return [
            'type' => trim($data['type'] ?? ''),
            'amount' => floatval(str_replace(',', '.', $data['amount'] ?? 0)),
            'description' => trim(strip_tags($data['description'] ?? ''))
        ];
    }

    public function hasErrors(array $errors): bool {
        return !empty($errors);
    }
}
