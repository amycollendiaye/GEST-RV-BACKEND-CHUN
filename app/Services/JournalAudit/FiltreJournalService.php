<?php

namespace App\Services\JournalAudit;

use App\Models\JournalAudit;
use App\Repositories\Interfaces\JournalAuditRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FiltreJournalService
{
    public function __construct(
        private readonly JournalAuditRepositoryInterface $journalAuditRepository
    ) {
    }

    public function paginer(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->journalAuditRepository->paginate($filters, $perPage);
    }

    public function exporterCsv(array $filters): StreamedResponse
    {
        $nomFichier = 'journal_audit_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, [
                'ID',
                'Date et Heure',
                'Type Action',
                'Auteur Login',
                'Auteur Rôle',
                'Détails résumé',
                'Adresse IP',
            ]);

            foreach ($this->journalAuditRepository->cursor($filters) as $journalAudit) {
                fputcsv($handle, [
                    $journalAudit->id,
                    $journalAudit->created_at?->format('d/m/Y H:i:s'),
                    $journalAudit->type_action,
                    $journalAudit->auteur?->infosConnexion?->login,
                    $journalAudit->auteur?->role,
                    $this->resumerDetails($journalAudit),
                    $journalAudit->adresse_ip,
                ]);
            }

            fclose($handle);
        }, $nomFichier, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $nomFichier . '"',
        ]);
    }

    private function resumerDetails(JournalAudit $journalAudit): string
    {
        $morceaux = [];

        array_walk_recursive($journalAudit->details ?? [], function ($value, $key) use (&$morceaux): void {
            if ($value === null || $value === '') {
                return;
            }

            $morceaux[] = $key . ': ' . (is_bool($value) ? ($value ? 'oui' : 'non') : $value);
        });

        return implode(' | ', $morceaux);
    }
}
