<?php

namespace App\Services\RendezVous;

use App\Exceptions\RendezVousDejaExistantException;
use App\Models\RendezVous;

class VerifierContrainteServiceService
{
    public function execute(string $patientId, string $serviceMedicalId): void
    {
        $exists = RendezVous::where('patient_id', $patientId)
            ->where('service_medical_id', $serviceMedicalId)
            ->where('statut', 'PLANIFIER')
            ->exists();

        if ($exists) {
            throw new RendezVousDejaExistantException();
        }
    }
}
