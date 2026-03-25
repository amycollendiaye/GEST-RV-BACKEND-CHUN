<?php

namespace App\Services\RendezVous;

use App\Events\ConsultationTerminee;
use App\Models\Consultation;
use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CloturerConsultationService
{
    public function __construct(
        private readonly DossierMedicalRepositoryInterface $dossierMedicalRepository
    ) {
    }

    public function execute(Consultation $consultation, ?array $miseAJourDossier = null): Consultation
    {
        return DB::transaction(function () use ($consultation, $miseAJourDossier) {
            $consultation->loadMissing(['rendezVous']);

            $rendezVous = $consultation->rendezVous;
            if (!$rendezVous) {
                abort(404, 'Rendez-vous introuvable');
            }

            if ($miseAJourDossier) {
                $dossier = $this->dossierMedicalRepository->findByPatientId($consultation->patient_id);
                if ($dossier) {
                    $dossier->update(array_filter([
                        'maladies_chroniques' => $miseAJourDossier['maladies_chroniques'] ?? null,
                        'traitements_en_cours' => $miseAJourDossier['traitements_en_cours'] ?? null,
                    ], fn ($value) => $value !== null));
                }
            }

            $consultation->update(['statut' => 'FAIT']);
            $rendezVous->update(['statut' => 'FAIT']);

            DB::afterCommit(function () use ($consultation, $rendezVous) {
                event(new ConsultationTerminee(
                    $consultation->fresh(['patient', 'medecin', 'rendezVous.serviceMedical']),
                    $rendezVous->fresh(['patient', 'medecin', 'serviceMedical'])
                ));
            });

            return $consultation->fresh(['patient', 'medecin', 'rendezVous.serviceMedical']);
        });
    }
}
