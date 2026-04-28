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

    public function exporter(array $filters, string $format = 'csv'): StreamedResponse
    {
        return $format === 'excel'
            ? $this->exporterExcel($filters)
            : $this->exporterCsv($filters);
    }

    public function exporterCsv(array $filters): StreamedResponse
    {
        $nomFichier = 'journal_audit_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Date et Heure',
                'Type Action',
                'Auteur',
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
                    $this->getAuteurNom($journalAudit),
                    $journalAudit->auteur?->infosConnexion?->login,
                    $journalAudit->auteur?->role,
                    $this->resumerDetails($journalAudit),
                    $journalAudit->adresse_ip,
                ]);
            }

            fclose($handle);
        }, $nomFichier, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomFichier . '"',
        ]);
    }

    public function exporterExcel(array $filters): StreamedResponse
    {
        $nomFichier = 'journal_audit_' . now()->format('Y-m-d') . '.xls';

        return response()->streamDownload(function () use ($filters): void {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
            echo 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
            echo 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
            echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
            echo '<Worksheet ss:Name="Journal audit"><Table>';

            $headers = [
                'ID',
                'Date et Heure',
                'Type Action',
                'Auteur',
                'Auteur Login',
                'Auteur Rôle',
                'Détails résumé',
                'Adresse IP',
            ];

            echo '<Row>';
            foreach ($headers as $header) {
                echo '<Cell><Data ss:Type="String">' . $this->escapeExcel($header) . '</Data></Cell>';
            }
            echo '</Row>';

            foreach ($this->journalAuditRepository->cursor($filters) as $journalAudit) {
                $ligne = [
                    (string) $journalAudit->id,
                    $journalAudit->created_at?->format('d/m/Y H:i:s') ?? '',
                    $journalAudit->type_action ?? '',
                    $this->getAuteurNom($journalAudit),
                    $journalAudit->auteur?->infosConnexion?->login ?? '',
                    $journalAudit->auteur?->role ?? '',
                    $this->resumerDetails($journalAudit),
                    $journalAudit->adresse_ip ?? '',
                ];

                echo '<Row>';
                foreach ($ligne as $cellule) {
                    echo '<Cell><Data ss:Type="String">' . $this->escapeExcel($cellule) . '</Data></Cell>';
                }
                echo '</Row>';
            }

            echo '</Table></Worksheet></Workbook>';
        }, $nomFichier, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
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

    private function getAuteurNom(JournalAudit $journalAudit): string
    {
        if (!$journalAudit->auteur) {
            return 'Système';
        }

        return trim(($journalAudit->auteur->prenom ?? '') . ' ' . ($journalAudit->auteur->nom ?? ''));
    }

    private function escapeExcel(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
