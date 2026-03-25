<?php

namespace App\Observers;

use App\Enums\TypeAction;
use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Services\JournalAudit\JournalAuditService;

class PatientObserver
{
    public function __construct(
        private readonly JournalAuditService $journalAuditService
    ) {
    }

    public function created(Patient $patient): void
    {
        $createur = auth()->user();

        $this->journalAuditService->journaliser(TypeAction::CREATIONDOSSIER, [
            'matricule_patient' => $patient->matricule,
            'nom_patient' => trim($patient->prenom . ' ' . $patient->nom),
            'createur_login' => $createur instanceof PersonelHopital ? $createur->infosConnexion?->login : null,
            'createur_role' => $createur instanceof PersonelHopital ? $createur->role : null,
        ]);
    }

    public function updated(Patient $patient): void
    {
        $champsModifies = array_values(array_filter(
            array_keys($patient->getChanges()),
            static fn (string $champ) => !in_array($champ, ['updated_at', 'deleted_at'], true)
        ));

        if ($champsModifies === []) {
            return;
        }

        $modificateur = auth()->user();

        $this->journalAuditService->journaliser(TypeAction::MODIFICATIONPATIENT, [
            'champs_modifies' => $champsModifies,
            'modificateur_login' => $modificateur instanceof PersonelHopital ? $modificateur->infosConnexion?->login : null,
        ]);
    }
}
