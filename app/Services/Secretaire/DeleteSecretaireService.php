<?php

namespace App\Services\Secretaire;

use App\Repositories\Interfaces\SecretaireRepositoryInterface;

class DeleteSecretaireService
{
    public function __construct(
        private readonly SecretaireRepositoryInterface $secretaireRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->secretaireRepository->delete($id);
    }
}
