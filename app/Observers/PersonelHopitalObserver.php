<?php

namespace App\Observers;

use App\Enums\TypeAction;
use App\Models\PersonelHopital;
use App\Services\JournalAudit\JournalAuditService;

class PersonelHopitalObserver
{
    public function __construct(
        private readonly JournalAuditService $journalAuditService
    ) {
    }

    public function created(PersonelHopital $personelHopital): void
    {
        $this->journalAuditService->journaliser(TypeAction::CREATIONPERSONNEL, [
            'matricule_cree' => $personelHopital->matricule,
            'role_cree' => $personelHopital->role,
            'nom_cree' => trim($personelHopital->prenom . ' ' . $personelHopital->nom),
            'createur_login' => auth()->user()?->infosConnexion?->login,
        ]);
    }

    public function updated(PersonelHopital $personelHopital): void
    {
        $champsModifies = $this->champsModifies($personelHopital);

        if ($champsModifies === []) {
            return;
        }

        $this->journalAuditService->journaliser(TypeAction::MODIFICATIONPERSONNEL, [
            'champs_modifies' => $champsModifies,
            'modificateur_login' => auth()->user()?->infosConnexion?->login,
        ]);
    }

    public function deleted(PersonelHopital $personelHopital): void
    {
        $this->journalAuditService->journaliser(TypeAction::SUPPRESSIONPERSONNEL, [
            'matricule_supprime' => $personelHopital->matricule,
            'role_supprime' => $personelHopital->role,
            'suppresseur_login' => auth()->user()?->infosConnexion?->login,
        ]);
    }

    private function champsModifies(PersonelHopital $personelHopital): array
    {
        return array_values(array_filter(
            array_keys($personelHopital->getChanges()),
            static fn (string $champ) => !in_array($champ, ['updated_at', 'deleted_at'], true)
        ));
    }
}
