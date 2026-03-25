<?php

namespace App\Services\JournalAudit;

use App\Enums\TypeAction;
use App\Models\PersonelHopital;
use App\Repositories\Interfaces\JournalAuditRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class JournalAuditService
{
    public function __construct(
        private readonly JournalAuditRepositoryInterface $journalAuditRepository
    ) {
    }

    public function journaliser(TypeAction $typeAction, array $details, ?string $personelHopitalId = null): void
    {
        try {
            $auteur = $this->resolveAuteur($personelHopitalId);

            $this->journalAuditRepository->create([
                'personel_hopital_id' => $auteur?->id,
                'type_action' => $typeAction->value,
                'details' => $details,
                'adresse_ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Echec de journalisation d\'audit.', [
                'type_action' => $typeAction->value,
                'personel_hopital_id' => $personelHopitalId,
                'details' => $details,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveAuteur(?string $personelHopitalId): ?PersonelHopital
    {
        if ($personelHopitalId) {
            return PersonelHopital::find($personelHopitalId);
        }

        $user = auth()->user();

        return $user instanceof PersonelHopital ? $user : null;
    }
}
