<?php

namespace App\Services\Medecin;

use App\Repositories\Interfaces\MedecinRepositoryInterface;

class ChangerStatutMedecinService
{
    public function __construct(
        private readonly MedecinRepositoryInterface $medecinRepository
    ) {
    }

    public function execute(string $id, string $statut)
    {
        return $this->medecinRepository->changerStatut($id, $statut);
    }
}
