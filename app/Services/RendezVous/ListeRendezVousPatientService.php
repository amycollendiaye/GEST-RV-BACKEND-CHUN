<?php

namespace App\Services\RendezVous;

use App\Repositories\Interfaces\RendezVousRepositoryInterface;

class ListeRendezVousPatientService
{
    public function __construct(
        private readonly RendezVousRepositoryInterface $rendezVousRepository
    ) {
    }

    public function execute(string $patientId, array $filters, int $perPage)
    {
        return $this->rendezVousRepository->findAllByPatient($patientId, $filters, $perPage);
    }
}
