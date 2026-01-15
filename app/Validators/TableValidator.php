<?php

namespace App\Validators;

class TableValidator
{
    public function validateStore(array $data)
    {
        $errors = [];
        $number = $data['number'] ?? null;

        if ($number === null || $number === '') {
            $errors[] = 'Número da mesa é obrigatório';
        } elseif (!is_numeric($number) || $number < 0) {
            $errors[] = 'O número deve ser um valor positivo';
        }

        return $errors;
    }

    public function validateDelete(array $data)
    {
        $errors = [];
        $number = $data['number'] ?? null;

        if ($number === null || $number === '') {
            $errors[] = 'Número da mesa é obrigatório';
        }

        return $errors;
    }

    public function hasErrors(array $errors)
    {
        return !empty($errors);
    }
}
