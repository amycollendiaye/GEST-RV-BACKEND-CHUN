<?php

namespace App\Services\Me;

use App\Models\Consultation;
use App\Models\JournalAudit;
use App\Models\Patient;
use App\Models\PersonelHopital;
use Illuminate\Support\Str;

class GetAdminMeService
{
    public function execute(PersonelHopital $admin): array
    {
        $admin->load(['infosConnexion']);

        $dernieresActions = JournalAudit::query()
            ->with('auteur')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (JournalAudit $journalAudit): array {
                $details = is_array($journalAudit->details)
                    ? json_encode($journalAudit->details, JSON_UNESCAPED_UNICODE)
                    : (string) $journalAudit->details;

                return [
                    'id' => $journalAudit->id,
                    'type_action' => $journalAudit->type_action,
                    'details' => Str::limit((string) $details, 80),
                    'auteur_nom' => $journalAudit->auteur
                        ? trim($journalAudit->auteur->nom . ' ' . $journalAudit->auteur->prenom)
                        : null,
                    'auteur_role' => $journalAudit->auteur?->role,
                    'created_at' => $journalAudit->created_at?->toISOString(),
                ];
            })
            ->values()
            ->all();

        return [
            'utilisateur' => [
                'id' => $admin->id,
                'matricule' => $admin->matricule,
                'nom' => $admin->nom,
                'prenom' => $admin->prenom,
                'email' => $admin->email,
                'telephone' => $admin->telephone,
                'login' => $admin->infosConnexion?->login,
                'role' => $admin->role,
                'statut' => $admin->statut,
                'first_login' => $admin->infosConnexion?->first_login,
                'created_at' => $admin->created_at?->toISOString(),
            ],
            'statistiques_rapides' => [
                'nombre_medecins_actifs' => PersonelHopital::query()->medecins()->actifs()->count(),
                'nombre_secretaires_actives' => PersonelHopital::query()->secretaires()->actifs()->count(),
                'nombre_patients_total' => Patient::query()->count(),
                'nombre_rendez_vous_aujourd_hui' => Consultation::query()
                    ->whereDate('created_at', today())
                    ->count(),
                'nombre_actions_journal_aujourd_hui' => JournalAudit::query()
                    ->whereDate('created_at', today())
                    ->count(),
            ],
            'dernieres_actions' => $dernieresActions,
        ];
    }
}
