<?php

namespace App\Services\Secretaire;

use App\Repositories\Interfaces\SecretaireRepositoryInterface;

class ChangerStatutSecretaireService
{
    public function __construct(
        private readonly SecretaireRepositoryInterface $secretaireRepository
    ) {
    }

    public function execute(string $id, string $statut)
    {
        return $this->secretaireRepository->changerStatut($id, $statut);
    }
}
