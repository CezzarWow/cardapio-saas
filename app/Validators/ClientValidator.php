<?php
namespace App\Validators;

/**
 * ClientValidator - Validação e Sanitização de Clientes
 */
class ClientValidator {

    public function validateStore(array $data): array {
        $errors = [];
        
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $errors['name'] = 'Nome é obrigatório';
        } elseif (strlen($name) > 200) {
            $errors['name'] = 'Nome deve ter no máximo 200 caracteres';
        }
        
        // Documento opcional, mas se informado deve ter formato válido
        $document = preg_replace('/\D/', '', $data['document'] ?? '');
        if (!empty($document) && !$this->isValidDocument($document)) {
            $errors['document'] = 'CPF/CNPJ inválido';
        }
        
        // Telefone opcional
        $phone = preg_replace('/\D/', '', $data['phone'] ?? '');
        if (!empty($phone) && strlen($phone) < 10) {
            $errors['phone'] = 'Telefone deve ter pelo menos 10 dígitos';
        }
        
        return $errors;
    }

    public function validateSearch(array $data): array {
        $errors = [];
        
        $term = trim($data['q'] ?? '');
        if (strlen($term) < 2) {
            $errors['q'] = 'Termo de busca deve ter pelo menos 2 caracteres';
        }
        
        return $errors;
    }

    public function sanitizeStore(array $data): array {
        return [
            'name' => trim(strip_tags($data['name'] ?? '')),
            'type' => in_array($data['type'] ?? '', ['PF', 'PJ']) ? $data['type'] : 'PF',
            'document' => preg_replace('/\D/', '', $data['document'] ?? ''),
            'phone' => preg_replace('/\D/', '', $data['phone'] ?? ''),
            'zip_code' => preg_replace('/\D/', '', $data['zip_code'] ?? ''),
            'address' => trim(strip_tags($data['address'] ?? '')),
            'address_number' => trim(strip_tags($data['address_number'] ?? '')),
            'neighborhood' => trim(strip_tags($data['neighborhood'] ?? '')),
            'city' => trim(strip_tags($data['city'] ?? '')),
            'credit_limit' => max(0, floatval($data['credit_limit'] ?? 0)),
            'due_day' => !empty($data['due_day']) ? min(31, max(1, intval($data['due_day']))) : null
        ];
    }

    public function hasErrors(array $errors): bool {
        return !empty($errors);
    }

    private function isValidDocument(string $doc): bool {
        // CPF: 11 dígitos, CNPJ: 14 dígitos
        return strlen($doc) === 11 || strlen($doc) === 14;
    }
}
