<?php

namespace App\Services\PlanningMedecin;

use App\Models\PlanningMedecin;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;

class DeletePlanningService
{
    public function __construct(
        private readonly PlanningMedecinRepositoryInterface $planningRepository,
        private readonly VerifierPlanningModifiableService $verifierPlanningModifiableService
    ) {
    }

    public function execute(PlanningMedecin $planning): bool
    {
        $this->verifierPlanningModifiableService->execute($planning->id);

        return $this->planningRepository->delete($planning->id);
    }
}
