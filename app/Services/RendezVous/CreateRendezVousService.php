<?php

namespace App\Services\RendezVous;

use App\Repositories\Interfaces\RendezVousRepositoryInterface;

class CreateRendezVousService
{
    public function __construct(
        private readonly RendezVousRepositoryInterface $rendezVousRepository,
        private readonly VerifierConsultationPrecedenteService $verifierConsultationPrecedenteService
    ) {
    }

    public function execute(string $patientId, array $data)
    {
        $serviceId = $data['service_medical_id'];

        $this->verifierConsultationPrecedenteService->execute($patientId, $serviceId);

        $payload = array_merge($data, [
            'patient_id' => $patientId,
            'statut' => 'PLANIFIER',
        ]);

        return $this->rendezVousRepository->create($payload);
    }
}
