<?php

namespace App\Services\Consultation;

use App\Repositories\Interfaces\ConsultationRepositoryInterface;

class UpdateConsultationService
{
    public function __construct(
        private readonly ConsultationRepositoryInterface $consultationRepository
    ) {
    }

    public function execute(string $id, array $data)
    {
        $allowed = [
            'tensionArtielle',
            'poids',
            'temperature',
            'sumptomes',
            'diagnostic',
            'traitement',
            'observations',
        ];

        $payload = array_intersect_key($data, array_flip($allowed));

        $mapped = [
            'tension_artielle' => $payload['tensionArtielle'] ?? null,
            'poids' => $payload['poids'] ?? null,
            'temperature' => $payload['temperature'] ?? null,
            'sumptomes' => $payload['sumptomes'] ?? null,
            'diagnostic' => $payload['diagnostic'] ?? null,
            'traitement' => $payload['traitement'] ?? null,
            'observations' => $payload['observations'] ?? null,
        ];

        $mapped = array_filter($mapped, static fn ($value) => $value !== null);

        return $this->consultationRepository->update($id, $mapped);
    }
}
