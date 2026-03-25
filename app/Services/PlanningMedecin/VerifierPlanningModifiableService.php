<?php

namespace App\Services\PlanningMedecin;

use App\Exceptions\PlanningNonModifiableException;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;

class VerifierPlanningModifiableService
{
    public function __construct(
        private readonly PlanningMedecinRepositoryInterface $planningRepository
    ) {
    }

    public function execute(string $planningId): void
    {
        if ($this->planningRepository->countAttributedRendezVous($planningId) > 0) {
            throw new PlanningNonModifiableException();
        }
    }
}
