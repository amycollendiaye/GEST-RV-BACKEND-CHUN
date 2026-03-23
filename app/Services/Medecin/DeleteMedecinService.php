<?php

namespace App\Services\Medecin;

use App\Repositories\Interfaces\MedecinRepositoryInterface;

class DeleteMedecinService
{
    public function __construct(
        private readonly MedecinRepositoryInterface $medecinRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->medecinRepository->delete($id);
    }
}
