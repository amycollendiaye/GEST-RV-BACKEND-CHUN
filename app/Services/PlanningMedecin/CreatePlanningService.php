<?php

namespace App\Services\PlanningMedecin;

use App\Models\PersonelHopital;
use App\Repositories\Interfaces\PlanningMedecinRepositoryInterface;

class CreatePlanningService
{
    public function __construct(
        private readonly PlanningMedecinRepositoryInterface $planningRepository,
        private readonly VerifierDisponibilitePlanningService $verifierDisponibilitePlanningService
    ) {
    }

    public function execute(PersonelHopital $medecin, array $data)
    {
        if (!$medecin->service_medical_id) {
            abort(422, 'Aucun service medical n est associe a ce medecin.');
        }

        $this->verifierDisponibilitePlanningService->execute(
            $medecin->id,
            $medecin->service_medical_id,
            $data['date']
        );

        return $this->planningRepository->create([
            'medecin_id' => $medecin->id,
            'service_medical_id' => $medecin->service_medical_id,
            'date' => $data['date'],
            'heure_ouverture' => $data['heure_ouverture'],
            'heure_fermeture' => $data['heure_fermeture'],
            'capacite' => $data['capacite'],
        ]);
    }
}
