<?php

namespace App\Services\RendezVous;

use App\Exceptions\AnnulationImpossibleException;
use App\Models\RendezVous;
use App\Repositories\Interfaces\RendezVousRepositoryInterface;
use Illuminate\Support\Carbon;

class AnnulerRendezVousService
{
    public function __construct(
        private readonly RendezVousRepositoryInterface $rendezVousRepository
    ) {
    }

    public function execute(RendezVous $rendezVous, string $patientId)
    {
        if ($rendezVous->patient_id !== $patientId) {
            abort(403, 'Non autorisé');
        }

        if ($rendezVous->date_rendez_vous) {
            if ($rendezVous->date_rendez_vous->lessThanOrEqualTo(Carbon::now()->addHours(24))) {
                throw new AnnulationImpossibleException();
            }
        }

        return $this->rendezVousRepository->update($rendezVous->id, [
            'statut' => 'ANNULER',
        ]);
    }
}
