<?php

namespace App\Services\PlanningMedecin;

use App\Models\PlanningMedecin;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;

class UpdatePlanningService
{
    public function __construct(
        private readonly PlanningMedecinRepositoryInterface $planningRepository,
        private readonly VerifierDisponibilitePlanningService $verifierDisponibilitePlanningService,
        private readonly VerifierPlanningModifiableService $verifierPlanningModifiableService
    ) {
    }

    public function execute(PlanningMedecin $planning, array $data): PlanningMedecin
    {
        $this->verifierPlanningModifiableService->execute($planning->id);

        $date = $data['date'] ?? $planning->date?->toDateString();
        if ($date) {
            $this->verifierDisponibilitePlanningService->execute(
                $planning->medecin_id,
                $planning->service_medical_id,
                $date,
                $planning->id
            );
        }

        return $this->planningRepository->update($planning->id, $data);
    }
}
