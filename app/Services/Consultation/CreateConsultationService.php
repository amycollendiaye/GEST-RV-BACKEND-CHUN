<?php

namespace App\Services\Consultation;

use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;
use App\Repositories\Interfaces\ConsultationRepositoryInterface;
use App\Repositories\Interfaces\RendezVousRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateConsultationService
{
    public function __construct(
        private readonly ConsultationRepositoryInterface $consultationRepository,
        private readonly RendezVousRepositoryInterface $rendezVousRepository,
        private readonly DossierMedicalRepositoryInterface $dossierMedicalRepository
    ) {
    }

    public function execute(array $data, string $medecinId)
    {
        return DB::transaction(function () use ($data, $medecinId) {
            $rendezVous = $this->rendezVousRepository->findById($data['rendez_vous_id']);

            if (!$rendezVous) {
                abort(404, 'Rendez-vous introuvable');
            }

            if ($rendezVous->medecin_id && $rendezVous->medecin_id !== $medecinId) {
                abort(403, 'Ce rendez-vous n est pas attribue a ce medecin.');
            }

            if ($rendezVous->consultation) {
                abort(409, 'Une consultation existe deja pour ce rendez-vous.');
            }

            $consultation = $this->consultationRepository->create([
                'rendez_vous_id' => $rendezVous->id,
                'patient_id' => $rendezVous->patient_id,
                'medecin_id' => $medecinId,
                'tension_artielle' => $data['tension_artielle'],
                'poids' => $data['poids'],
                'temperature' => $data['temperature'],
                'sumptomes' => $data['sumptomes'],
                'diagnostic' => $data['diagnostic'],
                'traitement' => $data['traitement'],
                'observations' => $data['observations'] ?? null,
                'date_heure' => now(),
                'statut' => 'EN_COURS',
            ]);

            if (!empty($data['mise_a_jour_dossier'])) {
                $dossier = $this->dossierMedicalRepository->findByPatientId($rendezVous->patient_id);
                if ($dossier) {
                    $dossier->update(array_filter([
                        'maladies_chroniques' => $data['mise_a_jour_dossier']['maladies_chroniques'] ?? null,
                        'traitements_en_cours' => $data['mise_a_jour_dossier']['traitements_en_cours'] ?? null,
                    ], fn ($value) => $value !== null));
                }
            }

            return $consultation->fresh(['patient', 'medecin', 'rendezVous.serviceMedical']);
        });
    }
}
