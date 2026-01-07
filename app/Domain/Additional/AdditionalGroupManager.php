<?php

namespace App\Domain\Additional;

use App\Repositories\AdditionalGroupRepository;
use Exception;

class AdditionalGroupManager
{
    private $repository;

    public function __construct()
    {
        $this->repository = new AdditionalGroupRepository();
    }

    /**
     * Cria um novo grupo, validando regras de domínio
     * Regra 1: Nome obrigatório
     * Regra 2: Nome único por restaurante (opcional, boa prática)
     */
    public function createGroup(int $restaurantId, string $name, bool $required): int
    {
        $name = trim($name);

        if (empty($name)) {
            throw new Exception("O nome do grupo é obrigatório.");
        }

        // Validação de duplicidade (Regra de Domínio)
        if ($this->repository->nameExists($restaurantId, $name)) {
            throw new Exception("Já existe um grupo com este nome.");
        }

        // Persistência
        return $this->repository->save($restaurantId, $name, $required ? 1 : 0);
    }
}
