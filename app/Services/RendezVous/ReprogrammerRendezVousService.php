<?php

namespace App\Services\RendezVous;

use App\Events\RendezVousReprogramme;
use App\Models\Consultation;
use App\Models\RendezVous;
use Illuminate\Support\Facades\DB;

class ReprogrammerRendezVousService
{
    public function __construct(
        private readonly AttributionAutomatiqueRendezVousService $attributionAutomatiqueRendezVousService
    ) {
    }

    public function execute(Consultation $consultation, string $motifSuivi): RendezVous
    {
        return DB::transaction(function () use ($consultation, $motifSuivi) {
            $consultation->loadMissing(['rendezVous.serviceMedical', 'rendezVous.medecin']);

            $rendezVousActuel = $consultation->rendezVous;
            if (!$rendezVousActuel) {
                abort(404, 'Rendez-vous introuvable');
            }

            $consultation->wasReprogrammed = true;
            $consultation->update(['statut' => 'FAIT']);
            $rendezVousActuel->update(['statut' => 'FAIT']);

            $nouveauRendezVous = $this->attributionAutomatiqueRendezVousService->assignForPatient(
                $consultation->patient_id,
                [
                    'service_medical_id' => $rendezVousActuel->service_medical_id,
                    'motif' => $motifSuivi,
                    'audit_context' => [
                        'type' => 'REPROG',
                        'ancien_rendez_vous' => [
                            'date' => $rendezVousActuel->date_rendez_vous?->format('Y-m-d H:i:s'),
                            'service' => $rendezVousActuel->serviceMedical?->nom,
                        ],
                    ],
                ]
            );

            DB::afterCommit(function () use ($rendezVousActuel, $nouveauRendezVous) {
                event(new RendezVousReprogramme(
                    $rendezVousActuel->load(['patient', 'medecin', 'serviceMedical']),
                    $nouveauRendezVous->load(['patient', 'medecin', 'serviceMedical'])
                ));
            });

            return $nouveauRendezVous;
        });
    }
}
