<?php

namespace App\Services\Me;

use App\Enums\StatutConsultation;
use App\Models\Consultation;
use App\Models\PersonelHopital;
use App\Models\PlanningMedecin;
use App\Models\RendezVous;

class GetMedecinMeService
{
    public function execute(PersonelHopital $medecin): array
    {
        $medecin->load(['infosConnexion', 'serviceMedical']);

        $moisCourant = now()->month;
        $anneeCourante = now()->year;

        $prochainRendezVous = RendezVous::query()
            ->with('patient')
            ->where('medecin_id', $medecin->id)
            ->where('date_rendez_vous', '>', now())
            ->orderBy('date_rendez_vous')
            ->first();

        $statsPlanningsMois = PlanningMedecin::query()
            ->leftJoinSub(
                RendezVous::query()
                    ->selectRaw('planning_medecin_id, COUNT(*) as total_attribues')
                    ->whereNull('deleted_at')
                    ->where('statut', '!=', StatutConsultation::ANNULER->value)
                    ->groupBy('planning_medecin_id'),
                'rendez_vous_attribues',
                'rendez_vous_attribues.planning_medecin_id',
                '=',
                'planning_medecins.id'
            )
            ->where('planning_medecins.medecin_id', $medecin->id)
            ->whereMonth('planning_medecins.date', $moisCourant)
            ->whereYear('planning_medecins.date', $anneeCourante)
            ->selectRaw('COALESCE(SUM(planning_medecins.capacite), 0) as total_capacite')
            ->selectRaw('COALESCE(SUM(COALESCE(rendez_vous_attribues.total_attribues, 0)), 0) as total_attribues')
            ->first();

        $totalCapacite = (int) ($statsPlanningsMois->total_capacite ?? 0);
        $totalAttribues = (int) ($statsPlanningsMois->total_attribues ?? 0);

        return [
            'utilisateur' => [
                'id' => $medecin->id,
                'matricule' => $medecin->matricule,
                'nom' => $medecin->nom,
                'prenom' => $medecin->prenom,
                'email' => $medecin->email,
                'telephone' => $medecin->telephone,
                'specialite' => $medecin->specialite,
                'login' => $medecin->infosConnexion?->login,
                'role' => $medecin->role,
                'statut' => $medecin->statut,
                'first_login' => $medecin->infosConnexion?->first_login,
                'created_at' => $medecin->created_at?->toISOString(),
            ],
            'service' => $medecin->serviceMedical ? [
                'id' => $medecin->serviceMedical->id,
                'nom' => $medecin->serviceMedical->nom,
                'heure_ouverture' => $medecin->serviceMedical->heure_ouverture,
                'heure_fermeture' => $medecin->serviceMedical->heure_fermeture,
            ] : null,
            'activite_jour' => [
                'nombre_consultations_aujourd_hui' => Consultation::query()
                    ->where('medecin_id', $medecin->id)
                    ->whereDate('created_at', today())
                    ->count(),
                'nombre_rendez_vous_planifies' => RendezVous::query()
                    ->where('statut', StatutConsultation::PLANIFIER->value)
                    ->whereHas('planningMedecin', function ($query) use ($medecin) {
                        $query->where('medecin_id', $medecin->id)
                            ->whereDate('date', today());
                    })
                    ->count(),
                'prochain_rendez_vous' => $prochainRendezVous ? [
                    'id' => $prochainRendezVous->id,
                    'date_rendez_vous' => $prochainRendezVous->date_rendez_vous?->toISOString(),
                    'heure_approximative' => $prochainRendezVous->date_rendez_vous?->format('H:i'),
                    'motif' => $prochainRendezVous->motif,
                    'patient_nom' => $prochainRendezVous->patient
                        ? trim($prochainRendezVous->patient->nom . ' ' . $prochainRendezVous->patient->prenom)
                        : null,
                ] : null,
                'nombre_plannings_actifs' => PlanningMedecin::query()
                    ->where('medecin_id', $medecin->id)
                    ->whereDate('date', '>', today())
                    ->count(),
            ],
            'statistiques_rapides' => [
                'nombre_patients_distincts' => Consultation::query()
                    ->where('medecin_id', $medecin->id)
                    ->whereMonth('created_at', $moisCourant)
                    ->whereYear('created_at', $anneeCourante)
                    ->distinct('patient_id')
                    ->count('patient_id'),
                'nombre_consultations_mois' => Consultation::query()
                    ->where('medecin_id', $medecin->id)
                    ->whereMonth('created_at', $moisCourant)
                    ->whereYear('created_at', $anneeCourante)
                    ->count(),
                'taux_remplissage_plannings' => $this->calculateRate($totalAttribues, $totalCapacite),
            ],
        ];
    }

    private function calculateRate(int $numerateur, int $denominateur): float
    {
        if ($denominateur === 0) {
            return 0.0;
        }

        return round(($numerateur / $denominateur) * 100, 2);
    }
}
