<?php

namespace App\Services\PlanningMedecin;

use App\Exceptions\PlanningDejaExistantException;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;

class VerifierDisponibilitePlanningService
{
    public function __construct(
        private readonly PlanningMedecinRepositoryInterface $planningRepository
    ) {
    }

    public function execute(string $medecinId, string $serviceId, string $date, ?string $ignoreId = null): void
    {
        if ($this->planningRepository->existsForDate($medecinId, $serviceId, $date, $ignoreId)) {
            throw new PlanningDejaExistantException();
        }
    }
}
