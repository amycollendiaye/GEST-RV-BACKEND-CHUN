<?php

namespace App\Services\RendezVous;

use App\Exceptions\ConsultationNonTermineeException;
use App\Models\RendezVous;

class VerifierConsultationPrecedenteService
{
    public function execute(string $patientId, string $serviceMedicalId): void
    {
        $exists = RendezVous::where('patient_id', $patientId)
            ->where('service_medical_id', $serviceMedicalId)
            ->whereNotIn('statut', ['FAIT', 'ANNULER'])
            ->exists();

        if ($exists) {
            throw new ConsultationNonTermineeException();
        }
    }
}
