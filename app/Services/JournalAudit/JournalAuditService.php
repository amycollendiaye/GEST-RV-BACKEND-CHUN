<?php

namespace App\Services\JournalAudit;

use App\Enums\TypeAction;
use App\Models\PersonelHopital;
use App\Repositories\Interfaces\JournalAuditRepositoryInterface;
use Illuminate\Support\Facades\DB;
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
        $payload = [
            'personel_hopital_id' => $this->resolveAuteurId($personelHopitalId),
            'type_action' => $typeAction->value,
            'details' => $details,
            'adresse_ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ];

        try {
            if (DB::transactionLevel() > 0) {
                DB::afterCommit(function () use ($payload, $typeAction, $personelHopitalId, $details): void {
                    $this->persist($payload, $typeAction, $personelHopitalId, $details);
                });

                return;
            }

            $this->persist($payload, $typeAction, $personelHopitalId, $details);
        } catch (Throwable $exception) {
            Log::error('Echec de journalisation d\'audit.', [
                'type_action' => $typeAction->value,
                'personel_hopital_id' => $personelHopitalId,
                'details' => $details,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function persist(array $payload, TypeAction $typeAction, ?string $personelHopitalId, array $details): void
    {
        try {
            $this->journalAuditRepository->create($payload);
        } catch (Throwable $exception) {
            Log::error('Echec de journalisation d\'audit.', [
                'type_action' => $typeAction->value,
                'personel_hopital_id' => $personelHopitalId,
                'details' => $details,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveAuteurId(?string $personelHopitalId): ?string
    {
        if ($personelHopitalId) {
            return $personelHopitalId;
        }

        $user = auth()->user();

        return $user instanceof PersonelHopital ? $user->id : null;
    }
}
