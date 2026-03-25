<?php

namespace App\Services\Statistiques;

use App\Enums\TypeAction;
use App\Models\JournalAudit;
use App\Models\PersonelHopital;
use App\Models\PlanningMedecin;
use App\Models\RendezVous;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatistiquesMedecinService
{
    public function getStatistiques(PersonelHopital $medecin): array
    {
        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();
        $aujourdhui = now()->toDateString();

        $planningsMois = PlanningMedecin::query()
            ->where('medecin_id', $medecin->id)
            ->whereBetween('date', [$debutMois->toDateString(), $finMois->toDateString()]);

        $creneauxTotaux = (int) (clone $planningsMois)->sum('capacite');
        $creneauxOccupes = RendezVous::query()
            ->where('medecin_id', $medecin->id)
            ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
            ->where('statut', '!=', 'ANNULER')
            ->count();

        $totalConsultations = DB::table('consultations')
            ->where('medecin_id', $medecin->id)
            ->count();

        $journalDisponible = Schema::hasTable('journal_audits');
        $consultationsReprogrammees = $journalDisponible
            ? JournalAudit::query()
                ->where('personel_hopital_id', $medecin->id)
                ->where('type_action', TypeAction::REPROG->value)
                ->count()
            : 0;

        $premieresConsultationsMois = DB::table('consultations')
            ->select('patient_id', DB::raw('min(date_heure) as premiere_date'))
            ->where('medecin_id', $medecin->id)
            ->groupBy('patient_id');

        return [
            'planning' => [
                'nombre_plannings_crees_mois' => PlanningMedecin::query()
                    ->where('medecin_id', $medecin->id)
                    ->whereBetween('created_at', [$debutMois, $finMois])
                    ->count(),
                'nombre_creneaux_totaux_disponibles_mois' => $creneauxTotaux,
                'nombre_creneaux_deja_occupes_mois' => $creneauxOccupes,
                'taux_remplissage' => $this->calculerTaux($creneauxOccupes, $creneauxTotaux),
            ],
            'consultations' => [
                'nombre_total_consultations' => $totalConsultations,
                'nombre_consultations_mois_courant' => DB::table('consultations')
                    ->where('medecin_id', $medecin->id)
                    ->whereBetween('date_heure', [$debutMois, $finMois])
                    ->count(),
                'nombre_consultations_jour_courant' => DB::table('consultations')
                    ->where('medecin_id', $medecin->id)
                    ->whereDate('date_heure', $aujourdhui)
                    ->count(),
                'nombre_consultations_ayant_entraine_reprogrammation' => $consultationsReprogrammees,
                'taux_reprogrammation' => $this->calculerTaux($consultationsReprogrammees, $totalConsultations),
                'evolution_6_derniers_mois' => $this->buildMonthlySeries(
                    DB::table('consultations')
                        ->selectRaw("to_char(date_heure, 'YYYY-MM') as periode, count(*) as total")
                        ->where('medecin_id', $medecin->id)
                        ->whereBetween('date_heure', [now()->subMonths(5)->startOfMonth(), $finMois])
                        ->groupBy('periode')
                        ->orderBy('periode')
                        ->pluck('total', 'periode')
                        ->all()
                ),
            ],
            'patients' => [
                'nombre_total_patients_distincts_consultes' => DB::table('consultations')
                    ->where('medecin_id', $medecin->id)
                    ->distinct('patient_id')
                    ->count('patient_id'),
                'nombre_nouveaux_patients_vus_mois' => DB::query()
                    ->fromSub($premieresConsultationsMois, 'premieres_consultations')
                    ->whereBetween('premiere_date', [$debutMois, $finMois])
                    ->count(),
                'top_5_pathologies_frequentes' => DB::table('consultations')
                    ->selectRaw('diagnostic, count(*) as occurrences')
                    ->where('medecin_id', $medecin->id)
                    ->whereNotNull('diagnostic')
                    ->where('diagnostic', '!=', '')
                    ->groupBy('diagnostic')
                    ->orderByDesc('occurrences')
                    ->limit(5)
                    ->get()
                    ->map(fn ($ligne) => [
                        'pathologie' => $ligne->diagnostic,
                        'occurrences' => (int) $ligne->occurrences,
                    ])
                    ->all(),
            ],
            'rendez_vous' => [
                'nombre_rendez_vous_planifier' => RendezVous::query()
                    ->where('medecin_id', $medecin->id)
                    ->where('statut', 'PLANIFIER')
                    ->count(),
                'prochain_rendez_vous' => $this->prochainRendezVous($medecin),
                'nombre_rendez_vous_annules_mois' => RendezVous::query()
                    ->where('medecin_id', $medecin->id)
                    ->where('statut', 'ANNULER')
                    ->whereBetween('date_rendez_vous', [$debutMois, $finMois])
                    ->count(),
            ],
        ];
    }

    private function prochainRendezVous(PersonelHopital $medecin): ?array
    {
        $rendezVous = RendezVous::query()
            ->with('patient')
            ->where('medecin_id', $medecin->id)
            ->where('statut', 'PLANIFIER')
            ->where('date_rendez_vous', '>=', now())
            ->orderBy('date_rendez_vous')
            ->first();

        if (!$rendezVous) {
            return null;
        }

        return [
            'date' => $rendezVous->date_rendez_vous?->format('Y-m-d'),
            'heure' => $rendezVous->date_rendez_vous?->format('H:i:s'),
            'nom_patient' => $rendezVous->patient ? trim($rendezVous->patient->prenom . ' ' . $rendezVous->patient->nom) : null,
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
