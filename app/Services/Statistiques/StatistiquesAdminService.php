<?php

namespace App\Services\Statistiques;

use App\Enums\TypeAction;
use App\Models\JournalAudit;
use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Models\RendezVous;
use App\Models\ServiceMedical;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class StatistiquesAdminService
{
    public function getStatistiques(): array
    {
        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();

        $totalRendezVousMois = RendezVous::query()
            ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
            ->count();

        $rendezVousPlanifies = RendezVous::query()
            ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
            ->where('statut', 'PLANIFIER')
            ->count();

        $rendezVousFaits = RendezVous::query()
            ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
            ->where('statut', 'FAIT')
            ->count();

        $rendezVousAnnules = RendezVous::query()
            ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
            ->where('statut', 'ANNULER')
            ->count();

        $journalDisponible = Schema::hasTable('journal_audits');

        return [
            'services' => [
                'nombre_total_services_actifs' => ServiceMedical::query()->where('etat', 'DISPONIBLE')->count(),
                'nombre_services_sans_medecin_affecte' => ServiceMedical::query()
                    ->whereDoesntHave('medecins', fn ($query) => $query->where('statut', 'ACTIF'))
                    ->count(),
                'top_5_services_rendez_vous_mois' => ServiceMedical::query()
                    ->select('service_medicals.id', 'service_medicals.nom')
                    ->selectSub(function ($query) use ($debutMois, $finMois) {
                        $query->from('rendez_vous')
                            ->selectRaw('count(*)')
                            ->whereColumn('rendez_vous.service_medical_id', 'service_medicals.id')
                            ->whereBetween('date_rendez_vous', [$debutMois, $finMois]);
                    }, 'nombre_rendez_vous')
                    ->orderByDesc('nombre_rendez_vous')
                    ->limit(5)
                    ->get()
                    ->map(fn ($service) => [
                        'nom_service' => $service->nom,
                        'nombre_rendez_vous' => (int) $service->nombre_rendez_vous,
                    ])
                    ->all(),
            ],
            'personnel' => [
                'nombre_total_medecins_actifs' => PersonelHopital::query()->where('role', 'MEDECIN')->where('statut', 'ACTIF')->count(),
                'nombre_total_secretaires_actives' => PersonelHopital::query()->where('role', 'SECRETAIRE')->where('statut', 'ACTIF')->count(),
                'nombre_personnels_en_conge' => PersonelHopital::query()->where('statut', 'ENCONGE')->count(),
                'nombre_personnels_inactifs' => PersonelHopital::query()->where('statut', 'INACTIF')->count(),
            ],
            'rendez_vous' => [
                'nombre_total_mois_courant' => $totalRendezVousMois,
                'nombre_statut_planifier' => $rendezVousPlanifies,
                'nombre_statut_fait' => $rendezVousFaits,
                'nombre_statut_annuler' => $rendezVousAnnules,
                'taux_annulation' => $this->calculerTaux($rendezVousAnnules, $totalRendezVousMois),
                'taux_realisation' => $this->calculerTaux($rendezVousFaits, $totalRendezVousMois),
                'evolution_6_derniers_mois' => $this->buildMonthlySeries(
                    RendezVous::query()
                        ->selectRaw("to_char(date_rendez_vous, 'YYYY-MM') as periode, count(*) as total")
                        ->whereBetween('date_rendez_vous', [now()->subMonths(5)->startOfMonth(), $finMois])
                        ->groupBy('periode')
                        ->orderBy('periode')
                        ->pluck('total', 'periode')
                        ->all()
                ),
            ],
            'patients' => [
                'nombre_total_patients' => Patient::query()->count(),
                'nombre_nouveaux_patients_mois_courant' => Patient::query()->whereBetween('created_at', [$debutMois, $finMois])->count(),
                'nombre_patients_ayant_consultation_mois' => DB::table('consultations')
                    ->whereBetween('date_heure', [$debutMois, $finMois])
                    ->distinct('patient_id')
                    ->count('patient_id'),
            ],
            'journal' => [
                'nombre_total_actions_aujourdhui' => $journalDisponible
                    ? JournalAudit::query()->whereDate('created_at', today())->count()
                    : 0,
                'nombre_connexions_aujourdhui' => $journalDisponible
                    ? JournalAudit::query()
                        ->whereDate('created_at', today())
                        ->where('type_action', TypeAction::CONNEXION->value)
                        ->count()
                    : 0,
                'dernieres_5_actions' => $journalDisponible
                    ? JournalAudit::query()
                        ->with(['auteur.infosConnexion'])
                        ->latest('created_at')
                        ->limit(5)
                        ->get()
                        ->map(fn (JournalAudit $journalAudit) => [
                            'type' => $journalAudit->type_action,
                            'auteur' => $journalAudit->auteur?->infosConnexion?->login
                                ?? ($journalAudit->auteur ? trim($journalAudit->auteur->prenom . ' ' . $journalAudit->auteur->nom) : null),
                            'heure' => $journalAudit->created_at?->format('H:i:s'),
                        ])
                        ->all()
                    : [],
            ],
        ];
    }

    private function calculerTaux(int $numerateur, int $denominateur): float
    {
        if ($denominateur === 0) {
            return 0.0;
        }

        return round(($numerateur / $denominateur) * 100, 2);
    }

    private function buildMonthlySeries(array $series): array
    {
        $resultat = [];

        for ($mois = 5; $mois >= 0; $mois--) {
            $date = Carbon::now()->subMonths($mois);
            $cle = $date->format('Y-m');
            $resultat[] = [
                'mois' => $date->translatedFormat('M Y'),
                'nombre' => (int) ($series[$cle] ?? 0),
            ];
        }

        return $resultat;
    }
}
