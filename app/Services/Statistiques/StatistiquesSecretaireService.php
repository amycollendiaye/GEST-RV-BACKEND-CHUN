<?php

namespace App\Services\Statistiques;

use App\Enums\TypeAction;
use App\Models\JournalAudit;
use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Models\RendezVous;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class StatistiquesSecretaireService
{
    public function getStatistiques(PersonelHopital $secretaire): array
    {
        $debutJour = now()->startOfDay();
        $finJour = now()->endOfDay();
        $debutSemaine = now()->startOfWeek();
        $finSemaine = now()->endOfWeek();
        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();

        $rendezVousJour = RendezVous::query()
            ->with(['patient', 'serviceMedical'])
            ->whereBetween('date_rendez_vous', [$debutJour, $finJour]);

        $annulesMois = RendezVous::query()
            ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
            ->where('statut', 'ANNULER')
            ->count();

        $totalMois = RendezVous::query()
            ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
            ->count();

        $journalDisponible = Schema::hasTable('journal_audits');

        return [
            'patients_temps_reel' => [
                'nombre_patients_enregistres_aujourdhui' => Patient::query()->whereBetween('created_at', [$debutJour, $finJour])->count(),
                'nombre_patients_enregistres_cette_semaine' => Patient::query()->whereBetween('created_at', [$debutSemaine, $finSemaine])->count(),
                'nombre_patients_enregistres_ce_mois' => Patient::query()->whereBetween('created_at', [$debutMois, $finMois])->count(),
                'nombre_total_patients_actifs' => Patient::query()->where('statut', 'ACTIF')->count(),
            ],
            'patients_par_periode' => [
                'evolution_30_derniers_jours' => $this->buildDailySeries(
                    Patient::query()
                        ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as periode, count(*) as total")
                        ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), $finJour])
                        ->groupBy('periode')
                        ->orderBy('periode')
                        ->pluck('total', 'periode')
                        ->all()
                ),
                'evolution_6_derniers_mois' => $this->buildMonthlySeries(
                    Patient::query()
                        ->selectRaw("to_char(created_at, 'YYYY-MM') as periode, count(*) as total")
                        ->whereBetween('created_at', [now()->subMonths(5)->startOfMonth(), $finMois])
                        ->groupBy('periode')
                        ->orderBy('periode')
                        ->pluck('total', 'periode')
                        ->all()
                ),
            ],
            'rendez_vous' => [
                'nombre_total_rendez_vous_jour' => (clone $rendezVousJour)->count(),
                'nombre_rendez_vous_planifier_jour' => (clone $rendezVousJour)->where('statut', 'PLANIFIER')->count(),
                'nombre_rendez_vous_annules_jour' => (clone $rendezVousJour)->where('statut', 'ANNULER')->count(),
                'nombre_rendez_vous_annules_mois' => $annulesMois,
                'taux_annulation_mois' => $this->calculerTaux($annulesMois, $totalMois),
                'liste_rendez_vous_jour' => (clone $rendezVousJour)
                    ->orderBy('date_rendez_vous')
                    ->get()
                    ->map(fn ($rendezVous) => [
                        'nom_patient' => $rendezVous->patient ? trim($rendezVous->patient->prenom . ' ' . $rendezVous->patient->nom) : null,
                        'service' => $rendezVous->serviceMedical?->nom,
                        'heure' => $rendezVous->date_rendez_vous?->format('H:i:s'),
                        'statut' => $rendezVous->statut,
                    ])
                    ->all(),
            ],
            'activite' => [
                'nombre_comptes_patients_crees_par_cette_secretaire_mois' => $journalDisponible
                    ? JournalAudit::query()
                        ->where('personel_hopital_id', $secretaire->id)
                        ->where('type_action', TypeAction::CREATIONDOSSIER->value)
                        ->whereBetween('created_at', [$debutMois, $finMois])
                        ->count()
                    : 0,
                'nombre_rendez_vous_reprogrammes_par_cette_secretaire_mois' => $journalDisponible
                    ? JournalAudit::query()
                        ->where('personel_hopital_id', $secretaire->id)
                        ->where('type_action', TypeAction::REPROG->value)
                        ->whereBetween('created_at', [$debutMois, $finMois])
                        ->count()
                    : 0,
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

    private function buildDailySeries(array $series): array
    {
        $resultat = [];

        for ($jour = 29; $jour >= 0; $jour--) {
            $date = Carbon::now()->subDays($jour);
            $cle = $date->format('Y-m-d');
            $resultat[] = [
                'date' => $date->format('Y-m-d'),
                'nombre' => (int) ($series[$cle] ?? 0),
            ];
        }

        return $resultat;
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
