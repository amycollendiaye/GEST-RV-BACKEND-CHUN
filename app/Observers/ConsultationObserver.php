<?php

namespace App\Observers;

use App\Enums\TypeAction;
use App\Models\Consultation;
use App\Services\JournalAudit\JournalAuditService;
use Illuminate\Support\Str;

class ConsultationObserver
{
    public function __construct(
        private readonly JournalAuditService $journalAuditService
    ) {
    }

    public function created(Consultation $consultation): void
    {
        $consultation->loadMissing(['patient', 'medecin', 'rendezVous.serviceMedical']);

        $this->journalAuditService->journaliser(TypeAction::ENRCONSUL, [
            'matricule_patient' => $consultation->patient?->matricule,
            'nom_patient' => $consultation->patient ? trim($consultation->patient->prenom . ' ' . $consultation->patient->nom) : null,
            'medecin_nom' => $consultation->medecin ? trim($consultation->medecin->prenom . ' ' . $consultation->medecin->nom) : null,
            'service_nom' => $consultation->rendezVous?->serviceMedical?->nom,
            'diagnostic_resume' => Str::limit((string) $consultation->diagnostic, 100, ''),
        ]);
    }

    public function updated(Consultation $consultation): void
    {
        if (!$consultation->wasChanged('statut') || $consultation->statut !== 'FAIT') {
            return;
        }

        $consultation->loadMissing(['patient', 'medecin']);

        $this->journalAuditService->journaliser(TypeAction::CLOTURECONSUL, [
            'matricule_patient' => $consultation->patient?->matricule,
            'medecin_nom' => $consultation->medecin ? trim($consultation->medecin->prenom . ' ' . $consultation->medecin->nom) : null,
            'statut_final' => $consultation->statut,
            'reprogramme' => $consultation->wasReprogrammed,
        ]);
    }
}
