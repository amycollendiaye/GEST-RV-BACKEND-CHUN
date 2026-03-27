<?php

namespace App\Services\Me;

use App\Enums\StatutConsultation;
use App\Models\Patient;
use App\Models\PersonelHopital;
use App\Models\RendezVous;

class GetSecretaireMeService
{
    public function execute(PersonelHopital $secretaire): array
    {
        $secretaire->load(['infosConnexion', 'serviceMedical']);

        $moisCourant = now()->month;
        $anneeCourante = now()->year;

        $listeRendezVousDuJour = RendezVous::query()
            ->with(['patient', 'serviceMedical'])
            ->whereDate('date_rendez_vous', today())
            ->orderBy('date_rendez_vous')
            ->limit(20)
            ->get()
            ->map(function (RendezVous $rendezVous): array {
                return [
                    'id' => $rendezVous->id,
                    'patient_nom' => $rendezVous->patient
                        ? trim($rendezVous->patient->nom . ' ' . $rendezVous->patient->prenom)
                        : null,
                    'service_nom' => $rendezVous->serviceMedical?->nom,
                    'heure_approximative' => $rendezVous->date_rendez_vous?->format('H:i'),
                    'statut' => $rendezVous->statut,
                ];
            })
            ->values()
            ->all();

        $totalRendezVousMois = RendezVous::query()
            ->whereMonth('date_rendez_vous', $moisCourant)
            ->whereYear('date_rendez_vous', $anneeCourante)
            ->count();

        $rendezVousAnnulesMois = RendezVous::query()
            ->whereMonth('date_rendez_vous', $moisCourant)
            ->whereYear('date_rendez_vous', $anneeCourante)
            ->where('statut', StatutConsultation::ANNULER->value)
            ->count();

        return [
            'utilisateur' => [
                'id' => $secretaire->id,
                'matricule' => $secretaire->matricule,
                'nom' => $secretaire->nom,
                'prenom' => $secretaire->prenom,
                'email' => $secretaire->email,
                'telephone' => $secretaire->telephone,
                'login' => $secretaire->infosConnexion?->login,
                'role' => $secretaire->role,
                'statut' => $secretaire->statut,
                'first_login' => $secretaire->infosConnexion?->first_login,
                'created_at' => $secretaire->created_at?->toISOString(),
            ],
            'service' => $secretaire->serviceMedical ? [
                'id' => $secretaire->serviceMedical->id,
                'nom' => $secretaire->serviceMedical->nom,
                'heure_ouverture' => $secretaire->serviceMedical->heure_ouverture,
                'heure_fermeture' => $secretaire->serviceMedical->heure_fermeture,
            ] : null,
            'activite_jour' => [
                'nombre_patients_enregistres_aujourd_hui' => Patient::query()
                    ->whereDate('created_at', today())
                    ->count(),
                'nombre_rendez_vous_aujourd_hui' => RendezVous::query()
                    ->whereDate('date_rendez_vous', today())
                    ->count(),
                'nombre_rendez_vous_annules_aujourd_hui' => RendezVous::query()
                    ->whereDate('date_rendez_vous', today())
                    ->where('statut', StatutConsultation::ANNULER->value)
                    ->count(),
                'liste_rendez_vous_du_jour' => $listeRendezVousDuJour,
            ],
            'statistiques_rapides' => [
                'nombre_patients_enregistres_mois' => Patient::query()
                    ->whereMonth('created_at', $moisCourant)
                    ->whereYear('created_at', $anneeCourante)
                    ->count(),
                'nombre_rendez_vous_annules_mois' => $rendezVousAnnulesMois,
                'taux_annulation_mois' => $this->calculateRate($rendezVousAnnulesMois, $totalRendezVousMois),
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
