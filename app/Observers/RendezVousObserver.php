<?php

namespace App\Observers;

use App\Enums\TypeAction;
use App\Models\RendezVous;
use App\Models\PersonelHopital;
use App\Services\JournalAudit\JournalAuditService;

class RendezVousObserver
{
    public function __construct(
        private readonly JournalAuditService $journalAuditService
    ) {
    }

    public function created(RendezVous $rendezVous): void
    {
        $rendezVous->loadMissing(['patient', 'serviceMedical', 'medecin']);

        if (($rendezVous->auditContext['type'] ?? null) === TypeAction::REPROG->value) {
            $this->journalAuditService->journaliser(TypeAction::REPROG, [
                'matricule_patient' => $rendezVous->patient?->matricule,
                'ancien_rendez_vous' => [
                    'date' => $rendezVous->auditContext['ancien_rendez_vous']['date'] ?? null,
                    'service' => $rendezVous->auditContext['ancien_rendez_vous']['service'] ?? null,
                ],
                'nouveau_rendez_vous' => [
                    'date' => $rendezVous->date_rendez_vous?->format('Y-m-d H:i:s'),
                    'service' => $rendezVous->serviceMedical?->nom,
                ],
                'medecin_nom' => $this->nomMedecin($rendezVous),
            ]);

            return;
        }

        $this->journalAuditService->journaliser(TypeAction::CREATIONRV, [
            'matricule_patient' => $rendezVous->patient?->matricule,
            'nom_patient' => $rendezVous->patient ? trim($rendezVous->patient->prenom . ' ' . $rendezVous->patient->nom) : null,
            'service_nom' => $rendezVous->serviceMedical?->nom,
            'date_rendez_vous' => $rendezVous->date_rendez_vous?->format('Y-m-d'),
            'heure_approximative' => $rendezVous->date_rendez_vous?->format('H:i:s'),
            'medecin_nom' => $this->nomMedecin($rendezVous),
        ]);
    }

    public function updated(RendezVous $rendezVous): void
    {
        if (!$rendezVous->wasChanged('statut')) {
            return;
        }

        $rendezVous->loadMissing(['patient', 'serviceMedical', 'medecin']);

        if ($rendezVous->statut === 'ANNULER') {
            $this->journalAuditService->journaliser(TypeAction::ANNULERRV, [
                'matricule_patient' => $rendezVous->patient?->matricule,
                'nom_patient' => $rendezVous->patient ? trim($rendezVous->patient->prenom . ' ' . $rendezVous->patient->nom) : null,
                'service_nom' => $rendezVous->serviceMedical?->nom,
                'date_rendez_vous' => $rendezVous->date_rendez_vous?->format('Y-m-d H:i:s'),
                'motif_annulation' => request()?->input('motif_annulation'),
            ]);
        }
    }

    private function nomMedecin(RendezVous $rendezVous): ?string
    {
        $medecin = $rendezVous->medecin;

        if (!($medecin instanceof PersonelHopital)) {
            return null;
        }

        return trim($medecin->prenom . ' ' . $medecin->nom);
    }
}
