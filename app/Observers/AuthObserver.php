<?php

namespace App\Observers;

use App\Enums\TypeAction;
use App\Models\PersonelHopital;
use App\Services\JournalAudit\JournalAuditService;

class AuthObserver
{
    public function __construct(
        private readonly JournalAuditService $journalAuditService
    ) {
    }

    public function connexion(?PersonelHopital $auteur, array $details): void
    {
        $this->journalAuditService->journaliser(
            TypeAction::CONNEXION,
            $details,
            $auteur?->id
        );
    }

    public function deconnexion(?PersonelHopital $auteur, array $details): void
    {
        $this->journalAuditService->journaliser(
            TypeAction::DECONNEXION,
            $details,
            $auteur?->id
        );
    }
}
