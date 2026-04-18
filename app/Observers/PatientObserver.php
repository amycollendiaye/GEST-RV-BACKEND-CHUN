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
        try {
            $createur = auth()->user();
            
            // Éviter d'accéder à la relation infosConnexion à l'intérieur d'une transaction
            // car cela peut causer des problèmes de deadlock avec PostgreSQL
            $createurLogin = null;
            $createurRole = null;
            
            if ($createur instanceof PersonelHopital) {
                // Utiliser le matricule au lieu de la relation pour éviter les problèmes
                $createurLogin = $createur->matricule;
                $createurRole = $createur->role;
            }

            $this->journalAuditService->journaliser(TypeAction::CREATIONDOSSIER, [
                'matricule_patient' => $patient->matricule,
                'nom_patient' => trim($patient->prenom . ' ' . $patient->nom),
                'createur_login' => $createurLogin,
                'createur_role' => $createurRole,
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer la création du patient si le audit échoue
            report($e);
        }
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
