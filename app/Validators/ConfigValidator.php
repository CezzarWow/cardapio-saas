<?php

namespace App\Validators;

/**
 * ConfigValidator - Validação de Configurações da Loja
 */
class ConfigValidator
{
    /**
     * Valida dados de configuração
     */
    public function validateUpdate(array $data, ?array $file = null): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Nome da loja é obrigatório';
        }

        if (empty(trim($data['phone'] ?? ''))) {
            $errors['phone'] = 'Telefone é obrigatório';
        }

        if (empty(trim($data['address'] ?? ''))) {
            $errors['address'] = 'Endereço é obrigatório';
        }

        // Validação de Upload (se houver arquivo enviado)
        if ($file && !empty($file['name'])) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors['logo'] = 'Erro no upload da logo. Código: ' . $file['error'];
            } else {
                $maxSize = 5 * 1024 * 1024; // 5MB
                if ($file['size'] > $maxSize) {
                    $errors['logo'] = 'A logo deve ter no máximo 5MB';
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($file['type'], $allowedTypes)) {
                    $errors['logo'] = 'Formato de imagem inválido (use JPG, PNG ou WebP)';
                }
            }
        }

        return $errors;
    }

    public function hasErrors(array $errors): bool
    {
        return !empty($errors);
    }

    public function getFirstError(array $errors): string
    {
        return reset($errors) ?: 'Erro de validação';
    }
}
