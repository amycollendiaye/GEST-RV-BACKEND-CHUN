<?php

namespace App\Services\Me;

use App\Enums\StatutConsultation;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\RendezVous;

class GetPatientMeService
{
    public function execute(Patient $patient): array
    {
        $patient->load(['dossierMedical']);

        $dossierMedical = $patient->dossierMedical;
        $prochainRendezVous = RendezVous::query()
            ->with('serviceMedical')
            ->where('patient_id', $patient->id)
            ->where('statut', StatutConsultation::PLANIFIER->value)
            ->where('date_rendez_vous', '>', now())
            ->orderBy('date_rendez_vous')
            ->first();

        return [
            'utilisateur' => [
                'id' => $patient->id,
                'matricule' => $patient->matricule,
                'nom' => $patient->nom,
                'prenom' => $patient->prenom,
                'email' => $patient->email,
                'telephone' => $patient->telephone,
                'date_naissance' => $patient->date_naissance?->toDateString(),
                'adresse' => $patient->adresse,
                'login' => $patient->login,
                'statut' => $patient->statut,
                'first_login' => $patient->first_login,
                'created_at' => $patient->created_at?->toISOString(),
            ],
            'dossier_medical' => $dossierMedical ? [
                'dossier_id' => $dossierMedical->id,
                'numero_dossier' => $dossierMedical->numero_dossier,
                'groupe_sanguin' => $dossierMedical->groupe_sanguin,
                'has_antecedents' => !empty($dossierMedical->antecedents_medicaux),
                'nombre_consultations_total' => Consultation::query()
                    ->where('patient_id', $patient->id)
                    ->count(),
            ] : null,
            'prochain_rendez_vous' => $prochainRendezVous ? [
                'id' => $prochainRendezVous->id,
                'date_rendez_vous' => $prochainRendezVous->date_rendez_vous?->toISOString(),
                'heure_approximative' => $prochainRendezVous->date_rendez_vous?->format('H:i'),
                'motif' => $prochainRendezVous->motif,
                'statut_rendez' => $prochainRendezVous->statut,
                'service' => $prochainRendezVous->serviceMedical ? [
                    'id' => $prochainRendezVous->serviceMedical->id,
                    'nom' => $prochainRendezVous->serviceMedical->nom,
                ] : null,
            ] : null,
            'statistiques_personnelles' => [
                'nombre_rendez_vous_total' => RendezVous::query()
                    ->where('patient_id', $patient->id)
                    ->count(),
                'nombre_rendez_vous_planifies' => RendezVous::query()
                    ->where('patient_id', $patient->id)
                    ->where('statut', StatutConsultation::PLANIFIER->value)
                    ->count(),
                'nombre_rendez_vous_annules' => RendezVous::query()
                    ->where('patient_id', $patient->id)
                    ->where('statut', StatutConsultation::ANNULER->value)
                    ->count(),
                'nombre_consultations_effectuees' => Consultation::query()
                    ->where('patient_id', $patient->id)
                    ->where('statut', StatutConsultation::FAIT->value)
                    ->count(),
            ],
        ];
    }
}
